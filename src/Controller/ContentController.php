<?php

namespace App\Controller;

use App\Entity\Content;
use App\Form\Type\ContentType;
use App\Form\Type\OrganisationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContentController extends AbstractController
{
    /**
     * @Route("/admin/content/show", name="content_show")
     */
    public function index()
    {
        $content = $this->getDoctrine()->getRepository(Content::class)->findAll();

        return $this->render('content/index.html.twig', [
            'content' => $content,
        ]);
    }

    /**
     * @Route("/admin/content/new", name="content_new")
     */
    public function newcontent(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $content = new Content();

        $content->setCreatedAt(new \DateTime());
        $content->setDate(new \DateTime());
        $form = $this->createForm(ContentType::class, $content);
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $content = $form->getData();
            $errors = $validator->validate($content);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($content);
                $em->flush();
                $text = $translator->trans('Erfolgreich angelegt');
                return $this->redirectToRoute('content_show', array('snack' => $text));
            }
        }
        $title = $translator->trans('Content anlegen');
        return $this->render('administrator/neu.html.twig', array('title' => $title, 'form' => $form->createView(), 'errors' => $errors));

    }
    /**
     * @Route("/admin/content/edit", name="content_edit")
     */
    public function editcontent(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $content =$this->getDoctrine()->getRepository(Content::class)->find($request->get('content_id'));
        $content->setDate(new \DateTime());

        $form = $this->createForm(ContentType::class, $content);
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $content = $form->getData();
            $errors = $validator->validate($content);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($content);
                $em->flush();
                $text = $translator->trans('Erfolgreich geändert');
                return $this->redirectToRoute('content_edit', array('content_id'=>$content->getId(),'snack' => $text));
            }
        }
        $title = $translator->trans('Content bearbeiten');
        return $this->render('administrator/neu.html.twig', array('title' => $title, 'form' => $form->createView(), 'errors' => $errors));
    }
    /**
     * @Route("/admin/content/activate", name="content_activate")
     */
    public function activatecontent(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $content =$this->getDoctrine()->getRepository(Content::class)->find($request->get('content_id'));
        $content->setDate(new \DateTime());
        if($content->getActiv()){
            $content->setActiv(false);
        }else{
            $content->setActiv(true);
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($content);
        $em->flush();
        $text = $translator->trans('Erfolgreich geändert');
        return $this->redirectToRoute('content_show', array('snack' => $text));
    }
    /**
     * @Route("/admin/content/delete", name="content_delete")
     */
    public function deletecontent(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $content =$this->getDoctrine()->getRepository(Content::class)->find($request->get('content_id'));

        $em = $this->getDoctrine()->getManager();
        $em->remove($content);
        $em->flush();
        $text = $translator->trans('Erfolgreich gelöscht');
        return $this->redirectToRoute('content_show', array('snack' => $text));
    }
}

