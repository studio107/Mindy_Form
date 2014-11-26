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
 * @date 23/04/14.04.2014 18:25
 */

namespace Mindy\Form\Fields;


use Mindy\Helper\JavaScript;
use Mindy\Helper\JavaScriptExpression;
use Mindy\Orm\Fields\ForeignField;
use Mindy\Orm\Fields\HasManyField;
use Mindy\Orm\Fields\ManyToManyField;

class Select2Field extends DropDownField
{
    public $options = [];

    public $pageSize = 10;

    public $modelField = 'name';

    public function render()
    {
        $label = $this->renderLabel();

        $hint = $this->hint ? $this->renderHint() : '';
        $errors = $this->renderErrors();
        $name = $this->getHtmlName();

        $model = $this->getForm()->getModel();
        $modelField = $model->getField($this->name);
        $multiple = $modelField instanceof ManyToManyField || $modelField instanceof HasManyField;

        $options = [
            'width' => 'resolve',
            'allowClear' => true,
            'blurOnChange' => true,
            'openOnEnter' => false,
            'multiple' => $multiple,
            'placeholder' => "Search for a repository",
            'minimumInputLength' => 2,
            'ajax' => [
                'url' => "",
                'dataType' => 'json',
                'quietMillis' => 250,
                'data' => new JavaScriptExpression('function (term, page) {
                    return {
                        select2: term,
                        page: page,
                        field: "' . $this->getName() . '",
                        pageSize: "' . $this->pageSize . '",
                        modelField: "' . $this->modelField . '"
                    };
                }'),
                'results' => new JavaScriptExpression('function (data, page) {
                    var more = (page * 30) < data.total_count;
                    return {
                        results: data.items,
                        more: more
                    };
                }'),
            ],
            'escapeMarkup' => new JavaScriptExpression('function (m) {
                return m;
            }')
        ];

        $data = [];
        if ($instance = $this->getForm()->getInstance()) {
            $field = $instance->getField($this->name);
            if ($field instanceof ForeignField) {
                $item = $field->getManager()->get();
                $data = ['id' => $item->pk, 'text' => (string)$item];
            } else {
                foreach ($field->getManager()->all() as $item) {
                    $data[] = ['id' => $item->pk, 'text' => (string)$item];
                };
            }
        }

        $out = implode("\n", [
            $label,
            "<input type='hidden' id='{$this->getHtmlId()}' name='{$name}' value='' />",
            $hint,
            $errors,
            '<script type="text/javascript">
                $("#' . $this->getHtmlId() . '").select2(' . JavaScript::encode($options) . ');
                $("#' . $this->getHtmlId() . '").select2("data", ' . JavaScript::encode($data) . ');
            </script>'
        ]);

        return $out;
    }
}
