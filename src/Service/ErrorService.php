<?php

/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01.
 */

namespace App\Service;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Contracts\Translation\TranslatorInterface;

class ErrorService
{
    private $arr;

    public function __construct(
        private TranslatorInterface $translator,
    ) {
        $this->arr = [];
    }

    public function createError($error, FormInterface $form)
    {
        $view = $form->createView();
        $this->getLabel($view);

        $errorString = [];
        foreach ($error as $data) {
            $errorString[] = ['type' => 'error', 'text' => $this->translator->trans($this->arr[$data->getPropertyPath()]) . ': ' . str_replace('"', '\"', $data->getMessage())];
        }

        return $errorString;
    }

    public function getLabel(FormView $form)
    {
        foreach ($form->children as $data) {
            if (sizeof($data->children) > 0) {
                $this->getLabel($data);
            } else {
                $this->arr[$data->vars['name']] = $data->vars['label'];
            }
        }
    }
}
