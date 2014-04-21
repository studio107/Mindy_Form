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
 * @date 17/04/14.04.2014 18:20
 */

namespace Mindy\Form;

use Exception;

abstract class ModelForm extends BaseForm
{
    public $ormClass = '\Mindy\Orm\Model';

    private $_instance;

    public function init()
    {
        parent::init();
        $this->setInstance($this->getModel());
    }

    public function isValid()
    {
        return parent::isValid() && $this->getInstance()->isValid();
    }

    public function setData(array $data)
    {
        $this->getInstance()->setData($data);
        return parent::setData($data);
    }

    public function setInstance($model)
    {
        if(is_subclass_of($model, $this->ormClass) || $model instanceof IFormModel) {
            $this->_instance = $model;
            return $this;
        }

        throw new Exception("Please use Mindy\\Orm\\Model or IFormModel");
    }

    public function getInstance()
    {
        if(!$this->_instance) {
            $this->_instance = $this->getModel();
        }
        return $this->_instance;
    }

    abstract public function getModel();
}
