<div class="wrap">
  <h1>Upload Research Paper</h1>
  <form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin-post.php' ); ?>">
    <?php wp_nonce_field( 'rpbg_upload_nonce_action', 'rpbg_upload_nonce' ); ?>
    <input type="hidden" name="action" value="rpbg_upload_paper" />
    <table class="form-table">
      <tr valign="top">
        <th scope="row"><label for="research_paper">Research Paper (PDF)</label></th>
        <td><input type="file" name="research_paper" id="research_paper" required /></td>
      </tr>
      <tr valign="top">
        <th scope="row"><label for="paper_link">Paper Link (optional)</label></th>
        <td><input type="url" name="paper_link" id="paper_link" /></td>
      </tr>
      <tr valign="top">
        <th scope="row"><label for="rpbg_categories">Select Categories</label></th>
        <td>
          <select name="rpbg_categories[]" id="rpbg_categories" multiple style="min-width:200px;">
            <?php 
              $all_categories = get_categories( array( 'hide_empty' => false ) );
              foreach ( $all_categories as $cat ) {
                echo '<option value="' . esc_attr( $cat->name ) . '">' . esc_html( $cat->name ) . '</option>';
              }
            ?>
          </select>
          <p class="description">Hold Ctrl (Cmd on Mac) to select multiple categories. If none selected, default categories will be used.</p>
        </td>
      </tr>
    </table>
    <?php submit_button( 'Upload Paper' ); ?>
  </form>
</div>
