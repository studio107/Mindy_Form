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
 * @date 07/05/14.05.2014 15:42
 */

class UserForm extends ModelForm
{
    public function init()
    {
        parent::init();
        $this->templates = [
            'block' => __DIR__ . '/../Templates/block.php'
        ];
    }

    public function getFields()
    {
        return [
            'name' => ['class' => CharField::className()]
        ];
    }

    public function getModel()
    {
        return new User;
    }
}
