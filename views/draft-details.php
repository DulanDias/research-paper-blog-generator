<?php
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Unauthorized user' );
}

$paper_id = isset( $_GET['paper_id'] ) ? absint( $_GET['paper_id'] ) : 0;
if ( !$paper_id ) {
    echo 'Invalid paper ID.';
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'rpbg_research_papers';
$paper = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $paper_id ) );
if ( !$paper ) {
    echo 'Paper not found.';
    exit;
}

$post_id = $paper->blog_post_id;
$post = get_post( $post_id );
if ( !$post ) {
    echo 'Draft blog post not found.';
    exit;
}

// Get excerpt, categories, and tags.
$excerpt = $post->post_excerpt;
$categories = get_the_category( $post_id );
$tags = get_the_tags( $post_id );
$social_description = "Discover our latest research-based insights!"; // Dummy social description.
?>

<div class="wrap">
  <h1>Draft Blog Article Details</h1>
  <table class="widefat fixed striped">
    <tr>
      <th>Title</th>
      <td><?php echo esc_html( $post->post_title ); ?></td>
    </tr>
    <tr>
      <th>Excerpt</th>
      <td><?php echo esc_html( $excerpt ); ?></td>
    </tr>
    <tr>
      <th>Categories</th>
      <td>
        <?php
        if ( ! empty( $categories ) ) {
            $cat_names = array();
            foreach ( $categories as $cat ) {
                $cat_names[] = $cat->name;
            }
            echo esc_html( implode( ', ', $cat_names ) );
        }
        ?>
      </td>
    </tr>
    <tr>
      <th>Tags</th>
      <td>
        <?php
        if ( ! empty( $tags ) ) {
            $tag_names = array();
            foreach ( $tags as $tag ) {
                $tag_names[] = $tag->name;
            }
            echo esc_html( implode( ', ', $tag_names ) );
        }
        ?>
      </td>
    </tr>
    <tr>
      <th>Social Media Description</th>
      <td><?php echo esc_html( $social_description ); ?></td>
    </tr>
    <tr>
      <th>Blog Post URL</th>
      <td>
        <?php if ( 'publish' === $post->post_status ) : ?>
          <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" target="_blank"><?php echo esc_url( get_permalink( $post_id ) ); ?></a>
        <?php else : ?>
          Not Published Yet
        <?php endif; ?>
      </td>
    </tr>
  </table>
  <p>
    <a href="<?php echo esc_url( get_preview_post_link( $post_id ) ); ?>" target="_blank" class="button button-primary">Preview Draft Blog Article</a>
  </p>
</div>
