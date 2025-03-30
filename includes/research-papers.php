<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function rpbg_save_research_paper( $file_path, $paper_link, $categories = '' ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rpbg_research_papers';
    $wpdb->insert( $table_name, array(
        'file_path'   => sanitize_text_field( $file_path ),
        'paper_link'  => esc_url_raw( $paper_link ),
        'status'      => 'pending',
        'categories'  => sanitize_text_field( $categories ),
    ) );
    return $wpdb->insert_id;
}

function rpbg_get_pending_papers() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rpbg_research_papers';
    return $wpdb->get_results( "SELECT * FROM $table_name WHERE status = 'pending'" );
}

function rpbg_get_all_research_papers() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rpbg_research_papers';
    return $wpdb->get_results( "SELECT * FROM $table_name ORDER BY created_at DESC" );
}

function rpbg_get_approved_papers() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rpbg_research_papers';
    return $wpdb->get_results( "SELECT * FROM $table_name WHERE status = 'approved'" );
}

function rpbg_update_paper_status( $id, $status, $blog_post_id = 0 ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rpbg_research_papers';
    $data = array( 'status' => sanitize_text_field( $status ) );
    if ( $blog_post_id ) {
        $data['blog_post_id'] = absint( $blog_post_id );
    }
    $wpdb->update( $table_name, $data, array( 'id' => absint( $id ) ) );
}

function rpbg_delete_research_paper( $id ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rpbg_research_papers';
    return $wpdb->delete( $table_name, array( 'id' => absint( $id ) ) );
}
?>
