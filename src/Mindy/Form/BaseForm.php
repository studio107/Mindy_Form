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

    private static $_templatePath = '';

    /* @var IFormRenderer */
    private static $_renderer;

    private $_id;

    public static $ids = [];

    private $_errors = [];

    private $_fields = [];

    public function init()
    {
        $this->initFields();
    }

    public function getId()
    {
        if ($this->_id) {
            return $this->_id;
        } else {
            $reflect = new ReflectionClass($this);
            $shortName = $reflect->getShortName();
            if (array_key_exists($shortName, self::$ids)) {
                self::$ids[$shortName]++;
            } else {
                self::$ids[$shortName] = 0;
            }

            return $this->_id = $shortName . '_' . self::$ids[$shortName];
        }
    }

    /**
     * Initialize fields
     * @void
     */
    public function initFields()
    {
        $fields = $this->getFields();
        foreach ($fields as $name => $config) {
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
        }

        throw new Exception("Unknown method $name");
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

    /**
     * @param $template
     * @return string
     */
    public function render($template)
    {
        $out = '';
        $r = self::$_renderer;
        foreach ($this->fields as $name => $field) {
            $out .= $r->renderField($name, $field);
        }
        return $r->renderContainer($template);
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
    public function addError($attribute, $error = '')
    {
        $this->_errors[$attribute][] = $error;
    }

    /**
     * Removes errors for all attributes or a single attribute.
     * @param string $attribute attribute name. Use null to remove errors for all attribute.
     */
    public function clearErrors($attribute = null)
    {
        if ($attribute === null) {
            $this->_errors = [];
        } else {
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
        return new ArrayIterator($this->_fields);
    }

    public function count()
    {
        return count($this->_fields);
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->_fields[] = $value;
        } else {
            $this->_fields[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->_fields[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->_fields[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->_fields[$offset]) ? $this->_fields[$offset] : null;
    }
}
