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

class WysiwygField extends TextAreaField
{
    public $options = [
        'minHeight' => 200,
    ];

    public function render()
    {
        $options = [
            'selector' => '#' . $this->getId(),
            'menubar' => false,
            'statusbar' => false,
            'skin_url' => '/static_admin/scss/modules/tinymce/',
            'plugins' => [
                "advlist autolink lists link image charmap print preview anchor",
                "searchreplace visualblocks code fullscreen",
                "insertdatetime media table contextmenu paste"
            ],
            'toolbar' => "undo redo | styleselect | bullist numlist | link image table"
        ];
        $options = JavaScript::encode($options);
        $js = "<script type='text/javascript'>
        tinyMCE.baseURL = '/static_admin/vendor/tinymce/';
        tinymce.init({$options});
        </script>";
        return parent::render() . $js;
    }

//    public function render()
//    {
//        if (!isset($this->options['flowOptions'])) {
//            $request = Mindy::app()->request;
//            $this->options['flowOptions'] = [
//                'target' => Mindy::app()->urlManager->reverse('files.upload'),
//                'chunkSize' => 1024 * 1024,
//                'testChunks' => false,
//                'query' => [$request->csrf->getName() => $request->csrf->getValue()]
//            ];
//        }
//        if (!isset($this->options['filemanUrl'])) {
//            $this->options['filemanUrl'] = Mindy::app()->urlManager->reverse('files.index');
//        }
//        $options = JavaScript::encode($this->options);
//
//        if (isset($this->options['airMode']) && $this->options['airMode']) {
//            $this->template = "<section id='{id}' name='{name}'{html}>{value}</section>";
//        }
//
//        $js = "<script type='text/javascript'>$('#" . $this->getId() . "').summernote({$options});</script>";
//        return parent::render() . $js;
//    }
}
