<?php

/**
 * Copyright © 2015-2021, Cacholong <info@cacholong.nl>
 *
 * This file is part of Cache Control
 *
 * Cache Control is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Cache Control is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Cache Control.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Cacholong\Libraries;

defined('ABSPATH') OR exit;

/**
 * Scheduler
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 *
 * @version     1.0.0     2019-02-20
 */
class Scheduler {

    /**
     * Set daily event (and remove old one if available)
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2019-02-20
     *
     * @param       string    $timeHHMM     Time in format HH:MM to schedule event.
     * @param       string    $hook         Action hook to execute when event is run.
     *
     * @return      false|void  False when event is not scheduled
     */
    public static function set_daily_event($timeHHMM, $hook) {

        //create hour and minute
        $safeTimeHHMM = cacholong_cc_sanitize_time_hh_mm_24h($timeHHMM);

        if ($safeTimeHHMM) {

            //guaranteed hh:mm format
            $time = explode(':', $timeHHMM);
            $hour = (int) $time[0];
            $minute = (int) $time[1];

            //remove old hook
            if (wp_next_scheduled($hook)) {
                self::unset_event($hook);
            }

            //add new
            self::set_event($hour, $minute, \Cacholong\Entities\SchedulerRecurrence::DAILY, $hook);
        } else {    //ok
            cacholong_cc_log_error("Failed to set daily event, because of faulty time HH MM string: {$timeHHMM}.");
        }
    }

    /**
     * Set schedule event
     *
     * Hook will trigger:
     * - if someone visits site after scheduled time
     * - when DISABLE_WP_CRON is active +  wp-cron.php is called with real cronjob after scheduled time
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2019-02-20
     * @version     1.0.1     2019-02-22    Abstracted get timezone to function
     * @version     1.0.2     2019-11-29    Moved todayFormatted line within if clause
     *
     * @param       int       $hour         Scheduled time hour.
     * @param       int       $minute       Scheduled time minute.
     * @param       string    $recurrence   How often the event should recur. Valid options are hourly, daily or twicedaily.
     * @param       string    $hook         Action hook to execute when event is run.
     * @param       array     $arguments    Optional. Arguments to pass to the hook's callback function. Default [].
     *
     * @return      void
     */
    private static function set_event($hour, $minute, $recurrence, $hook, $arguments = []) {

        $currentTimeZone = cacholong_cc_get_timezone();

        $dtzTimeZone = new \DateTimeZone($currentTimeZone);
        $today = new \DateTime('now', $dtzTimeZone);
        $today->setTime((int) $hour, (int) $minute);

        $scheduled = wp_schedule_event($today->getTimeStamp(), $recurrence, $hook, $arguments);

        if ($scheduled === false) {
            $todayFormatted = $today->format(\Cacholong\Entities\Identifier::DEFAULT_DATETIME);
            cacholong_cc_log_error("Failed to add event to scheduler. Parameters where: scheduled time: {$todayFormatted}, recurrence: {$recurrence}.");

            $logHook = print_r($hook, true);
            $logArguments = print_r($arguments, true);

            cacholong_cc_log_error("Hook: {$logHook}");
            cacholong_cc_log_error("Arguments: {$logArguments}");
        }
        //else do nop
    }

    /**
     * Unset scheduled event
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2019-02-20
     *
     * @param       string    $hook         Action hook to execute when event is run.
     * @param       array     $arguments    Optional. Arguments to pass to the hook's callback function. Default [].
     *
     * @return      false|void  False when event is not scheduled
     */
    public static function unset_event($hook, $arguments = []) {
        wp_clear_scheduled_hook($hook, $arguments);
    }

}

//test
//Scheduler::set_daily_event('10:32', 'cc_debug_hook');
