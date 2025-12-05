<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-superadmin',
    description: 'This command setups the application.',
)]
class CreateSuperadminCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'keycloakId',
                null,
                InputOption::VALUE_OPTIONAL,
                'The Keycloak ID for the super admin user'
            )
            ->addOption(
                'email',
                null,
                InputOption::VALUE_REQUIRED,
                'The email address for the super admin user'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $keycloakId = $input->getOption('keycloakId');
        $email = $input->getOption('email');

        if (!$email) {
            $io->error('The --email option is required.');
            return Command::FAILURE;
        }

        $superAdmin = (new User())
            ->setEmail($email)
            ->setVorname('Super')
            ->setNachname('Admin')
            ->setEnabled(true)
            ->setRoles(['ROLE_SUPER_ADMIN', 'ROLE_ADMIN'])
            ->setCreatedAt(new \DateTime('now'))
            ->setKeycloakId($keycloakId);

        $this->entityManager->persist($superAdmin);
        $this->entityManager->flush();

        $io->comment('Created super admin user with email: ' . $email);

        $io->success('All setup tasks completed successfully.');

        return Command::SUCCESS;
    }
}
