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
 * @date 21/04/14.04.2014 18:42
 */

namespace Mindy\Form;


interface IFormModel
{
    /**
     * @param array $data
     * @void
     */
    public function setData(array $data);

    /**
     * @return bool
     */
    public function isValid();
}
