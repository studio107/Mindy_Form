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
use Exception;
use Mindy\Form\ModelForm;
use Mindy\Orm\Model;

class DropDownField extends Field
{
    public $choices = [];

    public $template = "<select id='{id}' name='{name}' {html}>{value}</select>";

    public function render()
    {
        $out = parent::render();
        return "<input type='hidden' value='' name='{$this->name}' />" . $out;
    }

    public function getValue()
    {
        $out = '';
        $data = [];
        $selected = [];

        if(!empty($this->choices)) {
            if($this->choices instanceof Closure) {
                $data = $this->choices->__invoke();
            } else {
                $data = $this->choices;
            }
            return $this->valueToHtml($data, [$this->value instanceof Model ? $this->value->pk : $this->value]);
        }

        if($this->form instanceof ModelForm && $this->form->getModel()->hasField($this->name)) {
            $model = $this->form->getInstance();
            $field = $model->getField($this->name);

            if(is_a($field, $model->manyToManyField)) {
                $modelClass = $field->modelClass;
                $models = $modelClass::objects()->all();

                $selectedTmp = $field->getManager()->all();
                foreach($selectedTmp as $model) {
                    $selected[] = $model->pk;
                }

                $this->html['multiple'] = 'multiple';
                if(count($models) > 1) {
                    $data[''] = '';
                }

                foreach ($models as $model) {
                    $data[$model->pk] = (string) $model;
                }
            } elseif (is_a($field, $model->hasManyField)) {
                d(1);
            } elseif (is_a($field, $model->foreignField)) {
                $modelClass = $field->modelClass;
                /* @var $modelClass \Mindy\Orm\Model */
                $models = $modelClass::objects()->all();
                if($field->null) {
                    $data[''] = '';
                }
                foreach($models as $model) {
                    $data[$model->pk] = (string) $model;
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
                '{selected}' => in_array($value, $selected) || array_key_exists($value, $selected) ? " selected='selected'" : ""
            ]);
        };
        return $out;
    }
}
