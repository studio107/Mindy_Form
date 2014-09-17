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

class WysiwygField extends TextAreaField
{
    public function render()
    {
        $id = $this->getId();
        $js = "<script type='text/javascript'>
        tinyMCE.init({
            mode: 'exact',
            elements: '{$id}',
            theme: 'advanced',
            language : 'ru',
            width: '100%',
            height: '400px',
            plugins: 'watermark,jaretypograph,youtube,images,autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,inlinepopups,insertdatetime,media,contextmenu,paste,directionality,fullscreen,noneditable,nonbreaking,xhtmlxtras,template,loremipsum',
            theme_advanced_resizing : true,
            theme_advanced_resize_horizontal : 0,
            theme_advanced_resizing_use_cookie : 0,
            pagebreak_separator : '<!--pagebreak-->',
            theme_advanced_path : false,
            theme_advanced_buttons1 : 'undo,redo,|,bold,italic,underline,strikethrough,|,sub,sup,|,bullist,numlist,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect,|,forecolor,backcolor,fullscreen',
            theme_advanced_buttons2 : 'tablecontrols,|,hr,removeformat,|,youtube,images,image,|,pagebreak,outdent,indent,blockquote,|,link,unlink,cleanup,code,watermark,jaretypograph,loremipsum',
            theme_advanced_buttons3 : '',
            theme_advanced_toolbar_location : 'top',
            theme_advanced_toolbar_align : 'left',
            theme_advanced_statusbar_location : 'bottom',
            dialog_type : 'modal',
            relative_urls : false,
            remove_script_host : true,
            paste_auto_cleanup_on_paste : true,
            tab_focus : ':prev,:next',
            skin : 'o2k7',
            skin_variant : 'silver'
        });
        </script>";
        return parent::render() . $js;
    }

//    public function render()
//    {
//        $options = [
//            'selector' => '#' . $this->getId(),
//            'menubar' => false,
//            'statusbar' => false,
//            'skin_url' => '/static_admin/scss/modules/tinymce/',
//            'plugins' => [
//                "advlist autolink lists link image charmap print preview anchor",
//                "searchreplace visualblocks code fullscreen",
//                "insertdatetime media table contextmenu paste"
//            ],
//            'toolbar' => "undo redo | styleselect | bullist numlist | link image table"
//        ];
//        $options = JavaScript::encode($options);
//        $js = "<script type='text/javascript'>
//        tinyMCE.baseURL = '/static_admin/vendor/tinymce/';
//        tinymce.init({$options});
//        </script>";
//        return parent::render() . $js;
//    }

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
