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

use Exception;
use Mindy\Form\BaseForm;
use Mindy\Form\ModelForm;
use Mindy\Helper\Traits\Accessors;
use Mindy\Helper\Traits\Configurator;
use Mindy\Validation\Interfaces\IValidatorObject;
use Mindy\Validation\RequiredValidator;
use Mindy\Validation\Traits\ValidateField;

abstract class Field implements IValidatorObject
{
    use Accessors, Configurator, ValidateField;

    /**
     * @var bool Технические аттрибуты для inline моделей
     */
    public $hidden = false;

    /**
     * @var bool Технические аттрибуты для inline моделей
     */
    public $delete = false;

    public $value;

    public $required = false;

    public $widget;

    public $inputType;

    public $template = "<input type='{type}' id='{id}' name='{name}'{html}/>";

    public $hint;

    public $type = 'text';

    private $_html = '';

    public $choices = [];
    /**
     * @var string html class for render hint
     */
    public $hintClass = 'form-hint-text';

    public $label;

    /**
     * @var
     */
    private $_name;

    /**
     * @var BaseForm
     */
    private $_form;

    public $errorClass = 'error';


    private $_validatorClass = '\Mindy\Form\Validator\Validator';
    /**
     * @var string
     */
    private $_prefix;

    public function init()
    {
        if ($this->required) {
            $this->validators[] = new RequiredValidator();
        }
        foreach ($this->validators as $validator) {
            /** @var $validator \Mindy\Validation\Validator */
            if (is_subclass_of($validator, $this->_validatorClass)) {
                $validator->setName($this->label ? $this->label : $this->name);
            }
        }
    }

    public function __toString()
    {
        try {
            return (string)$this->render();
        } catch (Exception $e) {
            echo (string)$e;
            die();
        }
    }

    /**
     * @param BaseForm $form
     * @return $this
     */
    public function setForm(BaseForm $form)
    {
        $this->_form = $form;
        return $this;
    }

    /**
     * @return BaseForm
     */
    public function getForm()
    {
        return $this->_form;
    }

    public function setPrefix($value)
    {
        $this->_prefix = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        if ($this->_prefix === null) {
            return $this->form->getName();
        } else {
            return $this->_prefix . '[' . $this->form->getName() . '][' . $this->getId() . ']';
        }
    }

    public function getId()
    {
        return $this->form->getId();
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    public function renderInput()
    {
        return strtr($this->template, [
            '{type}' => $this->type,
            '{id}' => $this->getHtmlId(),
            '{name}' => $this->getHtmlName(),
            '{value}' => $this->getValue(),
            '{html}' => $this->getHtmlAttributes()
        ]);
    }

    public function render()
    {
        $label = $this->renderLabel();
        $input = $this->renderInput();

        $hint = $this->hint ? $this->renderHint() : '';
        $errors = $this->renderErrors();
        return implode("\n", [$label, $input, $hint, $errors]);
    }

    public function getHtmlName()
    {
        return $this->getPrefix() . '[' . $this->name . ']';
    }

    public function setHtmlAttributes($value)
    {
        $this->_html = $value;
        return $this;
    }

    public function getHtmlAttributes()
    {
        if (is_array($this->_html)) {
            $html = '';
            foreach ($this->_html as $name => $value) {
                $html .= is_numeric($name) ? " $value" : " $name='$value'";
            }
            return $html;
        } else {
            return $this->_html;
        }
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function getValue()
    {
        if ($this->form instanceof ModelForm) {
            $instance = $this->form->getInstance();
            if ($instance->hasField($this->name)) {
                return $instance->getField($this->name)->getValue();
            }
        }
        return $this->value;
    }

    public function renderLabel()
    {
        if ($this->label === false) {
            return '';
        }

        if ($this->label) {
            $label = $this->label;
        } else {
            if ($this->form instanceof ModelForm) {
                $instance = $this->form->getInstance();
                if ($instance->hasField($this->name)) {
                    $verboseName = $instance->getField($this->name)->verboseName;
                    if ($verboseName) {
                        $label = $verboseName;
                    }
                }
            }

            if (!isset($label)) {
                $label = ucfirst($this->name);
            }
        }

        return strtr("<label for='{for}'>{label}</label>", [
            '{for}' => $this->getHtmlId(),
            '{label}' => $label
        ]);
    }

    public function renderErrors()
    {
        $errors = "";
        foreach ($this->getErrors() as $error) {
            $errors .= "<li>{$error}</li>";
        }

        $html = "";
        if (!$errors) {
            $html = "style='display:none;'";
        }

        return "<ul class='{$this->errorClass}' id='{$this->getHtmlId()}_errors' {$html}>{$errors}</ul>";
    }

    public function renderHint()
    {
        return strtr('<p class="{class}">{hint}</p>', [
            '{class}' => $this->hintClass,
            '{hint}' => $this->hint
        ]);
    }

    /**
     * Format:
     * [
     *     "Main" => [
     *         "Name", "Url", "Content"
     *     ],
     *     "Extra" => [ ... ]
     * ]
     * @return array
     */
    public function getFieldSets()
    {
        return [];
    }

    public function getHtmlId()
    {
        return rtrim(str_replace(['][', '[]', '[', ']'], '_', $this->getPrefix()), '_') . '_' . $this->getName();
    }
}
