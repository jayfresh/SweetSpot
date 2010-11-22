<?php
/*
    $rows = array of fields to display
    $rows['key-field']['content'] => the themed output
    $rows['key-field']['row'] => the raw values of the field
        field_name  => the system name
        name        => the friendly name
        type        => the type of the field
        value       => the raw value for this field
        extra       => extra fields values

*/
?>

<div class="cct-fields">
    <?php foreach($rows as $key => $content) : ?>
    <?php if (strstr($key, '_fieldset')) : ?>
        <?php print $content['content']; ?>
    <?php else : ?>
        <div class="field-<?php echo $key; ?> field-<?php echo $content['row']['field_name']; ?>">
            <?php print $content['content']; ?>
        </div>
    <?php endif; ?>
    <?php endforeach; ?>
</div>