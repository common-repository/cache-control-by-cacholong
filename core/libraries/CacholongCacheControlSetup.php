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
 * Setup class for Cacholong Cache control
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @link        http://wordpress.stackexchange.com/questions/25910/uninstall-activate-deactivate-a-plugin-typical-features-how-to/25979#25979
 *
 * @version     1.0.0     Created at 2015-10-21
 * @version     1.1.0     2018-01-26 Renamed from CacholongCacheHelperSetup
 */
class CacholongCacheControlSetup
{

    /**
     * Activation of plugin
     * Note: Options are only written if not present in options table
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     Created at 2015-10-21
     * @version     1.1.0     2016-09-07 Aditional options
     * @version     1.2.0     2016-09-09 Bugfix: nonce check created are you sure question that got you nowhere in some cases. Removed.
     * @version     1.3.0     2017-12-15 Added CC_FIELD_REMOVE_STYLE_ID
     * @version     1.4.0     2019-02-19 Added cron settings
     * @version     1.4.1     2019-02-21 Default value cache fields 0 -> 1
     * @version     1.5.0     2019-11-12 Options abstracted to WordpressOption enum
     * @version     1.6.0     2019-11-12 Multisite support
     * @version     1.7.0     2019-11-13 WP CLI support
     */
    public static function on_activation()
    {
        if (current_user_can('activate_plugins') || cacholong_cc_is_wp_cli()) {
            $wordpressOptions = \Cacholong\Entities\WordpressOption::get_options_with_defaults();
            foreach ($wordpressOptions as $optionName => $optionValue) {
                AllSiteOptions::add($optionName, $optionValue);
            }

            $cronCacheSettings = cacholong_cc_get_cron_settings_caches_field_key_and_name();
            foreach ($cronCacheSettings as $cronCacheFieldKey => $cronCacheName) {
                AllSiteOptions::add($cronCacheFieldKey, 1);
            }
        } else {
            wp_die(__('You are not allowed to access this part of the site.', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN));
        }
    }

    /**
     * Reset all options to default values
     * Will update existing option or add new if option did not exist.
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @var         int       $networkID      Network id to reset options for.
     *
     * @version     1.0.0     Created at 2019-11-12
     * @version     1.1.0     2019-11-12 When option is not existing, add option
     * @version     1.2.0     2019-11-12 Multi site support
     * @version     1.3.0     2019-11-13 Added $site_id
     *
     */
    public static function reset_options_to_default($networkID)
    {
        $wordpressOptions = \Cacholong\Entities\WordpressOption::get_options_with_defaults();
        foreach ($wordpressOptions as $optionName => $optionValue) {
            AllSiteOptions::update_network($networkID, $optionName, $optionValue);
        }

        $cronCacheSettings = cacholong_cc_get_cron_settings_caches_field_key_and_name();
        foreach ($cronCacheSettings as $cronCacheFieldKey => $cronCacheName) {
            AllSiteOptions::update_network($networkID, $cronCacheFieldKey, 1);
        }
    }

    /**
     * Deinstall of plugin
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * @link        https://codex.wordpress.org/Function_Reference/register_uninstall_hook
     *
     * @version     1.0.0     Created at 2015-10-21
     * @version     1.1.0     2016-09-07 Aditional options
     * @version     1.2.0     2017-12-15 Added CC_FIELD_REMOVE_STYLE_ID
     * @version     1.3.0     2017-12-21 Removed check_admin_referer('bulk-plugins') + Also remove possible leftover flashData
     * @version     1.4.0     2019-02-19 Added cron settings
     * @version     1.5.0     2019-11-12 Options abstracted to WordpressOption enum
     * @version     1.6.0     2019-11-12 Multisite support
     * @version     1.7.0     2019-11-13 WP CLI support
     */
    public static function on_uninstall()
    {
        if (current_user_can('install_plugins') || cacholong_cc_is_wp_cli()) {

            $wordpressOptionNames = \Cacholong\Entities\WordpressOption::get_option_names();
            foreach ($wordpressOptionNames as $optionName) {
                AllSiteOptions::delete($optionName);
            }

            $cronCacheSettings = cacholong_cc_get_cron_settings_caches_field_key_and_name();
            foreach ($cronCacheSettings as $cronCacheFieldKey => $cronCacheName) {
                AllSiteOptions::delete($cronCacheFieldKey);
            }

            //remove possible leftover flashData
            \Cacholong\Libraries\FlashData::delete_all_flash_data();
        } else {
            wp_die(__('You are not allowed to access this part of the site.', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN));
        }
    }
}
