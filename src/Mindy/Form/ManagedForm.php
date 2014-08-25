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
 * @date 06/05/14.05.2014 20:00
 */

namespace Mindy\Form;


use Exception;
use Mindy\Helper\Arr;
use Mindy\Helper\Creator;
use Mindy\Helper\Traits\Accessors;
use Mindy\Helper\Traits\Configurator;
use Mindy\Utils\RenderTrait;


abstract class ManagedForm
{
    use Accessors, Configurator, RenderTrait;

    public $templates = [
        'block' => 'core/form/management/block.html',
        'table' => 'core/form/management/table.html',
        'ul' => 'core/form/management/ul.html',
    ];

    public $defaultTemplateType = 'block';

    public $instance;

    /**
     * \Mindy\Form\Form|\Mindy\Form\ModelForm
     */
    private $_form;

    /**
     * @var \Mindy\Form\InlineForm[]|\Mindy\Form\InlineModelForm[]
     */
    private $_inlines = [];

    /**
     * @var \Mindy\Form\InlineForm[]|\Mindy\Form\InlineModelForm[]
     */
    public $inlinesData = [];

    /**
     * @var \Mindy\Form\InlineForm[]|\Mindy\Form\InlineModelForm[]
     */
    public $inlinesDelete = [];

    /**
     * @var array
     */
    private $_inlineClasses = [];

    public function init()
    {
        $this->_form = Creator::createObject([
            'class' => $this->getFormClass(),
        ]);

        if ($this->instance) {
            $this->_form->setInstance($this->instance);
        }

        foreach ($this->getInlines() as $link => $className) {
            $this->_inlines[$link] = Creator::createObject([
                'class' => $className,
                'link' => $link
            ]);
        }

        foreach ($this->getInlines() as $link => $class) {
            $this->_inlineClasses[$class::classNameShort()] = $class;
        }
    }

    public function __call($name, $arguments)
    {
        if (strpos($name, 'as') === 0) {
            $type = strtolower(ltrim($name, 'as'));
            if (isset($this->templates[$type])) {
                $template = $this->getTemplateFromType($type);
                return $this->render($template);
            }
        }
        return $this->__callInternal($name, $arguments);
    }

    public function __toString()
    {
        $template = $this->getTemplateFromType($this->defaultTemplateType);
        return (string)$this->render($template);
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
     * @return string
     */
    public function render($template)
    {
        return $this->renderTemplate($template, [
            'form' => $this->getForm(),
            'inlines' => $this->getInlinesExist()
        ]);
    }

    public function getInlinesExist($extra = true)
    {
        $inlines = [];
        $model = $this->getForm()->getInstance();
        foreach ($this->_inlines as $link => $inline) {
            $name = $inline->getName();
            $models = $inline->getLinkModels([$link => $model]);
            if (count($models) > 0) {
                $inlines[$name] = [];
                foreach ($models as $model) {
                    $i = Creator::createObject([
                        'class' => $inline->className(),
                        'link' => $link,
                        'instance' => $model
                    ]);
                    $inlines[$name][] = $i;
                }
            }
            if ($extra && $inline->extra > 0 &&
                (
                    array_key_exists($name, $inlines) && count($inlines[$name]) != $inline->extra ||
                    array_key_exists($name, $inlines) === false
                )
            ) {
                foreach (range(1, $inline->extra) as $number) {
                    $inlines[$name][] = Creator::createObject([
                        'class' => $inline->className(),
                        'link' => $link
                    ]);
                }
            }
        }
        return $inlines;
    }

    public function getInstance()
    {
        return $this->getForm()->getInstance();
    }

    public function setInstance($instance)
    {
        $this->getForm()->setInstance($instance);
        return $this;
    }

    /**
     * @return \Mindy\Form\Form|\Mindy\Form\ModelForm
     */
    public function getForm()
    {
        return $this->_form;
    }

    public function setAttributes($data)
    {
        $form = $this->getForm();
        $form->setAttributes($data);

        $instance = $form->getInstance();

        $save = [];
        $delete = [];

        $inlinesData = array_flip($this->getInlines());
        $inlines = $this->getInlinesExist();
        foreach ($this->_inlineClasses as $classNameShort => $class) {
            if (array_key_exists($classNameShort, $data)) {
                $cleanData = Arr::cleanArrays($data[$classNameShort]);
                $count = 0;
                foreach ($cleanData as $item) {
                    $link = $inlinesData[$class];
                    if (count($inlines) > 0 && isset($inlines[$class]) && $inlines[$class]) {
                        $inline = array_shift($inlines[$class]);
                    } else {
                        $inline = Creator::createObject([
                            'class' => $class,
                            'link' => $link
                        ]);
                    }

                    $inline->setAttributes(array_merge([$link => $instance], $item));

                    if (array_key_exists(InlineModelForm::DELETE_KEY, $item)) {
                        $delete[] = $inline;
                    } else {
                        $save[] = $inline;
                    }

                    $count++;
                    if ($inline->max == $count) {
                        break;
                    }
                }
            }
        }

        $this->inlinesData = $save;
        $this->inlinesDelete = $delete;
        return [$save, $delete];
    }

    public function getErrors()
    {
        return $this->getForm()->getErrors();
    }

    /**
     * @return string form class
     */
    abstract public function getFormClass();

    /**
     * @return \Mindy\Form\InlineModelForm[]
     */
    public function getInlines()
    {
        return [];
    }

    /**
     * Возвращает инициализированные inline формы
     * @return InlineForm[]
     */
    public function getInlinesInit()
    {
        return $this->_inlines;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $form = $this->getForm();
        if ($form->isValid()) {
            if (empty($this->inlinesData)) {
                return true;
            } else {
                $valid = false;
                foreach ($this->inlinesData as $inline) {
                    $valid = $inline->isValid();
                }
                return $valid;
            }
        } else {
            return false;
        }
    }

    /**
     * @void
     */
    public function save()
    {
        $r = $this->getForm()->save();

        foreach ($this->inlinesData as $inline) {
            $r = $inline->save();
        }

        foreach ($this->inlinesDelete as $inline) {
            $inline->delete();
        }

        return $r;
    }
}
