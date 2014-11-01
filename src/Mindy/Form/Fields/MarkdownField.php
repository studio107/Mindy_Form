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
 * @date 08/05/14.05.2014 17:13
 */

namespace Mindy\Form\Fields;

use Mindy\Locale\Translate;

class MarkdownField extends TextAreaField
{
    public $html = [
        'rows' => 10
    ];

    public function render()
    {
        $t = Translate::getInstance();
        $out = parent::render();
        $html = <<<HTML
<dl class="tabs" data-tab>
    <dd class="active"><a href="#editor">{$t->t('form', 'Edit')}</a></dd>
    <dd><a href="#preview" onclick="preview()">{$t->t('form', 'Preview')}</a></dd>
</dl>
<div class="tabs-content">
    <div class="content active" id="editor">{$out}</div>
    <div class="content" id="preview"></div>
</div>
HTML;

        $js = <<<JS
<script type="text/javascript">
    var md = new Remarkable('full');
    var preview = function() {
        var source = $('#{$this->getHtmlId()}').val();
        console.log(source);
        $('#preview').html(md.render(source));
    };
</script>
JS;
        return $html . $js;
    }
}
