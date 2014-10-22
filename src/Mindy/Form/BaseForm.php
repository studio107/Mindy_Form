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
use Mindy\Helper\Arr;
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
     * @var array BaseForm[]
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
    /**
     * @var array BaseForm[]
     */
    private $_inlinesCreate = [];
    /**
     * @var array BaseForm[]
     */
    private $_inlinesDelete = [];

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
     * @param null|int $extra count of the extra inline forms for render
     * @return string
     */
    public function render($template, array $fields = [], $extra = null)
    {
        return $this->setRenderFields($fields)->renderInternal($template, [
            'form' => $this,
            'inlines' => $this->renderInlines($extra)
        ]);
    }

    /**
     * @param null|int $extra count of the extra inline forms for render
     * @return array of inline forms
     */
    public function renderInlines($extra = null)
    {
        $inlines = [];
        foreach ($this->getInlinesInit() as $params) {
            $link = key($params);
            $inline = $params[$link];
            /** @var $inline BaseForm */
            if ($extra === null) {
                $extra = 1;
            }

            $forms = [];
            for ($i = 0; $extra > $i; $i++) {
                $forms[] = clone $inline;
            }

            $inlines[$inline->getName()] = $forms;
        }
        return $inlines;
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

    /**
     * Возвращает инициализированные inline формы
     * @return InlineForm[]
     */
    public function getInlinesInit()
    {
        return $this->_inlines;
    }

    public function getLinkModels(array $attributes)
    {
        return $this->getModel()->objects()->filter($attributes)->all();
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
        $isValid = $this->isValidInternal();
        if (count($this->_inlines)) {
            foreach ($this->getInlinesCreate() as $i => $inline) {
                if ($inline->isValid() === false) {
                    $isValid = false;
                    $this->addErrors([
                        $inline->classNameShort() => [
                            $i => $inline->getErrors()
                        ]
                    ]);
                }
            }
            return $isValid;
        } else {
            return $isValid;
        }
    }

    /**
     * @param $attribute
     * @return \Mindy\Form\Fields\Field
     */
    public function getField($attribute)
    {
        return $this->_fields[$attribute];
    }

    public function prepare(array $data, array $files = [])
    {
        return PrepareData::collect($data, $files);
    }

    /**
     * @param array $data
     * @param array $files
     * @return $this
     */
    public function populate(array $data, array $files = [])
    {
        $tmp = empty($files) ? $data : $this->prepare($data, $files);
        if (!isset($tmp[$this->getName()])) {
            return $this;
        }

        $data = $tmp[$this->getName()];
        $this->setAttributes($data);
        return $this;
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

        $sourceInlines = $this->getInlinesInit();
        if (count($sourceInlines) > 0) {
            $this->_inlinesCreate = [];
            foreach ($sourceInlines as $params) {
                $link = key($params);
                $sourceInline = $params[$link];
                /** @var $sourceInline BaseForm */
                /** @var $inline BaseForm */
                if (isset($data[$sourceInline->classNameShort()])) {
                    foreach ($data[$sourceInline->classNameShort()] as $item) {

                        $tmp = Arr::cleanArrays($item);
                        if (empty($tmp)) {
                            continue;
                        }

                        $inline = clone $sourceInline;
                        $inline->setAttributes($item);

                        if (isset($item['_pk'])) {
                            /** @var $inline ModelForm */
                            $modelClass = $inline->getModel();
                            $model = is_string($modelClass) ? new $modelClass : $modelClass;
                            if ($instance = $model->objects()->filter(['pk' => $item['_pk']])->get()) {
                                $inline->setInstance($instance);
                            }
                        }

                        if (array_key_exists('_delete', $item)) {
                            $this->_inlinesDelete[] = $inline;
                        } else {
                            $this->_inlinesCreate[] = $inline;
                        }
                    }
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
        foreach ($this->getInlinesCreate() as $inline) {
            $inline->clearErrors();
        }
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

    /**
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

    /**
     * @return BaseForm[]
     */
    public function getInlinesCreate()
    {
        return $this->_inlinesCreate;
    }

    /**
     * @return BaseForm[]
     */
    public function getInlinesDelete()
    {
        return $this->_inlinesDelete;
    }
}
