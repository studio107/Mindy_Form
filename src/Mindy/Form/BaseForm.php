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
use Mindy\Helper\Creator;
use Mindy\Helper\Traits\Accessors;
use Mindy\Helper\Traits\Configurator;
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
    use Accessors, Configurator, ValidateObject;

    public $templates = [
        'block' => 'core/form/block.html',
        'table' => 'core/form/table.html',
        'ul' => 'core/form/ul.html',
    ];

    /**
     * @var string
     */
    public $link;
    /**
     * @var string
     */
    public $defaultTemplateType = 'block';
    /**
     * @var array
     */
    private $_exclude = [];
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
     * @var array
     */
    private $_inlines = [];
    /**
     * @var array
     */
    private $_inlineClasses = [];
    /**
     * @var \Mindy\Event\EventManager
     */
    private $_eventManager;
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
        $this->initEvents();
        $this->initInlines();
        $this->setRenderFields(array_keys($this->getFieldsInit()));
    }

    public function initEvents()
    {
        $signal = $this->getEventManager();
        $signal->handler($this, 'beforeValidate', [$this, 'beforeValidate']);
        $signal->handler($this, 'afterValidate', [$this, 'afterValidate']);
    }

    protected function getEventManager()
    {
        if ($this->_eventManager === null) {
            if (class_exists('\Mindy\Base\Mindy')) {
                $this->_eventManager = \Mindy\Base\Mindy::app()->getComponent('signal');
            } else {
                $this->_eventManager = new \Mindy\Event\EventManager();
            }
        }
        return $this->_eventManager;
    }

    /**
     * @return array
     */
    public function setExclude(array $value)
    {
        $this->_exclude = $value;
    }

    /**
     * @return array
     */
    public function getExclude()
    {
        return $this->_exclude;
    }

    /**
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
            return $this->_fields[$name]->getValue();
        } else {
            return $this->__getInternal($name);
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
        $fields = $this->getFields();
        foreach ($fields as $name => $config) {
            if (in_array($name, $this->exclude)) {
                continue;
            }

            if (!is_array($config)) {
                $config = ['class' => $config];
            }
            $field = Creator::createObject(array_merge([
                'name' => $name,
                'form' => $this,
                'prefix' => $this->getPrefix()
            ], $config));
            $this->_fields[$name] = $field;
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
     * @return string
     */
    public function render($template, array $fields = [])
    {
        return $this->setRenderFields($fields)->renderInternal($template, [
            'form' => $this,
            'inlines' => $this->getInlinesInit()
        ]);
    }

    abstract public function renderInternal($template, array $params);

    /**
     * Возвращает инициализированные inline формы
     * @return InlineForm[]
     */
    public function getInlinesInit()
    {
        return $this->_inlines;
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
                $this->_renderFields[$name] = $initFields[$name];
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
     * @return \Mindy\Orm\Fields\Field[]
     */
    public function getFieldsInit()
    {
        return $this->_fields;
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
     * @return \Mindy\Form\BaseForm[]
     */
    public function getInlines()
    {
        return [];
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

    private function initInlines()
    {
        $inlines = $this->getInlines();

        foreach ($inlines as $params) {
            if (!is_array($params)) {
                throw new Exception("Incorrect inline configuration");
            }
            $link = key($params);
            $className = $params[$link];
            $inline = Creator::createObject([
                'class' => $className,
                'link' => $link,
                'prefix' => $this->getName()
            ]);
            if (!in_array($link, $inline->exclude)) {
                $inline->addExclude($link);
            }
            $this->_inlines[][$link] = $inline;
            $this->_inlineClasses[$className::classNameShort()] = $className;
        }
    }

    public function addExclude($name)
    {
        $this->_exclude[] = $name;
    }
}
