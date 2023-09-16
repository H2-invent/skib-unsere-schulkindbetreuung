<?php

namespace App\Controller;

use App\Entity\Active;
use App\Entity\News;
use App\Entity\Organisation;
use App\Entity\Schule;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Form\Type\NewsType;
use App\Service\ElternService;
use App\Service\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Doctrine\ORM\QueryBuilder;

class NewsController extends AbstractController
{
    public function __construct(private \Doctrine\Persistence\ManagerRegistry $managerRegistry)
    {
    }
    /**
     * @Route("city_news/show", name="city_admin_news_anzeige")
     */
    public function index(Request $request)
    {
        $stadt = $this->managerRegistry->getRepository(Stadt::class)->find($request->get('id'));
        if ($stadt != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $activity = $this->managerRegistry->getRepository(News::class)->findBy(array('stadt' => $stadt));

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
        $stadt = $this->managerRegistry->getRepository(Stadt::class)->find($request->get('id'));
        if ($stadt != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $activity = new News();
        $activity->setStadt($stadt);
        $today = new \DateTime();
        $activity->setCreatedDate($today);
        $activity->setDate($today);
        $schulen = $stadt->getSchules();
        $schuljahre = $this->managerRegistry->getRepository(Active::class)->findFutureSchuljahreByCity($stadt);
        $form = $this->createForm(NewsType::class, $activity, array('schulen' => $schulen, 'schuljahre' => $schuljahre));
        $form->remove('schulen');
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $news = $form->getData();
            $errors = $validator->validate($news);
            if (count($errors) == 0) {
                $em = $this->managerRegistry->getManager();
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
        $activity = $this->managerRegistry->getRepository(News::class)->find($request->get('id'));

        if ($activity->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }

        $today = new \DateTime();
        $activity->setDate($today);
        $schulen = $activity->getStadt()->getSchules();
        $schuljahre = $this->managerRegistry->getRepository(Active::class)->findFutureSchuljahreByCity($activity->getStadt());
        $form = $this->createForm(NewsType::class, $activity, array('schulen' => $schulen, 'schuljahre' => $schuljahre));
        $form->remove('schulen');
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $news = $form->getData();
            $errors = $validator->validate($news);
            if (count($errors) == 0) {
                $em = $this->managerRegistry->getManager();
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
        $activity = $this->managerRegistry->getRepository(News::class)->find($request->get('id'));

        if ($activity->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $em = $this->managerRegistry->getManager();
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
        $news = $this->managerRegistry->getRepository(News::class)->find($request->get('id'));

        if ($news->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $news->setActiv(false);
        $em = $this->managerRegistry->getManager();
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
        $news = $this->managerRegistry->getRepository(News::class)->find($request->get('id'));

        if ($news->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        $today = new \DateTime();
        $news->setActiv(true);
        $news->setDate($today);

        $em = $this->managerRegistry->getManager();
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
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $activity = $this->managerRegistry->getRepository(News::class)->findBy(array('organisation' => $organisation));

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
        $organisation = $this->managerRegistry->getRepository(Organisation::class)->find($request->get('id'));
        if ($organisation != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

        $activity = new News();
        $activity->setOrganisation($organisation);
        $today = new \DateTime();
        $activity->setCreatedDate($today);
        $schulen = $this->managerRegistry->getRepository(Schule::class)->findBy(array('organisation' => $organisation));
        $schuljahre = $this->managerRegistry->getRepository(Active::class)->findFutureSchuljahreByCity($organisation->getStadt());
        $form = $this->createForm(NewsType::class, $activity, array('schulen' => $schulen, 'schuljahre' => $schuljahre));
        $form->remove('activ');
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $news = $form->getData();
            $news->setActiv(false);
            $errors = $validator->validate($news);
            if (count($errors) == 0) {
                $em = $this->managerRegistry->getManager();
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
        $activity = $this->managerRegistry->getRepository(News::class)->find($request->get('id'));

        if ($activity->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

        $today = new \DateTime();
        $activity->setDate($today);
        $schulen = $this->managerRegistry->getRepository(Schule::class)->findBy(array('organisation' => $activity->getOrganisation()));
        $schuljahre = $this->managerRegistry->getRepository(Active::class)->findFutureSchuljahreByCity($activity->getOrganisation()->getStadt());
        $form = $this->createForm(NewsType::class, $activity, array('schulen' => $schulen, 'schuljahre' => $schuljahre));
        $form->remove('activ');
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $news = $form->getData();
            $news->setActiv(false);
            $errors = $validator->validate($news);
            if (count($errors) == 0) {
                $em = $this->managerRegistry->getManager();
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
        $activity = $this->managerRegistry->getRepository(News::class)->find($request->get('id'));

        if ($activity->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $em = $this->managerRegistry->getManager();
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
        $news = $this->managerRegistry->getRepository(News::class)->find($request->get('id'));

        if ($news->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $news->setActiv(false);
        $em = $this->managerRegistry->getManager();
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
        $news = $this->managerRegistry->getRepository(News::class)->find($request->get('id'));

        if ($news->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }

        $today = new \DateTime();
        $news->setActiv(true);
        $news->setDate($today);

        $em = $this->managerRegistry->getManager();
        $em->persist($news);
        $em->flush();
        $text = $translator->trans('Erfolgreich aktiviert');
        return $this->redirectToRoute('org_news_anzeige', array('id' => $news->getOrganisation()->getId(), 'snack' => $text));
    }

    /**
     * @Route("/news/city/{slug}",name="news_show_page",methods={"GET"})
     */
    public function newsPageAction($slug, Request $request, TranslatorInterface $translator)
    {
        $stadt = $this->managerRegistry->getRepository(Stadt::class)->findOneBy(array('slug' => $slug));
        $news = $this->managerRegistry->getRepository(News::class)->findBy(array('stadt' => $stadt, 'activ' => true), array('date' => 'DESC'));

        $title = $translator->trans('Alle Neuigkeiten der Stadt') . ' ' . $stadt->getName() . ' | ' . $stadt->getName();

        return $this->render('news/newsPage.html.twig', array('title' => $title, 'stadt' => $stadt, 'news' => $news));


    }


    /**
     * @Route("/news/city/{slug}/{id}",name="news_show_all",methods={"GET"})
     */
    public function showNewsAction(Request $request, TranslatorInterface $translator)
    {
        $stadt = $this->managerRegistry->getRepository(Stadt::class)->findOneBy(array('slug' => $request->get('slug')));
        $news = $this->managerRegistry->getRepository(News::class)->find($request->get('id'));
        if (!$news){
            throw new NotFoundHttpException('News not found');
        }
        $title = $news->getTitle() . ' | ' . $news->getStadt()->getName();
        $metaDescription = $news->getMessage();

        if ($request->isXmlHttpRequest()) {
            return $this->render('news/showNews.html.twig', array('stadt' => $stadt, 'news' => $news));
        } else {

            return $this->render('news/showNewsPage.html.twig', array('title' => $title, 'metaDescription' => $metaDescription, 'stadt' => $stadt, 'news' => $news));

        }

    }


    /**
     * @Route("org_news/send", name="org_news_send", methods={"GET"})
     */
    public function orgNewsSendAction(Request $request, TranslatorInterface $translator, MailerService $mailerService, ElternService $elternService)
    {
        $news = $this->managerRegistry->getRepository(News::class)->find($request->get('id'));

        if ($news->getOrganisation() != $this->getUser()->getOrganisation()) {
            throw new \Exception('Wrong Organisation');
        }
        $today = new \DateTime();

        if ($news->getSchule()->isEmpty()) {
            $text = $translator->trans('Nachricht konnte nicht versendet werden');
            return $this->redirectToRoute('org_news_anzeige', array('id' => $news->getOrganisation()->getId(), 'snack' => $text));
        }

        $stammdaten = $this->getStammdatenFromNEws($news);
        $sendReport = $news->getSendHistory() ? $news->getSendHistory() : array();
        $mailContent = $this->renderView('email/news.html.twig', array('sender' => $news->getOrganisation(), 'news' => $news, 'stammdaten' => $stammdaten));
        foreach ($stammdaten as $data) {
            $data = $elternService->getLatestElternFromCEltern($data);
            if ($data){
                if (!in_array($data->getEmail(), $sendReport)) {
                    $mailerService->sendEmail(
                        'Ranzenpost',
                        $news->getOrganisation()->getEmail(),
                        $data->getEmail(),
                        $news->getTitle(),
                        $mailContent,
                        $news->getOrganisation()->getEmail());
                    $sendReport[] = $data->getEmail();
                }
                foreach ($data->getPersonenberechtigters() as $data2) {
                    if (!in_array($data2->getEmail(), $sendReport)) {
                        $mailerService->sendEmail(
                            'Ranzenpost',
                            $news->getOrganisation()->getEmail(),
                            $data2->getEmail(),
                            $news->getTitle(),
                            $mailContent,
                            $news->getOrganisation()->getEmail());
                        $sendReport[] = $data2->getEmail();
                    }
                }
            }


        }

        $text = $translator->trans('Nachricht versendet');
        $news->setSendHistory($sendReport);
        $em = $this->managerRegistry->getManager();
        $em->persist($news);
        $em->flush();
        return $this->redirectToRoute('org_news_anzeige', array('id' => $news->getOrganisation()->getId(), 'snack' => $text));
    }


    /**
     * @Route("city_news/send", name="city_news_send", methods={"GET"})
     */
    public function cityNewsSendAction(Request $request, TranslatorInterface $translator, MailerService $mailerService, ElternService $elternService)
    {
        $news = $this->managerRegistry->getRepository(News::class)->find($request->get('id'));

        if ($news->getStadt() != $this->getUser()->getStadt()) {
            throw new \Exception('Wrong City');
        }
        if ($news->getSchule()->isEmpty()) {
            $text = $translator->trans('Nachricht konnte nicht versendet werden. Bitte wählen Sie eine mindestend eine Schule aus');
            return $this->redirectToRoute('city_admin_news_anzeige', array('id' => $news->getStadt()->getId(), 'snack' => $text));
        }
        if ($news->getSchuljahre()->isEmpty()) {
            $text = $translator->trans('Nachricht konnte nicht versendet werden. Bitte wählen Sie eine mindestens ein Schuljahr aus');
            return $this->redirectToRoute('city_admin_news_anzeige', array('id' => $news->getStadt()->getId(), 'snack' => $text));
        }

        $stammdaten = $this->getStammdatenFromNEws($news);
        $mailContent = $this->renderView('email/news.html.twig', array('sender' => $news->getStadt(), 'news' => $news, 'stammdaten' => $stammdaten));
        $sendReport = $news->getSendHistory() ? $news->getSendHistory() : array();
        foreach ($stammdaten as $data) {
            $data = $elternService->getLatestElternFromCEltern($data);
            if ($data){
                if (!in_array($data->getEmail(), $sendReport)) {
                    $mailerService->sendEmail(
                        'Ranzenpost',
                        $news->getStadt()->getEmail(),
                        $data->getEmail(),
                        $news->getTitle(),
                        $mailContent,
                        $news->getStadt()->getEmail());
                    $sendReport[] = $data->getEmail();
                }
                foreach ($data->getPersonenberechtigters() as $data2) {
                    if (!in_array($data2->getEmail(), $sendReport)) {
                        $mailerService->sendEmail(
                            'Ranzenpost',
                            $news->getStadt()->getEmail(),
                            $data2->getEmail(),
                            $news->getTitle(),
                            $mailContent,
                            $news->getStadt()->getEmail());
                        $sendReport[] = $data2->getEmail();
                    }
                }
            }

        }

        $text = $translator->trans('Nachricht versendet');
        $news->setSendHistory($sendReport);
        $em = $this->managerRegistry->getManager();
        $em->persist($news);
        $em->flush();

        return $this->redirectToRoute('city_admin_news_anzeige', array('id' => $news->getStadt()->getId(), 'snack' => $text));
    }


    /**
     * @Route("/news/email_online/id",name="org_email_news_show_online",methods={"GET"})
     */
    public function orgShowNewsAction(Request $request)
    {
        $news = $this->managerRegistry->getRepository(News::class)->find($request->get('id'));

        if ($news->getOrganisation()) {
            $stadt = $news->getOrganisation()->getStadt();
            return $this->render('email/news.html.twig', array('stadt' => $stadt, 'sender' => $news->getOrganisation(), 'news' => $news));
        } else {
            return $this->render('email/news.html.twig', array('stadt' => $news->getStadt(), 'sender' => $news->getStadt(), 'news' => $news));

        }
    }

    private function getStammdatenFromNEws(News $news)
    {
        $qb = $this->managerRegistry->getRepository(Stammdaten::class)->createQueryBuilder('stammdaten');

        $qb->innerJoin('stammdaten.kinds', 'kinds')
            ->innerJoin('kinds.zeitblocks', 'kind_zeitblocks');
        $count = 0;
        $subSchule = $qb->expr()->orX();
        foreach ($news->getSchule() as $schule) {
            $subSchule->add('kind_zeitblocks.schule = :schule' . $count);
            $qb->setParameter('schule' . $count, $schule);
            $count++;
        }
        $qb->andWhere($subSchule);
        $subschuljahr = $qb->expr()->orX();
        foreach ($news->getSchuljahre() as $schuljahr) {
            $subschuljahr->add('kind_zeitblocks.active = :active' . $count);
            $qb->setParameter('active' . $count, $schuljahr);
            $count++;
        }
        $qb->andWhere($subschuljahr);

        $qb->andWhere('SIZE(kinds.beworben) = 0');


        $qb->andWhere('stammdaten.created_at IS NOT NULL');

        $query = $qb->getQuery();
        $stammdaten = $query->getResult();
        return $stammdaten;
    }
}
