<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ErrorService
{


    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function createError($error, FormInterface $form)
    {
        dump($form);

        $ele = $form->all();
        $arr = array();
        foreach ($ele as $data) {
            $arr[$data->getName()] = $data->getConfig()->getOption('label');

        }
        $errorString = array();
        foreach ($error as $data) {
            $errorString[]= $arr[$data->getPropertyPath()] . ': ' . $data->getMessage();
        }

        return $errorString;
    }
}
