<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_init', 'rpbg_register_settings' );
function rpbg_register_settings() {
    // OpenAI API key setting
    register_setting( 'rpbg_settings_group', 'rpbg_openai_api_key' );
    
    // Generation prompt setting with a default prompt
    register_setting( 'rpbg_settings_group', 'rpbg_generation_prompt' );
    
    // OpenAI Model setting (default "davinci")
    register_setting( 'rpbg_settings_group', 'rpbg_openai_model' );
    
    // Scheduler settings
    register_setting( 'rpbg_scheduler_options_group', 'rpbg_processing_frequency' );
    register_setting( 'rpbg_scheduler_options_group', 'rpbg_processing_time' );
    register_setting( 'rpbg_scheduler_options_group', 'rpbg_timezone' );
}
?>
