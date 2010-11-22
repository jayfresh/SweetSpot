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
      <th><?php _e('Values :','cct'); ?></th>
      <td><textarea name="select_values" width="20" height="10" ><?php echo $values['extra']['select_values']; ?></textarea></td>
    </tr>
    <tr>
      <th><?php _e('PHP Code :','cct'); ?></th>
      <td><textarea name="php_code" width="20" height="10" ><?php echo $values['extra']['php_code']; ?></textarea></td>
    </tr>
  </tbody>
</table>
