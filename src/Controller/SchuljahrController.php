<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Schule;
use App\Entity\Stadt;
use App\Form\Type\SchuljahrType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SchuljahrController extends AbstractController
{
    public function __construct(private \Doctrine\Persistence\ManagerRegistry $managerRegistry, private Security $security)
    {
    }

    /**
     * @Route("city_admin/stadtschuljahr/show", name="city_admin_schuljahr_anzeige")
     */
    public function index(Request $request)
    {
        $stadt = $this->managerRegistry->getRepository(Stadt::class)->find($request->get('id'));
        if ($stadt != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong Organisation');
        }
        $activity = $this->managerRegistry->getRepository(Active::class)->findBy(array('stadt' => $stadt));

        return $this->render('schuljahr/schuljahre.html.twig', [
            'city' => $stadt,
            'schuljahre' => $activity
        ]);
    }

    /**
     * @Route("city_admin/stadtschuljahr/neu", name="city_admin_schuljahr_neu")
     */
    public function neu(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $stadt = $this->managerRegistry->getRepository(Stadt::class)->find($request->get('id'));
        if ($stadt != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong Organisation');
        }
        $activity = new Active();
        $activity->setStadt($stadt);
        $token = $this->security->getToken();
        $impersonatorUser = false;
        if ($token instanceof SwitchUserToken) {
            $impersonatorUser = $token->getOriginalToken()->getUser();
        }
        $form = $this->createForm(SchuljahrType::class, $activity, [
            'user_changed' => $impersonatorUser ? true : false,
            'previous_roles' => $impersonatorUser ? $impersonatorUser->getRoles() : [],
        ]);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $activity = $form->getData();

            $errors = $validator->validate($activity);
            if (count($errors) == 0) {
                $activity->setAnmeldeEnde($activity->getAnmeldeEnde()->setTime(23, 59, 59));
                $em = $this->managerRegistry->getManager();
                $em->persist($activity);
                $em->flush();
                $text = $translator->trans('Erfolgreich angelegt');
                return $this->redirectToRoute('city_admin_schuljahr_anzeige', array('id' => $stadt->getId(), 'snack' => $text));
            }

        }
        $title = $translator->trans('Schuljahr anlegen');
        return $this->render('administrator/neu.html.twig', array('title' => $title, 'form' => $form->createView(), 'errors' => $errors));

    }

    /**
     * @Route("city_admin/stadtschuljahr/edit", name="city_admin_schuljahr_edit")
     */
    public function edit(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $activity = $this->managerRegistry->getRepository(Active::class)->find($request->get('id'));

        if ($activity->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong Organisation');
        }

        $token = $this->security->getToken();
        $impersonatorUser = false;
        if ($token instanceof SwitchUserToken) {
            $impersonatorUser = $token->getOriginalToken()->getUser();
        }
        $form = $this->createForm(SchuljahrType::class, $activity, [
            'user_changed' => $impersonatorUser ? true : false,
            'previous_roles' => $impersonatorUser ? $impersonatorUser->getRoles() : [],
        ]);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $activity = $form->getData();
            $errors = $validator->validate($activity);
            if (count($errors) == 0) {
                $activity->setAnmeldeEnde($activity->getAnmeldeEnde()->setTime(23, 59, 59));
                $em = $this->managerRegistry->getManager();
                $em->persist($activity);
                $em->flush();
                $text = $translator->trans('Erfolgreich geändert');
                return $this->redirectToRoute('city_admin_schuljahr_anzeige', array('id' => $activity->getStadt()->getId(), 'snack' => $text));
            }

        }
        $title = $translator->trans('Schuljahr bearbeiten');
        return $this->render('administrator/neu.html.twig', array('title' => $title, 'form' => $form->createView(), 'errors' => $errors));

    }

    /**
     * @Route("city_admin/stadtschuljahr/delete", name="city_admin_schuljahr_delete")
     */
    public function delete(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $activity = $this->managerRegistry->getRepository(Active::class)->find($request->get('id'));

        if ($activity->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong Organisation');
        }
        $em = $this->managerRegistry->getManager();
        foreach ($activity->getBlocks() as $data) {
            $data->setActive(null);
            $em->persist($data);
        }
        $em->flush();
        $em->remove($activity);
        $em->flush();
        $text = $translator->trans('Erfolgreich gelöscht');
        return $this->redirectToRoute('city_admin_schuljahr_anzeige', array('id' => $activity->getStadt()->getId(), 'snack' => $text));

    }
}
