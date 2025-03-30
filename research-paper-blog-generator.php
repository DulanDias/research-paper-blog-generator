<?php
/*
Plugin Name: Research Paper Blog Generator
Plugin URI: https://dulandias.com/
Description: Automatically generate engaging, SEO‑optimized blog articles from uploaded research papers with OpenAI integration and social media auto-posting.
Version: 1.0
Author: Dulan Dias
Author URI: https://dulandias.com/
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'RPBG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'RPBG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include required files
require_once RPBG_PLUGIN_DIR . 'includes/settings.php';
require_once RPBG_PLUGIN_DIR . 'includes/upload-handler.php';
require_once RPBG_PLUGIN_DIR . 'includes/scheduler.php';
require_once RPBG_PLUGIN_DIR . 'includes/openai-api.php';
require_once RPBG_PLUGIN_DIR . 'includes/social-media.php';
require_once RPBG_PLUGIN_DIR . 'includes/research-papers.php';

// Activation hook – create custom table and schedule cron jobs
register_activation_hook( __FILE__, 'rpbg_activate_plugin' );
function rpbg_activate_plugin() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rpbg_research_papers';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        file_path varchar(255) DEFAULT '' NOT NULL,
        paper_link varchar(255) DEFAULT '' NOT NULL,
        status varchar(20) DEFAULT 'pending' NOT NULL,
        blog_post_id bigint(20) DEFAULT 0 NOT NULL,
        categories varchar(255) DEFAULT '' NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    // Schedule recurring processing if not already scheduled
    if ( ! wp_next_scheduled( 'rpbg_cron_job' ) ) {
        wp_schedule_event( time(), 'hourly', 'rpbg_cron_job' );
    }
}

// Deactivation hook – clear scheduled cron events
register_deactivation_hook( __FILE__, 'rpbg_deactivate_plugin' );
function rpbg_deactivate_plugin() {
    wp_clear_scheduled_hook( 'rpbg_cron_job' );
}

// Admin Menus: Dashboard, OpenAI Settings, Scheduler, Research Papers List, and Draft Details view.
add_action( 'admin_menu', 'rpbg_create_admin_menus' );
function rpbg_create_admin_menus(){
    add_menu_page( 'RP Blog Generator', 'RP Blog Generator', 'manage_options', 'rpbg-main', 'rpbg_main_page_callback', 'dashicons-media-document', 6 );
    add_submenu_page( 'rpbg-main', 'Upload Research Paper', 'Upload Paper', 'manage_options', 'rpbg-upload-paper', 'rpbg_upload_page_callback' );
    add_submenu_page( 'rpbg-main', 'Research Papers', 'Research Papers', 'manage_options', 'rpbg-research-papers', 'rpbg_research_papers_page_callback' );
    add_submenu_page( 'rpbg-main', 'OpenAI API Settings', 'OpenAI Settings', 'manage_options', 'rpbg-openai-settings', 'rpbg_openai_settings_callback' );
    add_submenu_page( 'rpbg-main', 'Scheduler', 'Scheduler', 'manage_options', 'rpbg-scheduler', 'rpbg_scheduler_callback' );
    // Hidden page for draft details view.
    add_submenu_page( null, 'Draft Details', 'Draft Details', 'manage_options', 'rpbg-draft-details', 'rpbg_draft_details_callback' );
}

function rpbg_main_page_callback() {
    echo '<div class="wrap"><h1>Welcome to the Research Paper Blog Generator</h1><p>Use the submenu options to upload papers, view research papers, configure OpenAI API and scheduler.</p></div>';
}

function rpbg_upload_page_callback() {
    include RPBG_PLUGIN_DIR . 'views/upload-page.php';
}

function rpbg_research_papers_page_callback() {
    include RPBG_PLUGIN_DIR . 'views/research-papers-list.php';
}

function rpbg_openai_settings_callback() {
    include RPBG_PLUGIN_DIR . 'views/openai-settings.php';
}

function rpbg_scheduler_callback() {
    include RPBG_PLUGIN_DIR . 'views/scheduler.php';
}

function rpbg_draft_details_callback() {
    include RPBG_PLUGIN_DIR . 'views/draft-details.php';
}

// WP-Cron: Process only approved papers
add_action( 'rpbg_cron_job', 'rpbg_process_approved_papers' );
function rpbg_process_approved_papers() {
    $papers = rpbg_get_approved_papers(); // Only approved papers are processed.
    if ( ! empty( $papers ) ) {
        foreach ( $papers as $paper ) {
            // Publish the draft blog post (if not already published)
            $updated = wp_update_post( array(
                'ID'          => $paper->blog_post_id,
                'post_status' => 'publish'
            ) );
            if ( $updated ) {
                rpbg_update_paper_status( $paper->id, 'published', $paper->blog_post_id );
                // Post to social media (stub function)
                $post = get_post( $paper->blog_post_id );
                if ( $post ) {
                    rpbg_post_to_social_media( $paper->blog_post_id, $post->post_content );
                }
            } else {
                rpbg_update_paper_status( $paper->id, 'error', $paper->blog_post_id );
            }
        }
    }
}

// Ensure no closing PHP tag to prevent unintended output.
