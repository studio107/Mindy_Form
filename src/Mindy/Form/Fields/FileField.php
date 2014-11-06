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

use Mindy\Locale\Translate;
use Mindy\Orm\Fields\FileField as ModelFileField;

class FileField extends Field
{
    public $type = 'file';
    public $cleanValue = true;

    public $currentTemplate = '<p class="current-file-container">{label}:<br/><a class="current-file" href="{current}" target="_blank">{current}</a></p>';
    public $cleanTemplate = '<label for="{id}-clean" class="clean-label"><input type="checkbox" id="{id}-clean" name="{name}" value="{value}"> {label}</label>';
    public $template = "<input type='{type}' id='{id}' name='{name}'{html}/>";

    public $oldValue = null;

    public function render()
    {
        $t = Translate::getInstance();
        $label = $this->renderLabel();

        $input = strtr($this->template, [
            '{type}' => $this->type,
            '{id}' => $this->getHtmlId(),
            '{name}' => $this->getHtmlName(),
            '{html}' => $this->getHtmlAttributes()
        ]);

        $value = $this->getValue();
        if (is_array($value)) {
            $value = $this->getOldValue();
        }

        if ($value) {
            $currentLink = strtr($this->currentTemplate, [
                '{current}' => $value,
                '{label}' => $t->t('form', "Current file")
            ]);
            if ($this->required) {
                $clean = '';
            } else {
                $clean = strtr($this->cleanTemplate, [
                    '{id}' => $this->getHtmlId(),
                    '{name}' => $this->getHtmlName(),
                    '{value}' => $this->cleanValue,
                    '{label}' => $t->t('form', "Clean")
                ]);
            }
            $input = $currentLink . $clean . $input;
        }

        $hint = $this->hint ? $this->renderHint() : '';
        $errors = $this->renderErrors();
        return implode("\n", [$label, $input, $hint, $errors]);
    }

    public function setValue($value)
    {
        if ($value instanceof ModelFileField) {
            $value = $value->getUrl();
        }
        $this->setOldValue();
        $this->value = $value;
        return $this;
    }

    public function getOldValue()
    {
        return $this->oldValue;
    }

    public function setOldValue()
    {
        if (is_string($this->value) || !$this->oldValue) {
            $this->oldValue = $this->value;
        }
    }
}
