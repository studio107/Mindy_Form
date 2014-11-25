<?php

/**
 * All rights reserved.
 *
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 17/04/14.04.2014 18:20
 */

namespace Mindy\Form;

use Exception;
use Mindy\Form\Fields\DeleteInlineField;
use Mindy\Form\Fields\HiddenField;
use Mindy\Helper\Creator;
use Mindy\Locale\Translate;
use Mindy\Orm\Fields\FileField;
use Mindy\Orm\Manager;
use Mindy\Orm\Model;
use Mindy\Orm\QuerySet;

class ModelForm extends BaseForm
{
    public $ormClass = '\Mindy\Orm\Model';
    /**
     * @var \Mindy\Orm\Model
     */
    protected $instance;

    /**
     * Initialize fields
     * @void
     */
    public function initFields()
    {
        parent::initFields();

        // if prefix available - inline form
        $prefix = $this->getPrefix();

        $instance = $this->getInstance();
        foreach ($instance->getFieldsInit() as $name => $field) {
            if ($field->editable === false || is_a($field, Model::$autoField) || in_array($name, $this->exclude)) {
                continue;
            }

            $modelField = $field->setModel($instance)->getFormField($this);
            if ($modelField && !isset($this->_fields[$name])) {
                $this->_fields[$name] = $modelField;
            }

            $value = $instance->{$name};
            if ($value instanceof FileField) {
                $value = $value->getUrl();
            }
            $this->_fields[$name]->setValue($value);
        }

        if ($prefix) {
            $this->_fields['_pk'] = Creator::createObject(array_merge([
                'class' => HiddenField::className(),
                'name' => '_pk',
                'form' => $this,
                'value' => $this->getInstance()->pk,
                'prefix' => $prefix,
                'html' => [
                    'class' => '_pk'
                ]
            ]));
            $this->_fields['_changed'] = Creator::createObject(array_merge([
                'class' => HiddenField::className(),
                'name' => '_changed',
                'form' => $this,
                'prefix' => $prefix,
                'html' => [
                    'class' => '_changed'
                ]
            ]));
            $this->_fields['_delete'] = Creator::createObject(array_merge([
                'class' => DeleteInlineField::className(),
                'name' => '_delete',
                'form' => $this,
                'label' => Translate::getInstance()->t('form', 'Delete'),
                'value' => $this->getInstance()->pk,
                'prefix' => $prefix,
                'html' => [
                    'class' => '_delete'
                ]
            ]));
        }
    }

    /**
     * @return bool
     */
    public function isValid(array $ignore = [])
    {
        $this->clearErrors();

        /* @var $field \Mindy\Form\Fields\Field */
        $fields = $this->getFieldsInit();

        foreach ($fields as $name => $field) {
            if (in_array($name, $ignore)) {
                continue;
            }

            if (method_exists($this, 'clean' . ucfirst($name))) {
                $value = call_user_func([$this, 'clean' . ucfirst($name)], $field->getValue());
                $field->setValue($value);
            }

            if ($field->isValid() === false) {
                foreach ($field->getErrors() as $error) {
                    $this->addError($name, $error);
                }
            }

            $this->cleanedData[$name] = $field->getValue();
        }

        /*
        if (!$instance->isValid()) {
            foreach ($instance->getErrors() as $key => $errors) {
                // @TODO: duplication errors (email validation, for example)
                if (!$this->hasErrors($key)) {
                    foreach ($errors as $error) {
                        if (array_key_exists($key, $fields)) {
                            $this->addError($key, $error);
                            $fields[$key]->addError($error);
                        }
                    }
                }
            }
        }
        */

        return $this->hasErrors() === false && $this->isValidInlines();
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setAttributes(array $data)
    {
        parent::setAttributes($data);
        $this->getInstance()->setAttributes($data);
        return $this;
    }

    /**
     * @param $model \Mindy\Orm\Model
     * @return $this
     * @throws \Exception
     */
    public function setInstance($model)
    {
        if (is_a($model, $this->ormClass)) {
            $this->instance = $model;
            if ($this->getPrefix()) {
                $this->getField('_pk')->setValue($model->pk);
            }
            /* @var $model \Mindy\Orm\Model */
            foreach ($model->getFieldsInit() as $name => $field) {
                if (is_a($field, $model::$autoField)) {
                    continue;
                }

                if ($this->hasField($name)) {
                    $value = $model->{$name};
                    if ($value instanceof FileField) {
                        $value = $value->getUrl();
                    }
                    $this->getField($name)->setValue($value);
                }
            }
            return $this;
        } else {
            $this->instance = null;
            return $this;
        }
    }

    /**
     * @return \Mindy\Orm\Model|\Mindy\Orm\TreeModel|\Mindy\Orm\IFormModel
     */
    public function getInstance()
    {
        if ($this->instance === null) {
            $modelClass = $this->getModel();
            $this->instance = is_string($modelClass) ? new $modelClass : $modelClass;
        }

        return $this->instance;
    }

    public function delete()
    {
        return $this->getInstance()->delete();
    }

    public function save()
    {
        $instance = $this->getInstance();
        $saved = $instance->save();

        $inlineCreate = $this->getInlinesCreate();
        $inlineSaved = true;
        foreach ($inlineCreate as $inline) {
            $inline->setAttributes([
                $inline->link => $instance
            ]);

            if (($inline->isValid() && $inline->save()) === false) {
                $inlineSaved = false;
            }
        }

        foreach ($this->getInlinesDelete() as $inline) {
            $inline->delete();
        }

        return $saved && $inlineSaved;
    }

    /**
     * @return \Mindy\Orm\Model
     */
    public function getModel()
    {
        throw new Exception("Not implemented");
    }

    /**
     * @param null|int $extra count of the extra inline forms for render
     * @return array of inline forms
     */
    public function renderInlines($extra = 1)
    {
        if ($extra <= 0) {
            $extra = 1;
        }

        $instance = $this->getInstance();
        $inlines = [];
        $excludeModels = [];
        if ($this->_saveInlineFailed) {
            foreach ($this->getInlinesCreate() as $createInline) {
                $name = $createInline->getName();
                if (!isset($inlines[$name])) {
                    $inlines[$name] = [];
                }

                $createInstance = $createInline->getInstance();
                if ($createInstance->getIsNewRecord() === false) {
                    $excludeModels[] = $createInstance->pk;
                }
                $inlines[$name][] = $createInline;
            }
        }

        foreach ($this->getInlinesInit() as $params) {
            $link = key($params);
            $inline = $params[$link];

            $name = $inline->getName();
            $qs = $inline->getLinkModels([$link => $instance]);
            if ($qs instanceof QuerySet || $qs instanceof Manager) {
                if (count($excludeModels) > 0) {
                    $qs->exclude(['pk__in' => $excludeModels]);
                }
                $models = $qs->all();
            } else {
                $models = [];
            }
            if (count($models) > 0) {
                if (!isset($inlines[$name])) {
                    $inlines[$name] = [];
                }

                foreach ($models as $linkedModel) {
                    $new = clone $inline;
                    $new->cleanAttributes();
                    $new->setInstance($linkedModel);
                    $new->exclude = array_merge($inline->exclude, [$link]);
                    $inlines[$name][] = $new;
                }
            }

            /** @var $inline BaseForm */
            for ($i = 0; $extra > $i; $i++) {
                $newClean = clone $inline;
                $newClean->cleanAttributes();
                $newClean->setInstance(null);
                $inlines[$name][] = $newClean;
            }
        }
        return $inlines;
    }

    /**
     * @param array $attributes
     * @return \Mindy\Orm\Manager|\Mindy\Orm\QuerySet
     */
    public function getLinkModels(array $attributes)
    {
        return $this->getModel()->objects()->filter($attributes);
    }
}
