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
use Mindy\Orm\Manager;

class CharField extends Field
{
    public $template = "<input type='{type}' value='{value}' id='{id}' name='{name}'{html}/>";

    public function render()
    {
        $label = $this->renderLabel();
        $input = strtr($this->template, [
            '{type}' => $this->type,
            '{id}' => $this->getId(),
            '{name}' => $this->getName(),
            '{value}' => $this->getValue(),
            '{html}' => $this->getHtmlAttributes()
        ]);

        $hint = $this->hint ? $this->renderHint() : '';
        $errors = $this->renderErrors();
        return $label . $input . $hint . $errors;
    }

    public function getValue()
    {
        $value = parent::getValue();
        if($value) {
            return $value;
        }
        if($this->value instanceof Manager) {
            throw new Exception("Value must be a string, not a manager");
        }
        return $this->value;
    }
}
