<?php
/** @var $this \Mindy\Form\Fields\MarkdownField */
/** @var $t \Mindy\Locale\Translate */
$t = Mindy\Locale\Translate::getInstance();
?>

<dl class="tabs" data-tab>
    <dd class="active"><a href="#editor"><?php echo $t->t('form', 'Edit'); ?></a></dd>
    <dd><a href="#preview" onclick="preview()"><?php echo $t->t('form', 'Preview'); ?></a></dd>
</dl>
<div class="tabs-content">
    <div class="content active" id="editor">
        <?php
        $label = $this->renderLabel();
        $input = $this->renderInput();

        $hint = $this->hint ? $this->renderHint() : '';
        $errors = $this->renderErrors();
        echo implode("\n", [$label, $input, $hint, $errors]);
        ?>
    </div>
    <div class="content" id="preview">

    </div>
</div>

<script type="text/javascript">
    var md = new Remarkable('full');
    var preview = function() {
        var source = $('#<?php echo $this->getHtmlId() ?>').val();
        console.log(source);
        $('#preview').html(md.render(source));
    };
</script>