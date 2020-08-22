<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\Active;
use App\Entity\Schule;
use App\Entity\Stadt;
use App\Entity\Zeitblock;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PreisListeService
{

    private $templating;
    private $translator;
    protected $parameterBag;
    private $fileSystem;
    private $generator;
    private $schuljahrService;
    private $em;
    public function __construct(EntityManagerInterface $entityManager,SchuljahrService $schuljahrService, UrlGeneratorInterface $urlGenerator, FilesystemInterface $publicUploadsFilesystem, EngineInterface $templating, TranslatorInterface $translator, ParameterBagInterface $parameterBag)
    {
        $this->em = $entityManager;
        $this->templating = $templating;
        $this->translator = $translator;
        $this->parameterBag = $parameterBag;
        $this->fileSystem = $publicUploadsFilesystem;
        $this->generator = $urlGenerator;
        $this->schuljahrService = $schuljahrService;
    }

    public function preisliste(Stadt $stadt, Schule $schule,$gehaltIst,$artIst ){
        $schuljahr = $this->em->getRepository(Active::class)->findSchuljahrFromCity($stadt,new \DateTime());
        $schulen = $this->em->getRepository(Schule::class)->findBy(array('stadt'=>$stadt,'deleted'=>false));
        $gehalt = $stadt->getGehaltsklassen();
        $onlyOneType = null;
        $art = [
            $this->translator->trans('Ganztag') => 1,
            $this->translator->trans('Halbtag') => 2,
        ];

        $req = array(
            'deleted' => false,
            'active' => $schuljahr,
            'schule' => $schule,
        );

        $req['ganztag'] = $artIst;

        $qb = $this->em->getRepository(Zeitblock::class)->createQueryBuilder('zeitblock');
        $qb->andWhere('zeitblock.deleted = false')
            ->andWhere('zeitblock.active = :active')
            ->andWhere('zeitblock.schule = :schule')
            ->groupBy('zeitblock.ganztag')
            ->setParameter('active',$schuljahr)
            ->setParameter('schule',$schule);
        $query = $qb->getQuery();
        $checkBlock =$query->getResult();
        $onlyOneType=sizeof($checkBlock)>1?false:true;


         if($onlyOneType==1){
            $artIst = $checkBlock[0]->getGanztag();
            $req['ganztag'] = $artIst;
        }
        $block = $this->em->getRepository(Zeitblock::class)->findBy($req, array('von' => 'asc'));

        $renderBlocks = array();
        foreach ($block as $data) {
            $renderBlocks[$data->getWochentag()][] = $data;
        }

        return $this->templating->render('preisliste/index.html.twig', [
            'schulen' => $schulen,
            'gehalt' => $gehalt,
            'art' => array_flip($art),
            'schule' => $schule,
            'gehaltIst' => $gehaltIst,
            'blocks' => $renderBlocks,
            'artIst' => $artIst,
            'onlyOneType'=>$onlyOneType
        ]);
    }
}
