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
 * @date 23/04/14.04.2014 18:25
 */

namespace Mindy\Form\Fields;


class CheckboxField extends CharField
{
    public $type = "checkbox";

    public function render()
    {
        $label = $this->renderLabel();
        if($this->getValue()) {
            $this->html['checked'] = 'checked';
        }
        $input = strtr($this->template, [
            '{type}' => $this->type,
            '{id}' => $this->getId(),
            '{name}' => $this->name,
            '{value}' => $this->getValue(),
            '{html}' => $this->getHtmlAttributes()
        ]);

        $hint = $this->hint ? $this->renderHint() : '';
        $errors = $this->getErrors() ? $this->renderErrors() : '';
        return $input . $label . $hint . $errors;
    }
}
