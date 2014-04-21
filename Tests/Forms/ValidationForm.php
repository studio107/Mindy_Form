<?php
use Mindy\Form\Fields\CharField;
use Mindy\Form\Fields\EmailField;
use Mindy\Form\BaseForm;
use Mindy\Form\Validator\EmailValidator;
use Mindy\Form\Validator\RequiredValidator;

/**
 * 
 *
 * All rights reserved.
 * 
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 17/04/14.04.2014 18:25
 */

class ValidationForm extends BaseForm
{
    public function getFields()
    {
        return [
            'name' => [
                'class' => CharField::className(),
                'validators' => [
                    new RequiredValidator()
                ]
            ],
            'email' => [
                'class' => EmailField::className(),
                'validators' => [
                    new RequiredValidator(),
                    new EmailValidator()
                ]
            ],
        ];
    }
}
