<?php
global $_wp_additional_image_sizes;
?>
<table>
  <tbody>
    <tr>
      <th><?php _e('Label :','cct'); ?></th>
      <td>
        <select name="show_label">
            <option <?php echo $values['extra']['show_label']=='yes'?"selected":""; ?> value="yes"><?php _e('Show Label','cct'); ?></option>
            <option <?php echo $values['extra']['show_label']=='no'?"selected":""; ?> value="no"><?php _e('Do Not Show Label','cct'); ?></option>
        </select>
      </td>
    </tr>
    <tr>
      <th><?php _e('Default Icon Size:','cct'); ?></th>
      <td>
        <select name="icon_size">
              <?php
              $imgs=get_intermediate_image_sizes();
              foreach($imgs as $name) : ?>
              <option <?php echo $values['extra']['icon_size']==$name?"selected":""; ?> value="<?php echo $name ?>"><?php echo $name ?></option>
              <?php endforeach; ?>
        </select>
      </td>
    </tr>
    <tr>
      <th><?php _e('Default Medium Size:','cct'); ?></th>
      <td>
        <select name="medium_size">
              <?php
              $imgs=get_intermediate_image_sizes();
              foreach($imgs as $name) : ?>
              <option <?php echo $values['extra']['medium_size']==$name?"selected":""; ?> value="<?php echo $name ?>"><?php echo $name ?></option>
              <?php endforeach; ?>
        </select>
      </td>
    </tr>
    <tr>
      <th><?php _e('Keep Width:','cct'); ?></th>
      <td>
        <select name="kwidth">
            <option <?php echo $values['extra']['kwidth']=='yes'?"selected":""; ?> value="yes"><?php _e('Keep Width','cct'); ?></option>
            <option <?php echo $values['extra']['kwidth']=='no'?"selected":""; ?> value="no"><?php _e('Do not Keep Width','cct'); ?></option>
        </select>
      </td>
    </tr>
    <tr>
      <th><?php _e('Keep Height:','cct'); ?></th>
      <td>
        <select name="kheight">
            <option <?php echo $values['extra']['kheight']=='yes'?"selected":""; ?> value="yes"><?php _e('Keep Height','cct'); ?></option>
            <option <?php echo $values['extra']['kheight']=='no'?"selected":""; ?> value="no"><?php _e('Do not Keep Height','cct'); ?></option>
        </select>
      </td>
    </tr>
    <tr>
      <th><?php _e('Crop :','cct'); ?></th>
      <td>
        <select name="crop">
            <option <?php echo $values['extra']['crop']=='yes'?"selected":""; ?> value="yes"><?php _e('Resize and Crop','cct'); ?></option>
            <option <?php echo $values['extra']['crop']=='no'?"selected":""; ?> value="no"><?php _e('Do not Crop','cct'); ?></option>
        </select>
      </td>
    </tr>
  </tbody>
</table>
