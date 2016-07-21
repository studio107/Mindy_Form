# Mindy form component

[![Build Status](https://travis-ci.org/studio107/Mindy_Form.svg?branch=master)](https://travis-ci.org/studio107/Mindy_Form)
[![Coverage Status](https://coveralls.io/repos/studio107/Mindy_Form/badge.png)](https://coveralls.io/r/studio107/Mindy_Form)

# Widget

Использование виджетов необходимо когда к полю формы необходимо добавить некую логику. К примеру подключение
дополнительного поля с текстом лицензии, отображение рейтинга с помощью стороннего плагина, подключение карты и так далее.

Пример виджета:

```php
<?php

namespace Mindy\Form\Widget;

use Mindy\Form\Widget;
use Mindy\Helper\JavaScript;
use Mindy\Helper\JavaScriptExpression;

class RatingWidget extends Widget
{
    public $options = [];

    /**
     * @return string
     */
    public function render()
    {
        $field = $this->getField();
        $jsOptions = JavaScript::encode(array_merge([
            'starType' => 'i',
            'numberMax' => 5,
            'score' => $field->getValue(),
            'click' => new JavaScriptExpression('function(score, evt) {
                $("#' . $field->getHtmlId() . '").val(score);
            }')
        ], $this->options));
        $js = "<div id='{$field->getHtmlId()}_rating' class='rating-input'></div><script type='text/javascript'>$('#{$field->getHtmlId()}_rating').raty({$jsOptions});</script>";
        return $field->renderInput() . $js;
    }
}
```

Использование:

```php
<?php

class MyForm extends Form
{
    public function getFields()
    {
        return [
            'rating' => [
                'class' => CharField::class,
                'widget' => new RatingWidget
            ]
        ];
    }
}
```