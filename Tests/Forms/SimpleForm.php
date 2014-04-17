<?php
use Mindy\Form\Fields\CharField;
use Mindy\Form\Form;

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

class SimpleForm extends Form
{
    public function getFields()
    {
        return [
            'name' => ['class' => CharField::className()],
            'email' => ['class' => EmailField::className()],
        ];
    }
}
