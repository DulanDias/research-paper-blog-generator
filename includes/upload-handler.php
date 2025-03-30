<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Existing upload handler.
add_action( 'admin_post_rpbg_upload_paper', 'rpbg_handle_upload' );
function rpbg_handle_upload() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized user' );
    }
    check_admin_referer( 'rpbg_upload_nonce_action', 'rpbg_upload_nonce' );

    // Validate file upload.
    if ( isset( $_FILES['research_paper'] ) && ! empty( $_FILES['research_paper']['name'] ) ) {
        $allowed_types = array( 'application/pdf' );
        if ( ! in_array( $_FILES['research_paper']['type'], $allowed_types ) ) {
            wp_die( 'Only PDF files are allowed.' );
        }
        $upload = wp_handle_upload( $_FILES['research_paper'], array( 'test_form' => false ) );
        if ( isset( $upload['error'] ) ) {
            wp_die( 'Upload error: ' . esc_html( $upload['error'] ) );
        }
        $file_path = $upload['file'];
    } else {
        $file_path = '';
    }

    // Sanitize paper link.
    $paper_link = isset( $_POST['paper_link'] ) ? esc_url_raw( $_POST['paper_link'] ) : '';

    // Save the research paper record with status "pending".
    rpbg_save_research_paper( $file_path, $paper_link );

    wp_redirect( admin_url( 'admin.php?page=rpbg-research-papers' ) );
    exit;
}

// Delete handler.
add_action( 'admin_post_rpbg_delete_paper', 'rpbg_handle_delete_paper' );
function rpbg_handle_delete_paper() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized user' );
    }
    check_admin_referer( 'rpbg_delete_nonce_action', 'rpbg_delete_nonce' );
    
    $paper_id = isset( $_POST['paper_id'] ) ? absint( $_POST['paper_id'] ) : 0;
    if ( $paper_id ) {
        rpbg_delete_research_paper( $paper_id );
    }
    wp_redirect( admin_url( 'admin.php?page=rpbg-research-papers' ) );
    exit;
}

// --- New Action: Generate Draft ---
// This creates a blog post as DRAFT for a paper with status "pending".
add_action( 'admin_post_rpbg_generate_draft', 'rpbg_handle_generate_draft' );
function rpbg_handle_generate_draft() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized user' );
    }
    check_admin_referer( 'rpbg_generate_draft_nonce_action', 'rpbg_generate_draft_nonce' );

    $paper_id = isset( $_POST['paper_id'] ) ? absint( $_POST['paper_id'] ) : 0;
    if ( ! $paper_id ) {
        wp_redirect( admin_url( 'admin.php?page=rpbg-research-papers' ) );
        exit;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'rpbg_research_papers';
    $paper = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $paper_id ) );
    if ( ! $paper || 'pending' !== $paper->status ) {
        wp_redirect( admin_url( 'admin.php?page=rpbg-research-papers' ) );
        exit;
    }

    // Generate blog content using the OpenAI API.
    $blog_content = rpbg_generate_blog_content( $paper->file_path, $paper->paper_link );
    if ( ! $blog_content ) {
        rpbg_update_paper_status( $paper->id, 'error' );
        wp_redirect( admin_url( 'admin.php?page=rpbg-research-papers' ) );
        exit;
    }
    // Generate title, excerpt, tags, and get default categories.
    $post_title = rpbg_generate_topic( $blog_content );
    $post_excerpt = rpbg_generate_excerpt( $blog_content );
    $post_tags = rpbg_generate_tags( $blog_content );
    $default_categories = rpbg_get_default_categories();

    // Determine author: use "dulandias" if exists.
    $author = get_user_by( 'login', 'dulandias' );
    $author_id = $author ? $author->ID : get_current_user_id();

    // Create the blog post as a DRAFT.
    $post_id = wp_insert_post( array(
        'post_title'    => sanitize_text_field( $post_title ),
        'post_content'  => wp_kses_post( $blog_content ),
        'post_excerpt'  => wp_strip_all_tags( $post_excerpt ),
        'post_status'   => 'draft',
        'post_type'     => 'post',
        'post_category' => $default_categories,
        'tags_input'    => $post_tags,
        'post_author'   => $author_id,
    ) );

    if ( $post_id ) {
        // Optionally set featured image.
        rpbg_set_featured_image( $post_id, $paper->file_path );
        // Update paper status to "draft" and save the blog post ID.
        rpbg_update_paper_status( $paper->id, 'draft', $post_id );
    } else {
        rpbg_update_paper_status( $paper->id, 'error' );
    }

    wp_redirect( admin_url( 'admin.php?page=rpbg-research-papers' ) );
    exit;
}

// --- New Action: Approve Draft ---
// Marks a draft paper as approved. Only papers with status "draft" can be approved.
add_action( 'admin_post_rpbg_approve_draft', 'rpbg_handle_approve_draft' );
function rpbg_handle_approve_draft() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized user' );
    }
    check_admin_referer( 'rpbg_approve_draft_nonce_action', 'rpbg_approve_draft_nonce' );
    
    $paper_id = isset( $_POST['paper_id'] ) ? absint( $_POST['paper_id'] ) : 0;
    if ( !$paper_id ) {
        wp_redirect( admin_url( 'admin.php?page=rpbg-research-papers' ) );
        exit;
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'rpbg_research_papers';
    $paper = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $paper_id ) );
    if ( !$paper || 'draft' !== $paper->status ) {
        wp_redirect( admin_url( 'admin.php?page=rpbg-research-papers' ) );
        exit;
    }
    
    // Update status to "approved" (blog post remains as draft).
    rpbg_update_paper_status( $paper->id, 'approved', $paper->blog_post_id );
    
    wp_redirect( admin_url( 'admin.php?page=rpbg-research-papers' ) );
    exit;
}

// --- New Action: Publish Draft Now ---
// Publishes a draft blog post that has been approved.
add_action( 'admin_post_rpbg_publish_draft', 'rpbg_handle_publish_draft' );
function rpbg_handle_publish_draft() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized user' );
    }
    check_admin_referer( 'rpbg_publish_draft_nonce_action', 'rpbg_publish_draft_nonce' );
    
    $paper_id = isset( $_POST['paper_id'] ) ? absint( $_POST['paper_id'] ) : 0;
    if ( !$paper_id ) {
        wp_redirect( admin_url( 'admin.php?page=rpbg-research-papers' ) );
        exit;
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'rpbg_research_papers';
    $paper = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $paper_id ) );
    if ( !$paper || 'approved' !== $paper->status ) {
        wp_redirect( admin_url( 'admin.php?page=rpbg-research-papers' ) );
        exit;
    }
    
    // Update the draft blog post status to "publish".
    $updated = wp_update_post( array(
        'ID'          => $paper->blog_post_id,
        'post_status' => 'publish'
    ) );
    
    if ( $updated ) {
        rpbg_update_paper_status( $paper->id, 'published', $paper->blog_post_id );
    } else {
        rpbg_update_paper_status( $paper->id, 'error', $paper->blog_post_id );
    }
    
    wp_redirect( admin_url( 'admin.php?page=rpbg-research-papers' ) );
    exit;
}

// Featured image function remains unchanged.
function rpbg_set_featured_image( $post_id, $pdf_path ) {
    if ( class_exists( 'Imagick' ) ) {
        try {
            $imagick = new Imagick();
            $imagick->readImage( $pdf_path . '[0]' ); // Read first page of PDF
            $width  = $imagick->getImageWidth();
            $height = $imagick->getImageHeight();
            $crop_height = floor( $height * 0.3 );
            $imagick->cropImage( $width, $crop_height, 0, 0 );
            $upload_dir = wp_upload_dir();
            $image_filename = 'featured-' . time() . '-' . basename( $pdf_path ) . '.jpg';
            $image_path = trailingslashit( $upload_dir['path'] ) . $image_filename;
            $imagick->setImageFormat( 'jpeg' );
            $imagick->writeImage( $image_path );
            
            $wp_filetype = wp_check_filetype( $image_filename, null );
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title'     => sanitize_file_name( $image_filename ),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );
            $attach_id = wp_insert_attachment( $attachment, $image_path, $post_id );
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            $attach_data = wp_generate_attachment_metadata( $attach_id, $image_path );
            wp_update_attachment_metadata( $attach_id, $attach_data );
            set_post_thumbnail( $post_id, $attach_id );
        } catch ( Exception $e ) {
            error_log( 'RPBG Image extraction error: ' . $e->getMessage() );
        }
    }
}
?>
