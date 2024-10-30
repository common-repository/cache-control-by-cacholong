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
 * Observer class for NginxPageSpeedCacheHelperObserver
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 */
class NginxPageSpeedCacheHelperObserver extends NginxCacheHelperObserver {
    /*
     * @var string   Name for this cache helper
     */

    protected static $cacheHelperName = 'pagespeed';

    /*
     * @var string   Name of WP CLI command
     */
    protected static $cacheHelperWPCLICommandName = 'pagespeed';

    /*
     * @var string   Cron setting field key for this cache helper
     */
    protected static $cacheHelperCronSettingCacheFieldKey = 'cron_pagespeed';

    /*
     * @var string   Hook name for this cache helper (only use hook valid chars)
     */
    protected static $cacheHelperHookName = 'pagespeed';

    /*
     * @var string   Hook name for this cache helper (only use hook valid chars)
     */
    protected static $hostsCacheKeyName = \Cacholong\Libraries\Hosts::NGINX_PAGESPEED_KEY;
    
    /*
     * @var string   HTTP purge method (use enum from HttpApiRequest)
     */
    protected static $httpMethodPurge = HttpApiRequest::HTTP_METHOD_PURGE;
}
