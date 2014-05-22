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


use Mindy\Form\Validator\EmailValidator;

class EmailField extends Field
{
    public function init()
    {
        $this->validators = array_merge([
            new EmailValidator()
        ], $this->validators);
    }
}
