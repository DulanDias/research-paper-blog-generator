<div class="wrap">
  <h1>OpenAI API Settings</h1>
  <form method="post" action="options.php">
    <?php settings_fields( 'rpbg_settings_group' ); ?>
    <table class="form-table">
      <tr valign="top">
        <th scope="row">OpenAI API Key</th>
        <td><input type="text" name="rpbg_openai_api_key" value="<?php echo esc_attr( get_option( 'rpbg_openai_api_key' ) ); ?>" class="regular-text" /></td>
      </tr>
    </table>
    <?php submit_button(); ?>
  </form>
</div>
