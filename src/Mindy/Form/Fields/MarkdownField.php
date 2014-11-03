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

    protected function getRenderedLabel()
    {
        if ($this->label === false) {
            return '';
        }

        if ($this->label) {
            $label = $this->label;
        } else {
            if ($this->form instanceof ModelForm) {
                $instance = $this->form->getInstance();
                if ($instance->hasField($this->name)) {
                    $verboseName = $instance->getField($this->name)->verboseName;
                    if ($verboseName) {
                        $label = $verboseName;
                    }
                }
            }

            if (!isset($label)) {
                $label = ucfirst($this->name);
            }
        }
        return $label;
    }

    public function render()
    {
        $t = Translate::getInstance();

        $label = $this->getRenderedLabel();
        $input = $this->renderInput();

        $hint = $this->hint ? $this->renderHint() : '';
        $errors = $this->renderErrors();
        $out = implode("\n", [$input, $hint, $errors]);

        $html = <<<HTML
<dl class="tabs" data-tab>
    <dd class="active"><a href="#editor" onclick="$('#{$this->getHtmlId()}').focus()">{$label}</a></dd>
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
