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
 * @date 17/04/14.04.2014 18:26
 */

namespace Mindy\Form\Fields;

class DateTimeField extends CharField
{
    public function render()
    {
        return parent::render() . $this->getStatic();
    }

    public function getStatic()
    {
        $id = $this->getId();
        $js = "
            <script>
                $('#$id').pickmeup({
                    format  : 'Y-m-d H:M'
                });
            </script>
        ";
        return $js;
    }
}
