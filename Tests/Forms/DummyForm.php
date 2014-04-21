<?php
use Mindy\Form\Fields\CharField;
use Mindy\Form\ModelForm;

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
 * @date 21/04/14.04.2014 18:46
 */

class DummyForm extends ModelForm
{
    public function getFields()
    {
        return [
            'name' => ['class' => CharField::className()],
        ];
    }

    public function getModel()
    {
        return new DummyModel();
    }
}
