<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// --- Upload Handler (unchanged) ---
add_action( 'admin_post_rpbg_upload_paper', 'rpbg_handle_upload' );
function rpbg_handle_upload() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized user' );
    }
    check_admin_referer( 'rpbg_upload_nonce_action', 'rpbg_upload_nonce' );

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

    $paper_link = isset( $_POST['paper_link'] ) ? esc_url_raw( $_POST['paper_link'] ) : '';
    
    // Process category selection.
    $selected_categories = array();
    if ( isset( $_POST['rpbg_categories'] ) && is_array( $_POST['rpbg_categories'] ) ) {
        $selected_categories = array_map( 'sanitize_text_field', $_POST['rpbg_categories'] );
    }
    $categories_str = ! empty( $selected_categories ) ? implode( ',', $selected_categories ) : '';
    
    // Save the research paper record (including categories).
    rpbg_save_research_paper( $file_path, $paper_link, $categories_str );

    wp_redirect( admin_url( 'admin.php?page=rpbg-research-papers' ) );
    exit;
}

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

    // Generate the structured output using OpenAI API.
    $generated = rpbg_generate_blog_content( $paper->file_path, $paper->paper_link );
    if ( ! $generated || ! is_array( $generated ) ) {
        rpbg_update_paper_status( $paper->id, 'error' );
        wp_redirect( admin_url( 'admin.php?page=rpbg-research-papers' ) );
        exit;
    }

    // Ensure that the required keys exist; if not, treat as error.
    $required_keys = array('title', 'article', 'excerpt', 'tags', 'socialMediaDescription');
    foreach ( $required_keys as $key ) {
        if ( empty( $generated[$key] ) ) {
            rpbg_update_paper_status( $paper->id, 'error' );
            wp_redirect( admin_url( 'admin.php?page=rpbg-research-papers' ) );
            exit;
        }
    }

    // Use the generated output directly.
    $post_title  = $generated['title'];
    $blog_article = $generated['article'];
    $post_excerpt = $generated['excerpt'];
    $post_tags    = $generated['tags'];
    $social_desc  = $generated['socialMediaDescription'];
    $default_categories = rpbg_get_default_categories();

    // Determine author: use user "dulandias" if exists.
    $author = get_user_by( 'login', 'dulandias' );
    $author_id = $author ? $author->ID : get_current_user_id();

    // Create the blog post as a DRAFT.
    $post_id = wp_insert_post( array(
        'post_title'    => sanitize_text_field( $post_title ),
        'post_content'  => wp_kses_post( $blog_article ),
        'post_excerpt'  => wp_strip_all_tags( $post_excerpt ),
        'post_status'   => 'draft',
        'post_type'     => 'post',
        'post_category' => $default_categories,
        'tags_input'    => $post_tags,
        'post_author'   => $author_id,
    ) );

    if ( $post_id ) {
        rpbg_set_featured_image( $post_id, $paper->file_path );
        // Save the social media description as post meta.
        update_post_meta( $post_id, '_rpbg_social_description', $social_desc );
        rpbg_update_paper_status( $paper->id, 'draft', $post_id );
    } else {
        rpbg_update_paper_status( $paper->id, 'error' );
    }

    wp_redirect( admin_url( 'admin.php?page=rpbg-research-papers' ) );
    exit;
}


// --- New Action: Approve Draft ---
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
    
    rpbg_update_paper_status( $paper->id, 'approved', $paper->blog_post_id );
    wp_redirect( admin_url( 'admin.php?page=rpbg-research-papers' ) );
    exit;
}

// --- New Action: Publish Draft Now ---
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
    
    $updated = wp_update_post( array(
        'ID'          => $paper->blog_post_id,
        'post_status' => 'publish'
    ) );
    
    if ( $updated ) {
        rpbg_update_paper_status( $paper->id, 'published', $paper->blog_post_id );
        // When publishing, pass the saved social description to social media posting.
        $social_desc = get_post_meta( $paper->blog_post_id, '_rpbg_social_description', true );
        rpbg_post_to_social_media( $paper->blog_post_id, $social_desc );
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
            $imagick->readImage( $pdf_path . '[0]' );
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

// Ensure no closing PHP tag to prevent unintended output.
