<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\Active;
use App\Entity\Kind;
use App\Entity\Organisation;
use App\Entity\Schule;
use App\Entity\Stadt;
use App\Entity\Stammdaten;

use App\Entity\Zeitblock;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteOctober\TCPDFBundle\Controller\TCPDFController;

class SchulkindBetreuungKindNeuService
{
    private $em;
    private $translator;
    private $validator;
    private $generator;
    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator, ValidatorInterface $validator,UrlGeneratorInterface $urlGenerator)
    {
        $this->em = $em;
        $this->translator = $translator;
        $this->validator = $validator;
        $this->generator = $urlGenerator;
    }
    public function prepareKind(Kind $kind, Schule $schule, Stammdaten $eltern){
        $kind->setEltern($eltern);
        $kind->setSchule($schule);
        return $kind;
    }
    public function getGanztagBlocks(Active $schuljahr, Schule $schule){
        $req = array(
            'deleted' => false,
            'active' => $schuljahr,
            'schule' => $schule,
            'ganztag' => 1
        );
        return $this->em->getRepository(Zeitblock::class)->findBy($req, array('von' => 'asc'));
    }
    public function getHalbtagBlocks(Active $schuljahr, Schule $schule){
        $req = array(
            'deleted' => false,
            'active' => $schuljahr,
            'schule' => $schule,
            'ganztag' => 2
        );
        return $this->em->getRepository(Zeitblock::class)->findBy($req, array('von' => 'asc'));
    }
    public function cleanUpForm(Form $form, Active $schuljahr, Schule $schule){
        $ganztag = $this->getGanztagBlocks($schuljahr,$schule);
        $halbtag = $this->getHalbtagBlocks($schuljahr,$schule);

        if (empty($ganztag) && empty($halbtag)) {

        } elseif (empty($ganztag)) {
            $form->remove('art');
        } elseif (empty($halbtag)) {
            $form->remove('art');
        }
        return $form;
    }
    public function cleanUpKind( Active $schuljahr, Schule $schule,Kind $kind){
        $ganztag = $this->getGanztagBlocks($schuljahr,$schule);
        $halbtag = $this->getHalbtagBlocks($schuljahr,$schule);

        if (empty($ganztag) && empty($halbtag)) {

        } elseif (empty($ganztag)) {
            $kind->setArt(2);

        } elseif (empty($halbtag)) {
            $kind->setArt(1);

        }
        return $kind;
    }
    public  function saveKind(Kind $kind, bool $hasRole,Stadt $stadt){
        $errors = $this->validator->validate($kind);
        if($kind->getMasernImpfung() === false && $hasRole){
            //todo fehlertext
            $text = $this->translator->trans('Fehler. Bitte kreuzen Sie masern an');
            return new JsonResponse(array('error' => 1, 'snack' => $text));
        }
        if (count($errors) == 0) {
            $this->em->persist($kind);
            $this->em->flush();
            $text = $this->translator->trans('Erfolgreich gespeichert');
            return new JsonResponse(array('error' => 0, 'snack' => $text, 'next' => $this->generator->generate('loerrach_workflow_schulen_kind_zeitblock', array('slug' => $stadt->getSlug(), 'kind_id' => $kind->getId()))));
        }
    }
}
