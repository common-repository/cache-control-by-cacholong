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

require_once(CACHOLONG_CACHE_DIR . '/core/libraries/HttpApiRequest.php');

use \Cacholong\Entities\AdminNoticeClass;

/**
 * Observer class for cache helper
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 *
 */
abstract class CacheHelperObserver {

    /*
     * @const int       HTTP API time out
     */
    CONST HTTP_API_TIMEOUT = 5;

    /*
     * @const string    All hosts
     */
    CONST ALL_HOSTS = '*';

    /*
     * @const string    All content
     */
    CONST ALL_CONTENT = '*';
    
    /*
     * @var string   Name for this cache helper
     */
    protected static $cacheHelperName;

    /*
     * @var string   Name of WP CLI command
     */
    protected static $cacheHelperWPCLICommandName = 'all';
    
    /*
     * @var string   Cron setting field key for this cache helper
     */
    protected static $cacheHelperCronSettingCacheFieldKey;

    /*
     * @var string   Hook name for this cache helper (only use hook valid chars)
     */
    protected static $cacheHelperHookName;
    
    /*
     * @var string   Hook name for this cache helper (only use hook valid chars)
     */
    protected static $hostsCacheKeyName;

    /*
     * @var bool      Cache flush could be delayed (true) or not (false)
     */
    protected static $cacheFlushCouldBeDelayed = false;

    /*
     * @var array     Array with results
     */

    private $_results = array();

    /**
     * Constructor with optional subject to bind observer immediatly
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-27
     *
     * @param       CacheHelperSubject  $subject       Optional. Subject to attact this observer to. Default null.
     *
     * @return      void
     */
    public function __construct(CacheHelperSubject $subject = null) {
        if (is_object($subject) && $subject instanceof CacheHelperSubject) {
            $subject->attach($this);
        }
    }

    /**
     * Get host names for failed purges, based on given http api request results
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @var         array     $host_info                    All info of hosts
     * @var         array     $result                       Results from http api request. Each key refers to key in host_info
     * @var         mixed     $http_status_code_success     Optiona. Status code(s) for succesfull purge. Default null, means all stati are valid.
     *
     * @version     1.0.0     2015-10-28
     * @version     1.0.1     2015-11-02 Added status code
     * @version     1.1.0     2018-02-28 Removed curl references
     *
     * @return      array     Array with all failed purge names or empty array.
     */
    protected function get_failed_purge_names($host_info, $result, $http_status_code_success = null) {
        $failedNames = array();

        if (!empty($http_status_code_success)) {
            $http_status_code_success = is_array($http_status_code_success) ? $http_status_code_success : (array) $http_status_code_success;

            foreach ($result as $key => $single_result) {
                if (!in_array($single_result, $http_status_code_success)) {
                    $failedNames[] = $host_info[$key][\Cacholong\Libraries\Hosts::NAME_KEY];
                }
                //else succesfull purge
            }
        }
        //else all results are valid

        return $failedNames;
    }

    /**
     * Get host names for succesfull purges, based on given results
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @var         array     $host_info                    All info of hosts
     * @var         array     $result                       Result from http api request. Each key refers to key in host_info
     * @var         string    $hostCacheKeyName             Key name used in $hosts_info for cache. Use constant such as NGINX_FASTCGI_KEY / NGINX_PAGESPEED_KEY.
     * @var         mixed     $http_status_code_success     Optiona. Status code(s) for succesfull purge. Default null, means all stati are valid.
     *
     * @version     1.0.0     2016-09-09
     * @version     1.1.0     2018-02-28 Removed curl references
     *
     * @return      array     Array with all succesfull purge names or empty array.
     */
    protected function get_succes_purge_names($host_info, $result, $hostCacheKeyName, $http_status_code_success = null) {
        $succesNames = array();
        $http_status_code_success = is_array($http_status_code_success) ? $http_status_code_success : (array) $http_status_code_success;

        //check for each host
        foreach ($host_info as $key => $host) {
            if ($host[$hostCacheKeyName]) {
                if (empty($http_status_code_success)) {    //
                    $succesNames[] = $host_info[$key][\Cacholong\Libraries\Hosts::NAME_KEY];
                } else {
                    $single_result = $result[$key];
                    if (in_array($single_result, $http_status_code_success)) {
                        $succesNames[] = $host_info[$key][\Cacholong\Libraries\Hosts::NAME_KEY];
                    }
                    //else invalid
                }
            }
            //else this cache is not active for this host, do nop
        }

        return $succesNames;
    }

    /**
     * Get cache helper name
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2016-09-09
     *
     * @return      string   Name of cache helper
     */
    public static function getCacheHelperName() {
        return static::$cacheHelperName;
    }
    
     /**
     * Required get method to get WP CLI command name
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2021-06-18
     *
     * @return      string   Name of WP CLI command
     */
    public static function getWPCLICommandName()
    {
        return static::$cacheHelperWPCLICommandName;
    }

    /**
     * Get cache helper hook name
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2016-09-09
     *
     * @return      string   Name of hook for cache helper
     */
    public static function getCacheHelperHookName() {
        return static::$cacheHelperHookName;
    }

     /**
     * Get cache helper cron setting cache field key
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2019-02-19
     *
     * @return      string   cache helper cron setting cache field key
     */
    public static function getCacheHelperCronSettingCacheFieldKey() {
        return static::$cacheHelperCronSettingCacheFieldKey;
    }
    
    /**
     * Get cache flush could be delayed switch
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0    2018-01-24
     *
     * @return      bool     Cache flush could be delayed (true) or not (false)
     */
    public static function getFlushCouldBeDelayed() {
        return static::$cacheFlushCouldBeDelayed;
    }
    
     /**
     * Get hosts cache key name
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2016-09-09
     *
     * @return      string   Name of cache helper
     */
    public static function getHostsCacheKeyName() {
        return static::$hostsCacheKeyName;
    }

    /**
     * Required update method for purge all
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-27
     *
     * @return      void
     */
    abstract public function purgeAll($subject);

    /**
     * Required update method for purge of single item
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-27
     *
     * @return      void
     */
    abstract public function purgeSingleURL($subject);

    /**
     * Required update method for purge of multiple items
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2016-09-08
     *
     * @return      void
     */
    abstract public function purgeMultipleURL($subject);

    /**
     * Handle purge results
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2016-09-09    Abstracted from children
     * @version     1.0.1     2016-09-09    Added switch to detect if cache is enabled (to prevent message that cache is cleared, when it is not active).
     * @version     1.1.0     2018-01-24    Call to getFlushCouldBeDelayed
     * @version     1.2.0     2018-02-28    All references to curl removed
     *
     * @var         array     $host_info                    All info of hosts
     * @var         array     $result                       Results from http api request. Each key refers to key in host_info
     * @var         mixed     $cacheItem                    Cache item, name to show in message.
     * @var         mixed     $httpResponseCodes            HTTP Status code(s)
     * @var         string    $hostCacheKeyName             Key name used in $hosts_info for cache. Use constant such as NGINX_FASTCGI_KEY / NGINX_PAGESPEED_KEY.
     *
     * @return      void
     */
    public function handlePurgeResult($hosts_info, $result, $httpResponseCodes, $cacheItem, $hostCacheKeyName) {
        $failedNames = $this->get_failed_purge_names($hosts_info, $result, $httpResponseCodes);

        if (empty($failedNames)) {
            $successNames = $this->get_succes_purge_names($hosts_info, $result, $hostCacheKeyName, $httpResponseCodes);

            if (!empty($successNames)) {
                $this->setPurgeResult($this::getCacheHelperName(), AdminNoticeClass::SUCCESS, $successNames, $cacheItem, $this::getFlushCouldBeDelayed());
            }
            //else no fails, no success -> no message
        } else {
            $this->setPurgeResult($this::getCacheHelperName(), AdminNoticeClass::ERROR, $failedNames, $cacheItem, $this::getFlushCouldBeDelayed());
        }
    }

    /**
     * Set purge result in _result var
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2016-03-25
     * @version     1.1.0     2018-01-24    Added $flushCouldBeDelayed + change in output array
     *
     * @var         string              $cacheHelperName            Name of cache helper
     * @var         string              $adminNoticeClass           Admin notice class
     * @var         array|string        $hostNames                  Optional. Hostname or hostnames. Default self::ALL_HOSTS.
     * @var         string              $cacheItem                  Optional. Cache item. Default self::ALL_CONTENT.
     * @var         bool                $flushCouldBeDelayed        Optional. Flush could be delayed (true) or not (false). Default false.
     *
     * @return      void
     */
    protected function setPurgeResult($cacheHelperName, $adminNoticeClass, $hostNames = self::ALL_HOSTS, $cacheItem = self::ALL_CONTENT, $flushCouldBeDelayed = false) {
        $hostNames = is_array($hostNames) ? $hostNames : (array) $hostNames;

        foreach ($hostNames as $hostName) {
            $this->_results[$adminNoticeClass][$cacheHelperName]['hosts'][$hostName] = $cacheItem;
            $this->_results[$adminNoticeClass][$cacheHelperName]['flushDelayPossible'] = $flushCouldBeDelayed;
        }
    }

    /**
     * Get all purge results
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-11-02
     * @version     1.1.0     2018-01-24 Improved phpDoc
     *
     * @var         $subject        Subject
     * @var         $eraseResults   Optional. Erase results after function returns results. Default true.
     *
     * @return      array     Array with purge results, like this: [$adminNoticeClass][$cacheHelperName]['hosts'][$hostName] = $cacheItem AND [$adminNoticeClass][$cacheHelperName]['flushDelayPossible'] = bool
     */
    public function getPurgeResults($subject, $eraseResults = true) {
        $results = $this->_results;
        if ($eraseResults) {
            $this->_results = array();
        }
        return $results;
    }

    /**
     * Prepare purge host url
     * - adds https when called from https domain.
     * - adds relative path to url when given
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2018-02-28        Abstracted from xCacheHelperObserver->purge() to here
     * @param       string    $hostUrl          Host url
     * @param       string    $relative_path    Optional. Relative path to add to host url. Default null.
     *
     * @return      string    $url
     */
    public function preparePurgeHostUrl($hostUrl, $relative_path = null) {
        //Prepare host (remove http(s) + add relative path)
        $hostUrl = preg_replace('#^https?://#', '', $hostUrl);
        $hostUrl .= !empty($relative_path) ? '/' . $relative_path : null;

        //If the website is handled over SSL, use correct protocol.
        $protocol = 'http://';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $protocol = 'https://';
        }

        $hostUrl = $protocol . $hostUrl;


        if (WP_DEBUG === TRUE) {
            $cacheName = $this::getCacheHelperName();
            cacholong_cc_log_error("Purge {$cacheName} cache: {$hostUrl}");
        }

        return $hostUrl;
    }

}
