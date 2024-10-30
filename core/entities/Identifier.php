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

defined( 'ABSPATH' ) OR exit;

/**
 * Entity Identifier, use as enum
 *
 * Contains identifiers for remaining IDs
 * Note: use prefix to be save in options table in DB
 *
 * @since 2015-10-22
 * @author Piet Rol <info@preliot.nl>
 * @version 1.0.0
 * @version 1.1.0 2016-09-07 Added ID's
 * @version 1.2.0 2018-02-23 All values > prefix from cc_ to cacholong_cc_
 * @version 1.3.0 2019-02-19 Cron fields
 *
 */
abstract class Identifier {

    //Text domain is used for translations (no prefix needed)
    const CC_TEXT_DOMAIN = 'cacholong-cache-control';       //match plugin slug

    //page and section
    const CC_SETTINGS_PAGE_ID = 'cacholong-cache-control-settings-page';        //also visible in url
    const CC_SETTINGS_SECTION = 'cacholong_cc_settings_section';
    const CC_SETTINGS_CRONJOB_SECTION = 'cacholong_cc_settings_cronjob_section';
    const CC_SETTINGS_SECTION_FORM_ID = 'cacholong_cc_settings_form';

    //option group for all fields
    const CC_OPTIONS = 'cacholong_cc_cache_options';

    //fields
    const CC_FIELD_HOSTS_FILE = 'cacholong_cc_field_hosts_file';
    const CC_FIELD_SINGLE_PURGE = 'cacholong_cc_field_single_purge';
    const CC_FIELD_DEFAULT_POST_PURGE_SETTINGS = 'cacholong_cc_field_default_post_purge_settings';    //field name used in javascript
    const CC_FIELD_CUSTOM_POST_PURGE_SETTINGS = 'cacholong_cc_field_custom_purge_settings';           //field name used in javascript
    const CC_FIELD_REMOVE_STYLE_ID = 'cacholong_cc_field_remove_style_id';
    const CC_FIELD_CRON_ENABLED = 'cacholong_cc_field_cron_enabled';
    const CC_FIELD_CRON_TIME = 'cacholong_cc_field_cron_time';
    const CC_FIELD_CRON_CACHE_PREFIX = 'cacholong_cc_field_';
    const CC_HOOK_PURGE_CACHE_PREFIX = 'cacholong_cc_purge_';

    //fieldsets
    const CC_FIELDSET_DEFAULT_POST_PURGE_SETTINGS = 'cacholong_cc_fieldset_default_purge_settings';     //field name used in javascript
    const CC_FIELDSET_CUSTOM_POST_PURGE_SETTINGS = 'cacholong_cc_fieldset_custom_purge_settings';       //field name used in javascript
    const CC_FIELDSET_CRON_CACHE_HELPERS = 'cacholong_cc_fieldset_cron_cache_helpers';

    //Radio options for default post purge settings
    const CC_DEFAULT_POST_PURGE_SETTINGS_DEFAULT = 'cacholong_cc_default_post_purge_settings_default';
    const CC_DEFAULT_POST_PURGE_SETTINGS_HOME = 'cacholong_cc_default_post_purge_settings_home';
    const CC_DEFAULT_POST_PURGE_SETTINGS_CATEGORIES_WORDPRESS = 'cacholong_cc_default_post_purge_settings_categories_wordpress';
    const CC_DEFAULT_POST_PURGE_SETTINGS_CATEGORIES_WOOCOMMERCE = 'cacholong_cc_default_post_purge_settings_categories_woocommerce';
    const CC_DEFAULT_POST_PURGE_SETTINGS_FLUSH_ALL = 'cacholong_cc_default_post_purge_settings_flush_all';

    //Radio options for custom post purge settings
    const CC_CUSTOM_POST_PURGE_SETTINGS_DEFAULT = 'cacholong_cc_custom_post_purge_settings_default';
    const CC_CUSTOM_POST_PURGE_SETTINGS_HOME = 'cacholong_cc_custom_post_purge_settings_home';
    const CC_CUSTOM_POST_PURGE_SETTINGS_CATEGORIES_WORDPRESS = 'cacholong_cc_custom_post_purge_settings_categories_wordpress';
    const CC_CUSTOM_POST_PURGE_SETTINGS_CATEGORIES_WOOCOMMERCE = 'cacholong_cc_custom_post_purge_settings_categories_woocommerce';
    const CC_CUSTOM_POST_PURGE_SETTINGS_FLUSH_ALL = 'cacholong_cc_custom_post_purge_settings_flush_all';

    //admin toolbar node
    const CC_TOOLBAR_NODE_PURGE = 'cacholong_cc_node_purge';
    const CC_TOOLBAR_NODE_PURGE_ALL = 'cacholong_cc_node_purge_all';
    const CC_TOOLBAR_NODE_PURGE_PAGESPEED = 'cacholong_cc_node_purge_pagespeed';
    const CC_TOOLBAR_NODE_PURGE_FASTCGI = 'cacholong_cc_node_purge_fastcgi';

    //actions for all request types
    const CC_REQUEST_ACTION_KEY = 'cacholong_cc_action';

    const CC_REQUEST_ACTION_PURGE_ALL_ADMIN_BAR = 'cacholong_cc_purge_all_admin_bar';

    const CC_REQUEST_ACTION_PURGE_ALL_NGINX_FASTCGI = 'cacholong_cc_purge_all_nginx_fastcgi';
    const CC_REQUEST_ACTION_NGINX_FASTCGI_PATH = 'cacholong_cc_fastcgi_path';
    const CC_REQUEST_ACTION_PURGE_ALL_NGINX_PAGESPEED = 'cacholong_cc_purge_all_nginx_pagespeed';

    //ajax actions
    const CC_AJAX_ACTION_GET_MESSAGES = 'cacholong_cc_action_get_messages';       //note: do not use wp_ajax_ prefix here

    const DEFAULT_DATETIME = 'Y-m-d H:i:s';

    //Regex for time of day (24H)
    const TIME_OF_DAY_24H_REGEX = '/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/';

    const CC_TEMP_OVERRIDE_IPS = 'cacholong_cc_temp_override_ips';             //override hosts.json with these ips
}
/* End of file Identifier.php */