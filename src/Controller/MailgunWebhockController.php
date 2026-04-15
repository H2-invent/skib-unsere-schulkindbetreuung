<?php

namespace App\Controller;

use App\Entity\EmailResponse;
use App\Service\GroupORMService;
use App\Service\MailgunWebhockService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MailgunWebhockController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
    ) {
    }

    #[Route(path: '/mailgun/webhock/success', name: 'mailgun_webhock_delived', methods: ['POST'])]
    public function success(Request $request, MailgunWebhockService $mailgunWebhockService)
    {
        $res = true;
        $parametersAsArray = [];
        try {
            if ($content = $request->getContent()) {
                $parametersAsArray = json_decode($content, true);
            }
            $res = $mailgunWebhockService->saveSuccess($parametersAsArray);
        } catch (\Exception) {
            return new JsonResponse(['error' => true]);
        }

        return new JsonResponse(['error' => $res]);
    }

    #[Route(path: '/mailgun/webhock/fail', name: 'mailgun_webhock_fail', methods: ['POST'])]
    public function fail(Request $request, MailgunWebhockService $mailgunWebhockService)
    {
        $res = true;
        $parametersAsArray = [];
        try {
            if ($content = $request->getContent()) {
                $parametersAsArray = json_decode($content, true);
            }
            $res = $mailgunWebhockService->saveFailure($parametersAsArray);
        } catch (\Exception) {
            return new JsonResponse(['error' => true]);
        }

        return new JsonResponse(['error' => $res]);
    }

    #[Route(path: '/admin/mailgun/index', name: 'admin_mailgun_index', methods: ['GET'])]
    public function index(Request $request, MailgunWebhockService $mailgunWebhockService, GroupORMService $groupORMService)
    {
        $mails = $this->managerRegistry->getRepository(EmailResponse::class)->findBy([], ['createdAt' => 'desc']);
        $mailsChart = [];

        $mailsChart[] = ['label' => 'Erfolgreich', 'data' => $groupORMService->groupData($this->managerRegistry->getRepository(EmailResponse::class)->findBy(['allert' => false, 'warning' => false], ['createdAt' => 'desc']))];
        $mailsChart[] = ['label' => 'Permament', 'data' => $groupORMService->groupData($this->managerRegistry->getRepository(EmailResponse::class)->findBy(['allert' => true], ['createdAt' => 'desc']))];
        $mailsChart[] = ['label' => 'Temporary', 'data' => $groupORMService->groupData($this->managerRegistry->getRepository(EmailResponse::class)->findBy(['warning' => true], ['createdAt' => 'desc']))];

        return $this->render('mailgun_webhock/index.html.twig', ['emails' => $mails, 'title' => 'Mail-Übersicht', 'chart' => $mailsChart]);
    }

    #[Route(path: '/admin/mailgun/history', name: 'admin_mailgun_history', methods: ['GET'])]
    public function hist(Request $request, MailgunWebhockService $mailgunWebhockService)
    {
        $email = $this->managerRegistry->getRepository(EmailResponse::class)->findBy(['messageId' => $request->get('message-id')], ['createdAt' => 'desc']);

        return $this->render('mailgun_webhock/index.html.twig', ['emails' => $email, 'title' => $request->get('message-id')]);
    }

    #[Route(path: '/admin/mailgun/detail', name: 'admin_mailgun_detail', methods: ['GET'])]
    public function detail(Request $request, MailgunWebhockService $mailgunWebhockService)
    {
        $email = $this->managerRegistry->getRepository(EmailResponse::class)->find($request->get('message-id'));
        $json = json_decode((string) $email->getPayload());

        return $this->render('mailgun_webhock/detail.html.twig', ['json' => $json, 'emails' => $email, 'title' => 'Detail']);
    }
}
