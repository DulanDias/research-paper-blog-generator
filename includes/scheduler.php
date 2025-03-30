<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Returns the next scheduled timestamp for processing based on user-defined time and timezone.
 *
 * Settings used:
 * - rpbg_processing_time: The desired time of day in "HH:MM" (24-hour format, e.g., "09:00").
 * - rpbg_timezone: The timezone identifier (e.g., "America/New_York"). Defaults to the WordPress timezone_string or "UTC".
 *
 * @return int Timestamp for the next scheduled event.
 */
function rpbg_get_next_scheduled_time() {
    // Get the processing time; default to "09:00" if not set.
    $processing_time = get_option( 'rpbg_processing_time', '09:00' );
    // Get the timezone setting; fallback to WordPress timezone or UTC.
    $timezone_string = get_option( 'rpbg_timezone', get_option( 'timezone_string', 'UTC' ) );

    try {
        // Create a DateTime object for "now" in the specified timezone.
        $now = new DateTime( 'now', new DateTimeZone( $timezone_string ) );
    } catch ( Exception $e ) {
        $now = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
    }

    // Create a DateTime object for today at the specified processing time.
    $today_date = $now->format( 'Y-m-d' );
    $scheduled_time_str = $today_date . ' ' . $processing_time;
    try {
        $scheduled_datetime = new DateTime( $scheduled_time_str, new DateTimeZone( $timezone_string ) );
    } catch ( Exception $e ) {
        // Fallback to now if there's an error.
        $scheduled_datetime = clone $now;
    }

    // If the scheduled time for today has already passed, schedule for the next day.
    if ( $scheduled_datetime < $now ) {
        $scheduled_datetime->modify( '+1 day' );
    }

    return $scheduled_datetime->getTimestamp();
}

/**
 * Reschedules the cron event for processing research papers.
 *
 * It reads the "rpbg_processing_frequency" setting (which can be "hourly", "twicedaily", or "daily")
 * and schedules the event at the next occurrence based on the user-defined processing time.
 */
function rpbg_reschedule_cron() {
    // Clear any existing scheduled event.
    $timestamp = wp_next_scheduled( 'rpbg_cron_job' );
    if ( $timestamp ) {
        wp_unschedule_event( $timestamp, 'rpbg_cron_job' );
    }
    
    // Get processing frequency from settings; default to "daily".
    $frequency = get_option( 'rpbg_processing_frequency', 'daily' );

    // Calculate the next scheduled time based on the processing time and timezone.
    $next_timestamp = rpbg_get_next_scheduled_time();

    // Determine the recurrence based on the frequency setting.
    switch ( $frequency ) {
        case 'hourly':
            $recurrence = 'hourly';
            break;
        case 'twicedaily':
            $recurrence = 'twicedaily';
            break;
        case 'daily':
        default:
            $recurrence = 'daily';
            break;
    }

    wp_schedule_event( $next_timestamp, $recurrence, 'rpbg_cron_job' );
}

/**
 * Update scheduler settings.
 *
 * This function is hooked to 'admin_init' so that whenever the admin area is loaded,
 * the cron event is rescheduled according to the current settings.
 */
function rpbg_update_scheduler() {
    rpbg_reschedule_cron();
}
add_action( 'admin_init', 'rpbg_update_scheduler' );
?>
