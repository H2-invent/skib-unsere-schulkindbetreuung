<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Organisation;
use App\Entity\Schule;
use App\Entity\Zeitblock;
use App\Form\Type\BlockType;
use App\Service\AnmeldeEmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
class BlockController extends AbstractController
{
    private $einkommensgruppen;
    public function __construct(TranslatorInterface $translator)
    {
        $this->einkommensgruppen = array(
            '0 - 1.499 Euro' => 0,
            '1.500 - 2.499 Euro' => 1,
            '2.500 . 3.499 Euro' => 2,
            '3.500 . 5.999 Euro' => 3,
            'über 6.000 Euro' => 4,
        );

    }
    /**
     * @Route("/org_child/show/schule/show", name="block_schulen_schow",methods={"GET"})
     */
    public function showSchulen(Request $request)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('id'));
        $schule = $this->getDoctrine()->getRepository(Schule::class)->findBy(array('organisation'=>$organisation,'deleted'=>false));
        if($organisation != $this->getUser()->getOrganisation()){
            throw new \Exception('Wrong Organisation');
        }
        return $this->render('block/schulen.html.twig',array('schule'=>$schule));
    }
    /**
     * @Route("/org_child/show/schule/block/show", name="block_schule_schow",methods={"GET"})
     */
    public function showBlocks(Request $request)
    {
        $schule = $this->getDoctrine()->getRepository(Schule::class)->find($request->get('id'));
        if($schule->getOrganisation() != $this->getUser()->getOrganisation()){
            throw new \Exception('Wrong Organisation');
        }
        $activity = $this->getDoctrine()->getRepository(Active::class)->findBy(array('stadt'=>$schule->getStadt()),array('bis'=>'desc'));
        $blocks = $this->getDoctrine()->getRepository(Zeitblock::class)->findAll();
        return $this->render('block/blocks.html.twig',array('schuljahre'=>$activity,'schule'=>$schule,'blocks'=>$blocks));
    }
    /**
     * @Route("/org_child/show/schule/block/getBlocks", name="block_schule_getBlocks",methods={"GET"})
     */
    public function getBlocks(Request $request)
    {
        $schule = $this->getDoctrine()->getRepository(Schule::class)->find($request->get('shool'));
        if ($schule->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $activity = $this->getDoctrine()->getRepository(Active::class)->findOneBy(array('id'=>$request->get('id'),'stadt'=>$schule->getStadt()));
        $blocks = $this->getDoctrine()->getRepository(Zeitblock::class)->findBy(array('schule'=>$schule,'active'=>$activity),array('von'=>'asc'));

        $renderBlock = array();
        foreach ($blocks as $data){
            $renderBlock[$data->getWochentag()][]=$data;
        }

        return $this->render('block/blockContent.html.twig',array('blocks'=>$renderBlock,'year'=>$activity,'shool'=>$schule));

    }

    /**
     * @Route("/org_block/schule/block/newBlock", name="block_schule_newBlocks",methods={"GET","POST"})
     */
    public function newBlock(Request $request,ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $activity = $this->getDoctrine()->getRepository(Active::class)->find($request->get('year'));
        $shool = $this->getDoctrine()->getRepository(Schule::class)->find($request->get('shool'));
        if ($shool->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $block = new Zeitblock();
        $block->setSchule($shool);
        $block->setActive($activity);
        $block->setWochentag($request->get('weekday'));
        $block->setPreise(array_fill(0,$shool->getStadt()->getpreiskategorien(), 0));
        $form = $this->createForm(BlockType::class, $block,[
            'action' => $this->generateUrl('block_schule_newBlocks',array('year'=>$activity->getId(),'shool'=>$shool->getId(),'weekday'=>$block->getWochentag())),
            'anzahlPreise'=>$shool->getStadt()->getpreiskategorien()
        ]);

        $form->remove('save');

        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $block = $form->getData();
            $errors = $validator->validate($block);
            //try {
                if (count($errors) == 0) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($block);
                    $em->flush();
                    $text = $translator->trans('Erfolgreich gespeichert');
                    return new JsonResponse(array('error' => 0, 'snack' => $text));
                }
            //}catch (\Exception $e){
            //    $text = $translator->trans('Fehler. Bitte versuchen Sie es erneut.');
            //    return new JsonResponse(array('error' => 1, 'snack' => $text));
          //  }

        }
        return $this->render('block/blockForm.html.twig',array('block'=>$block,'form'=>$form->createView()));

    }
    /**
     * @Route("/org_block/schule/block/deleteBlock", name="block_schule_deleteBlocks",methods={"GET"})
     */
    public function deleteBlock(Request $request,ValidatorInterface $validator, TranslatorInterface $translator,AnmeldeEmailService $anmeldeEmailService)
    {
       $block = $this->getDoctrine()->getRepository(Zeitblock::class)->find($request->get('id'));
        if ($block->getSchule()->getOrganisation() != $this->getUser()->getOrganisation()) {
            $text = $translator->trans('Fehler: Falsche Organisation');
            return new JsonResponse(array('error'=>1,'snack'=>$text));


        }
        $block->setDeleted(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($block);
        $em->flush();
        $kinder = $block->getKindwithFin();
        foreach ($kinder as $data){
            $anmeldeEmailService->sendEmail($data,$data->getEltern(),$block->getSchule()->getStadt(),$this->einkommensgruppen);
        }

        $text = $translator->trans('Erfolgreich gelöscht');
        return new JsonResponse(array('error'=>0,'snack'=>$text));
    }
    /**
     * @Route("/org_block/schule/block/editBlock", name="block_schule_editBlocks",methods={"GET","POST"})
     */
    public function editBlock(Request $request,ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $block = $this->getDoctrine()->getRepository(Zeitblock::class)->find($request->get('id'));
        if ($block->getSchule()->getOrganisation() != $this->getUser()->getOrganisation()) {
            $text = $translator->trans('Fehler: Falsche Organisation');
            return new JsonResponse(array('error'=>1,'snack'=>$text));


        }

        $form = $this->createForm(BlockType::class, $block,[
            'action' => $this->generateUrl('block_schule_editBlocks',array('id'=>$block->getId())),
        ]);

        $form->remove('save');
        $form->remove('preise');
        $form->remove('min');
        $form->remove('max');
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $block = $form->getData();
            $errors = $validator->validate($block);
            try {
                if (count($errors) == 0) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($block);
                    $em->flush();
                    $text = $translator->trans('Erfolgreich gespeichert');
                    return new JsonResponse(array('error' => 0, 'snack' => $text));
                }
            }catch (\Exception $e){
                $text = $translator->trans('Fehler. Bitte versuchen Sie es erneut.');
                return new JsonResponse(array('error' => 1, 'snack' => $text));
            }

        }
        return $this->render('block/blockForm.html.twig',array('block'=>$block,'form'=>$form->createView()));

    }

}
