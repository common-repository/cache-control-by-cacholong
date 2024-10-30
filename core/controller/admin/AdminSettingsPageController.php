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

namespace Cacholong\Controller\Admin;

defined('ABSPATH') OR exit;

use Cacholong\Libraries\Scheduler;

/**
 * Controller class for admin settings page
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 *
 * @version     1.0.0     Created at 2015-10-21
 */
class AdminSettingsPageController {
    /*
     * @const     string         FLASHDATA_KEY_CRON_CACHE_SETTINGS_CHANGED       Updated cron cache settings on settings page (can't use bool, because of new UserOptions lib)
     */

    const FLASHDATA_KEY_CRON_CACHE_SETTINGS_CHANGED = 'updated_cron_cache_settings';

    /**
     * Add options page to admin > settings
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     Created at 2015-10-21
     * @version     1.1.0     2019-02-21 Added action update_cron_cache_purging_if_needed
     */
    public static function init() {
        do_action('cacholong_cache_control_admin_settings_before_init');

        add_action('admin_menu', array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'create_settings_page'));
        add_action('admin_init', array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'create_settings_page_sections'));
        add_action('admin_init', array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'register_settings'));

        add_action('admin_init', array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'update_cron_cache_purging_if_needed'));

        do_action('cacholong_cache_control_admin_settings_after_init');
    }

    /**
     * Handle updated options for cron settings on settings page
     * Note: will only set flash signal, cron handling in self::update_cron_cache_purging_if_needed().
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2019-02-20
     *
     * @param       mixed     $oldValue     Old value
     * @param       mixed     $newValue     New value
     * @param       string    $optionName   Name of option
     *
     * @return      void
     */
    public static function handle_update_options_for_cron_settings($oldValue, $newValue, $optionName) {
        \Cacholong\Libraries\FlashData::set_flash([self::FLASHDATA_KEY_CRON_CACHE_SETTINGS_CHANGED => '1']);
    }

    /**
     * Update cron cache purging if needed
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2019-02-20
     *
     * @return      void
     */
    public static function update_cron_cache_purging_if_needed() {

        $cronCacheSettingsChanged = \Cacholong\Libraries\FlashData::get_flash(self::FLASHDATA_KEY_CRON_CACHE_SETTINGS_CHANGED); //one or more cron cache settings changed

        if ($cronCacheSettingsChanged[self::FLASHDATA_KEY_CRON_CACHE_SETTINGS_CHANGED] === '1') {
            $cronKeysAndHookPurgeNames = cacholong_cc_get_cron_settings_caches_field_key_and_hook_purge_name();

            $cronOptionEnabled = get_option(\Cacholong\Entities\Identifier::CC_FIELD_CRON_ENABLED);
            $cronOptionTime = cacholong_cc_get_option_cron_time();
            $cronOptionsCache = cacholong_cc_get_options_cron_caches();

            //Set / unset all scheduled events
            foreach ($cronKeysAndHookPurgeNames as $cronCacheFieldKey => $cronCacheHookPurgeName) {
                if ($cronOptionEnabled) {
                    if ($cronOptionsCache[$cronCacheFieldKey] === '1') {
                        Scheduler::set_daily_event($cronOptionTime, $cronCacheHookPurgeName);
                    }
                    else
                    {
                        Scheduler::unset_event($cronCacheHookPurgeName);
                    }
                } else {    //ok
                    //Unsetting can be done for all, no need to check if event was there.
                    Scheduler::unset_event($cronCacheHookPurgeName);
                }
            }
        }
        //else do nop
    }

    /**
     * Create settings page
     * Note: when settings page is not created and it gives a 'no permission', it is due to hooking it wrong
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-21
     *
     * @return      void
     */
    public static function create_settings_page() {
        add_options_page(
                _x('Cache control by Cacholong', 'Title of settings page', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), _x('Cache control', 'Menu title of settings page', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), 'manage_options', \Cacholong\Entities\Identifier::CC_SETTINGS_PAGE_ID, array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'render_page')
        );
    }

    /**
     * Create all sections
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-21
     *
     * @return      void
     */
    public static function create_settings_page_sections() {
        self::create_section_settings();
    }

    /**
     * Create section for settings
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-21
     * @version     1.1.0     2016-09-07 Add purge settings
     * @version     1.2.0     2017-12-15 Add CC_FIELD_REMOVE_STYLE_ID
     * @version     1.3.0     2019-02-19 Cronjob settings section + cronjob settings
     * @return      void
     */
    private static function create_section_settings() {
        //create sections on options page
        add_settings_section(
                \Cacholong\Entities\Identifier::CC_SETTINGS_SECTION, _x('Settings', 'Subtitle for settings section', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'render_section_settings'), \Cacholong\Entities\Identifier::CC_SETTINGS_PAGE_ID);

        add_settings_section(
                \Cacholong\Entities\Identifier::CC_SETTINGS_CRONJOB_SECTION, _x('Cronjob settings', 'Subtitle for cronjob settings section', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'render_section_settings_cronjob'), \Cacholong\Entities\Identifier::CC_SETTINGS_PAGE_ID);

        //create fields in section CC_SETTINGS_SECTION on options page
        add_settings_field(
                \Cacholong\Entities\Identifier::CC_FIELD_HOSTS_FILE, _x('File with hosts (JSON)', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'render_field_location_json_hosts_file'), \Cacholong\Entities\Identifier::CC_SETTINGS_PAGE_ID, \Cacholong\Entities\Identifier::CC_SETTINGS_SECTION);

        add_settings_field(
                \Cacholong\Entities\Identifier::CC_FIELD_REMOVE_STYLE_ID, _x('Pagespeed optimized CSS', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'render_field_remove_style_id'), \Cacholong\Entities\Identifier::CC_SETTINGS_PAGE_ID, \Cacholong\Entities\Identifier::CC_SETTINGS_SECTION);

        add_settings_field(
                \Cacholong\Entities\Identifier::CC_FIELD_DEFAULT_POST_PURGE_SETTINGS, _x('Purge settings default post types', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'render_field_default_post_purge_settings'), \Cacholong\Entities\Identifier::CC_SETTINGS_PAGE_ID, \Cacholong\Entities\Identifier::CC_SETTINGS_SECTION);

        if (\Cacholong\Entities\PostType::has_custom()) {
            add_settings_field(
                    \Cacholong\Entities\Identifier::CC_FIELD_CUSTOM_POST_PURGE_SETTINGS, _x('Purge settings custom post type(s)', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'render_field_custom_post_purge_settings'), \Cacholong\Entities\Identifier::CC_SETTINGS_PAGE_ID, \Cacholong\Entities\Identifier::CC_SETTINGS_SECTION);
        }

        //create fields in section CC_SETTINGS_CRONJOB_SECTION on options page
        add_settings_field(
                \Cacholong\Entities\Identifier::CC_FIELD_CRON_ENABLED, _x('Cronjob purging', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'render_field_cron_enabled'), \Cacholong\Entities\Identifier::CC_SETTINGS_PAGE_ID, \Cacholong\Entities\Identifier::CC_SETTINGS_CRONJOB_SECTION);


        add_settings_field(
                \Cacholong\Entities\Identifier::CC_FIELD_CRON_TIME, _x('Cronjob time of each day', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'render_field_cron_time'), \Cacholong\Entities\Identifier::CC_SETTINGS_PAGE_ID, \Cacholong\Entities\Identifier::CC_SETTINGS_CRONJOB_SECTION);

        add_settings_field(
                \Cacholong\Entities\Identifier::CC_FIELDSET_CRON_CACHE_HELPERS, _x('Purge caches', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'render_field_cron_cache_helpers'), \Cacholong\Entities\Identifier::CC_SETTINGS_PAGE_ID, \Cacholong\Entities\Identifier::CC_SETTINGS_CRONJOB_SECTION);

    }

    /**
     * Render page 'render_page'
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-21
     *
     * @return      void
     */
    public static function render_page() {
        require(CACHOLONG_VIEW_DIR . '/admin/settings/page/cacholong_cache_control.phtml');
    }

    /**
     * Render section 'cacholong_cache_control_settings'
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-21
     *
     * @return      void
     */
    public static function render_section_settings() {
        require(CACHOLONG_VIEW_DIR . '/admin/settings/section/cacholong_cache_control_settings.phtml');
    }

    /**
     * Render section 'cacholong_cache_control_settings_cronjob'
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2019-02-19
     *
     * @return      void
     */
    public static function render_section_settings_cronjob() {
        require(CACHOLONG_VIEW_DIR . '/admin/settings/section/cacholong_cache_control_settings_cronjob.phtml');
    }

    /**
     * Render field 'remove_style_id'
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2017-12-15
     *
     * @return      void
     */
    public static function render_field_remove_style_id() {
        require(CACHOLONG_VIEW_DIR . '/admin/settings/field/remove_style_id.phtml');
    }

    /**
     * Render field 'location_json_hosts_file'
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-21
     *
     * @return      void
     */
    public static function render_field_location_json_hosts_file() {
        require(CACHOLONG_VIEW_DIR . '/admin/settings/field/location_json_hosts_file.phtml');
    }

    /**
     * Render field 'cacholong_cc_field_cron_enabled'
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2019-02-19
     *
     * @return      void
     */
    public static function render_field_cron_enabled() {
        require(CACHOLONG_VIEW_DIR . '/admin/settings/field/cron_enabled.phtml');
    }

    /**
     * Render field 'cacholong_cc_field_cron_time'
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2019-02-19
     *
     * @return      void
     */
    public static function render_field_cron_time() {
        require(CACHOLONG_VIEW_DIR . '/admin/settings/field/cron_time.phtml');
    }

    /**
     * Render field 'cacholong_cc_fieldset_cron_cache_helpers'
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2019-02-19
     *
     * @return      void
     */
    public static function render_field_cron_cache_helpers() {
        require(CACHOLONG_VIEW_DIR . '/admin/settings/field/cron_cache_helpers.phtml');
    }

    /* Render field 'cc_field_custom_purge_settings'
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2016-09-07
     *
     * @return      void
     */

    public static function render_field_custom_post_purge_settings() {
        require(CACHOLONG_VIEW_DIR . '/admin/settings/field/custom_post_purge_settings.phtml');
    }

    /**
     * Render field 'cc_field_default_post_purge_settings'
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2016-09-07
     *
     * @return      void
     */
    public static function render_field_default_post_purge_settings() {
        require(CACHOLONG_VIEW_DIR . '/admin/settings/field/default_post_purge_settings.phtml');
    }

    /**
     * Register settings
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-21
     * @version     1.1.0     2016-09-07 All purge settings
     * @version     1.2.0     2017-12-15 Added CC_FIELD_REMOVE_STYLE_ID
     * @version     1.3.0     2019-02-19 Added cron settings
     * @return      void
     */
    public static function register_settings() {
        register_setting(
                \Cacholong\Entities\Identifier::CC_OPTIONS, \Cacholong\Entities\Identifier::CC_FIELD_HOSTS_FILE, array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'sanitize_field_location_json_hosts_file'));

        register_setting(
                \Cacholong\Entities\Identifier::CC_OPTIONS, \Cacholong\Entities\Identifier::CC_FIELD_REMOVE_STYLE_ID, array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'sanitize_field_boolean'));

        //default post types
        register_setting(
                \Cacholong\Entities\Identifier::CC_OPTIONS, \Cacholong\Entities\Identifier::CC_DEFAULT_POST_PURGE_SETTINGS_DEFAULT, array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'sanitize_field_boolean'));
        register_setting(
                \Cacholong\Entities\Identifier::CC_OPTIONS, \Cacholong\Entities\Identifier::CC_DEFAULT_POST_PURGE_SETTINGS_HOME, array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'sanitize_field_boolean'));
        register_setting(
                \Cacholong\Entities\Identifier::CC_OPTIONS, \Cacholong\Entities\Identifier::CC_DEFAULT_POST_PURGE_SETTINGS_CATEGORIES_WORDPRESS, array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'sanitize_field_boolean'));
        register_setting(
                \Cacholong\Entities\Identifier::CC_OPTIONS, \Cacholong\Entities\Identifier::CC_DEFAULT_POST_PURGE_SETTINGS_CATEGORIES_WOOCOMMERCE, array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'sanitize_field_boolean'));
        register_setting(
                \Cacholong\Entities\Identifier::CC_OPTIONS, \Cacholong\Entities\Identifier::CC_DEFAULT_POST_PURGE_SETTINGS_FLUSH_ALL, array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'sanitize_field_boolean'));

        //custom post types
        register_setting(
                \Cacholong\Entities\Identifier::CC_OPTIONS, \Cacholong\Entities\Identifier::CC_CUSTOM_POST_PURGE_SETTINGS_DEFAULT, array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'sanitize_field_boolean'));
        register_setting(
                \Cacholong\Entities\Identifier::CC_OPTIONS, \Cacholong\Entities\Identifier::CC_CUSTOM_POST_PURGE_SETTINGS_HOME, array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'sanitize_field_boolean'));
        register_setting(
                \Cacholong\Entities\Identifier::CC_OPTIONS, \Cacholong\Entities\Identifier::CC_CUSTOM_POST_PURGE_SETTINGS_CATEGORIES_WORDPRESS, array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'sanitize_field_boolean'));
        register_setting(
                \Cacholong\Entities\Identifier::CC_OPTIONS, \Cacholong\Entities\Identifier::CC_CUSTOM_POST_PURGE_SETTINGS_CATEGORIES_WOOCOMMERCE, array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'sanitize_field_boolean'));
        register_setting(
                \Cacholong\Entities\Identifier::CC_OPTIONS, \Cacholong\Entities\Identifier::CC_CUSTOM_POST_PURGE_SETTINGS_FLUSH_ALL, array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'sanitize_field_boolean'));

        //cronjob settings
        register_setting(
                \Cacholong\Entities\Identifier::CC_OPTIONS, \Cacholong\Entities\Identifier::CC_FIELD_CRON_ENABLED, array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'sanitize_field_boolean'));
        register_setting(
                \Cacholong\Entities\Identifier::CC_OPTIONS, \Cacholong\Entities\Identifier::CC_FIELD_CRON_TIME, array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'sanitize_field_hh_mm_24h'));

        $cronCacheSettings = cacholong_cc_get_cron_settings_caches_field_key_and_name();
        foreach ($cronCacheSettings as $cronCacheFieldKey => $cronCacheName) {
            register_setting(
                    \Cacholong\Entities\Identifier::CC_OPTIONS, $cronCacheFieldKey, array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'sanitize_field_boolean'));
        }
    }

    /**
     * Sanitize input for field 'location_json_hosts_file'
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-26
     * @version     1.0.1     2015-11-02    Replace slashes
     * @version     1.1.0     2019-02-21    Extra trim + safeguards
     *
     * @param       string    $unsafeInput  Unsafe input for location field
     *
     * @return      string    Sanatized location field
     */
    public static function sanitize_field_location_json_hosts_file($unsafeInput) {
        $unsafeInput = trim($unsafeInput);
        $finalHostsFile = null;

        if ($unsafeInput) {
            $input = str_replace('\\', '/', $unsafeInput);
            $input = ltrim($input, '/');
            $finalHostsFile = $input;
        } elseif(defined('CACHOLONG_CACHE_DEFAULT_JSON_HOSTS_PATH'))
        {
            $finalHostsFile = CACHOLONG_CACHE_DEFAULT_JSON_HOSTS_PATH;
        }
        //else default

        return $finalHostsFile;
    }

    /**
     * Sanitize input for boolean field for DB
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2016-09-07    Original
     *
     * @param       string      $unsafeInput  Unsafe input for a boolean field
     *
     * @return      bool        Sanatized boolean field
     */
    public static function sanitize_field_boolean($unsafeInput) {
        $activated = (bool) $unsafeInput;
        return $activated ? '1' : '0';
    }

    /**
     * Sanitize input for hh mm field (24hour format)
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2019-02-19
     * @version     1.1.0     2019-02-20 abstracted to cacholong_cc_sanitize_time_hh_mm_24h()
     *
     * @param       string      $unsafeInput  Unsafe input for a hh:mm field
     *
     * @return      bool        Sanatized hh:mm field
     */
    public static function sanitize_field_hh_mm_24h($unsafeInput) {

        return cacholong_cc_sanitize_time_hh_mm_24h($unsafeInput);
    }

}
