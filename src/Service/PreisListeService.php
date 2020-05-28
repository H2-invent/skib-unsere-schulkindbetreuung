<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\TCPDFBundle\Controller\TCPDFController;

class PreisListeService
{

    private $templating;
    private $translator;
    protected $parameterBag;
    private $fileSystem;
    private $generator;

    public function __construct(UrlGeneratorInterface $urlGenerator, FilesystemInterface $publicUploadsFilesystem, EngineInterface $templating, TranslatorInterface $translator, ParameterBagInterface $parameterBag)
    {

        $this->templating = $templating;
        $this->translator = $translator;
        $this->parameterBag = $parameterBag;
        $this->fileSystem = $publicUploadsFilesystem;
        $this->generator = $urlGenerator;
    }

    public function preisliste(){

    }
}
