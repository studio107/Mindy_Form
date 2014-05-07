<?php
use Mindy\Form\Fields\CharField;
use Mindy\Form\InlineModelForm;

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
class CustomerInlineForm extends InlineModelForm
{
    public $extra = 1;

    public $max = 1;

    public function init()
    {
        parent::init();
        $this->templates = [
            'inline' => __DIR__ . '/../Templates/inline.php'
        ];
    }

    public function getFields()
    {
        return [
            'address' => ['class' => CharField::className()]
        ];
    }

    public function getModel()
    {
        return new Customer;
    }
}
