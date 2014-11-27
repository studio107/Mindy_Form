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
    public $template = "<input type='{type}' id='{id}' value='{value}' name='{name}'{html}/>";

    public $type = "checkbox";

    public function render()
    {
        if (!empty($this->choices)) {
            $inputs = [];
            $i = 0;
            foreach ($this->choices as $value => $labelStr) {
                $label = strtr("<label for='{for}'>{label}</label>", [
                    '{for}' => $this->getHtmlId() . '_' . $i,
                    '{label}' => $labelStr
                ]);
                $input = strtr($this->template, [
                    '{type}' => $this->type,
                    '{id}' => $this->getHtmlId() . '_' . $i,
                    '{name}' => $this->getHtmlName() . '[]',
                    '{value}' => $value,
                    '{html}' => $this->getHtmlAttributes()
                ]);
                $i++;
                $inputs[] = implode("\n", [$input, $label]);
            }
            return implode("\n", [
                "<input type='hidden' value='' name='" . $this->getHtmlName() . '[]' . "' />",
                implode("\n", $inputs),
                $this->hint ? $this->renderHint() : '',
                $this->renderErrors(),
            ]);
        } else {
            $label = $this->renderLabel();
            if($this->value) {
                $this->html['checked'] = 'checked';
            }
            $input = strtr($this->template, [
                '{type}' => $this->type,
                '{id}' => $this->getHtmlId(),
                '{name}' => $this->getHtmlName(),
                '{value}' => 1,
                '{html}' => $this->getHtmlAttributes()
            ]);

            $hint = $this->hint ? $this->renderHint() : '';
            $errors = $this->renderErrors();
            return implode("\n", [
                "<input type='hidden' value='' name='" . $this->getHtmlName() . "' />",
                $input, $label, $hint, $errors
            ]);
        }
    }
}
