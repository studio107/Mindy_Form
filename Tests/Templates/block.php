<?php
/* @var $form \Mindy\Form\BaseForm */
/* @var $inlines \Mindy\Form\BaseForm[] */
foreach($form as $name => $field) {
    echo $field . "\n";
}

foreach($inlines as $params) {
    $link = key($params);
    $inline = $params[$link];
    echo $inline;
}