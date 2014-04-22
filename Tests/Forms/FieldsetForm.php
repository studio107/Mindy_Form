<?php
use Mindy\Form\BaseForm;
use Mindy\Form\Fields\CharField;
use Mindy\Form\Fields\EmailField;

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
 * @date 22/04/14.04.2014 13:14
 */

class FieldsetForm extends BaseForm
{
    public function getFieldSets()
    {
        return [
            "Main information" => [
                "name"
            ],
            "Extra information" => [
                "email"
            ]
        ];
    }

    public function getFields()
    {
        return [
            'name' => [
                'class' => CharField::className()
            ],
            'email' => [
                'class' => EmailField::className()
            ],
        ];
    }
}
