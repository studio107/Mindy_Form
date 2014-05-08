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
 * @date 06/05/14.05.2014 20:08
 */

namespace Mindy\Form;


use Mindy\Form\Fields\CheckboxField;
use Mindy\Form\Fields\DeleteInlineField;
use Mindy\Form\Fields\HiddenField;
use Mindy\Helper\Creator;

abstract class InlineModelForm extends ModelForm
{
    const DELETE_KEY = 'to_be_deleted';

    public $extra = 3;

    public $link;

    public $max = PHP_INT_MAX;

    public $showAddButton = true;

    public $templates = [
        'inline' => 'core/form/inline.twig',
    ];

    public $defaultTemplateType = 'inline';

    public function init()
    {
        parent::init();
        $this->prefix[] = self::shortClassName();
        $this->setRenderOptions();
    }

    public function getFieldsInit()
    {
        $fields = parent::getFieldsInit();
        $isNew = $this->getInstance()->getIsNewRecord();
        if(!$isNew) {
            $pkName = $this->getInstance()->primaryKey();
            $fields[$pkName] = Creator::createObject([
                'class' => HiddenField::className(),
                'form' => $this,
                'label' => 'Primary Key',
                'name' => $pkName,
                'value' => $this->getInstance()->pk
            ]);
        }
        $fields[self::DELETE_KEY] = Creator::createObject([
            'class' => DeleteInlineField::className(),
            'form' => $this,
            'label' => 'Delete',
            'name' => self::DELETE_KEY,
            'html' => $isNew ? ['disabled' => 'disabled'] : [],
        ]);
        return $fields;
    }

    public function setRenderOptions()
    {
        $field = $this->getInstance()->getField($this->link);
        if(is_a($field, $this->getModel()->oneToOneField)) {
            $this->extra = 1;
            $this->max = 1;
            $this->showAddButton = false;
        }
    }

    public function delete()
    {
        return $this->getInstance()->delete();
    }

    public function getLinkModels(array $attributes)
    {
        return $this->getModel()->objects()->filter($attributes)->all();
    }
}
