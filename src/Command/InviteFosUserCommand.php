<?php

namespace App\Command;

use App\Entity\Active;
use App\Entity\User;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;

class InviteFosUserCommand extends Command
{
    protected static $defaultName = 'app:invite:fosUser';
    protected static $defaultDescription = 'Add a short description for your command';
    private $em;
    private $mailer;
    private $environment;

    public function __construct(EntityManagerInterface $entityManager, MailerService $mailerService, Environment $environment, string $name = null)
    {
        parent::__construct($name);
        $this->em = $entityManager;
        $this->mailer = $mailerService;
        $this->environment = $environment;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('url', InputArgument::OPTIONAL, 'This is the url of the page you want to send the invitation link from')
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $url = $input->getArgument('url');

        if ($url) {
            $io->note(sprintf('You passed an argument: %s', $url));

        }else{
            $io->error('Please enter the url for the link');
            return Command::FAILURE;
        }
        $qb = $this->em->getRepository(User::class)->createQueryBuilder('u');

        $users = $qb->andWhere($qb->expr()->isNull('u.keycloakId'))
            ->andWhere($qb->expr()->isNull('u.invitationToken'))
            ->getQuery()
            ->getResult();
        $count = 0;
        $progressBar = new ProgressBar($output, sizeof($users));


        $progressBar->start();
        foreach ($users as $data) {
            $data->setInvitationToken(md5(uniqid()));
            $this->em->persist($data);
            $this->em->flush();
            $progressBar->advance(1);
            $html = $this->environment->render('email/invitationEmailInitial.html.twig', array('user' => $data, 'url'=>$url));
            $this->mailer->sendEmail('Unsere Schulkinbetreuung', 'info@unsere-schulkindbetreuung.de', $data->getEmail(), 'Neue Anmeldung zur Software SKIB', $html,'info@unsere-schulkindbetreuung.de');
            $count++;
        }


        $io->success(sprintf('We send %s emails', $count));

        return Command::SUCCESS;
    }
}
