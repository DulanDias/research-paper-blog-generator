<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_post_rpbg_upload_paper', 'rpbg_handle_upload' );
function rpbg_handle_upload() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized user' );
    }
    check_admin_referer( 'rpbg_upload_nonce_action', 'rpbg_upload_nonce' );

    // Validate file upload
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

    // Sanitize paper link
    $paper_link = isset( $_POST['paper_link'] ) ? esc_url_raw( $_POST['paper_link'] ) : '';

    // Save the research paper record
    rpbg_save_research_paper( $file_path, $paper_link );

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

function rpbg_set_featured_image( $post_id, $pdf_path ) {
    if ( class_exists( 'Imagick' ) ) {
        try {
            $imagick = new Imagick();
            $imagick->readImage( $pdf_path . '[0]' ); // Read first page of PDF
            $width  = $imagick->getImageWidth();
            $height = $imagick->getImageHeight();
            // Crop the top 30% of the image for the featured image
            $crop_height = floor( $height * 0.3 );
            $imagick->cropImage( $width, $crop_height, 0, 0 );
            $upload_dir = wp_upload_dir();
            $image_filename = 'featured-' . time() . '-' . basename( $pdf_path ) . '.jpg';
            $image_path = trailingslashit( $upload_dir['path'] ) . $image_filename;
            $imagick->setImageFormat( 'jpeg' );
            $imagick->writeImage( $image_path );
            
            // Insert the image into the WordPress media library
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
