<div class="wrap">
  <h1>OpenAI API Settings</h1>
  <form method="post" action="options.php">
    <?php settings_fields( 'rpbg_settings_group' ); ?>
    <table class="form-table">
      <tr valign="top">
        <th scope="row">OpenAI API Key</th>
        <td><input type="text" name="rpbg_openai_api_key" value="<?php echo esc_attr( get_option( 'rpbg_openai_api_key' ) ); ?>" class="regular-text" /></td>
      </tr>
      <tr valign="top">
        <th scope="row">OpenAI Generation Prompt</th>
        <td>
          <textarea name="rpbg_generation_prompt" rows="5" cols="50" class="large-text"><?php echo esc_textarea( get_option('rpbg_generation_prompt', 'Using the following research paper excerpt:' . "\n\n%s\n\n" . 'Generate a human-like, engaging, SEO-optimized blog article including a proper excerpt and comma separated tags. End the article with "Read the full paper here: %s".') ); ?></textarea>
          <p class="description">Use <code>%s</code> as placeholders for the research paper excerpt and the paper link respectively.</p>
        </td>
      </tr>
    </table>
    <?php submit_button(); ?>
  </form>
</div>
