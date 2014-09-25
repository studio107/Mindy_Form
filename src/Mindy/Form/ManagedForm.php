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
use Mindy\Base\Mindy;
use Mindy\Helper\Arr;
use Mindy\Helper\Creator;
use Mindy\Helper\File;
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
            'class' => $this->getFormClass()
        ]);

        if ($this->instance) {
            $this->_form->setInstance($this->instance);
        }

        foreach ($this->getInlines() as $link => $className) {
            $this->_inlines[$link] = Creator::createObject([
                'class' => $className,
                'link' => $link,
            ]);
            if(!in_array($link, $this->_inlines[$link]->exclude)) {
                $this->_inlines[$link]->exclude[] = $link;
            }
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
                    $inlines[$name][] = Creator::createObject([
                        'class' => $inline->className(),
                        'link' => $link,
                        'instance' => $model,
                        'exclude' => array_merge($inline->exclude, [$link])
                    ]);
                }
            }

            if (
                $extra && $inline->extra > 0 && (
                    array_key_exists($name, $inlines) &&
                    count($inlines[$name]) != $inline->extra &&
                    count($inlines[$name]) < $inline->max || array_key_exists($name, $inlines) === false
                )
            ) {
                foreach (range(1, $inline->extra) as $number) {
                    $inlines[$name][] = Creator::createObject([
                        'class' => $inline->className(),
                        'link' => $link,
                        'isExtra' => true,
                        'exclude' => array_merge($inline->exclude, [$link])
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

    public function setAttributes(array $data, array $files = [])
    {
        $form = $this->getForm();
        $files = Arr::cleanArrays(File::fixMultiFile($files));
        $data = array_merge_recursive($data, $files);
        $form->setAttributes($data);
        $instance = $form->getInstance();
        $signal = Mindy::app()->signal;

        $save = [];
        $delete = [];

        $inlinesData = array_flip($this->getInlines());
        $inlines = $this->getInlinesExist(false);
        foreach ($this->_inlineClasses as $classNameShort => $class) {
            if (array_key_exists($classNameShort, $data)) {
                $cleanData = $data[$classNameShort];
                $count = 0;
                foreach ($cleanData as $item) {
                    $shortName = explode('\\', $class);
                    $shortName = end($shortName);
                    $link = $inlinesData[$class];
                    if (isset($inlines[$shortName]) && count($inlines[$shortName]) > 0) {
                        $inline = array_shift($inlines[$shortName]);
                    } else {
                        $inline = Creator::createObject(['class' => $class, 'link' => $link]);
                    }

                    if(isset($item['_pk']) && !empty($item['_pk'])) {
                        $modelClass = $inline->getModel();
                        $model = is_string($modelClass) ? new $modelClass : $modelClass;
                        $modelInstance = $model->objects()->filter(['pk' => $item['_pk']])->get();
                        if($modelInstance) {
                            $inline->setInstance($modelInstance);
                        }
                    }

                    $results = $signal->send($inline, 'beforeSetAttributes', $instance, $item);
                    $item = $results->getLast()->value;

                    if(array_key_exists(InlineModelForm::DELETE_KEY, $item) === false) {
                        $tmp = Arr::cleanArrays($item);
                        if(empty($tmp)) {
                            continue;
                        }
                    }

                    if (array_key_exists(InlineModelForm::DELETE_KEY, $item) && !empty($item[InlineModelForm::DELETE_KEY])) {
                        $delete[] = $inline;
                    } else {
                        unset($item[InlineModelForm::DELETE_KEY]);
                        if(empty($item)) {
                            continue;
                        }
                        $inline->setAttributes(array_merge([$link => $instance], $item));
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
    public function getFormClass()
    {
        return ModelForm::className();
    }

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
        $instance = $this->getForm()->getInstance();
        $signal = Mindy::app()->signal;

        $merged = array_merge($this->inlinesData, $this->inlinesDelete);
        array_map(function($inline) use ($signal, $instance) {
            $signal->send($inline, 'beforeOwnerSave', $instance);
        }, $merged);

        $r = $this->getForm()->save();

        array_map(function($inline) use ($signal, $instance) {
            if (is_a($inline, InlineModelForm::className())) {
                $link = $inline->link;
                if ($inline->getInstance()->hasAttribute($link) || $inline->getInstance()->hasField($link)) {
                    $inline->getInstance()->{$link} = $instance->pk;
                }
            }
        }, $merged);

        array_map(function($inline) use ($signal, $instance) {
            $signal->send($inline, 'afterOwnerSave', $instance);
        }, $merged);

        foreach ($this->inlinesData as $inline) {
            $r = $inline->save();
        }

        foreach ($this->inlinesDelete as $inline) {
            $inline->delete();
        }

        return $r;
    }
}
