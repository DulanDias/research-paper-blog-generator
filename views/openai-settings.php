<div class="wrap">
  <h1>OpenAI API Settings</h1>
  <form method="post" action="options.php">
    <?php settings_fields( 'rpbg_settings_group' ); ?>
    <table class="form-table">
      <tr valign="top">
        <th scope="row">OpenAI API Key</th>
        <td>
          <input type="text" name="rpbg_openai_api_key" value="<?php echo esc_attr( get_option( 'rpbg_openai_api_key' ) ); ?>" class="regular-text" />
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">OpenAI Generation Prompt</th>
        <td>
          <textarea name="rpbg_generation_prompt" rows="5" cols="50" class="large-text"><?php echo esc_textarea( get_option('rpbg_generation_prompt', "Using the following research paper excerpt:\n\n```\n%s\n```\n\nPlease generate an output in JSON format with the following keys:\n- \"title\": A catchy blog post title (max 10 words).\n- \"article\": A well-structured, human-like, engaging blog article that is SEO-optimized. Use proper HTML formatting for paragraphs and headings.\n- \"excerpt\": A short excerpt (around 40 words) summarizing the article.\n- \"tags\": A comma-separated list of relevant SEO-friendly tags.\n- \"socialMediaDescription\": A compelling, human-like description for sharing on social media.\n\nEnd the output with the following string: \"Read the full paper here: %s\"") ); ?></textarea>
          <p class="description">Use <code>%s</code> as placeholders for the research paper excerpt and the paper link respectively.</p>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">OpenAI Model</th>
        <td>
          <input type="text" name="rpbg_openai_model" value="<?php echo esc_attr( get_option( 'rpbg_openai_model', 'davinci' ) ); ?>" class="regular-text" />
          <p class="description">Enter the model name (e.g., <code>davinci</code>, <code>curie</code>, etc.).</p>
        </td>
      </tr>
    </table>
    <?php submit_button(); ?>
  </form>
</div>
