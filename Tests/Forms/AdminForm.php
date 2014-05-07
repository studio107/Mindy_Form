<?php
use Mindy\Form\ManagedForm;

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
 * @date 07/05/14.05.2014 15:39
 */

class AdminForm extends ManagedForm
{
    public function init()
    {
        parent::init();
        $this->templates = [
            'ul' => __DIR__ . '/../Templates/managed.php'
        ];
    }

    /**
     * @return string form class
     */
    public function getFormClass()
    {
        return UserForm::className();
    }

    public function getInlines()
    {
        return [
            'user' => CustomerInlineForm::className()
        ];
    }
}
