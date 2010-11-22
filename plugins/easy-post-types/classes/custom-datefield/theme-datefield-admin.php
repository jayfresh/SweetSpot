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
      <th><?php _e('Date format :','cct'); ?></th>
      <td>
        <select name="date_format">
            <option <?php echo $values['extra']['date_format']=='Long'?"selected":""; ?> value="Long"><?php _e('Long : Monday, December 17, 2009','cct'); ?></option>
            <option <?php echo $values['extra']['date_format']=='Medium'?"selected":""; ?> value="Medium"><?php _e('Medium : December 17, 2009', 'cct'); ?></option>
            <option <?php echo $values['extra']['date_format']=='Short'?"selected":""; ?> value="Short"><?php _e('Short : 12/17/2009','cct'); ?></option>
        </select>
      </td>
    </tr>
    <tr>
      <th><?php _e('Enter custom format:','cct'); ?></th>
      <td><input type="text" name="custom_date_format" value="<?php echo $values['extra']['custom_date_format']; ?>" /></td>
    </tr>
  </tbody>
</table>
