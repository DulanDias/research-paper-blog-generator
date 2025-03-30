<div class="wrap">
  <h1>Research Papers</h1>
  <table class="wp-list-table widefat fixed striped">
    <thead>
      <tr>
        <th>ID</th>
        <th>File Path</th>
        <th>Paper Link</th>
        <th>Status</th>
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
          <td>
            <?php
            if ( $paper->blog_post_id ) {
                $post_url = get_permalink( $paper->blog_post_id );
                echo '<a href="' . esc_url( $post_url ) . '" target="_blank">View Post</a>';
            } else {
                echo 'Not Published';
            }
            ?>
          </td>
          <td><?php echo esc_html( $paper->created_at ); ?></td>
          <td>
            <?php if ( 'pending' === $paper->status || 'error' === $paper->status ) : ?>
              <!-- Generate & Publish Now Button -->
              <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to generate and publish this paper immediately?');">
                <?php wp_nonce_field( 'rpbg_generate_nonce_action', 'rpbg_generate_nonce' ); ?>
                <input type="hidden" name="action" value="rpbg_generate_paper">
                <input type="hidden" name="paper_id" value="<?php echo absint( $paper->id ); ?>">
                <?php submit_button( 'Generate & Publish Now', 'primary', 'submit', false ); ?>
              </form>
              <!-- Delete Button -->
              <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this paper?');">
                <?php wp_nonce_field( 'rpbg_delete_nonce_action', 'rpbg_delete_nonce' ); ?>
                <input type="hidden" name="action" value="rpbg_delete_paper">
                <input type="hidden" name="paper_id" value="<?php echo absint( $paper->id ); ?>">
                <?php submit_button( 'Delete', 'delete', 'submit', false ); ?>
              </form>
            <?php else : ?>
              -
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
