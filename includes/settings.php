<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_init', 'rpbg_register_settings' );
function rpbg_register_settings() {
    // OpenAI API key setting
    register_setting( 'rpbg_settings_group', 'rpbg_openai_api_key' );
    
    // Scheduler setting
    register_setting( 'rpbg_scheduler_options_group', 'rpbg_processing_frequency' );
}
?>
