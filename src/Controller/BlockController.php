<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Organisation;
use App\Entity\Schule;
use App\Entity\Zeitblock;
use App\Form\Type\BlockAbhangigkeitType;
use App\Form\Type\BlockType;
use App\Service\AnmeldeEmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class BlockController extends AbstractController
{

    public function __construct(TranslatorInterface $translator, private \Doctrine\Persistence\ManagerRegistry $managerRegistry)
    {


    }

    /**
     * @Route("/org_child/show/schule/show", name="block_schulen_schow",methods={"GET"})
     */
    public function showSchulen(Request $request)
    {
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('id'));
        $schule = $this->managerRegistry->getRepository(Schule::class)->findBy(array('organisation' => $organisation, 'deleted' => false));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        return $this->render('block/schulen.html.twig', array('schule' => $schule));
    }

    /**
     * @Route("/org_child/show/schule/block/show", name="block_schule_schow",methods={"GET"})
     */
    public function showBlocks(Request $request)
    {
        $schule = $this->managerRegistry->getRepository(Schule::class)->find($request->get('id'));
        if ($schule->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $activity = $this->managerRegistry->getRepository(Active::class)->findBy(array('stadt' => $schule->getStadt()), array('bis' => 'desc'));
        $blocks = $this->managerRegistry->getRepository(Zeitblock::class)->findAll();
        return $this->render('block/blocks.html.twig', array('schuljahre' => $activity, 'schule' => $schule, 'blocks' => $blocks));
    }

    /**
     * @Route("/org_child/show/schule/block/getBlocks", name="block_schule_getBlocks",methods={"GET"})
     */
    public function getBlocks(Request $request)
    {
        $schule = $this->managerRegistry->getRepository(Schule::class)->find($request->get('shool'));
        if ($schule->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $activity = $this->managerRegistry->getRepository(Active::class)->findOneBy(array('id' => $request->get('id'), 'stadt' => $schule->getStadt()));
        $blocks = $this->managerRegistry->getRepository(Zeitblock::class)->findBy(array('schule' => $schule, 'active' => $activity), array('von' => 'asc'));

        $renderBlock = array();
        foreach ($blocks as $data) {
            $renderBlock[$data->getWochentag()][] = $data;
        }

        return $this->render('block/blockContent.html.twig', array('blocks' => $renderBlock, 'year' => $activity, 'shool' => $schule));

    }

    /**
     * @Route("/org_block/schule/block/newBlock", name="block_schule_newBlocks",methods={"GET","POST"})
     */
    public function newBlock(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $activity = $this->managerRegistry->getRepository(Active::class)->find($request->get('year'));
        $shool = $this->managerRegistry->getRepository(Schule::class)->find($request->get('shool'));
        if ($shool->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $block = new Zeitblock();
        $block->setSchule($shool);
        $block->setActive($activity);
        $block->setWochentag($request->get('weekday'));
        $block->setPreise(array_fill(0, $shool->getStadt()->getpreiskategorien(), 0));
        $form = $this->createForm(BlockType::class, $block, [
            'action' => $this->generateUrl('block_schule_newBlocks', array('year' => $activity->getId(), 'shool' => $shool->getId(), 'weekday' => $block->getWochentag())),
            'anzahlPreise' => $shool->getStadt()->getpreiskategorien()
        ]);

        $form->remove('save');

        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $block = $form->getData();
            $errors = $validator->validate($block);

            if (count($errors) == 0) {
                $em = $this->managerRegistry->getManager();
                $em->persist($block);
                $em->flush();
                $text = $translator->trans('Erfolgreich gespeichert');
                return new JsonResponse(array('error' => 0, 'snack' => $text));
            }


        }
        return $this->render('block/blockForm.html.twig', array('block' => $block, 'form' => $form->createView()));

    }


    /**
     * @Route("/org_block/schule/block/editBlock", name="block_schule_editBlocks",methods={"GET","POST"})
     */
    public function editBlock(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $block = $this->managerRegistry->getRepository(Zeitblock::class)->find($request->get('id'));
        if ($block->getSchule()->getOrganisation() != $this->getUser()->getOrganisation()) {
            $text = $translator->trans('Fehler: Falsche Organisation');
            return new JsonResponse(array('error' => 1, 'snack' => $text));


        }

        $form = $this->createForm(BlockType::class, $block, [
            'action' => $this->generateUrl('block_schule_editBlocks', array('id' => $block->getId())),
        ]);
        $form->remove('ganztag')
            ->remove('save')
            ->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $block = $form->getData();
            $errors = $validator->validate($block);
            try {
                if (count($errors) == 0) {
                    $em = $this->managerRegistry->getManager();
                    $em->persist($block);
                    $em->flush();
                    $text = $translator->trans('Erfolgreich gespeichert');
                    return new JsonResponse(array('error' => 0, 'snack' => $text));
                }
            } catch (\Exception $e) {
                $text = $translator->trans('Fehler. Bitte versuchen Sie es erneut.');
                return new JsonResponse(array('error' => 1, 'snack' => $text));
            }

        }
        return $this->render('block/blockForm.html.twig', array('block' => $block, 'form' => $form->createView()));

    }

    /**
     * @Route("/org_block/schule/block/linkBlock", name="block_schule_linkBlock",methods={"GET","POST"})
     */
    public function linkBlock(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $block = $this->managerRegistry->getRepository(Zeitblock::class)->find($request->get('id'));
        if ($block->getSchule()->getOrganisation() != $this->getUser()->getOrganisation()) {
            $text = $translator->trans('Fehler: Falsche Organisation');
            return new JsonResponse(array('error' => 1, 'snack' => $text));
        }
        $blocks1 = $this->managerRegistry->getRepository(Zeitblock::class)->findBy(
            array('schule' => $block->getSchule(),
                'ganztag' => $block->getGanztag(),
                'active' => $block->getActive(), 'deleted' => false), array('von' => 'ASC'),);
        $blocks = array();
        foreach ($blocks1 as $data) {
            if ($data != $block) {
                $blocks[] = $data;
            }
        }
        $form = $this->createForm(BlockAbhangigkeitType::class, $block, [
            'action' => $this->generateUrl('block_schule_linkBlock', array('id' => $block->getId())),
            'blocks' => $blocks
        ]);

        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $block = $form->getData();
            $errors = $validator->validate($block);
            try {
                if (count($errors) == 0) {
                    $em = $this->managerRegistry->getManager();
                    $em->persist($block);
                    $em->flush();
                    $text = $translator->trans('Erfolgreich gespeichert');
                    return new JsonResponse(array('error' => 0, 'snack' => $text));
                }
            } catch (\Exception $e) {
                $text = $translator->trans('Fehler. Bitte versuchen Sie es erneut.');
                return new JsonResponse(array('error' => 1, 'snack' => $text));
            }

        }
        return $this->render('block/blockLinkForm.html.twig', array('block' => $block, 'form' => $form->createView()));
    }

    /**
     * @Route("/org_block/schule/block/linkBlockSilent", name="block_schule_linkBlockSilent",methods={"GET","POST"})
     */
    public function linkBlockSilent(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $block = $this->managerRegistry->getRepository(Zeitblock::class)->find($request->get('id'));
        if ($block->getSchule()->getOrganisation() !== $this->getUser()->getOrganisation()) {
            $text = $translator->trans('Fehler: Falsche Organisation');
            return new JsonResponse(['error' => 1, 'snack' => $text]);
        }
        $blocks1 = $this->managerRegistry->getRepository(Zeitblock::class)->findBy([
            'schule' => $block->getSchule(),
            'ganztag' => $block->getGanztag(),
            'active' => $block->getActive(),
            'deleted' => false
        ],
            ['von' => 'ASC']
        );
        $blocks = [];
        foreach ($blocks1 as $data) {
            if ($data !== $block) {
                $blocks[] = $data;
            }
        }
        $form = $this->createForm(BlockAbhangigkeitType::class, $block, [
            'action' => $this->generateUrl('block_schule_linkBlockSilent', ['id' => $block->getId()]),
            'blocks' => $blocks,
            'silent' => true,
        ]);

        $form->handleRequest($request);

        $errors = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $block = $form->getData();
            $errors = $validator->validate($block);
            try {
                if (count($errors) === 0) {
                    $em = $this->managerRegistry->getManager();
                    $em->persist($block);
                    $em->flush();
                    $text = $translator->trans('Erfolgreich gespeichert');
                    return new JsonResponse(['error' => 0, 'snack' => $text]);
                }
            } catch (\Exception $e) {
                $text = $translator->trans('Fehler. Bitte versuchen Sie es erneut.');
                return new JsonResponse(['error' => 1, 'snack' => $text]);
            }

        }
        return $this->render('block/blockLinkForm.html.twig', ['block' => $block, 'form' => $form->createView(), 'silent' => true]);
    }

    /**
     * @Route("/org_block/schule/block/linkBlock/remove", name="block_schule_linkBlock_remove",methods={"DELETE"})
     */
    public function removeLinkBlock(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $block = $this->managerRegistry->getRepository(Zeitblock::class)->find($request->get('block_id'));
        if ($block->getSchule()->getOrganisation() !== $this->getUser()->getOrganisation()) {
            $text = $translator->trans('Fehler: Falsche Organisation');
            return new JsonResponse(array('error' => 1, 'snack' => $text));
        }
        foreach ($block->getVorganger() as $data) {
            $block->removeVorganger($data);
        }
        $em = $this->managerRegistry->getManager();
        $em->persist($block);
        $em->flush();
        return new JsonResponse(array('redirect' => $this->generateUrl('block_schule_schow', array('id' => $block->getSchule()->getId()))));
    }

    /**
     * @Route("/org_block/schule/block/linkBlockSilent/remove", name="block_schule_linkBlockSilent_remove",methods={"DELETE"})
     */
    public function removeLinkBlockSilent(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $block = $this->managerRegistry->getRepository(Zeitblock::class)->find($request->get('block_id'));
        if ($block->getSchule()->getOrganisation() !== $this->getUser()->getOrganisation()) {
            $text = $translator->trans('Fehler: Falsche Organisation');
            return new JsonResponse(['error' => 1, 'snack' => $text]);
        }
        foreach ($block->getVorgangerSilent() as $data) {
            $block->removeVorgangerSilent($data);
        }
        $em = $this->managerRegistry->getManager();
        $em->persist($block);
        $em->flush();
        return new JsonResponse(['redirect' => $this->generateUrl('block_schule_schow', ['id' => $block->getSchule()->getId()])]);
    }

    /**
     * @Route("/org_block/schule/block/duplicate", name="block_schule_duplicateBlocks",methods={"GET","POST"})
     */
    public function duplicateBlock(Request $request, TranslatorInterface $translator)
    {
        $block = $this->managerRegistry->getRepository(Zeitblock::class)->find($request->get('id'));
        if (!$block) {
            return new JsonResponse(array('error' => 1, 'snack' => $translator->trans('Block nicht gefunden.')));
        }

        if ($block->getSchule()->getOrganisation() != $this->getUser()->getOrganisation()) {
            $text = $translator->trans('Fehler: Falsche Organisation');
            return new JsonResponse(array('error' => 1, 'snack' => $text));
        }

        $this->denyAccessUnlessGranted('ROLE_ORG_BLOCK_MANAGEMENT');

        if ($request->isMethod('POST')) {
            $weekdays = array_unique(array_map('intval', $request->request->all('weekdays')));
            $weekdays = array_values(array_filter($weekdays, static function (int $weekday): bool {
                return $weekday >= 0 && $weekday <= 4;
            }));

            if (count($weekdays) === 0) {
                return new JsonResponse(array('error' => 1, 'snack' => $translator->trans('Bitte mindestens einen Tag auswählen.')));
            }

            $em = $this->managerRegistry->getManager();
            foreach ($weekdays as $weekday) {
                $newBlock = new Zeitblock();
                $newBlock->setSchule($block->getSchule());
                $newBlock->setActive($block->getActive());
                $newBlock->setWochentag($weekday);
                $newBlock->setVon(new \DateTime($block->getVon()->format('H:i:s')));
                $newBlock->setBis(new \DateTime($block->getBis()->format('H:i:s')));
                $newBlock->setGanztag($block->getGanztag());
                $newBlock->setPreise($block->getPreise());
                $newBlock->setMin($block->getMin());
                $newBlock->setMax($block->getMax());
                $newBlock->setDeaktiviert($block->getDeaktiviert());
                $newBlock->setDirektbuchungDeaktiviert($block->getDirektbuchungDeaktiviert());
                if ($block->getHidePrice() !== null) {
                    $newBlock->setHidePrice($block->getHidePrice());
                }
                $newBlock->setCloneOf($block);

                foreach (['de', 'en', 'fr'] as $locale) {
                    $newBlock->translate($locale)->setExtraText($block->translate($locale)->getExtraText());
                    $newBlock->translate($locale)->setBlockbezeichnung($block->translate($locale)->getBlockbezeichnung());
                }
                $newBlock->mergeNewTranslations();
                $em->persist($newBlock);
            }
            $em->flush();

            return new JsonResponse(array('error' => 0, 'snack' => $translator->trans('Block erfolgreich dupliziert.')));
        }

        return $this->render('block/blockDuplicateForm.html.twig', [
            'block' => $block,
            'weekdayLabels' => [
                0 => 'Montag',
                1 => 'Dienstag',
                2 => 'Mittwoch',
                3 => 'Donnerstag',
                4 => 'Freitag',
            ]
        ]);
    }
}
