<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 13/09/16
 * Time: 21:50
 */

namespace Mindy\Form;

use Mindy\Creator\Creator;
use Mindy\Orm\Fields\HasManyField;
use Mindy\Orm\Fields\ManyToManyField;

/**
 * Class ModelForm
 * @package Mindy\Form
 */
class ModelForm extends Form
{
    /**
     * @var array
     */
    public $exclude = [];
    /**
     * @var \Mindy\Orm\ModelInterface|FormModelInterface
     */
    protected $model;
    /**
     * @var bool
     */
    protected $initialized = false;

    /**
     * ModelForm constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        if ($this->getModel() && $this->initialized === false) {
            $this->setModel($this->getModel());
        }
    }

    /**
     * @param FormModelInterface $model
     */
    public function setModel($model)
    {
        $this->model = $model;
        $this->initializeForm($model);
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param FormModelInterface|\Mindy\Orm\ModelInterface $model
     */
    private function initializeForm($model)
    {
        if ($this->initialized === false) {
            $fields = $this->getFields();
            $attributes = $model->getAttributes();
            foreach ($model->getMeta()->getFields() as $name => $field) {

                if (in_array($name, $this->exclude)) {
                    continue;
                }

                $modelField = $model->getField($name);
                $field = $modelField->getFormField();

                if ($field === null || $field === false) {
                    continue;
                }

                if (($field instanceof FieldInterface) === false) {
                    $field = Creator::createObject(isset($fields[$name]) ? array_merge($field, $fields[$name]) : $field);
                } else {
                    $field->configure(['name' => $name]);
                }

                if (isset($fields[$name]) && is_array($fields[$name])) {
                    $field->configure($fields[$name]);
                }

                if (array_key_exists($name, $attributes)) {
                    $field->setValue($attributes[$name]);
                } else if ($modelField instanceof HasManyField || $modelField instanceof ManyToManyField) {
                    $field->setValue($modelField->getRelatedModel()->objects()->valuesList(['pk'], true));
                }
                $this->fields[$name] = $field;
            }

            $this->initialized = true;
        }
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasField($name) : bool
    {
        // Foreign fields with auto generated form
        if (array_key_exists($name . '_id', $this->fields)) {
            return true;
        }
        return parent::hasField($name);
    }

    /**
     * @param string $name
     * @return FieldInterface
     */
    public function getField(string $name) : FieldInterface
    {
        // Foreign fields with auto generated form
        if (array_key_exists($name . '_id', $this->fields)) {
            $name .= '_id';
        }
        return parent::getField($name);
    }

    public function getAttributes()
    {
        $attributes = parent::getAttributes();
        foreach ($this->exclude as $name) {
            unset($attributes[$name]);
        }
        return $attributes;
    }

    /**
     * @return bool
     */
    public function save() : bool
    {
        $this->setModelAttributes($this->getAttributes());
        $state = $this->model->save();
        $this->setAttributes($this->model->getAttributes());
        return $state;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function setModelAttributes(array $attributes)
    {
        $this->model->setAttributes($attributes);
        return $this;
    }
}