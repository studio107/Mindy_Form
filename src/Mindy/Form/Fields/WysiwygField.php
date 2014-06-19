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

use Mindy\Base\Mindy;
use Mindy\Helper\JavaScript;
use Yii;
use CJavaScript;

class WysiwygField extends TextAreaField
{
    public $options = [
        'airMode' => false,
        'minHeight' => 200
    ];

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
            $this->html['class'] .= ' wysiwyg';
        }
    }

    public function render()
    {
        if(!isset($this->options['flowOptions'])) {
            $http = Mindy::app()->request;
            $this->options['flowOptions'] = [
                'target' => Mindy::app()->urlManager->createUrl('files.upload'),
                'chunkSize' => 1024 * 1024,
                'testChunks' => false,
                'query' => [$http->csrfTokenName => $http->getCsrfToken()]
            ];
        }
        $options = JavaScript::encode($this->options);

        if(isset($this->options['airMode']) && $this->options['airMode']) {
            $this->template = "<section id='{id}' name='{name}'{html}>{value}</section>";
        }

        $js = "<script type='text/javascript'>$('.wysiwyg').summernote({$options});</script>";
        return parent::render() . $js;
    }
}
