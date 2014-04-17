<?php
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
 * @date 17/04/14.04.2014 18:21
 */

namespace Mindy\Form\Fields;

use Mindy\Form\Form;

abstract class Field
{
    public $widget;

    /**
     * @var Form
     */
    protected $form;

    protected $name;

    /**
     * @return string the fully qualified name of this class.
     */
    public static function className()
    {
        return get_called_class();
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setForm(Form $form)
    {
        $this->form = $form;
        return $this;
    }
}
