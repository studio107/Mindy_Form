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

class FileField extends Field
{
    public $type = 'file';
    public $cleanValue = 'NULL';

    public $currentTemplate = '{label}: <a class="current-file" href="{current}" target="_blank">{current}</a>';
    public $cleanTemplate = '<label for="{id}-clean" class="clean-label">{label}</label><input type="checkbox" id="{id}-clean" name="{name}" value="{value}">';
    public $template = "<input type='{type}' id='{id}' name='{name}'{html}/>";

    public function render()
    {
        $label = $this->renderLabel();

        $input = strtr($this->template, [
            '{type}' => $this->type,
            '{id}' => $this->getId(),
            '{name}' => $this->getName(),
            '{html}' => $this->getHtmlAttributes()
        ]);

        $value = $this->getValue();

        /**
         * @TODO: refactor
         */
        if ($value){
            $currentLink = strtr($this->currentTemplate, [
                '{current}' => $value,
                // @TODO: translate
                '{label}' => "Current file"
            ]);
            $clean = strtr($this->cleanTemplate, [
                '{id}' => $this->getId(),
                '{name}' => $this->getName(),
                '{value}' => $this->cleanValue,
                // @TODO: translate
                '{label}' => "Clean"
            ]);
            $input = $currentLink.$clean .'<br/>'. $input;
        }

        $hint = $this->hint ? $this->renderHint() : '';
        $errors = $this->getErrors() ? $this->renderErrors() : '';
        return $label . $input . $hint . $errors;
    }

    public function setValue($value)
    {
        if (is_string($value) && $value && $value != $this->cleanValue){
            $this->value = $value;
        }
        return $this;
    }
}
