<?php if (is_array($values['value'])) : ?>
    <div class="list-images">
        <ul>
            <?php $index=0; foreach($values['value'] as $key=>$image) : ?>
            <li>
                <?php echo $image[IMAGE_FIELD_TITLE] ?>
                <?php $res = $this->getImage($image['value'], $values['field_name'], $values['posttype'], $values['extra']['icon_size']); echo $res['html']; ?>
                <span class="remove"><a href="Javascript:imageField.removeImage({'url':'<?php echo $this->mainContentType->ajaxUrl; ?>','index':'<?php echo $index; ?>','postid':'<?php echo $values['postid']; ?>'});" id="remove_image">remove</a></span>
            </li>
            <?php $index++; endforeach; ?>
        </ul>
    </div>
<?php endif; ?>