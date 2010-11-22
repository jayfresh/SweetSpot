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
      <th><?php _e('Multiline :','cct'); ?></th>
      <td>
        <select name="multiline">
            <option <?php echo $values['extra']['multiline']=='no'?"selected":""; ?> value="no"><?php _e('Single Line','cct'); ?></option>
            <option <?php echo $values['extra']['multiline']=='yes'?"selected":""; ?> value="yes"><?php _e('Multiline','cct'); ?></option>
        </select>
      </td>
    </tr>
  </tbody>
</table>
