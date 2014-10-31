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


use Closure;
use Mindy\Form\ModelForm;
use Mindy\Orm\Model;

class DropDownField extends Field
{
    public $choices = [];

    /**
     * Span needs for custom frontend. See: http://stackoverflow.com/questions/23920990/firefox-30-is-not-hiding-select-box-arrows-anymore
     * @var string
     */
    public $template = "<span class='select-holder'><select id='{id}' name='{name}' {html}>{value}</select></span>";

    public $multiple = false;

    public $empty = '';

    public function render()
    {
        $label = $this->renderLabel();
        $input = strtr($this->template, [
            '{type}' => $this->type,
            '{id}' => $this->getHtmlId(),
            '{value}' => $this->getValue(),
            '{name}' => $this->multiple ? $this->getHtmlName() . '[]' : $this->getHtmlName(),
            '{html}' => $this->getHtmlAttributes()
        ]);

        $hint = $this->hint ? $this->renderHint() : '';
        $errors = $this->renderErrors();

        $name = $this->getHtmlName();
        return implode("\n", ["<input type='hidden' value='' name='{$name}' />", $label, $input, $hint, $errors]);
    }

    public function getValue()
    {
        $out = '';
        $data = [];
        $selected = [];

        if (!empty($this->choices)) {
            $choices = $this->choices;
        } else {
            $choices = $this->form->getInstance()->getField($this->name)->choices;
        }

        if (!empty($choices)) {
            if ($choices instanceof Closure) {
                $data = $choices->__invoke();
            } else {
                $data = $choices;
            }
            if ($this->form instanceof ModelForm) {
                $model = $this->form->getInstance();
                $field = $model->getField($this->name);
                if ($field->null) {
                    $data = ['' => ''] + $data;
                }

                if (is_a($field, $model::$foreignField)) {
                    $related = $model->{$this->name};
                    if ($related) {
                        $selected[] = $related->pk;
                    }
                }
            }
            return $this->valueToHtml($data, $selected);
        }

        if ($this->form instanceof ModelForm && $this->form->getInstance()->hasField($this->name)) {
            $model = $this->form->getInstance();
            $field = $model->getField($this->name);

            if (is_a($field, $model::$manyToManyField)) {
                $this->multiple = true;

                $modelClass = $field->modelClass;
                $models = $modelClass::objects()->all();

                $selectedTmp = $field->getManager()->all();
                foreach ($selectedTmp as $model) {
                    $selected[] = $model->pk;
                }

                $this->html['multiple'] = 'multiple';
                if (count($models) > 1) {
                    $data[''] = '';
                }

                foreach ($models as $model) {
                    $data[$model->pk] = (string)$model;
                }
            } elseif (is_a($field, $model::$hasManyField)) {
                $this->multiple = true;

                $modelClass = $field->modelClass;
                $models = $modelClass::objects()->all();

                $this->html['multiple'] = 'multiple';
                if (count($models) > 1) {
                    $data[''] = '';
                }

                foreach ($models as $model) {
                    $data[$model->pk] = (string)$model;
                }
            } elseif (is_a($field, $model::$foreignField)) {
                $modelClass = $field->modelClass;
                $qs = $modelClass::objects();
                if (get_class($model) == $modelClass && $model->getIsNewRecord() === false) {
                    $qs = $qs->exclude(['pk' => $model->pk]);
                }
                /* @var $modelClass \Mindy\Orm\Model */
                if (!$this->required) {
                    $data[''] = $this->empty;
                }
                $related = $model->{$this->name};
                if ($related) {
                    $selected[] = $related->pk;
                }
                foreach ($qs->all() as $model) {
                    $data[$model->pk] = (string)$model;
                }
            } else {
                $data = parent::getValue();
            }
        } else {
            $data = parent::getValue();
        }

        if (is_array($data)) {
            return $this->valueToHtml($data, $selected);
        } else {
            return $out;
        }
    }

    protected function valueToHtml(array $data, array $selected = [])
    {
        $out = '';
        foreach ($data as $value => $name) {
            $out .= strtr("<option value='{value}'{selected}>{name}</option>", [
                '{value}' => $value,
                '{name}' => $name,
                '{selected}' => in_array($value, $selected) ? " selected='selected'" : ""
            ]);
        };
        return $out;
    }
}
