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
    public $template = "<input type='{type}' id='{id}' name='{name}'{html}/>";

    public $type = "checkbox";

    public function render()
    {
        $label = $this->renderLabel();
        if($this->value) {
            $this->html['checked'] = 'checked';
        }
        $input = strtr($this->template, [
            '{type}' => $this->type,
            '{id}' => $this->getId(),
            '{name}' => $this->getName(),
            '{value}' => $this->getValue(),
            '{html}' => $this->getHtmlAttributes()
        ]);

        $hint = $this->hint ? $this->renderHint() : '';
        $errors = $this->renderErrors();
        $checkbox = $input . $label . $hint . $errors;

        $name = implode('_', $this->form->prefix) . "[" . $this->form->getId() . "][" . $this->name . "]";
        return "<input type='hidden' value='' name='" . $name . "' />" . $checkbox;
    }
}
