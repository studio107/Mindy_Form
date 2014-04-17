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

use ArrayIterator;
use Exception;
use IteratorAggregate;
use Mindy\Form\Renderer\IFormRenderer;
use Mindy\Helper\Creator;

abstract class Form implements IteratorAggregate
{
    public $fields = [];

    public $templates = [
        'block' => [
            "<p class='form-label'>{label}</p><p>{value}</p><span class='hint'>{hint}</span><p class='error'>{error}</p>",
            "section",
            "section",
            ['class' => "form-row"]
        ],
        'table' => [
            "<td class='form-td-label'>{label}</td><td>{value}<div class='hint'>{hint}</div>{error}</td>",
            "table",
            "tr",
            ['class' => "form-table"]
        ],
        'ul' => [
            "{label}{value}<br/><span class='hint'>{hint}</span>{error}",
            "ul",
            "li",
            ["class" => "form-ul"]
        ]
    ];

    public $defaultTemplateType = 'block';

    private static $_templatePath = '';

    /* @var IFormRenderer */
    private static $_renderer;

    public function __construct()
    {
        $this->initFields();
    }

    public function initFields()
    {
        $fields = $this->getFields();
        foreach($fields as $name => $config) {
            $field = Creator::createObject($config);
            $field->setName($name);
            $field->setForm($this);
            $this->fields[$name] = $field;
        }
    }

    public function __call($name, $arguments)
    {
        $type = strtolower(ltrim($name, 'as'));
        if(isset($this->templates[$type])) {
            $template = $this->getTemplateFromType($type);
            return $this->render($template);
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
        return (string) $this->render($this->getTemplateFromType($this->defaultTemplateType));
    }

    public function getTemplateFromType($type)
    {
        if(array_key_exists($type, $this->templates)) {
            $template = self::$_templatePath ? self::$_templatePath . $this->templates[$type] : $this->templates[$type];
        } else {
            throw new Exception("Template type {$type} not found");
        }
        return $template;
    }

    /**
     * @param null $activeForm MActiveForm
     * @param string $template for formatting output
     * @return string
     */
    public function renderTemplate($template = '{label}{value}{error}{hint}', $block, $htmlOptions = [])
    {
        $this->populateFields();
        $fields = [];

        foreach ($this->fields as $attribute => $field) {
            $fields[$attribute] = $this->renderField($template, $this->renderFields[$attribute]);
        }

        return $this->parseFieldsets($fields, $container, $block, $htmlOptions);
    }

    /**
     * @param $template
     * @return string
     */
    public function render($template)
    {
        $out = '';
        $r = self::$_renderer;
        foreach($this->fields as $name => $field) {
            $out .= $r->renderField($template, $field);
        }
        return $r->renderContainer($out);
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
        return new ArrayIterator($this->fields);
    }
}
