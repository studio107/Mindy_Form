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
 * @date 23/04/14.04.2014 18:22
 */

namespace Mindy\Form\Fields;


class AceField extends CharField
{
    public $template = "<textarea id='{id}' class='hide' name='{name}'{html}>{value}</textarea>";

    public $aceMode = "ace/mode/twig";

    public $aceTheme = "ace/theme/crimson_editor";

    public function render()
    {
        $out = strtr('<div id="{id}-ace-editor" class="ace-editor">{value}</div>
        <script src="https://nightwing.github.io/emmet-core/emmet.js"></script>
        <script type="text/javascript">
            var editor = ace.edit("{id}-ace-editor");
            editor.setOptions({
                enableBasicAutocompletion: true,
                enableSnippets: true,
                enableLiveAutocompletion: false,
                enableEmmet: true
            });
            ' . ($this->aceMode ? 'editor.getSession().setMode("' . $this->aceMode . '");' : '') . '
            ' . ($this->aceTheme ? 'editor.setTheme("' . $this->aceTheme . '");' : '') . '
            editor.getSession().on("change", function(e) {
                $("#{id}").val(editor.getValue());
            });
        </script>', [
            '{id}' => $this->getHtmlId(),
            '{value}' => $this->getValue()
        ]);
        return parent::render() . $out;
    }
}
