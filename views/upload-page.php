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
    </table>
    <?php submit_button( 'Upload Paper' ); ?>
  </form>
</div>
