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

require_once(CACHOLONG_CACHE_DIR . '/core/designpattern/observer/CacheHelperObserver.php');

use Cacholong\Entities\HttpApiRequest;

/**
 * Observer class for NginxFastCGICacheHelperObserver
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 */
abstract class NginxCacheHelperObserver extends CacheHelperObserver {
  
    /*
     * @var bool    Cache flush could be delayed (true) or not (false)
     */
    protected static $cacheFlushCouldBeDelayed = true;

    /*
     * @var array   HTTP response code(s) for succesfull purge
     */
    protected static $httpResponseCodesPurgeSuccess = [200];
    
    /*
     * @var string   HTTP purge method (use enum from HttpApiRequest)
     */
    protected static $httpMethodPurge = HttpApiRequest::HTTP_METHOD_PURGE;
    
    /**
     * Purge total Nginx cache for given hosts
     * Note: Add /* to host to purge total cache.
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @param       object    $subject      Subject
     *
     * @version     1.0.0     2015-10-27
     * @version     1.0.2     2016-03-28 Change AdminMessage::set_message -> setFastCGIPurgeResult
     * @version     1.1.0     2016-09-09 Abstracted handling of purge result
     * @version     1.2.0     2018-02-28 From curl -> http api request
     *
     * @return      void
     */
    public function purgeAll($subject) {
        $hosts_info = $subject->get_hosts_info();
        $purge_all_suffix = '*';

        $result = $this->purge($hosts_info, $purge_all_suffix);
        $this->handlePurgeResult($hosts_info, $result, $this::get_http_response_codes_success(), self::ALL_CONTENT, $this::getHostsCacheKeyName());
    }

    /**
     * Purge single URL in Nginx cache for given hosts
     * Note: Provide full url to purge single item.
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @param       object    $subject      Subject
     *
     * @version     1.0.0     2015-10-27
     * @version     1.0.1     2016-03-28 Change AdminMessage::set_message -> setFastCGIPurgeResult
     * @version     1.1.0     2016-09-09 Abstracted handling of purge result
     * @version     1.2.0     2018-02-28 From curl -> http api request
     *
     * @return      void
     */
    public function purgeSingleURL($subject) {
        $hosts_info = $subject->get_hosts_info();
        $relative_path = $subject->get_single_relative_path_url();

        $result = $this->purge($hosts_info, $relative_path);
        $this->handlePurgeResult($hosts_info, $result, $this::get_http_response_codes_success(), $relative_path, $this::getHostsCacheKeyName());
    }

    /**
     * Purge multiple URLs in Nginx cache for given hosts
     * Note: Provide full url to purge a item.
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @param       object    $subject      Subject
     *
     * @version     1.0.0     2016-09-08
     * @version     1.1.0     2016-09-09 Abstracted handling of purge result
     * @version     1.2.0     2018-02-28 From curl -> http api request
     *
     * @return      void
     */
    public function purgeMultipleURL($subject) {
        $hosts_info = $subject->get_hosts_info();
        $relative_paths = $subject->get_multiple_relative_path_urls();

        if (is_array($relative_paths) && $relative_paths) {
            foreach ($relative_paths as $relative_path) {
                $result = $this->purge($hosts_info, $relative_path);
                $this->handlePurgeResult($hosts_info, $result, $this::get_http_response_codes_success(), $relative_path, $this::getHostsCacheKeyName());
            }
        }
    }

    /**
     * Purge Nginx cache with HTTP API
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * @link        https://codex.wordpress.org/HTTP_API#Other_Arguments        Valid $options for wp_remote_request
     *
     * @param       array     $hosts_info               Array with arrays. Each array contains hosts info
     * @param       string    $relative_path            Optional. Relative path to add to purge host. Default null.
     * @param       array     $httpApiRequestOptions    Optional. Array with options for http api request, see link for details. Default array().
     *
     * @version     1.0.0     2021-06-18
     *
     * @return      array     Original key as key, value true (succes) or false (failure).
     */
    protected function purge($hosts_info, $relative_path = null, $httpApiRequestOptions = array()) {
        $result = array();

        $default_options[HttpApiRequest::HEADERS][HttpApiRequest::HEADERS_HOST] = cacholong_cc_get_host_name();
        $default_options[HttpApiRequest::TIME_OUT] = self::HTTP_API_TIMEOUT;
        //Disabled to prevent issues with malformed SSL certificates in (shared) hosting environments
        $default_options[HttpApiRequest::SSL_VERIFY] = false;           
        $default_options[HttpApiRequest::HTTP_METHOD] = static::$httpMethodPurge;

        //which identical keys $options array overrules default
        $final_options = $httpApiRequestOptions + $default_options;

        foreach ($hosts_info as $key => $host) {
            $hostKeyName = $this::getHostsCacheKeyName();
            if ($host[$hostKeyName]) {
                $purgeHost = $this->preparePurgeHostUrl($host[\Cacholong\Libraries\Hosts::HOST_KEY], $relative_path);
                $result[$key] = \Cacholong\Libraries\HttpApiRequest::execute($purgeHost, $final_options);
            }
            //else cache is not available, no need to purge
        }

        return $result;
    }
    
    /**
     * Get HTTP response codes for a success
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-11-02
     *
     * @return      array     HTTP response codes
     */
    protected static function get_http_response_codes_success() {
        $statusCodes = static::$httpResponseCodesPurgeSuccess;
        
        if (WP_DEBUG === TRUE) {
            $statusCodes[] = 404;
        }
        
        return array_unique($statusCodes);
    }
}
