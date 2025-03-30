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
            <option value="hourly" <?php selected( get_option( 'rpbg_processing_frequency', 'daily' ), 'hourly' ); ?>>Hourly</option>
            <option value="twicedaily" <?php selected( get_option( 'rpbg_processing_frequency' ), 'twicedaily' ); ?>>Twice Daily</option>
            <option value="daily" <?php selected( get_option( 'rpbg_processing_frequency' ), 'daily' ); ?>>Daily</option>
          </select>
          <p class="description">Select how often to process pending research papers.</p>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Processing Time</th>
        <td>
          <input type="time" name="rpbg_processing_time" value="<?php echo esc_attr( get_option( 'rpbg_processing_time', '09:00' ) ); ?>">
          <p class="description">Set the time of day when processing should occur (24-hour format, e.g., 09:00).</p>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Timezone</th>
        <td>
          <?php
          $timezones = timezone_identifiers_list();
          $current_timezone = get_option( 'rpbg_timezone', date_default_timezone_get() );
          ?>
          <select name="rpbg_timezone">
            <?php foreach ( $timezones as $tz ) : ?>
              <option value="<?php echo esc_attr( $tz ); ?>" <?php selected( $current_timezone, $tz ); ?>><?php echo esc_html( $tz ); ?></option>
            <?php endforeach; ?>
          </select>
          <p class="description">Select the timezone for scheduling the processing event.</p>
        </td>
      </tr>
    </table>
    <?php submit_button(); ?>
  </form>
</div>
