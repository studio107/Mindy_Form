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
 * @date 17/04/14.04.2014 18:26
 */

namespace Mindy\Form\Fields;

class DateField extends CharField
{
    public function render()
    {
        $id = $this->getHtmlId();
        $js = "<script type='text/javascript'>$('#$id').pickmeup({format  : 'Y-m-d'});</script>";
        return parent::render() . $js;
    }
}
