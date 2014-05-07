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
 * @date 17/04/14.04.2014 18:14
 */

namespace Mindy\Form;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Exception;
use IteratorAggregate;
use Mindy\Core\Object;
use Mindy\Form\Renderer\IFormRenderer;
use Mindy\Helper\Creator;
use ReflectionClass;

abstract class BaseForm extends Object implements IteratorAggregate, Countable, ArrayAccess
{
    public $fields = [];

    public $templates = [
        'block' => 'core/form/block.twig',
        'table' => 'core/form/table.twig',
        'ul' => 'core/form/ul.twig',
    ];

    public $defaultTemplateType = 'block';

    public $prefix = [];

    private static $_templatePath = '';

    /* @var IFormRenderer */
    private static $_renderer;

    private $_id;

    public static $ids = [];

    private $_errors = [];

    private $_fields = [];

    private $_renderFields = [];

    public function init()
    {
        $this->initFields();
        $this->setRenderFields(array_keys($this->getFieldsInit()));
    }

    public function getName()
    {
        return $this->shortClassName();
    }

    public function getFieldsets()
    {
        return [];
    }

    public function __get($name)
    {
        if(array_key_exists($name, $this->_fields)) {
            return $this->_fields[$name]->getValue();
        } else {
            return parent::__get($name);
        }
    }

    public function __set($name, $value)
    {
        if(array_key_exists($name, $this->_fields)) {
            $this->_fields[$name]->setValue($value);
        } else {
            parent::__set($name, $value);
        }
    }

    public function getId()
    {
        if (!$this->_id) {
            $className = self::className();
            if (array_key_exists($className, self::$ids)) {
                self::$ids[$className]++;
            } else {
                self::$ids[$className] = 0;
            }

            $this->_id = self::shortClassName() . '_' . self::$ids[$className];
        }

        return $this->_id;
    }

    /**
     * Initialize fields
     * @void
     */
    public function initFields()
    {
        $fields = $this->getFields();
        foreach ($fields as $name => $config) {
            if(!is_array($config)) {
                $config = ['class' => $config];
            }
            $field = Creator::createObject(array_merge([
                'name' => $name,
                'form' => $this,
            ], $config));
            $this->_fields[$name] = $field;
        }
    }

    public function __call($name, $arguments)
    {
        $type = strtolower(ltrim($name, 'as'));
        if (isset($this->templates[$type])) {
            $template = $this->getTemplateFromType($type);
            return $this->render($template);
        } else {
            return parent::__call($name, $arguments);
        }
    }

    public static function setRenderer(IFormRenderer $renderer)
    {
        self::$_renderer = $renderer;
    }

    public static function setTemplatePath($path)
    {
        self::$_templatePath = $path;
    }

    public function getFields()
    {
        return [];
    }

    public function __toString()
    {
        $template = $this->getTemplateFromType($this->defaultTemplateType);
        return (string)$this->render($template);
    }

    public function getTemplateFromType($type)
    {
        if (array_key_exists($type, $this->templates)) {
            $template = self::$_templatePath ? self::$_templatePath . $this->templates[$type] : $this->templates[$type];
        } else {
            throw new Exception("Template type {$type} not found");
        }
        return $template;
    }

    public static function getTemplatePath()
    {
        return self::$_templatePath;
    }

    public static function getRenderer()
    {
        return self::$_renderer;
    }

    /**
     * @param $template
     * @param array $fields
     * @return string
     */
    public function render($template, array $fields = [])
    {
        if(!empty($fields)) {
            $this->setRenderFields($fields);
        } else {
            $this->setRenderFields(array_keys($this->getFieldsInit()));
        }
        return self::$_renderer->render($template, ['form' => $this]);
    }

    public function setRenderFields(array $fields)
    {
        $this->_renderFields = [];

        $initFields = $this->getFieldsInit();
        foreach($fields as $name) {
            if(array_key_exists($name, $initFields)) {
                $this->_renderFields[$name] = $initFields[$name];
            }
        }
    }

    public function getRenderFields()
    {
        return $this->_renderFields;
    }

    /**
     * Return initialized fields
     * @return \Mindy\Orm\Fields\Field[]
     */
    public function getFieldsInit()
    {
        return $this->_fields;
    }

    /**
     * Adds a new error to the specified attribute.
     * @param string $attribute attribute name
     * @param string $error new error message
     */
    public function addError($attribute, $error)
    {
        if($this->hasField($attribute)) {
            $this->getField($attribute)->addError($error);
            $this->_errors[$attribute][] = $error;
        }
    }

    public function hasField($attribute)
    {
        return array_key_exists($attribute, $this->_fields);
    }

    /**
     * @param $attribute
     * @return \Mindy\Form\Fields\Field
     */
    public function getField($attribute)
    {
        return $this->_fields[$attribute];
    }

    /**
     * Removes errors for all attributes or a single attribute.
     * @param string $attribute attribute name. Use null to remove errors for all attribute.
     */
    public function clearErrors($attribute = null)
    {
        if ($attribute === null) {
            foreach($this->getFieldsInit() as $field) {
                $field->clearErrors();
            }
            $this->_errors = [];
        } else {
            if($this->hasField($attribute)) {
                $this->getField($attribute)->clearErrors();
            }
            unset($this->_errors[$attribute]);
        }
    }

    /**
     * Returns a value indicating whether there is any validation error.
     * @param string|null $attribute attribute name. Use null to check all attributes.
     * @return boolean whether there is any error.
     */
    public function hasErrors($attribute = null)
    {
        return $attribute === null ? !empty($this->_errors) : isset($this->_errors[$attribute]);
    }

    /**
     * Returns the errors for all attribute or a single attribute.
     * @param string $attribute attribute name. Use null to retrieve errors for all attributes.
     * @property array An array of errors for all attributes. Empty array is returned if no error.
     * The result is a two-dimensional array. See [[getErrors()]] for detailed description.
     * @return array errors for all attributes or the specified attribute. Empty array is returned if no error.
     * Note that when returning errors for all attributes, the result is a two-dimensional array, like the following:
     *
     * ~~~
     * [
     *     'username' => [
     *         'Username is required.',
     *         'Username must contain only word characters.',
     *     ],
     *     'email' => [
     *         'Email address is invalid.',
     *     ]
     * ]
     * ~~~
     *
     * @see getFirstErrors()
     * @see getFirstError()
     */
    public function getErrors($attribute = null)
    {
        if ($attribute === null) {
            return $this->_errors === null ? [] : $this->_errors;
        } else {
            return isset($this->_errors[$attribute]) ? $this->_errors[$attribute] : [];
        }
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $this->clearErrors();

        /* @var $field \Mindy\Orm\Fields\Field */
        $fields = $this->getFieldsInit();
        foreach ($fields as $name => $field) {
            if ($field->isValid() === false) {
                foreach ($field->getErrors() as $error) {
                    $this->addError($name, $error);
                }
            }
        }
        return $this->hasErrors() === false;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $fields = $this->getFieldsInit();
        foreach ($data as $key => $value) {
            if(array_key_exists($key, $fields)) {
                $fields[$key]->setValue($value);
            }
        }
        return $this;
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
        return new ArrayIterator($this->_renderFields);
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
}
