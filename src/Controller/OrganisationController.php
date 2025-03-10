<?php

namespace App\Controller;

use App\Entity\Organisation;
use App\Entity\Stadt;
use App\Form\Type\OrganisationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrganisationController extends AbstractController
{
    public function __construct(private \Doctrine\Persistence\ManagerRegistry $managerRegistry)
    {
    }
    /**
     * @Route("/city_admin/organisation/show", name="city_admin_organisation_show")
     */
    public function index(Request $request)
    {
        $city = $this->managerRegistry->getRepository(Stadt::class)->findOneBy(array('id' => $request->get('id'), 'deleted' => false));

        if ($city != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->findBy(array('stadt' => $city, 'deleted' => false));

        return $this->render('cityAdminOrganisation/organisationen.html.twig', [
            'organisation' => $organisation,
            'city' => $city
        ]);
    }

    /**
     * @Route("/city_admin/organisation/new", name="city_admin_organisation_new",methods={"GET","POST"})
     */
    public function newOrg(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $city = $this->managerRegistry->getRepository(Stadt::class)->find($request->get('id'));
        if ($city != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $organisation = new Organisation();
        $form = $this->createForm(OrganisationType::class, $organisation);
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $organisation = $form->getData();
            $organisation->setStadt($city);
            $organisation->setSlug($this->friendlyUrl($organisation->getName()));
            $errors = $validator->validate($organisation);
            if (count($errors) == 0) {
                $em = $this->managerRegistry->getManager();
                $em->persist($organisation);
                $em->flush();
                $text = $translator->trans('Erfolgreich angelegt');
                return $this->redirectToRoute('city_admin_organisation_show', array('snack' => $text, 'id' => $city->getId()));
            }

        }
        $title = $translator->trans('Organisation anlegen');
        return $this->render('administrator/neu.html.twig', array('title' => $title, 'stadt' => $city, 'form' => $form->createView(), 'errors' => $errors));

    }

    /**
     * @Route("/org_edit/organisation/edit", name="city_admin_organisation_edit",methods={"GET","POST"})
     */
    public function editOrg(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {

        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('id'));

        if ($organisation->getStadt() != $this->getUser()->getStadt() && $this->getUser()->getOrganisation() != $organisation) {
            throw new \Exception('Wrong City');
        }

        $form = $this->createForm(OrganisationType::class, $organisation);
        if ($organisation->getStadt()->getFerienprogramm() === false) {
            $form->remove('ferienprogramm');
        }
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $organisation = $form->getData();
            $errors = $validator->validate($organisation);
            if (count($errors) == 0) {
                $em = $this->managerRegistry->getManager();
                $em->persist($organisation);
                $em->flush();
                $text = $translator->trans('Erfolgreich geändert');
                return $this->redirectToRoute('city_admin_organisation_detail', array('snack' => $text, 'id' => $organisation->getId()));
            }

        }
        $title = $translator->trans('Organisation anlegen');
        return $this->render('administrator/neu.html.twig', array('title' => $title, 'form' => $form->createView(), 'errors' => $errors));

    }

    /**
     * @Route("/city_admin/organisation/delete", name="city_admin_organisation_delete",methods={"GET"})
     */
    public function deleteSchool(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('id'));
        $city = $organisation->getStadt();
        if ($organisation->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }

        $organisation->setDeleted(true);
        $em = $this->managerRegistry->getManager();
        $em->persist($organisation);
        $em->flush();
        $text = $translator->trans('Erfolgreich gelöscht');
        return $this->redirectToRoute('city_admin_organisation_show', array('snack' => $text, 'id' => $city->getId()));
    }

    /**
     * @Route("/org_edit/organisation/detail", name="city_admin_organisation_detail",methods={"GET"})
     */
    public function detailSchool(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('id'));
        $city = $organisation->getStadt();
        if ($organisation->getStadt() != $this->getUser()->getStadt() && $this->getUser()->getOrganisation() != $organisation) {
            throw new \Exception('Wrong City');
        }
        return $this->render('cityAdminOrganisation/organisationDetail.html.twig', array('stadt' => $city, 'organisation' => $organisation));

    }

    private function friendlyUrl($url)
    {
        // everything to lower and no spaces begin or end
        $url = strtolower(trim($url));
        //replace accent characters, depends your language is needed

        // decode html maybe needed if there's html I normally don't use this

        // adding - for spaces and union characters
        $find = array(' ', '&', '\r\n', '\n', '+', ',');
        $url = str_replace($find, '-', $url);
        //delete and replace rest of special chars
        $find = array('/[^a-z0-9\-<>]/', '/[\-]+/', '/<[^>]*>/');
        $repl = array('', '-', '');
        $url = preg_replace($find, $repl, $url);

        return $url;
    }
}
