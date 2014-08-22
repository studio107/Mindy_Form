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
 * @date 21/04/14.04.2014 18:29
 */

namespace Mindy\Form\Validator;


class RequiredValidator extends Validator
{
    public function validate($value)
    {
        if (empty($value)) {
            $this->addError(strtr('"{name}" cannot be empty', ['{name}' => $this->getName()]));
        }

        return $this->hasErrors() === false;
    }
}

