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
use App\Entity\Schule;
use App\Entity\Stadt;
use App\Entity\Stammdaten;
use App\Entity\Zeitblock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SchulkindBetreuungKindNeuService
{
    private $em;
    private $translator;
    private $validator;
    private $generator;
    private $error;
    private SchuljahrService  $schuljahrService;
    public function __construct(ErrorService $errorService, EntityManagerInterface $em, TranslatorInterface $translator, ValidatorInterface $validator,UrlGeneratorInterface $urlGenerator, SchuljahrService  $schuljahrService)
    {
        $this->em = $em;
        $this->translator = $translator;
        $this->validator = $validator;
        $this->generator = $urlGenerator;
        $this->error = $errorService;
        $this->schuljahrService  = $schuljahrService;
    }
    public function prepareKind(Kind $kind, Schule $schule, Stammdaten $eltern){
        $kind->setEltern($eltern);
        $kind->setSchule($schule);
        $schuljahr = $this->schuljahrService->getSchuljahr($schule->getStadt());
        if (new \DateTime() < $schuljahr->getAnmeldeEnde() && new \DateTime()>$schuljahr->getAnmeldeStart()){//ist im ANmeldezeitraum, alos wahrscheinlich ein Elternteil oder ein Mitarbeiter, der ein Kind so anmelden möchte
            $kind->setStartDate($schuljahr->getVon());
        }else{
            $kind->setStartDate((new \DateTime())->modify($schule->getStadt()->getSettingSkibDefaultNextChange()));
        }
        if (!$kind->getTracing()){
            $kind->setTracing(md5(uniqid('kinder', true)));
        }
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
    public  function saveKind(Kind $kind, bool $hasRole,Stadt $stadt,FormInterface $form){
        $errors = $this->validator->validate($kind);
        $errorString = array();


        if (count($errors) == 0 && ($kind->getMasernImpfung() === true || $hasRole) ) {
            $this->em->persist($kind);
            $this->em->flush();
            $text = array(array('type'=>'success','text'=>$this->translator->trans('Erfolgreich gespeichert')));
            return new JsonResponse(array('error' => 0, 'snack' => $text, 'next' => $this->generator->generate('loerrach_workflow_schulen_kind_zeitblock', array('slug' => $stadt->getSlug(), 'kind_id' => $kind->getId()))));
        }else{
            if ($kind->getMasernImpfung() === false && !$hasRole){
            $errorString[]= array('type'=>'error','text'=>$this->translator->trans('Fehler. Sie können Ihre Kind nur mit einer Masernimmunisierung anmelden'));
            }

            $errorString = array_merge($errorString, $this->error->createError($errors,$form));
            return new JsonResponse(array('error' => 1, 'snack' => $errorString));

        }
    }
}
