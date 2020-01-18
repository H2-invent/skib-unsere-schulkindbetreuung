<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\News;
use App\Entity\Organisation;
use App\Entity\Schule;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Form\Type\LoerrachKind;
use App\Form\Type\NewsType;
use App\Form\Type\SchuljahrType;
use App\Service\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Doctrine\ORM\QueryBuilder;

class NewsController extends AbstractController
{
    /**
     * @Route("city_news/show", name="city_admin_news_anzeige")
     */
    public function index(Request $request)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('id'));
        if ($stadt != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $activity = $this->getDoctrine()->getRepository(News::class)->findBy(array('stadt' => $stadt));

        return $this->render('news/news.html.twig', [
            'city' => $stadt,
            'news' => $activity
        ]);
    }

    /**
     * @Route("city_news/neu", name="city_admin_news_neu")
     */
    public function neu(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('id'));
        if ($stadt != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $activity = new News();
        $activity->setStadt($stadt);
        $today = new \DateTime();
        $activity->setCreatedDate($today);
        $schulen = $stadt->getSchules();
        $form = $this->createForm(NewsType::class, $activity, array('schulen' => $schulen));
        $form->remove('schulen');
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $news = $form->getData();
            $errors = $validator->validate($news);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($news);
                $em->flush();
                $text = $translator->trans('Erfolgreich angelegt');
                return $this->redirectToRoute('city_admin_news_anzeige', array('id' => $stadt->getId(), 'snack' => $text));
            }

        }
        $title = $translator->trans('Neuigkeit erstellt');
        return $this->render('administrator/neu.html.twig', array('title' => $title, 'form' => $form->createView(), 'errors' => $errors));

    }

    /**
     * @Route("city_news/edit", name="city_admin_news_edit")
     */
    public function edit(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $activity = $this->getDoctrine()->getRepository(News::class)->find($request->get('id'));

        if ($activity->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }

        $today = new \DateTime();
        $activity->setDate($today);
        $schulen = $activity->getStadt()->getSchules();
        $form = $this->createForm(NewsType::class, $activity, array('schulen' => $schulen));
        $form->remove('schulen');
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $news = $form->getData();
            $errors = $validator->validate($news);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($news);
                $em->flush();
                $text = $translator->trans('Erfolgreich geändert');
                return $this->redirectToRoute('city_admin_news_anzeige', array('id' => $activity->getStadt()->getId(), 'snack' => $text));
            }

        }
        $title = $translator->trans('Neuigkeit bearbeiten');
        return $this->render('administrator/neu.html.twig', array('title' => $title, 'form' => $form->createView(), 'errors' => $errors));

    }

    /**
     * @Route("city_news/delete", name="city_admin_news_delete")
     */
    public function delete(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $activity = $this->getDoctrine()->getRepository(News::class)->find($request->get('id'));

        if ($activity->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($activity);
        $em->flush();
        $text = $translator->trans('Erfolgreich gelöscht');
        return $this->redirectToRoute('city_admin_news_anzeige', array('id' => $activity->getStadt()->getId(), 'snack' => $text));
    }

    /**
     * @Route("city_news/deactivate", name="city_admin_news_deactivate")
     */
    public function deactivateAction(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $news = $this->getDoctrine()->getRepository(News::class)->find($request->get('id'));

        if ($news->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $news->setActiv(false);
        $em = $this->getDoctrine()->getManager();
        $em->persist($news);
        $em->flush();
        $text = $translator->trans('Erfolgreich deaktiviert');
        return $this->redirectToRoute('city_admin_news_anzeige', array('id' => $news->getStadt()->getId(), 'snack' => $text));
    }

    /**
     * @Route("city_news/activate", name="city_admin_news_activate")
     */
    public function activateAction(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $news = $this->getDoctrine()->getRepository(News::class)->find($request->get('id'));

        if ($news->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $today = new \DateTime();
        $news->setActiv(true);
        $news->setDate($today);

        $em = $this->getDoctrine()->getManager();
        $em->persist($news);
        $em->flush();
        $text = $translator->trans('Erfolgreich aktiviert');
        return $this->redirectToRoute('city_admin_news_anzeige', array('id' => $news->getStadt()->getId(), 'snack' => $text));
    }


    /**
     * @Route("org_news/show", name="org_news_anzeige", methods={"GET"})
     */
    public function orgIndex(Request $request)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('id'));
        $stadt = $organisation->getStadt();
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $activity = $this->getDoctrine()->getRepository(News::class)->findBy(array('organisation' => $organisation));

        $new = $this->generateUrl('org_news_neu', array('id' => $organisation->getId()));
        return $this->render('news/orgNews.html.twig', [
            'org' => $organisation,
            'news' => $activity,
            'link' => $new,
        ]);
    }


    /**
     * @Route("org_news/neu", name="org_news_neu", methods={"GET","POST"})
     */
    public function orgNewsNeu(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $organisation = $this->getDoctrine()->getRepository(Organisation::class)->find($request->get('id'));
        $stadt = $organisation->getStadt();
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

        $activity = new News();
        $activity->setOrganisation($organisation);
        $today = new \DateTime();
        $activity->setCreatedDate($today);
        $schulen = $this->getDoctrine()->getRepository(Schule::class)->findBy(array('organisation' => $organisation));
        $form = $this->createForm(NewsType::class, $activity, array('schulen' => $schulen));
        $form->remove('activ');
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $news = $form->getData();
            $news->setActiv(false);
            $errors = $validator->validate($news);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($news);
                $em->flush();
                $text = $translator->trans('Erfolgreich angelegt');
                return $this->redirectToRoute('org_news_anzeige', array('id' => $organisation->getId(), 'snack' => $text));
            }

        }
        $title = $translator->trans('Ranzenpost erstellen');
        return $this->render('administrator/neu.html.twig', array('title' => $title, 'form' => $form->createView(), 'errors' => $errors));

    }


    /**
     * @Route("org_news/edit", name="org_news_edit", methods={"GET","POST"})
     */
    public function orgNewsEdit(Request $request, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $activity = $this->getDoctrine()->getRepository(News::class)->find($request->get('id'));

        if ($activity->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

        $today = new \DateTime();
        $activity->setDate($today);
        $schulen = $this->getDoctrine()->getRepository(Schule::class)->findBy(array('organisation' => $activity->getOrganisation()));
        $form = $this->createForm(NewsType::class, $activity, array('schulen' => $schulen));
        $form->remove('activ');
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $news = $form->getData();
            $news->setActiv(false);
            $errors = $validator->validate($news);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($news);
                $em->flush();
                $text = $translator->trans('Erfolgreich geändert');
                return $this->redirectToRoute('org_news_anzeige', array('id' => $activity->getOrganisation()->getId(), 'snack' => $text));
            }

        }
        $title = $translator->trans('Ranzenpost bearbeiten');
        return $this->render('administrator/neu.html.twig', array('title' => $title, 'form' => $form->createView(), 'errors' => $errors));

    }

    /**
     * @Route("org_news/delete", name="org_news_delete",methods={"GET","POST"})
     */
    public function orgNewsDelete(Request $request, TranslatorInterface $translator)
    {
        $activity = $this->getDoctrine()->getRepository(News::class)->find($request->get('id'));

        if ($activity->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($activity);
        $em->flush();
        $text = $translator->trans('Erfolgreich gelöscht');
        return $this->redirectToRoute('org_news_anzeige', array('id' => $activity->getOrganisation()->getId(), 'snack' => $text));
    }

    /**
     * @Route("org_news/deactivate", name="org_news_deactivate", methods={"GET","POST"})
     */
    public function orgNewsDeactivate(Request $request, TranslatorInterface $translator)
    {
        $news = $this->getDoctrine()->getRepository(News::class)->find($request->get('id'));

        if ($news->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $news->setActiv(false);
        $em = $this->getDoctrine()->getManager();
        $em->persist($news);
        $em->flush();
        $text = $translator->trans('Erfolgreich deaktiviert');
        return $this->redirectToRoute('org_news_anzeige', array('id' => $news->getOrganisation()->getId(), 'snack' => $text));
    }

    /**
     * @Route("org_news/activate", name="org_news_activate", methods={"GET","POST"})
     */
    public function orgNewsActivate(Request $request, TranslatorInterface $translator)
    {
        $news = $this->getDoctrine()->getRepository(News::class)->find($request->get('id'));

        if ($news->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

        $today = new \DateTime();
        $news->setActiv(true);
        $news->setDate($today);

        $em = $this->getDoctrine()->getManager();
        $em->persist($news);
        $em->flush();
        $text = $translator->trans('Erfolgreich aktiviert');
        return $this->redirectToRoute('org_news_anzeige', array('id' => $news->getOrganisation()->getId(), 'snack' => $text));
    }

    /**
     * @Route("/news/city/{slug}",name="news_show_page",methods={"GET"})
     */
    public function newsPageAction($slug, Request $request)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->findOneBy(array('slug' => $slug));
        $news = $this->getDoctrine()->getRepository(News::class)->findBy(array('stadt' => $stadt, 'activ' => true));
        return $this->render('news/newsPage.twig', array('stadt' => $stadt, 'news' => $news));

    }


    /**
     * @Route("/news/city/{slug}/{id}",name="news_show_all",methods={"GET"})
     */
    public function showNewsAction(Request $request)
    {
        $stadt = $this->getDoctrine()->getRepository(Stadt::class)->find($request->get('slug'));
        $news = $this->getDoctrine()->getRepository(News::class)->find($request->get('id'));
        if ($request->isXmlHttpRequest()) {
            return $this->render('news/showNews.html.twig', array('stadt' => $stadt, 'news' => $news));
        } else {
            return $this->render('news/showNewsPage.twig', array('stadt' => $stadt, 'news' => $news));
        }
    }


    /**
     * @Route("org_news/send", name="org_news_send", methods={"GET"})
     */
    public function orgNewsSendAction(Request $request, TranslatorInterface $translator, MailerService $mailerService)
    {
        $news = $this->getDoctrine()->getRepository(News::class)->find($request->get('id'));

        if ($news->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $today = new \DateTime();

        if ($news->getSchule()->isEmpty()) {
            $text = $translator->trans('Nachricht konnte nicht versendet werden');
            return $this->redirectToRoute('org_news_anzeige', array('id' => $news->getOrganisation()->getId(), 'snack' => $text));
        }

        $stammdaten = $this->getStammdatenFromNEws($news,$today);

        $mailContent = $this->renderView('email/news.html.twig', array('sender' => $news->getOrganisation(), 'news' => $news, 'stammdaten' => $stammdaten));
        foreach ($stammdaten as $data) {
            $mailerService->sendEmail('Ranzenpost', $news->getOrganisation()->getEmail(), $data->getEmail(), $news->getTitle(), $mailContent);
        }

        $text = $translator->trans('Nachricht versendet');
        return $this->redirectToRoute('org_news_anzeige', array('id' => $news->getOrganisation()->getId(), 'snack' => $text));
    }


    /**
     * @Route("city_news/send", name="city_news_send", methods={"GET"})
     */
    public function cityNewsSendAction(Request $request, TranslatorInterface $translator, MailerService $mailerService)
    {
        $news = $this->getDoctrine()->getRepository(News::class)->find($request->get('id'));

        if ($news->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $today = new \DateTime();

        if ($news->getSchule()->isEmpty()) {
            $text = $translator->trans('Nachricht konnte nicht versendet werden');
            return $this->redirectToRoute('city_admin_news_anzeige', array('id' => $news->getStadt()->getId(), 'snack' => $text));
        }
        $stammdaten = $this->getStammdatenFromNEws($news,$today);

        dump($stammdaten);
        return 0;
        $mailContent = $this->renderView('email/news.html.twig', array('sender' => $news->getStadt(), 'news' => $news, 'stammdaten' => $stammdaten));
        foreach ($stammdaten as $data) {
            $mailerService->sendEmail('Ranzenpost', $news->getStadt()->getEmail(), $data->getEmail(), $news->getTitle(), $mailContent);
        }

        $text = $translator->trans('Nachricht versendet');
        return $this->redirectToRoute('city_admin_news_anzeige', array('id' => $news->getStadt()->getId(), 'snack' => $text));
    }


    /**
     * @Route("/news/email_online/id",name="org_email_news_show_online",methods={"GET"})
     */
    public function orgShowNewsAction(Request $request)
    {
        $news = $this->getDoctrine()->getRepository(News::class)->find($request->get('id'));

        if ($news->getOrganisation()) {
            $stadt = $news->getOrganisation()->getStadt();
            return $this->render('email/news.html.twig', array('stadt' => $stadt, 'sender' => $news->getOrganisation(), 'news' => $news));
        } else {
            return $this->render('email/news.html.twig', array('stadt' => $news->getStadt(), 'sender' => $news->getStadt(), 'news' => $news));

        }
    }

    private function getStammdatenFromNEws(News $news, \DateTime $today){
        $qb = $this->getDoctrine()->getRepository(Stammdaten::class)->createQueryBuilder('stammdaten');

        $qb->innerJoin('stammdaten.kinds', 'kinds')
            ->innerJoin('kinds.zeitblocks', 'kind_zeitblocks')
            ->innerJoin('kind_zeitblocks.active', 'active');
        $count = 0;

        foreach ($news->getSchule() as $schule) {
            $qb->orWhere('kind_zeitblocks.schule = :schule' . $count)
                ->setParameter('schule' . $count, $schule);
            $count++;
        }
        $qb->andWhere('stammdaten.fin = 1')
            ->andWhere('stammdaten.saved = 1')
            ->andWhere('active.bis >= :today')
            ->andWhere('active.von <= :today')
            ->setParameter('today', $today);

        $query = $qb->getQuery();
        $stammdaten = $query->getResult();
        return $stammdaten;
    }
}
