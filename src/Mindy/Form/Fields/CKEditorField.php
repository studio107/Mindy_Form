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
 * @date 15/05/14.05.2014 18:11
 */

namespace Mindy\Form\Fields;


class CKEditorField extends TextAreaField
{
    public function init()
    {
        parent::init();
        if($this->html === null) {
            $this->html = [];
        }
        if(is_array($this->html)) {
            if(!array_key_exists('class', $this->html)) {
                $this->html['class'] = '';
            }
            $this->html['class'] .= ' ckeditor';
        }
    }
}
