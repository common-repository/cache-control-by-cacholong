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

require_once(CACHOLONG_CACHE_DIR . '/core/designpattern/observer/NginxCacheHelperObserver.php');

use Cacholong\Entities\HttpApiRequest;

/**
 * Observer class for NginxFastCGICacheHelperObserver
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 */
class NginxFastCGICacheHelperObserver extends NginxCacheHelperObserver {
    
    /*
     * @const string   Prefix to use in url to purge single item. host/{PURGE_PREFIX_RELATIVE_URL}/path/to/item
     */
    const PURGE_PREFIX_RELATIVE_URL = 'purge';

    /*
     * @var string   Name for this cache helper
     */

    protected static $cacheHelperName = 'fastCGI';

    /*
     * @var string   Name of WP CLI command
     */
    protected static $cacheHelperWPCLICommandName = 'fastcgi';

    /*
     * @var string   Cron setting field key for this cache helper
     */
    protected static $cacheHelperCronSettingCacheFieldKey = 'cron_fastCGI';

    /*
     * @var string   Hook name for this cache helper (only use hook valid chars)
     */
    protected static $cacheHelperHookName = 'fastcgi';

    /*
     * @var string   Hook name for this cache helper (only use hook valid chars)
     */
    protected static $hostsCacheKeyName = \Cacholong\Libraries\Hosts::NGINX_FASTCGI_KEY;
    
    /*
     * @var string   HTTP purge method (use enum from HttpApiRequest)
     */
    protected static $httpMethodPurge = HttpApiRequest::HTTP_METHOD_GET;
    
    /**
     * Purge FastCGI Nginx cache with HTTP API
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
        
        $prefixed_relative_path = self::PURGE_PREFIX_RELATIVE_URL . '/' . ltrim($relative_path, '/');
        return parent::purge($hosts_info, $prefixed_relative_path, $httpApiRequestOptions);
    }
}
