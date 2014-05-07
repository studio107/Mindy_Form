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
use Mindy\Core\Object;
use Mindy\Helper\Creator;

abstract class ManagedForm extends Object
{
    public $templates = [
        'block' => 'core/form/management/block.twig',
        'table' => 'core/form/management/table.twig',
        'ul' => 'core/form/management/ul.twig',
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

    public function init()
    {
        $this->_form = Creator::createObject([
            'class' => $this->getFormClass(),
            'instance' => $this->instance
        ]);

        foreach($this->getInlines() as $link => $config) {
            $this->_inlines[$link] = Creator::createObject([
                'class' => $config,
                'link' => $link
            ]);
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

    public function __toString()
    {
        $template = $this->getTemplateFromType($this->defaultTemplateType);
        return (string)$this->render($template);
    }

    public function getTemplateFromType($type)
    {
        if (array_key_exists($type, $this->templates)) {
            $templatePath = $this->getForm()->getTemplatePath();
            $template = $templatePath ? $templatePath . $this->templates[$type] : $this->templates[$type];
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
        return $this->getForm()->getRenderer()->render($template, [
            'form' => $this->getForm(),
            'inlines' => $this->getExistInlines()
        ]);
    }

    public function getExistInlines()
    {
        $inlines = [];
        $model = $this->getForm()->getInstance();
        foreach($this->_inlines as $link => $inline) {
            $name = $inline->getName();
            $inlines[$name] = [];

            $models = $inline->getModel()->objects()->filter([$link => $model])->all();
            foreach($models as $model) {
                $inlines[$name][] = Creator::createObject([
                    'class' => $inline->className(),
                    'instance' => $model,
                    'link' => $link
                ]);
            }

            if($inline->extra > 0) {
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

    public function setData($data)
    {
        $form = $this->getForm();
        $form->setData($data);

        $instance = $form->getInstance();

        $save = [];
        $delete = [];
        foreach($this->_inlines as $link => $sourceInline) {
            $shortClassName = $sourceInline->shortClassName();

            if(array_key_exists($shortClassName, $data)) {
                $count = 0;
                foreach($data[$shortClassName] as $item) {
                    if($sourceInline->max == $count) {
                        break;
                    }

                    $inline = clone $sourceInline;
                    $inline->setData(array_merge([$link => $instance], $item));

                    if(array_key_exists(InlineModelForm::DELETE_KEY, $item)) {
                        $delete[] = $inline;
                    } else {
                        $save[] = $inline;
                    }
                    $count++;
                }
            }
        }
        $this->inlinesData = $save;
        $this->inlinesDelete = $delete;
        return [$save, $delete];
    }

    /**
     * @return string form class
     */
    abstract public function getFormClass();

    /**
     * @return array
     */
    public function getInlines()
    {
        return [];
    }

    /**
     * Возвращает инициализированные inline формы
     * @return InlineForm[]
     */
    public function getInitInlines()
    {
        return $this->_inlines;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $form = $this->getForm();
        if($form->isValid()) {
            if(empty($this->inlinesData)) {
                return true;
            } else {
                $valid = false;
                foreach($this->inlinesData as $inline) {
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
        $this->getForm()->save();

        foreach($this->inlinesData as $inline) {
            $inline->save();
        }

        foreach($this->inlinesDelete as $inline) {
            $inline->delete();
        }
    }
}