<?php
use Mindy\Form\IFormModel;

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
 * @date 21/04/14.04.2014 18:44
 */
class DummyModel implements IFormModel
{
    private $_data = [];

    public static $count = 0;

    public function __construct()
    {
        self::$count++;
    }

    /**
     * @param array $data
     * @void
     */
    public function setData(array $data)
    {
        $this->_data = $data;
    }

    public function getData()
    {
        return $this->_data;
    }

    public function isValid()
    {
        return !empty($this->_data);
    }
}

