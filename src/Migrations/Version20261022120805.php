<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\StadtTranslation;
use App\Repository\StadtTranslationRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261022120805 extends AbstractMigration implements ContainerAwareInterface
{
    private ?ContainerInterface $container;

    public function setContainer(?ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function getDescription(): string
    {
        return 'fix all translation mail fields that could have been broken by the WYSIWYG Editors html encoding';
    }

    public function up(Schema $schema): void
    {
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        $stadtTranslationRepository = $entityManager->getRepository(StadtTranslation::class);

        $translations = $stadtTranslationRepository->findAll();

        foreach ($translations as $translation) {
            $hasChanged = false;

            $twigTemplate = $translation->getEmailtemplateAbmeldung();
            $fixedTemplate = $this->fixTwigTemplate($twigTemplate);
            if ($fixedTemplate !== $twigTemplate) {
                $translation->setEmailtemplateAbmeldung($fixedTemplate);
                $hasChanged = true;
            }

            $twigTemplate = $translation->getEmailtemplateAnmeldung();
            $fixedTemplate = $this->fixTwigTemplate($twigTemplate);
            if ($fixedTemplate !== $twigTemplate) {
                $translation->setEmailtemplateAnmeldung($fixedTemplate);
                $hasChanged = true;
            }

            $twigTemplate = $translation->getEmailtemplateBuchung();
            $fixedTemplate = $this->fixTwigTemplate($twigTemplate);
            if ($fixedTemplate !== $twigTemplate) {
                $translation->setEmailtemplateBuchung($fixedTemplate);
                $hasChanged = true;
            }

            $twigTemplate = $translation->getEmailtemplateStammdatenEdit();
            $fixedTemplate = $this->fixTwigTemplate($twigTemplate);
            if ($fixedTemplate !== $twigTemplate) {
                $translation->setEmailtemplateStammdatenEdit($fixedTemplate);
                $hasChanged = true;
            }

            $twigTemplate = $translation->getPdftemplateGebuehrenbescheid();
            $fixedTemplate = $this->fixTwigTemplate($twigTemplate);
            if ($fixedTemplate !== $twigTemplate) {
                $translation->setPdftemplateGebuehrenbescheid($fixedTemplate);
                $hasChanged = true;
            }

            $twigTemplate = $translation->getPdftemplateGebuehrenbescheidFerien();
            $fixedTemplate = $this->fixTwigTemplate($twigTemplate);
            if ($fixedTemplate !== $twigTemplate) {
                $translation->setPdftemplateGebuehrenbescheidFerien($fixedTemplate);
                $hasChanged = true;
            }

            if ($hasChanged) {
                $entityManager->flush();
            }
        }
    }

    private function fixTwigTemplate(?string $template): ?string
    {
        if ($template === null || $template === '') {
            return $template;
        }

        $patterns = [
            '/\{\{(.*?)\}\}/s' => '{{%s}}',
            '/\{%(.*?)%\}/s'   => '{%%%s%%}', // %% to escape sprintf
        ];

        foreach ($patterns as $pattern => $template_format) {
            $template = preg_replace_callback(
                $pattern,
                static fn (array $subject): string => sprintf(
                    $template_format,
                    html_entity_decode($subject[1], ENT_QUOTES | ENT_HTML5, 'UTF-8')
                ),
                $template
            );
        }

        return $template;
    }

    public function down(Schema $schema): void
    {
    }
}
