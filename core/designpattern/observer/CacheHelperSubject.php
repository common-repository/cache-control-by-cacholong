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

namespace Cacholong\Designpattern\Observer;

defined('ABSPATH') OR exit;

use \Cacholong\Libraries\AdminMessage;
use \Cacholong\Entities\AdminNoticeClass;

/**
 * Subject class for cache helper
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 *
 */
class CacheHelperSubject {
    /*
     * @var CacheHelperObserver Observers for this class
     */

    protected $observers;

    /*
     * @var array   Array of hosts
     */
    private $hosts;

    /*
     * @var string  Relative path for single url
     */
    private $relative_path;

    /*
     * @var array Relative paths for selection of urls
     */
    private $relative_paths;

    /**
     * Initialize variable(s)
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-27
     * @version     1.0.1     2016-09-08 relative_paths = null
     *
     * @return      void
     */
    public function __construct() {
        $this->observers = array();
        $this->hosts = array();

        $this->relative_path = null;
        $this->relative_paths = array();
    }

    /**
     * Attach observer
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-27
     *
     * @return      void
     */
    public function attach(CacheHelperObserver $observer) {
        //prevent same object observing twice or more
        $i = array_search($observer, $this->observers);
        if ($i === false) {
            $this->observers[] = $observer;
        }
    }

    /**
     * Detach observer
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-27
     *
     * @return      void
     */
    public function detach(CacheHelperObserver $observer) {
        if (!empty($this->observers)) {
            $index = array_search($observer, $this->observers);
            if ($index !== false) {
                unset($this->observers[$index]);
            }
        }
    }

    /**
     * Get all observers
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-27
     *
     * @return      array     Array of CacheHelperObserver objects or empty array
     */
    public function getObservers() {
        return $this->observers;
    }

    /**
     * Notify all observers of purge all request
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-27    Original
     * @version     1.0.1     2016-03-25    Call to handlePurgeResults*
     *
     * @return      void
     */
    public function notifyPurgeAllRequest() {
        $this->notify($this, 'purgeAll');
        $this->handlePurgeResults();
    }

    /**
     * Notify all observers of purge all request
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-27    Original
     * @version     1.0.1     2016-03-25    Call to handlePurgeResults
     *
     * @return      void
     */
    public function notifyPurgeSingleRequest() {
        $this->notify($this, 'purgeSingleURL');
        $this->handlePurgeResults();
    }

    /**
     * Notify all observers of a purge multiple request
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2016-09-08
     *
     * @return      void
     */
    public function notifyPurgeMultipleRequest() {
        $this->notify($this, 'purgeMultipleURL');
        $this->handlePurgeResults();
    }

    /**
     * Get purge results from all observers and create admin message
     * Note: Because no information about items is preserved, identical messages could be set when an post is saved and also a slug is changed.
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-27
     * @version     1.1.0     2016-08-31 Admin route / non-admin route
     * @version     1.2.0     2016-09-09 Added prefix/suffix var and added it to all hosts
     * @version     1.3.0     2017-12-21 Adjusted success text
     * @version     1.4.0     2018-01-24 flushDelayPossible implemented
     * @version     1.4.1     2018-01-24 Bugfix: 'or' needed to allow a single flushDelayPossible to make the text show
     * @return      void
     */
    private function handlePurgeResults() {
        $purgeResults = $this->notify($this, 'getPurgeResults', true);

        //Content of purgeResults -> [$adminNoticeClass][$cacheHelperName]['hosts'][$hostName] = item
        //Content of purgeResults -> [$adminNoticeClass][$cacheHelperName]['flushDelayPossible'] = bool

        foreach ($purgeResults as $adminNoticeClass => $cacheHelpers) {
            //cache helper sentences
            $cacheHelperSentences = array();

            //flush delay possible
            $flushDelayPossible = false;

            foreach ($cacheHelpers as $cacheHelper => $cacheHelperResult) {
                //host information per cache helper
                $hostNames = array_keys($cacheHelperResult['hosts']);

                $prefix = '<b>';
                $suffix = '</b>';

                if ($hostNames[0] == CacheHelperObserver::ALL_HOSTS && count($hostNames) == 1) {
                    $hostNamesSentence = $prefix . __("all hosts", \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN) . $suffix;
                } else {
                    $hostNamesSentence = cacholong_cc_itemlist_to_sentence($hostNames, $prefix, $suffix);
                }

                //no info needed about items

                $cacheHelperSentences[] = sprintf(_x("%s cache (%s)", "'Name of cache' cache (List of hosts)", \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), $cacheHelper, $hostNamesSentence);

                //flush delay possible
                $flushDelayPossible |= (array_key_exists('flushDelayPossible', $cacheHelperResult) && $cacheHelperResult['flushDelayPossible']);
            }

            //create final message
            $finalCacheHelperSentence = cacholong_cc_itemlist_to_sentence($cacheHelperSentences);

            switch ($adminNoticeClass) {
                case AdminNoticeClass::SUCCESS: //ok
                    $message = sprintf(__("Succesfully purged %s.", \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), $finalCacheHelperSentence);

                    if ($flushDelayPossible) {
                        $message .= ' ' . __("It could take a few minutes to be visible at the frontend.", \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN);
                    }

                    if (is_admin()) {
                        AdminMessage::set_message($message, AdminNoticeClass::SUCCESS, true);
                    } else {
                        cacholong_cc_log_error($message);
                    }
                    break;
                case AdminNoticeClass::ERROR:   //ok
                    $message = sprintf(__("Failed to purge %s.", \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), $finalCacheHelperSentence);

                    if (is_admin()) {
                        AdminMessage::set_message($message, AdminNoticeClass::ERROR, true);
                    } else {
                        cacholong_cc_log_error($message);
                    }
                    break;
                default:    //ok
                    cacholong_cc_log_error(__("CacheHelperSubject > handlePurgeResults > No handling available for admin notice class '{$adminNoticeClass}'. Programmer, fix this!", \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), __FILE__);
                    break;
            }
        }
    }

    /**
     * Notify observers with customMethod.
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-27        Original
     * @verson      1.0.1     2016-03-25        Added return result option
     *
     * @param       object    $subject          Subject to pass to observer
     * @param       string    $customMethod     Method name to notify of change on observer.
     * @param       bool      $returnResult     Optional. Return result of $customMethod (true) or not (false). Default false.
     *
     * @return      mixed     Array with result of $customMethod or nothing (void)
     */
    private function notify($subject, $customMethod, $returnResult = false) {
        $result = array();

        if (!empty($this->observers)) {
            foreach ($this->observers as $observer) {
                if (!empty($customMethod) && method_exists($observer, $customMethod)) {
                    if ($returnResult) {
                        $observerResult = $observer->$customMethod($subject);
                        $result = array_merge_recursive($result, $observerResult);   //will merge all non-numeric keys
                    } else {
                        $observer->$customMethod($subject);
                    }
                } else {
                    $observer_name = get_class($observer);
                    cacholong_cc_log_exception(
                            new \Exception(
                            sprintf(
                                    _x("Custom method '%s' does not exist in observer class '%s'.", 'Internal exception message', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), $customMethod, $observer_name)
                            )
                    );
                }
            }

            if ($returnResult) {
                return $result;
            }
        }
    }

    /**
     * Set info for one / more host(s)
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @var         array|string        $hosts          Array of hosts info (see Hosts library for keys and used constants)
     * @version     1.0.0               2015-10-27
     *
     * @return      void
     */
    public function set_hosts_info($hosts) {
        if (is_array($hosts)) {
            $this->hosts = $hosts;
        }
    }

    /**
     * Get info for one / more host(s)
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-27
     *
     * @return      array      Array of hosts info
     */
    public function get_hosts_info() {
        return $this->hosts;
    }

    /**
     * Set single path, relative to host
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-27
     * @version     1.1.0     2016-09-08 Rename function from set_relative_path_url
     *
     * @return      void
     */
    public function set_single_relative_path_url($path) {
        $path = ltrim($path, '/');
        $this->relative_path = $path;
    }

    /**
     * Get single path, relative from host
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-27
     * @version     1.1.0     2016-09-08 Rename function from get_relative_path_url
     *
     * @return      string    Relative url path
     */
    public function get_single_relative_path_url() {
        return $this->relative_path;
    }

    /**
     * Set multiple relative paths, relative to host
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2016-09-07
     *
     * @param       string|array      $paths        Path(s) to set
     *
     * @return      void
     */
    public function set_multiple_relative_path_urls($paths) {
        $relative_paths = is_array($paths) ? $paths : (array) $paths;
        array_walk($relative_paths, function(&$single_path) {
            $single_path = ltrim($single_path, '/');
        });

        $this->relative_paths = array_merge($this->relative_paths, $relative_paths);
    }

    /**
     * Get multiple relative paths, relative to host
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2016-09-07
     *
     * @return      array     Multiple relative url paths
     */
    public function get_multiple_relative_path_urls() {
        return $this->relative_paths;
    }

    /**
     * Get cron settings field key for cache helper and cache helper name
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2019-02-20
     *
     * @return      array     Multiple relative url paths
     */
    public function getCronSettingsFieldKeyAndName() {
        $cronSettings = array();
        $observers = $this->getObservers();

        foreach ($observers as $observer) {
            $key = \Cacholong\Entities\Identifier::CC_FIELD_CRON_CACHE_PREFIX . $observer::getCacheHelperCronSettingCacheFieldKey();
            $name = $observer::getCacheHelperName();
            $cronSettings[$key] = $name;
        }
        return $cronSettings;
    }

    /**
     * Get cron settings field key for cache helper and cache helper hook purge name
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2019-02-21
     *
     * @return      array     Multiple relative url paths
     */
    public function getCronSettingsFieldKeyAndHookPurgeName() {
        $cronSettings = array();
        $observers = $this->getObservers();

        foreach ($observers as $observer) {
            $key = \Cacholong\Entities\Identifier::CC_FIELD_CRON_CACHE_PREFIX . $observer::getCacheHelperCronSettingCacheFieldKey();
            $hookName = \Cacholong\Entities\Identifier::CC_HOOK_PURGE_CACHE_PREFIX . $observer::getCacheHelperHookName();
            $cronSettings[$key] = $hookName;
        }
        return $cronSettings;
    }

}
