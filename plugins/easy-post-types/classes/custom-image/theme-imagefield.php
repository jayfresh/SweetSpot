<?php if ($values['extra']['show_label']=='yes') : ?>
    <span class="label"><?php echo $values['label']; ?></span>
<?php endif; ?>
<?php if (is_array($values['images'])) : ?>
    <div class="list-images">
        <ul>
            <?php foreach($values['images'] as $key=>$image) :
                if (empty($values['extra']['icon_size']))
                    $size='thumbnail';
                else
                    $size=$values['extra']['icon_size'];
                ?>
            <li>
                <?php //var_dump($image); ?>
                 <?php echo $image['value'][IMAGE_FIELD_TITLE] ?>
                 <?php echo $image['size'][$size]['html']; ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>