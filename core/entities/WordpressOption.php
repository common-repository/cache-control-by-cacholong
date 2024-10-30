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
namespace Cacholong\Entities;

defined('ABSPATH') OR exit;

/**
 * Entity WordpressOption, use as enum
 *
 * @since 2019-02-20
 * @author Piet Rol <info@preliot.nl>
 * @version 1.0
 */
abstract class WordpressOption {

    /*
     * @const   array   All options with default value
     */
    const OPTIONS_WITH_DEFAULTS = [
        \Cacholong\Entities\Identifier::CC_FIELD_HOSTS_FILE => CACHOLONG_CACHE_DEFAULT_JSON_HOSTS_PATH,

        \Cacholong\Entities\Identifier::CC_FIELD_REMOVE_STYLE_ID => 1,

        \Cacholong\Entities\Identifier::CC_DEFAULT_POST_PURGE_SETTINGS_DEFAULT => 1,
        \Cacholong\Entities\Identifier::CC_DEFAULT_POST_PURGE_SETTINGS_HOME => 0,
        \Cacholong\Entities\Identifier::CC_DEFAULT_POST_PURGE_SETTINGS_CATEGORIES_WORDPRESS => 0,
        \Cacholong\Entities\Identifier::CC_DEFAULT_POST_PURGE_SETTINGS_CATEGORIES_WOOCOMMERCE => 0,
        \Cacholong\Entities\Identifier::CC_DEFAULT_POST_PURGE_SETTINGS_FLUSH_ALL => 0,

        \Cacholong\Entities\Identifier::CC_CUSTOM_POST_PURGE_SETTINGS_DEFAULT => 1,
        \Cacholong\Entities\Identifier::CC_CUSTOM_POST_PURGE_SETTINGS_HOME => 0,
        \Cacholong\Entities\Identifier::CC_CUSTOM_POST_PURGE_SETTINGS_CATEGORIES_WORDPRESS => 0,
        \Cacholong\Entities\Identifier::CC_CUSTOM_POST_PURGE_SETTINGS_CATEGORIES_WOOCOMMERCE => 0,
        \Cacholong\Entities\Identifier::CC_CUSTOM_POST_PURGE_SETTINGS_FLUSH_ALL => 0,

        \Cacholong\Entities\Identifier::CC_FIELD_CRON_ENABLED => 0,
        \Cacholong\Entities\Identifier::CC_FIELD_CRON_TIME => '00:00',
    ];

    public static function get_options_with_defaults()
    {
        return self::OPTIONS_WITH_DEFAULTS;
    }

    public static function get_option_names()
    {
        return array_keys(self::OPTIONS_WITH_DEFAULTS);
    }

}

/* End of file WordpressOption.php */