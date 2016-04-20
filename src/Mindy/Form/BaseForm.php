<?php

namespace Mindy\Form;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Exception;
use IteratorAggregate;
use Mindy\Helper\Collection;
use Mindy\Helper\Creator;
use Mindy\Helper\Traits\Accessors;
use Mindy\Helper\Traits\Configurator;
use Mindy\Utils\RenderTrait;
use Mindy\Validation\Interfaces\IValidateObject;
use Mindy\Validation\Traits\ValidateObject;

/**
 * Class BaseForm
 * @package Mindy\Form
 * @method string asBlock(array $renderFields = [])
 * @method string asUl(array $renderFields = [])
 * @method string asTable(array $renderFields = [])
 */
abstract class BaseForm implements IteratorAggregate, Countable, ArrayAccess, IValidateObject
{
    use Accessors, Configurator, ValidateObject, RenderTrait;

    public $templates = [
        'block' => 'core/form/block.html',
        'table' => 'core/form/table.html',
        'ul' => 'core/form/ul.html',
    ];
    /**
     * @var string
     */
    public $defaultTemplateType = 'block';
    /**
     * @var array
     */
    public $exclude = [];
    /**
     * @var array
     */
    private $_extraExclude = [];
    /**
     * @var string
     */
    private $_prefix;
    /**
     * @var int
     */
    private $_id;
    /**
     * @var array
     */
    public static $ids = [];
    /**
     * @var \Mindy\Form\Fields\Field[]
     */
    protected $_fields = [];
    /**
     * @var array
     */
    protected $_renderFields = [];

    public function init()
    {
        $this->initFields();
        $this->setRenderFields(array_keys($this->getFieldsInit()));
    }

    protected function getEventManager()
    {
        /**
         * @var \Mindy\Event\EventManager
         */
        static $_eventManager;
        if ($_eventManager === null) {
            if (class_exists('\Mindy\Base\Mindy')) {
                $_eventManager = \Mindy\Base\Mindy::app()->getComponent('signal');
            } else {
                $_eventManager = new \Mindy\Event\EventManager();
            }
        }
        return $_eventManager;
    }

    /**
     * @param array $value
     * @return array
     */
    public function setExclude(array $value)
    {
        $this->exclude = $value;
    }

    /**
     * @param array $value
     * @return array
     */
    public function setExtraExclude(array $value)
    {
        $this->_extraExclude = $value;
    }

    /**
     * @return array
     */
    public function getExclude()
    {
        return array_merge($this->_extraExclude, $this->exclude);
    }

    /**
     * @param $prefix
     * @return array
     */
    public function setPrefix($prefix)
    {
        $this->_prefix = $prefix;
    }

    /**
     * @return array
     */
    public function getPrefix()
    {
        return $this->_prefix;
    }

    /**
     * @param $owner BaseForm
     */
    public function beforeValidate($owner)
    {
    }

    /**
     * @param $owner BaseForm
     */
    public function afterValidate($owner)
    {
    }

    public function getName()
    {
        return $this->classNameShort();
    }

    public function getFieldsets()
    {
        return [];
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->_fields)) {
            return $this->_fields[$name];
        } else {
            return $this->__getInternal($name);
        }
    }

    public function __clone()
    {
        $this->_id = null;

        foreach ($this->_fields as $name => $field) {
            $newField = clone $field;
            $newField->setForm($this);
            $this->_fields[$name] = $newField;
        }
    }

    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->_fields)) {
            $this->_fields[$name]->setValue($value);
        } else {
            $this->__setInternal($name, $value);
        }
    }

    public function getId()
    {
        if ($this->_id === null) {
            $className = self::className();
            if (array_key_exists($className, self::$ids)) {
                self::$ids[$className]++;
            } else {
                self::$ids[$className] = 0;
            }

            $this->_id = self::$ids[$className];
        }

        return $this->_id;
    }

    /**
     * Initialize fields
     * @void
     */
    public function initFields()
    {
        $prefix = $this->getPrefix();
        $fields = $this->getFields();
        foreach ($fields as $name => $config) {
            if (in_array($name, $this->getExclude())) {
                continue;
            }

            if (!is_array($config)) {
                $config = ['class' => $config];
            }

            $this->_fields[$name] = Creator::createObject(array_merge([
                'name' => $name,
                'form' => $this,
                'prefix' => $prefix,
            ], $config));
        }
    }

    public function __call($name, $arguments)
    {
        $type = strtolower(ltrim($name, 'as'));
        if (isset($this->templates[$type])) {
            $template = $this->getTemplateFromType($type);
            return call_user_func_array([$this, 'render'], array_merge([$template], $arguments));
        } else {
            return $this->__callInternal($name, $arguments);
        }
    }

    public function getFields()
    {
        return [];
    }

    public function __toString()
    {
        $template = $this->getTemplateFromType($this->defaultTemplateType);
        try {
            return (string)$this->render($template);
        } catch (Exception $e) {
            return (string)$e;
        }
    }

    public function getTemplateFromType($type)
    {
        if (array_key_exists($type, $this->templates)) {
            $template = $this->templates[$type];
        } else {
            throw new Exception("Template type {$type} not found");
        }
        return $template;
    }

    /**
     * @param $template
     * @param array $fields
     * @param null|int $extra count of the extra inline forms for render
     * @return string
     */
    public function render($template, array $fields = [], $extra = null)
    {
        return $this->setRenderFields($fields)->renderInternal($template, [
            'form' => $this,
        ]);
    }

    /**
     * @param $template
     * @param array $params
     * @return string
     */
    public function renderInternal($template, array $params)
    {
        return self::renderTemplate($template, $params);
    }

    public function renderType($templateType, array $fields = [], $extra = null)
    {
        $template = $this->getTemplateFromType($templateType);
        return $this->setRenderFields($fields)->renderInternal($template, [
            'form' => $this,
        ]);
    }

    /**
     * Set fields for render
     * @param array $fields
     * @throws \Exception
     * @return $this
     */
    public function setRenderFields(array $fields = [])
    {
        if (empty($fields)) {
            $fields = array_keys($this->getFieldsInit());
        }
        $this->_renderFields = [];
        $initFields = $this->getFieldsInit();
        foreach ($fields as $name) {
            if (in_array($name, $this->exclude)) {
                continue;
            }
            if (array_key_exists($name, $initFields)) {
                $this->_renderFields[] = $name;
            } else {
                throw new Exception("Field $name not found");
            }
        }
        return $this;
    }

    public function getRenderFields()
    {
        return $this->_renderFields;
    }

    /**
     * Return initialized fields
     * @return \Mindy\Form\Fields\Field[]
     */
    public function getFieldsInit()
    {
        return $this->_fields;
    }

    /**
     * @param string $attribute
     * @return bool
     */
    public function hasField($attribute)
    {
        return array_key_exists($attribute, $this->_fields);
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $this->isValidInternal();
        return $this->hasErrors() === false;
    }

    /**
     * @param $attribute
     * @return \Mindy\Form\Fields\Field
     */
    public function getField($attribute)
    {
        return $this->_fields[$attribute];
    }

    public function prepare(array $data, array $files = [], $fixFiles = true)
    {
        return PrepareData::collect($data, $files, $fixFiles);
    }

    /**
     * @param array|Collection $data
     * @param array|Collection $files
     * @return $this
     */
    public function populate($data, $files = [])
    {
        if ($data instanceof Collection) {
            $data = $data->all();
        } else if (!is_array($data)) {
            throw new Exception('$data must be a array');
        }

        $fixFiles = true;
        if ($files instanceof Collection) {
            $fixFiles = false;
            $files = $files->all();
        }

        $tmp = empty($files) ? $data : $this->prepare($data, $files, $fixFiles);
        if (!isset($tmp[$this->classNameShort()])) {
            return $this;
        }

        $data = $tmp[$this->classNameShort()];
        $this->setAttributes($data);
        return $this;
    }

    /**
     * @param \Mindy\Form\BaseForm|\Mindy\Form\ModelForm $owner
     * @param array $attributes
     * @return array
     */
    public function beforeSetAttributes($owner, array $attributes)
    {
        return $attributes;
    }

    public function afterOwnerSave($owner)
    {
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setAttributes(array $data)
    {
        $fields = $this->getFieldsInit();
        if (empty($data)) {
            foreach ($fields as $field) {
                $field->setValue(null);
            }
        } else {
            foreach ($data as $key => $value) {
                if (array_key_exists($key, $fields)) {
                    $fields[$key]->setValue($value);
                }
            }
        }
        return $this;
    }

    /**
     * Removes errors for all attributes or a single attribute.
     * @param string $attribute attribute name. Use null to remove errors for all attribute.
     */
    public function clearErrors($attribute = null)
    {
        $this->clearErrorsInternal($attribute);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return \Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        $fields = [];
        foreach ($this->_renderFields as $key) {
            $fields[$key] = $this->_fields[$key];
        }
        return new ArrayIterator($fields);
    }

    public function count()
    {
        return count($this->_renderFields);
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->_renderFields[] = $value;
        } else {
            $this->_renderFields[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->_renderFields[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->_renderFields[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->_renderFields[$offset]) ? $this->_renderFields[$offset] : null;
    }

    /**
     * @DEPRECATED
     * @param $name
     */
    public function addExclude($name)
    {
        $this->exclude[] = $name;
    }

    /**
     * @return $this
     */
    public function cleanAttributes()
    {
        $fields = $this->getFieldsInit();
        foreach ($fields as $field) {
            $field->setValue(null);
        }
        return $this;
    }

    /**
     * Return form attributes
     * @return array
     */
    public function getAttributes()
    {
        $attributes = [];
        foreach ($this->getFieldsInit() as $name => $field) {
            $attributes[$name] = $field->getValue();
        }
        return $attributes;
    }

    public function getJsonErrors()
    {
        $data = [];
        foreach ($this->getErrors() as $name => $errors) {
            $data[$this->getField($name)->getHtmlName()] = $errors;
        }
        return $data;
    }
}
