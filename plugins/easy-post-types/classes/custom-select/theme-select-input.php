<span class="label"><?php echo $values['name']; ?></span>
<select name="<?php echo $values['field_name']; ?>">
    <?php foreach($values['options'] as $option) : ?>
    <option <?php print $option['selected']; ?> value="<?php print $option['key']; ?>"><?php print $option['value']; ?></option>
    <?php endforeach; ?>
</select>
