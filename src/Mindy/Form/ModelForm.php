<?php

namespace Mindy\Form;

use Exception;
use Mindy\Form\Fields\DeleteInlineField;
use Mindy\Form\Fields\HiddenField;
use Mindy\Helper\Creator;
use Mindy\Locale\Translate;
use Mindy\Orm\Fields\FileField;
use Mindy\Orm\Manager;
use Mindy\Orm\Model;
use Mindy\Orm\QuerySet;

/**
 * Class ModelForm
 * @package Mindy\Form
 */
class ModelForm extends BaseForm
{
    public $ormClass = '\Mindy\Orm\Model';
    /**
     * @var \Mindy\Orm\Model
     */
    protected $_instance;
    /**
     * @var \Mindy\Orm\Model
     */
    private $_model;

    /**
     * Initialize fields
     * @void
     */
    public function initFields()
    {
        $instance = $this->getInstance();
        $model = $this->getModel();
        // if prefix available - inline form
        $prefix = $this->getPrefix();
        $fields = $this->getFields();

        foreach ($model->getFieldsInit() as $name => $field) {
            if ($field->editable === false || is_a($field, Model::$autoField) || in_array($name, $this->getExclude())) {
                continue;
            }

            if (array_key_exists($name, $fields)) {
                $this->_fields[$name] = Creator::createObject(array_merge([
                    'name' => $name,
                    'form' => $this,
                    'prefix' => $prefix,
                    'choices' => $field->choices
                ], is_array($fields[$name]) ? $fields[$name] : ['class' => $fields[$name]]));
            } else {
                $modelField = $field->setModel($instance ? $instance : $model)->getFormField($this);
                if ($modelField) {
                    $this->_fields[$name] = $modelField;
                }
            }

            if ($instance) {
                $value = $instance->{$name};
                if ($value instanceof FileField) {
                    $value = $value->getUrl();
                }
                $this->_fields[$name]->setValue($value);
            }
        }


        foreach ($fields as $name => $config) {
            if (isset($this->_fields[$name]) || in_array($name, $this->getExclude())) {
                continue;
            }

            if (!is_array($config)) {
                $config = ['class' => $config];
            }

            $this->_fields[$name] = Creator::createObject(array_merge([
                'name' => $name,
                'form' => $this,
                'prefix' => $prefix
            ], is_array($config) ? $config : ['class' => $config]));

            if ($instance && $instance->hasField($name)) {
                $value = $instance->{$name};
                if ($value instanceof FileField) {
                    $value = $value->getUrl();
                }
                $this->_fields[$name]->setValue($value);
            }
        }
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setModelAttributes(array $data)
    {
        $instance = $this->getInstance();
        if ($instance === null) {
            $instance = $this->getModel();
            $this->_instance = $instance;
        }
        $instance->setAttributes($data);
        return $this;
    }

    /**
     * @param $model \Mindy\Orm\Model
     * @return $this
     * @throws \Exception
     */
    public function setInstance(\Mindy\Orm\Model $model)
    {
        $this->_instance = $model;
    }

    /**
     * Clear instance variable
     */
    public function clearInstance()
    {
        $this->_instance = null;
    }

    /**
     * @return \Mindy\Orm\Model|\Mindy\Orm\TreeModel|null
     */
    public function getInstance()
    {
        return $this->_instance;
    }

    public function delete()
    {
        return $this->getInstance()->delete();
    }

    public function save()
    {
        $this->setModelAttributes($this->cleanedData);
        return $this->getInstance()->save();
    }

    /**
     * @throws \Exception
     * @return \Mindy\Orm\Model
     */
    public function getModel()
    {
        if ($this->_model === null) {
            throw new Exception("Not implemented");
        }
        return $this->_model;
    }

    public function setModel(Model $model)
    {
        $this->_model = $model;
    }

    protected function populateFromInstance(\Mindy\Orm\Model $model)
    {
        foreach ($this->getFieldsInit() as $name => $field) {
            if ($model->hasField($name)) {
                $value = $model->{$name};
                if ($value instanceof FileField) {
                    $value = $value->getUrl();
                }
                $this->_fields[$name]->setValue($value);
            }
        }

        if ($this->getPrefix()) {
            $this->getField('_pk')->setValue($model->pk);
        }
    }

    /**
     * @param array $attributes
     * @return \Mindy\Orm\Manager|\Mindy\Orm\QuerySet
     */
    public function getLinkModels(array $attributes)
    {
        return $this->getModel()->objects()->filter($attributes);
    }
}
