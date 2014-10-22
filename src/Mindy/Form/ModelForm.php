<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 17/04/14.04.2014 18:20
 */

namespace Mindy\Form;

use Exception;
use Mindy\Orm\Fields\FileField;
use Mindy\Orm\Model;

class ModelForm extends BaseForm
{
    public $ormClass = '\Mindy\Orm\Model';

    protected $instance;

    /**
     * Initialize fields
     * @void
     */
    public function initFields()
    {
        parent::initFields();
        $instance = $this->getInstance();
        foreach ($instance->getFieldsInit() as $name => $field) {
            if ($field->editable === false || is_a($field, Model::$autoField) || in_array($name, $this->exclude)) {
                continue;
            }

            $modelField = $field->getFormField($this);
            if ($modelField && !isset($this->_fields[$name])) {
                $this->_fields[$name] = $modelField;

                $value = $instance->{$name};
                if ($value instanceof FileField) {
                    $value = $value->getValue();
                }
                $this->_fields[$name]->setValue($value);
            }
        }
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $instance = $this->getInstance();

        $this->clearErrors();
        $instance->clearErrors();

        /* @var $field \Mindy\Form\Fields\Field */
        $fields = $this->getFieldsInit();

        foreach ($fields as $name => $field) {
            if (method_exists($this, 'clean' . ucfirst($name))) {
                $value = call_user_func([$this, 'clean' . ucfirst($name)], $field->getValue());
                if ($value) {
                    $this->cleanedData[$name] = $value;
                    $field->setValue($value);
                }
            }

            if ($field->isValid() === false) {
                $errors = $field->getErrors();
                if (empty($errors)) {
                    if ($field->isValid() === false) {
                        foreach ($field->getErrors() as $error) {
                            $this->addError($name, $error);
                        }
                    }
                } else {
                    foreach ($errors as $error) {
                        $this->addError($name, $error);
                    }
                }
            }

            $this->cleanedData[$name] = $field->getValue();
        }

        if (!$instance->isValid()) {
            foreach ($instance->getErrors() as $key => $errors) {
                // @TODO: duplication errors (email validation, for example)
                if (!$this->hasErrors($key)) {
                    foreach ($errors as $error) {
                        if (array_key_exists($key, $fields)) {
                            $this->addError($key, $error);
                            $fields[$key]->addError($error);
                        }
                    }
                }
            }
        }

        return $this->hasErrors() === false;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setAttributes(array $data)
    {
        parent::setAttributes($data);
        $this->getInstance()->setAttributes($data);
        return $this;
    }

    /**
     * @param $model \Mindy\Orm\Model
     * @return $this
     * @throws \Exception
     */
    public function setInstance($model)
    {
        if (is_a($model, $this->ormClass)) {
            $this->instance = $model;
            /* @var $model \Mindy\Orm\Model */
            foreach ($model->getFieldsInit() as $name => $field) {
                if (is_a($field, $model::$autoField)) {
                    continue;
                }

                if ($this->hasField($name)) {
                    $value = $model->{$name};
                    if ($value instanceof FileField) {
                        $value = $value->getValue();
                    }
                    $this->getField($name)->setValue($value);
                }
            }
            return $this;
        } else {
            $this->instance = null;
            return $this;
        }
    }

    /**
     * @return \Mindy\Orm\Model|\Mindy\Orm\TreeModel|\Mindy\Orm\IFormModel
     */
    public function getInstance()
    {
        if ($this->instance === null) {
            $modelClass = $this->getModel();
            $this->instance = is_string($modelClass) ? new $modelClass : $modelClass;
        }

        return $this->instance;
    }

    public function delete()
    {
        return $this->getInstance()->delete();
    }

    public function save()
    {
        $instance = $this->getInstance();
        $saved = $instance->save();
        foreach ($this->getInlinesCreate() as $inline) {
            $inline->setAttributes([
                $inline->link => $instance
            ]);
            $inline->save();
        }
        foreach ($this->getInlinesDelete() as $inline) {
            $inline->delete();
        }
        return $saved;
    }

    /**
     * @return \Mindy\Orm\Model
     */
    public function getModel()
    {
        throw new Exception("Not implemented");
    }

    /**
     * @param null|int $extra count of the extra inline forms for render
     * @return array of inline forms
     */
    public function renderInlines($extra = null)
    {
        $instance = $this->getInstance();
        $inlines = [];
        foreach ($this->getInlinesInit() as $params) {
            $link = key($params);
            $inline = $params[$link];

            $name = $inline->classNameShort();
            $models = $inline->getLinkModels([$link => $instance]);
            if (count($models) > 0) {
                $inlines[$name] = [];
                foreach ($models as $linkedModel) {
                    $new = clone $inline;
                    $new->setInstance($linkedModel);
                    $new->exclude = array_merge($inline->exclude, [$link]);
                    $inlines[$name][] = $new;
                }
            }

            /** @var $inline BaseForm */
            if ($extra === null) {
                $extra = 1;
            }

            for ($i = 0; $extra > $i; $i++) {
                $newClean = clone $inline;
                $newClean->cleanAttributes();
                $newClean->setInstance(null);
                $inlines[$name][] = $newClean;
            }
        }
        return $inlines;
    }

    public function getLinkModels(array $attributes)
    {
        return $this->getModel()->objects()->filter($attributes)->all();
    }
}
