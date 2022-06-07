<?php

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Config')) oes_include('/includes/admin/tools/config.class.php');

if (!class_exists('Cache_Scheduler')) :

    /**
     * Class Cache_Scheduler
     *
     * Implement the config tool for admin configurations.
     */
    class Cache_Scheduler extends Config
    {
        //Overwrite parent
        function information_html(): string
        {
            /* get current scheduled event */
            $updateEvents = [];
            $events = get_option('cron') ?? [];
            foreach($events as $eventTimestamp => $event)
                if(is_array($event))
                foreach($event as $hookKey => $hook)
                    if($hookKey === 'oes_update_archive_cache')
                        foreach($hook as $call)
                            $updateEvents[$eventTimestamp] = $call;

            $recentEvent = '';
            if($updateEvents){
                foreach($updateEvents as $eventTimestamp => $updateEvent)
                    $recentEvent .= '<p class="oes-cache-recent-event" style="color:red">' . sprintf(
                            __('Update event scheduled for %s, repeating %s.'),
                            date('d.m.y h:i', $eventTimestamp),
                            $updateEvent['schedule']
                        ) . '</p>';
                if(empty($recentEvent))
                    $recentEvent = '<p class="oes-cache-no-recent-event">' . __('No update event scheduled.', 'oes') . '</p>';
            }

            return '<div class="oes-tool-information-wrapper-cache-scheduler  oes-tool-information-wrapper">' .
                '<h3>' . __('Scheduler', 'oes') . '</h3>' .
                '<p style="color:red">UNDER CONSTRUCTION</p>' .
                '<p>' .
                __('To update the cache periodically you can schedule an update event. ' .
                    'The scheduled event will be triggered by WordPress at the specified interval. ' .
                    'The action will trigger when someone visits your WordPress site if the scheduled time has passed.'
                    , 'oes') .
                '</p></div>' .
                '<div>' . $recentEvent . '</div>';
        }


        //Overwrite parent
        function data_html(): string
        {
            $enable = '<tr><th><strong>' . __('Enable Scheduler') . '</strong>' .
                '<div>' . __('Enable the scheduler.', 'oes') .
                '</div></th><td>' .
                oes_html_get_form_element('checkbox',
                    'oes_cache-enabled',
                    'oes_cache-enabled',
                    get_option('oes_cache-enabled')) .
                '</td></tr>';


            $timestamp = '<tr><th><strong>' . __('Timestamp') . '</strong>' .
                '<div>' . __('The timestamp is the first time you want the event to occur. ' .
                    'The time format has to be HHii, e.g. for a cache update at 3am insert 0300. ' .
                    'The default value is 0300.', 'oes') .
                '</div></th><td>' .
                oes_html_get_form_element('text',
                    'oes_cache-timestamp',
                    'oes_cache-timestamp',
                    get_option('oes_cache-timestamp')) .
                '</td></tr>';

            $interval = '<tr><th><strong>' . __('Interval') . '</strong>' .
                '<div>' . __('The interval defines the recurrence determining how often and when you want the event to run.', 'oes') .
                '</div></th><td>' .
                oes_html_get_form_element('select',
                    'oes_cache-interval',
                    'oes_cache-interval',
                    get_option('oes_cache-interval'),
                    ['options' => [
                        'daily' => 'Daily',
                        'hourly' => 'Hourly',
                        'twicedaily' => 'Twice Daily'
                    ]]) .
                '</td></tr>';


            return '<table class="oes-option-table oes-config-table oes-replace-select2-inside wp-list-table widefat fixed table-view-list">' .
                '<thead><tr><th colspan="2"><strong>' .
                __('Scheduler Options', 'oes') . '</strong></th></tr></thead><tbody>' .
                $enable .
                $timestamp .
                $interval .
                '</tbody></table>';
        }


        //Implement parent
        function admin_post_tool_action()
        {
            /* add or delete option */
            $options = [
                'oes_cache-enabled',
                'oes_cache-timestamp',
                'oes_cache-interval'
            ];
            foreach ($options as $option)
                if (!oes_option_exists($option)) add_option($option,
                    $option === 'oes_cache-enabled' ? isset($_POST[$option]) : $_POST[$option]
                );
                else update_option($option,
                    $option === 'oes_cache-enabled' ? isset($_POST[$option]) : $_POST[$option]);

            if ($_POST['oes_cache-enabled']) oes_schedule_cache_event();
            else oes_schedule_clear_cache_event();
        }
    }

    // initialize
    register_tool('\OES\Admin\Tools\Cache_Scheduler', 'cache-scheduler');

endif;

/**
 * Schedule a cache update event.
 */
function oes_schedule_cache_event()
{
    /* only prepare schedule event when option is set */
    if (get_option('oes_cache-enabled')) {

        /* clear previous events */
        if(wp_next_scheduled('oes_update_archive_cache')) oes_schedule_clear_cache_event();

        /* prepare event */
        $timeString = get_option('oes_cache-timestamp');
        $interval = get_option('oes_cache-interval');
        wp_schedule_event(
        $timeString ? strtotime($timeString) : time(),
        $interval ?? 'daily',
        'oes_update_archive_cache');
    }
    else {
        oes_schedule_clear_cache_event();
    }
}

/**
 * TODO @nextRelease : improve + fix, should also be done on deactivation register_deactivation_hook(__FILE__, 'oes_schedule_events_cleanup');
 */
function oes_schedule_clear_cache_event()
{
    wp_clear_scheduled_hook('oes_update_archive_cache');
}