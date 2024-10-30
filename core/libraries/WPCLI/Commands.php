<?php

namespace Cacholong\Libraries\WPCLI;

use Cacholong\Designpattern\Observer;
use Cacholong\Entities;
use Cacholong\Libraries\{ArrayHelper, AllSiteOptions};

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
class Commands
{

    /**
     * Purge specifc cache (fastcgi, pagespeed) or all caches (all).
     *
     * ## OPTIONS
     *
     * [--cache=<cache>]
     * : Type of cache to purge.
     * ---
     * default: all
     * options:
     *   - fastcgi
     *   - pagespeed
     *   - all
     * ---
     *
     * [--ips=<ips>]
     * : Optional. One or more IP adresses to use in factory reset. Using this argument ignores hosts.json.
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     Created at 2020-10-15
     * @version     1.0.1     Handle override ips
     * @version     1.0.2     * -> all
     *
     * @param       array   $arguments     Possitional argument(s), not used.
     * @param       array   $assoc_args    Associative argument(s)
     *
     * @return      void
     */
    public function purge($arguments, $assoc_args)
    {
        $cacheName = $this->getFlagValue($assoc_args, 'cache');
        $this->handleOverrideIps($assoc_args);

        switch ($cacheName) {
            case Observer\NginxFastCGICacheHelperObserver::getWPCLICommandName():
                $this->handlePurgeNginxFastCGI();
                break;
            case Observer\NginxPageSpeedCacheHelperObserver::getWPCLICommandName():
                $this->handlePurgeNginxPageSpeed();
                break;
            case Observer\CacheHelperObserver::getWPCLICommandName():
                $this->handlePurgeAll();
                break;
            default:
                $this->handlePurgeUnknownCommand($cacheName);
        }
    }

    /**
     * Factory reset options of current or specified blog.
     *
     * [--site_id=<site_id>]
     * : Optional. Site id used in factory reset. Default: current blog id.
     *
     * @alias factory-reset
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     Created at 2020-10-16
     *
     * @param       array   $arguments     Possitional argument(s)
     * @param       array   $assoc_args    Associative argument(s)
     *
     * @return      void
     */
    public function factory_reset($arguments, $assoc_args)
    {
        $siteId = $this->getFlagValue($assoc_args, 'site_id');
        \Cacholong\Libraries\CacholongCacheControlSetup::reset_options_to_default($siteId);

        $message = sprintf(__('Factory reset site with id %s.', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), $siteId);
        static::outputAndExitSuccess($message);
    }

    /**
     * Handle purge nginx fastCGI
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     Created at 2020-10-15
     *
     * @return      void
     */
    private function handlePurgeNginxFastCGI()
    {
        $this->handlePurgeAllSingleCache(Observer\NginxFastCGICacheHelperObserver::class);
    }

    /**
     * Handle purge nginx pagespeed
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     Created at 2020-10-15
     *
     * @return      void
     */
    private function handlePurgeNginxPageSpeed()
    {
        $this->handlePurgeAllSingleCache(Observer\NginxPageSpeedCacheHelperObserver::class);
    }

    /**
     * Handle purge all for specified cache
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     Created at 2020-10-16
     *
     * @param       string    $cacheHelperObserver  Valid (sub)class name of Observer\CacheHelperObserver::class
     * @return      void
     */
    private function handlePurgeAllSingleCache($cacheHelperObserver)
    {
        if (is_subclass_of($cacheHelperObserver, Observer\CacheHelperObserver::class, true)) { //ok
            $hookName = \Cacholong\Entities\Identifier::CC_HOOK_PURGE_CACHE_PREFIX . $cacheHelperObserver::getCacheHelperHookName();
            \Cacholong\Controller\Admin\PurgeCacheController::handle_purge_all_specific($hookName);

            $cacheHelperName = $cacheHelperObserver::getCacheHelperName();
            $message = sprintf(__("Purged %s cache."), $cacheHelperName);
            static::outputAndExitSuccess($message);
        } else {    //ok
            $message = __("Tried to handle purge for single cache, but cacheHelperObserver ({$cacheHelperObserver}) is invalid.");
            cacholong_cc_log_error($message, __FILE__);
            static::outputAndExitError($message);
        }
    }

    /**
     * Handle purge all
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     Created at 2020-10-15
     *
     * @return      void
     */
    private function handlePurgeAll()
    {
        $message = __('Purged all caches.', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN);
        \Cacholong\Controller\Admin\PurgeCacheController::handle_purge_all();
        static::outputAndExitSuccess($message);
    }

    /**
     * Handle purge unknown command
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     Created at 2020-10-15
     *
     * @return      void
     */
    private function handlePurgeUnknownCommand($cacheName)
    {
        $message = sprintf(__("Purge recieved unknown command '%s'.", \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), $cacheName);
        cacholong_cc_log_error($message, __FILE__);
        static::outputAndExitError($message);
    }

    /**
     * Output success message and exits Entities\WPCLIExitCode::SUCCESS
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     Created at 2020-10-16
     * @see         https://make.wordpress.org/cli/handbook/references/internal-api/wp-cli-log/
     *
     * @param       string    $message          Message
     *
     * @return      void
     */
    public static function outputAndExitSuccess($message)
    {
        \WP_CLI::success($message);
        exit(Entities\WPCLIExitCode::SUCCESS);
    }

    /**
     * Output error message and exits Entities\WPCLIExitCode::ERROR_GENERIC
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     Created at 2020-10-16
     * @see         https://make.wordpress.org/cli/handbook/references/internal-api/wp-cli-log/
     *
     * @param       string    $message          Message
     *
     * @return      void
     */
    public static function outputAndExitError($message)
    {
        \WP_CLI::error($message, Entities\WPCLIExitCode::ERROR_GENERIC);    //also exists
    }

    private function getFlagValue($assoc_args, $flag)
    {
        return \WP_CLI\Utils\get_flag_value($assoc_args, $flag);
    }

    /**
     * Store possible ip(s) if existing. These ips will be used in get_hosts() to create a override.
     * Supports single ip, multiple ips comma-seperated or "ip1, ip2, <...>" (double quoted)
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     Created at 2021-04-07
     *
     * @param       array   $assoc_args    Associative argument(s) with possible 'ips' argument.
     *
     * @return      void
     */
    private function handleOverrideIps($assoc_args)
    {
        $ips = $this->getFlagValue($assoc_args, 'ips');
        if ($ips) {

            $ips = explode(',', $ips);
            $trimmed_ips = ArrayHelper::trim($ips);

            add_option(Entities\Identifier::CC_TEMP_OVERRIDE_IPS, $trimmed_ips);
        }
    }
}

\WP_CLI::add_command(Entities\WPCLICommand::CC_COMMAND_OUTPUT_EXIT_CODE, Commands::class);
