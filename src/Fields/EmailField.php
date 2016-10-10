<?php

namespace Mindy\Form\Fields;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class EmailField
 * @package Mindy\Form
 */
class EmailField extends TextField
{
    public $template = "<input type='email' value='{value}' id='{id}' name='{name}'{html}/>";

    public function getValidationConstraints() : array
    {
        return array_merge(parent::getValidationConstraints(), [
            new Assert\Email([
            ])
        ]);
    }
}
