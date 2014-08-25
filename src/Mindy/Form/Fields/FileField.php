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

use Modules\Core\CoreModule;

class FileField extends Field
{
    public $type = 'file';
    public $cleanValue = '';

    public $currentTemplate = '<p>{label}:<br/><a class="current-file" href="{current}" target="_blank">{current}</a></p>';
    public $cleanTemplate = '<label for="{id}-clean" class="clean-label"><input type="checkbox" id="{id}-clean" name="{name}" value="{value}"> {label}</label>';
    public $template = "<input type='{type}' id='{id}' name='{name}'{html}/>";

    public $oldValue = null;

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
        if (is_array($value)) {
            $value = $this->getOldValue();
        }
        /**
         * @TODO: refactor
         */

        if ($value) {
            $currentLink = strtr($this->currentTemplate, [
                '{current}' => $value,
                // @TODO: translate
                '{label}' => CoreModule::t("Current file")
            ]);
            if($this->required) {
                $clean = '';
            } else {
                $clean = strtr($this->cleanTemplate, [
                    '{id}' => $this->getId(),
                    '{name}' => $this->getName(),
                    '{value}' => $this->cleanValue,
                    // @TODO: translate
                    '{label}' => CoreModule::t("Clean")
                ]);
            }
            $input = $currentLink . $clean . '<br/>' . $input;
        }

        $hint = $this->hint ? $this->renderHint() : '';
        $errors = $this->getErrors() ? $this->renderErrors() : '';
        return $label . $input . $hint . $errors;
    }

    public function setValue($value)
    {
        if (is_object($value)) {
            $this->setOldValue();
            $this->value = $value->getUrl();
        }elseif($value == $this->cleanValue || is_null($value)){
            $this->setOldValue();
            $this->value = null;
        }elseif(is_string($value) || is_array($value)) {
            $this->setOldValue();
            $this->value = $value;
        }
        return $this;
    }

    public function getOldValue()
    {
        return $this->oldValue;
    }

    public function setOldValue()
    {
        if (is_string($this->value) || !$this->oldValue){
            $this->oldValue = $this->value;
        }
    }
}
