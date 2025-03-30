<div class="wrap">
  <h1>Scheduler Settings</h1>
  <form method="post" action="options.php">
    <?php settings_fields( 'rpbg_scheduler_options_group' ); ?>
    <?php do_settings_sections( 'rpbg_scheduler' ); ?>
    <table class="form-table">
      <tr valign="top">
        <th scope="row">Processing Frequency</th>
        <td>
          <select name="rpbg_processing_frequency">
            <option value="hourly" <?php selected( get_option( 'rpbg_processing_frequency', 'hourly' ), 'hourly' ); ?>>Hourly</option>
            <option value="twicedaily" <?php selected( get_option( 'rpbg_processing_frequency' ), 'twicedaily' ); ?>>Twice Daily</option>
            <option value="daily" <?php selected( get_option( 'rpbg_processing_frequency' ), 'daily' ); ?>>Daily</option>
          </select>
        </td>
      </tr>
    </table>
    <?php submit_button(); ?>
  </form>
</div>
