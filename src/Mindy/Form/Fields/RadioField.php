<?php

namespace Mindy\Form\Fields;

/**
 * Class RadioField
 * @package Mindy\Form
 */
class RadioField extends CharField
{
    public $template = "<input type='{type}' id='{id}' value='{value}' name='{name}'{html}/>";

    public $type = "radio";

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

                $checked = false;
                if (is_array($this->value)) {
                    foreach ($this->value as $v) {
                        if ($v == $value) {
                            $checked = true;
                        }
                    }
                } else {
                    if ($this->value == $value) {
                        $checked = true;
                    }
                }

                $input = strtr($this->template, [
                    '{type}' => $this->type,
                    '{id}' => $this->getHtmlId() . '_' . $i,
                    '{name}' => $this->getHtmlName(),
                    '{value}' => $value,
                    '{html}' => $this->getHtmlAttributes() . ($checked ? " checked='checked'" : '')
                ]);
                $i++;
                $hint = $this->hint ? $this->renderHint() : '';
                $inputs[] = implode("\n", [$input, $label, $hint]);
            }
            return implode("\n", $inputs) . $this->renderErrors();
        } else {
            if ($this->value) {
                $this->html['checked'] = 'checked';
            }
            return implode("\n", [
                "<input type='hidden' value='' name='" . $this->getHtmlName() . "' />",
                $this->renderInput(),
                $this->renderLabel(),
                $this->hint ? $this->renderHint() : '',
                $this->renderErrors()
            ]);
        }
    }

    public function renderInput()
    {
        if (!empty($this->choices)) {
            $inputs = [];
            $i = 0;
            foreach ($this->choices as $value => $labelStr) {
                $label = strtr("<label for='{for}'>{label}</label>", [
                    '{for}' => $this->getHtmlId() . '_' . $i,
                    '{label}' => $labelStr
                ]);

                $checked = false;
                if (is_array($this->value)) {
                    foreach ($this->value as $v) {
                        if ($v == $value) {
                            $checked = true;
                        }
                    }
                } else {
                    if ($this->value == $value) {
                        $checked = true;
                    }
                }

                $input = $this->renderInputInternal($this->getHtmlId() . '_' . $i, $value,  ($checked ? " checked='checked'" : ''));
                $i++;
                $hint = $this->hint ? $this->renderHint() : '';
                $inputs[] = implode("\n", [
                    $input,
                    $label,
                    $hint
                ]);
            }
            return implode("\n", $inputs) . $this->renderErrors();
        } else {
            if ($this->value) {
                $this->html['checked'] = 'checked';
            }
            $label = $this->renderLabel();
            $input = $this->renderInputInternal($this->getHtmlId(), 1);
            $hint = $this->hint ? $this->renderHint() : '';
            $errors = $this->renderErrors();
            return implode("\n", [
                "<input type='hidden' value='' name='" . $this->getHtmlName() . "' />",
                $input, $label, $hint, $errors
            ]);
        }
    }

    protected function renderInputInternal($id, $value, $html = '')
    {
        return strtr($this->template, [
            '{type}' => $this->type,
            '{id}' => $id,
            '{name}' => $this->getHtmlName(),
            '{value}' => $value,
            '{html}' => $this->getHtmlAttributes() . $html
        ]);
    }
}
