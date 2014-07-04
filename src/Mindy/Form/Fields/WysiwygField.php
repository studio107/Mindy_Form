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
        if (!isset($this->options['flowOptions'])) {
            $http = Mindy::app()->request;
            $this->options['flowOptions'] = [
                'target' => Mindy::app()->urlManager->createUrl('files.upload'),
                'chunkSize' => 1024 * 1024,
                'testChunks' => false,
                'query' => [$http->csrfTokenName => $http->getCsrfToken()]
            ];
        }
        if (!isset($this->options['filemanUrl'])) {
            $this->options['filemanUrl'] = Mindy::app()->urlManager->createUrl('files.index');
        }
        $options = JavaScript::encode($this->options);

        if (isset($this->options['airMode']) && $this->options['airMode']) {
            $this->template = "<section id='{id}' name='{name}'{html}>{value}</section>";
        }

        $js = "<script type='text/javascript'>$('#" . $this->getId() . "').summernote({$options});</script>";
        return parent::render() . $js;
    }
}
