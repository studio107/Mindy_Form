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

use Mindy\Exception\Exception;

class CharField extends Field
{
    public $template = "<input type='{type}' value='{value}' id='{id}' name='{name}'{html}/>";

    public function render()
    {
        $label = $this->renderLabel();
        $input = $this->renderInput();
        $hint = $this->hint ? $this->renderHint() : '';
        $errors = $this->renderErrors();
        return implode("\n", [$label, $input, $hint, $errors]);
    }

    public function getValue()
    {
        $value = parent::getValue();
        if($value) {
            return $value;
        }
        if($this->value instanceof \Mindy\Orm\Manager) {
            throw new Exception("Value must be a string, not a manager");
        }
        return $this->value;
    }
}
