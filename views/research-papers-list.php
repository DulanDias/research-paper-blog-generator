<div class="wrap">
  <h1>Research Papers</h1>
  <table class="wp-list-table widefat fixed striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>File Path</th>
        <th>Paper Link</th>
        <th>Status</th>
        <th>Categories</th> <!-- Added Categories column -->
        <th>Blog Post</th>
        <th>Uploaded At</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php
    $papers = rpbg_get_all_research_papers();
    if ( $papers ) :
      foreach ( $papers as $paper ) : ?>
        <tr>
          <td><?php echo absint( $paper->id ); ?></td>
          <td><?php echo esc_html( $paper->file_path ); ?></td>
          <td><?php echo esc_url( $paper->paper_link ); ?></td>
          <td><?php echo esc_html( $paper->status ); ?></td>
          <td><?php echo esc_html( $paper->categories ); ?></td> <!-- Display categories -->
          <td>
            <?php
            if ( 'published' === $paper->status ) {
                $post_url = get_permalink( $paper->blog_post_id );
                echo '<a href="' . esc_url( $post_url ) . '" target="_blank">View Post</a>';
            } else {
                echo 'Not Published';
            }
            ?>
          </td>
          <td><?php echo esc_html( $paper->created_at ); ?></td>
          <td>
            <?php if ( 'pending' === $paper->status ) : ?>
              <!-- Generate Draft Button -->
              <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" style="display:inline;" onsubmit="return confirm('Generate draft blog article for this paper?');">
                <?php wp_nonce_field( 'rpbg_generate_draft_nonce_action', 'rpbg_generate_draft_nonce' ); ?>
                <input type="hidden" name="action" value="rpbg_generate_draft">
                <input type="hidden" name="paper_id" value="<?php echo absint( $paper->id ); ?>">
                <?php submit_button( 'Generate Draft', 'secondary', 'submit', false ); ?>
              </form>
            <?php elseif ( 'draft' === $paper->status ) : ?>
              <!-- View Draft Details -->
              <a href="<?php echo admin_url( 'admin.php?page=rpbg-draft-details&paper_id=' . absint( $paper->id) ); ?>" title="View Draft Details" class="dashicons dashicons-visibility"></a>
              <!-- Approve Button -->
              <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" style="display:inline;" onsubmit="return confirm('Approve this draft blog article?');">
                <?php wp_nonce_field( 'rpbg_approve_draft_nonce_action', 'rpbg_approve_draft_nonce' ); ?>
                <input type="hidden" name="action" value="rpbg_approve_draft">
                <input type="hidden" name="paper_id" value="<?php echo absint( $paper->id ); ?>">
                <?php submit_button( 'Approve', 'secondary', 'submit', false ); ?>
              </form>
            <?php elseif ( 'approved' === $paper->status ) : ?>
              <!-- View Draft Details -->
              <a href="<?php echo admin_url( 'admin.php?page=rpbg-draft-details&paper_id=' . absint( $paper->id) ); ?>" title="View Draft Details" class="dashicons dashicons-visibility"></a>
              <!-- Publish Now Button -->
              <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" style="display:inline;" onsubmit="return confirm('Publish this approved draft now?');">
                <?php wp_nonce_field( 'rpbg_publish_draft_nonce_action', 'rpbg_publish_draft_nonce' ); ?>
                <input type="hidden" name="action" value="rpbg_publish_draft">
                <input type="hidden" name="paper_id" value="<?php echo absint( $paper->id ); ?>">
                <?php submit_button( 'Publish Now', 'primary', 'submit', false ); ?>
              </form>
            <?php else : ?>
              <!-- For error state, allow retry generating the draft -->
              <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" style="display:inline;" onsubmit="return confirm('Generate draft blog article for this paper?');">
                <?php wp_nonce_field( 'rpbg_generate_draft_nonce_action', 'rpbg_generate_draft_nonce' ); ?>
                <input type="hidden" name="action" value="rpbg_generate_draft">
                <input type="hidden" name="paper_id" value="<?php echo absint( $paper->id ); ?>">
                <?php submit_button( 'Generate Draft', 'secondary', 'submit', false ); ?>
              </form>
            <?php endif; ?>
            <?php if ( 'published' !== $paper->status ) : ?>
              <!-- Delete Button -->
              <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this paper?');">
                <?php wp_nonce_field( 'rpbg_delete_nonce_action', 'rpbg_delete_nonce' ); ?>
                <input type="hidden" name="action" value="rpbg_delete_paper">
                <input type="hidden" name="paper_id" value="<?php echo absint( $paper->id ); ?>">
                <?php submit_button( 'Delete', 'delete', 'submit', false ); ?>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach;
    else: ?>
      <tr><td colspan="7">No research papers found.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
<!-- Ensure no trailing whitespace or unintended output -->
