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
 * @date 23/04/14.04.2014 18:24
 */

namespace Mindy\Form\Fields;


class ShortUrlField extends CharField
{
    public function getValue()
    {
        $slugs = explode('/', parent::getValue());
        return end($slugs);
    }
}
