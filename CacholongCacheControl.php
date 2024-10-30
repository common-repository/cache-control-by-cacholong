<?php
/*
  Plugin Name: Cache control by Cacholong
  Description: Automates purging of Nginx Pagespeed cache and Nginx FastCGI cache on your Nginx server(s).
  Version: 5.4.1
  Author: Cacholong
  Author URI: http://www.cacholong.nl
  License: GPLv3
  License URI: https://www.gnu.org/licenses/gpl-3.0.html
  Text Domain: cacholong-cache-control
  Domain Path: /languages

  Copyright © 2016-2019, Cacholong <info@cacholong.nl>

  This file is part of Cache Control

  Cache Control is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  Cache Control is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Cache Control.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Cacholong;

defined('ABSPATH') OR exit;

class CacholongCacheControl
{

    /**
     * @var CacholongCacheControl The single instance of the class
     */
    private static $_instance = null;

    /**
     * Init $_instance with singleton object when admin interface is enabled
     * Note: Plugin will only be loaded when in admin interface (or if someone attempts to access admin interface). Reduces overhead on frontend.
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     Created at 2015-10-21
     *
     * @return      CacholongCache        Instance of CacholongCacheControl
     */
    public static function init()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * Constructor
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     Created at 2015-10-21
     * @version     1.1.0     2018-01-26 Changed hook name
     *
     */
    protected function __construct()
    {
        do_action('cacholong_cache_control_construct');

        $this->define_constants();
        $this->requires();
        $this->init_hooks();
    }

    /**
     * Define all constants
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     Created at 2015-10-21
     *
     */
    private function define_constants()
    {
        $dir = dirname(__FILE__);
        $dir = str_replace('\\', '/', $dir);

        $plugin_dir_url = plugin_dir_url(__FILE__);
        $plugin_dir_url = rtrim($plugin_dir_url, '/');

        $this->define('CACHOLONG_CACHE_BASENAME', plugin_basename(__FILE__));           //Plugin base name
        $this->define('CACHOLONG_CACHE_URL', $plugin_dir_url);                          //Full URL to plugin
        $this->define('CACHOLONG_CACHE_DIR', $dir);                                     //Full dir to plugin
        $this->define('CACHOLONG_CACHE_DEFAULT_JSON_HOSTS_PATH', 'wp-content/uploads/cacholong-cache-control/hosts.json');                   //Default location of JSON file with all hosts
        //helpers
        $this->define('CACHOLONG_VIEW_DIR', CACHOLONG_CACHE_DIR . '/core/view');

        $this->define('CACHOLONG_LOG_PATH', WP_CONTENT_DIR . '/cacholong-cache-control.log');
    }

    /**
     * Define constant if not already set
     *
     * @author      Woocommerce | Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     Created at 2015-10-21
     *
     * @param       string          $name           Name of constant
     * @param       mixed           $value          Value of constant
     *
     * @return      void
     */
    private function define($name, $value)
    {
        $name_uppercase = strtoupper($name);

        if (!defined($name_uppercase)) {
            define($name_uppercase, $value);
        }
    }

    /**
     * Require files
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     Created at 2015-10-21
     *
     * @return      void
     */
    private function requires()
    {
        //entities
        require_once(CACHOLONG_CACHE_DIR . '/core/entities/Identifier.php');
        require_once(CACHOLONG_CACHE_DIR . '/core/entities/AdminPostAction.php');
        require_once(CACHOLONG_CACHE_DIR . '/core/entities/AdminNoticeClass.php');
        require_once(CACHOLONG_CACHE_DIR . '/core/entities/AjaxResponseType.php');
        require_once(CACHOLONG_CACHE_DIR . '/core/entities/PostType.php');
        require_once(CACHOLONG_CACHE_DIR . '/core/entities/HttpApiRequest.php');
        require_once(CACHOLONG_CACHE_DIR . '/core/entities/SchedulerRecurrence.php');
        require_once(CACHOLONG_CACHE_DIR . '/core/entities/URL.php');
        require_once(CACHOLONG_CACHE_DIR . '/core/entities/WordpressOption.php');

        //libraries / helpers
        require_once(CACHOLONG_CACHE_DIR . '/core/libraries/Helpers.php');
        require_once(CACHOLONG_CACHE_DIR . '/core/libraries/ArrayHelper.php');
        require_once(CACHOLONG_CACHE_DIR . '/core/libraries/FlashData.php');
        require_once(CACHOLONG_CACHE_DIR . '/core/libraries/File.php');
        require_once(CACHOLONG_CACHE_DIR . '/core/libraries/AdminMessage.php');
        require_once(CACHOLONG_CACHE_DIR . '/core/libraries/Json.php');
        require_once(CACHOLONG_CACHE_DIR . '/core/libraries/Style.php');
        require_once(CACHOLONG_CACHE_DIR . '/core/libraries/Scheduler.php');
        require_once(CACHOLONG_CACHE_DIR . '/core/libraries/AllSiteOptions.php');
        require_once(CACHOLONG_CACHE_DIR . '/core/libraries/WPML.php');

        if (cacholong_cc_is_wp_cli()) {
            require_once(CACHOLONG_CACHE_DIR . '/core/entities/WPCLICommand.php');
            require_once(CACHOLONG_CACHE_DIR . '/core/entities/WPCLIExitCode.php');
            require_once(CACHOLONG_CACHE_DIR . '/core/libraries/WPCLI/Commands.php');
        }

        //setup
        require_once(CACHOLONG_CACHE_DIR . '/core/libraries/CacholongCacheControlSetup.php');

        //controllers
        require_once(CACHOLONG_CACHE_DIR . '/core/controller/admin/AdminSettingsPageController.php');
        require_once(CACHOLONG_CACHE_DIR . '/core/controller/admin/PurgeCacheController.php');
        require_once(CACHOLONG_CACHE_DIR . '/core/controller/admin/ToolbarController.php');
    }

    /**
     * Init hooks
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     Created at 2015-10-21
     * @version     1.1.0     2019-11-13 Fix for wp-cli
     *
     * @return      void
     */
    private function init_hooks()
    {
        //setup
        if (is_admin() || cacholong_cc_is_wp_cli()) {
            register_activation_hook(__FILE__, array('\Cacholong\Libraries\CacholongCacheControlSetup', 'on_activation'));  //hooks for setup
            register_uninstall_hook(__FILE__, array('\Cacholong\Libraries\CacholongCacheControlSetup', 'on_uninstall'));    //test in development with register_deactivation_hook
        }

        //admin
        if (is_admin()) {
            //actions and filters
            add_action('init', array('\Cacholong\CacholongCacheControl', 'load_plugin_textdomain'));

            add_action('init', array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'init'));

            //post types

            add_action('wp_update_nav_menu', array('\Cacholong\CacholongCacheControl', 'handle_nav_menu_update'), 10, 2);   //nav bar save
            add_filter('plugin_action_links', array('\Cacholong\CacholongCacheControl', 'plugin_action_links'), 10, 2);     //plugin action links in index

            add_action('admin_enqueue_scripts', array('\Cacholong\CacholongCacheControl', 'enqueue_scripts_and_styles_backend'));

            //handle messages
            $ajax_action_name = 'wp_ajax_' . \Cacholong\Entities\Identifier::CC_AJAX_ACTION_GET_MESSAGES;
            add_action($ajax_action_name, array('\Cacholong\CacholongCacheControl', 'handle_ajax_messages'));

            //helper messages to backend

            add_action('admin_notices', array('\Cacholong\CacholongCacheControl', 'add_helper_messages'), 999);

        }
        //handle shutdown actions
        add_action('shutdown', array('\Cacholong\CacholongCacheControl', 'handle_shutdown'));

        //gutenberg (check on gutenberg can only be made after replace_editor hook
        add_action('pre_post_update', array('\Cacholong\Controller\Admin\PurgeCacheController', 'handle_slug_change'), 1, 2);                   //purge when slug changes
        add_action('save_post', array('\Cacholong\Controller\Admin\PurgeCacheController', 'handle_purge_post_save'), 10, 3);                    //purge when new post and updating post
        add_action('wp_trash_post', array('\Cacholong\Controller\Admin\PurgeCacheController', 'handle_purge_post_trash'), 10);                  //purge when post is about to be trashed (just before)
        add_action('untrashed_post', array('\Cacholong\Controller\Admin\PurgeCacheController', 'handle_purge_post_untrash'), 10, 2);            //purge when post is retored from trash (afterwards)
        //delete_post -> hook when post is finally deleted

        //front- and back-end, but only on single site or network site leave
        if (!is_network_admin()) {
            add_action('wp_enqueue_scripts', array('\Cacholong\CacholongCacheControl', 'enqueue_scripts_and_styles_frontend'));
            add_action('admin_bar_menu', array('\Cacholong\Controller\Admin\ToolbarController', 'create_toolbar_node_purge'), 999);
            add_action('admin_bar_init', array('\Cacholong\CacholongCacheControl', 'handle_purge_from_admin_bar'), 999);
        }

        //style removal
        add_action('init', array('\Cacholong\CacholongCacheControl', 'init_hooks_style'));

        //Cannot hide this underneath is_admin (http api requests involved)
        add_action('init', array('\Cacholong\Controller\Admin\PurgeCacheController', 'init'));

        //Debug
        add_action('cc_debug_hook', array('\Cacholong\CacholongCacheControl', 'handle_debug_hook'));

        self::init_hooks_cron();
    }

    /**
     * Init hooks cronjobs
     *
     * This function will hook into updating of cron settings. Whenever a cron option is updated, handle_update_options_for_cron_settings is called.
     * New cron settings must be added to this function in order to be caught.
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     Created at 2019-02-20
     *
     * @return      void
     */
    private function init_hooks_cron()
    {

        //collect all cron options
        $cronOptions[] = \Cacholong\Entities\Identifier::CC_FIELD_CRON_ENABLED;
        $cronOptions[] = \Cacholong\Entities\Identifier::CC_FIELD_CRON_TIME;

        $cronCacheSettings = cacholong_cc_get_cron_settings_caches_field_key_and_name();
        foreach ($cronCacheSettings as $cronSettingCacheKey => $cacheName) {
            $cronOptions[] = $cronSettingCacheKey;
        }

        //Add action to each option
        foreach ($cronOptions as $cronOption) {
            add_action('update_option_' . $cronOption, array('\Cacholong\Controller\Admin\AdminSettingsPageController', 'handle_update_options_for_cron_settings'), 10, 3); //Action called when option is saved
        }
    }

    private function init_hooks_post_save()
    {
        add_action('admin_enqueue_scripts', array('\Cacholong\CacholongCacheControl', 'enqueue_scripts_and_styles_backend'));
    }

    public static function init_hooks_style()
    {
        //remove style id when:
        //
        // option = 1
        // !is_admin: Not seeing an admin url
        // !is_admin_bar_showing: Not seeing an admin bar (front or backend)
        if (get_option(\Cacholong\Entities\Identifier::CC_FIELD_REMOVE_STYLE_ID) == 1 && !is_admin() && !is_admin_bar_showing()) {
            add_filter('style_loader_tag', array('\Cacholong\Libraries\Style', 'remove_id'));                            //remove style id (if allowed)
        }
    }

    /**
     * Enqueu all scripts and styles backend
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-11-25
     * @version     1.1.0     2016-09-07    Some settingsSection* vars
     * @version     1.2.0     2018-02-28    Cache busting with fingerprinting
     * @versopm     1.3.0     2019-02-21    Added style
     * @return      void
     */
    public static function enqueue_scripts_and_styles_backend()
    {
        $cc_plugin_data['ajaxUrl'] = admin_url('admin-ajax.php');
        $cc_plugin_data['ajaxResponseTypeSuccess'] = \Cacholong\Entities\AjaxResponseType::SUCCESS;
        $cc_plugin_data['ajaxResponseTypeError'] = \Cacholong\Entities\AjaxResponseType::ERROR;
        $cc_plugin_data['ajaxActionGetMessages'] = \Cacholong\Entities\Identifier::CC_AJAX_ACTION_GET_MESSAGES;

        //All fieldsets with unique checkboxes... what a name :)
        //Results in object of arrays
        $cc_plugin_data['settingsSectionFieldSetWithUniqueCheckboxes'] = array(
            \Cacholong\Entities\Identifier::CC_FIELDSET_DEFAULT_POST_PURGE_SETTINGS => array(
                \Cacholong\Entities\Identifier::CC_DEFAULT_POST_PURGE_SETTINGS_FLUSH_ALL
            ),
            \Cacholong\Entities\Identifier::CC_FIELDSET_CUSTOM_POST_PURGE_SETTINGS => array(
                \Cacholong\Entities\Identifier::CC_CUSTOM_POST_PURGE_SETTINGS_FLUSH_ALL
            )
        );

        wp_register_script("cacholong-cache-control", CACHOLONG_CACHE_URL . '/js/cacholong-cache-control.js', array('jquery'), Libraries\File::fingerprint(CACHOLONG_CACHE_DIR . '/js/cacholong-cache-control.js'));
        wp_localize_script('cacholong-cache-control', 'cacholong_cc_plugin_data', $cc_plugin_data);

        wp_enqueue_script('cacholong-cache-control');

        wp_enqueue_style("cacholong-cache-control-style", CACHOLONG_CACHE_URL . "/css/cacholong-cache-control.css", array(), Libraries\File::fingerprint(CACHOLONG_CACHE_DIR . '/css/cacholong-cache-control.css'));
    }

    /**
     * Enqueu all scripts and styles (frontend)
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2019-02-21
     * @return      void
     */
    public static function enqueue_scripts_and_styles_frontend()
    {
        //on frontend because of adminbar
        wp_enqueue_style("cacholong-cache-control-style", CACHOLONG_CACHE_URL . "/css/cacholong-cache-control.css", array(), Libraries\File::fingerprint(CACHOLONG_CACHE_DIR . '/css/cacholong-cache-control.css'));
    }

    /**
     * Load translation file for current language
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-11-09
     *
     * @return      void
     */
    public static function load_plugin_textdomain()
    {
        load_plugin_textdomain(\Cacholong\Entities\Identifier::CC_TEXT_DOMAIN, false, dirname(CACHOLONG_CACHE_BASENAME) . '/languages/');
    }

    /**
     * Handle purge from admin bar
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-31
     * @version     1.1.0     2020-10-16    Also handle specific purges
     *
     * @return      void
     */
    public static function handle_purge_from_admin_bar()
    {
        if (array_key_exists(\Cacholong\Entities\Identifier::CC_REQUEST_ACTION_KEY, $_GET)) {
            $nonce = $_GET['_wpnonce'];
            if (!wp_verify_nonce($nonce)) {
                cacholong_cc_log_error(__('Purge from admin bar failed silently, because nonce was not valid.', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), __FILE__);
            }

            $action = $_GET[\Cacholong\Entities\Identifier::CC_REQUEST_ACTION_KEY];

            switch ($action) {
                case \Cacholong\Entities\Identifier::CC_REQUEST_ACTION_PURGE_ALL_ADMIN_BAR: //ok
                    \Cacholong\Controller\Admin\PurgeCacheController::handle_purge_all_from_admin_bar();
                    break;
                case \Cacholong\Entities\Identifier::CC_REQUEST_ACTION_PURGE_ALL_NGINX_FASTCGI: //ok
                    \Cacholong\Controller\Admin\PurgeCacheController::handle_purge_nginx_fastcgi_from_admin_bar();
                    break;
                case \Cacholong\Entities\Identifier::CC_REQUEST_ACTION_PURGE_ALL_NGINX_PAGESPEED:   //ok
                    \Cacholong\Controller\Admin\PurgeCacheController::handle_purge_nginx_pagespeed_from_admin_bar();
                    break;
                default:
                    cacholong_cc_log_error(sprintf(__('Purge from admin bar failed silently, because action "%s" was not valid.', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), $action), __FILE__);
            }
        }
    }

    /**
     * Add helper messages to admin screen
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2019-11-12
     * @version     1.0.1     2021-06-21 Removed check on server var fast cgi path
     * @return      void
     */
    public static function add_helper_messages()
    {
        $purgeCache = new \Cacholong\Libraries\PurgeCache();

        if (!$purgeCache->hasMinimalOneCacheEnabled()) {
            $message = "“File with hosts (JSON)” from plugin “Cache control by Cacholong” does not contain active caches. Please activate at least one cache!";
            \Cacholong\Libraries\AdminMessage::set_message($message, \Cacholong\Entities\AdminNoticeClass::ERROR, true);
        }
    }

    /**
     * Handle ajax messages
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     Created at 2015-11-26
     *
     * @return      void
     */
    public static function handle_ajax_messages()
    {
        \Cacholong\Libraries\AdminMessage::handle_ajax_messages();
    }

    /**
     * Handle nav update
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     Created at 2015-11-27
     *
     * @param       string      $menu_id        Id of menu
     * @param       array       $menu_data      Optional. Array with menu data. Default array().
     *
     * @return      void
     */
    public static function handle_nav_menu_update($menu_id, $menu_data = array())
    {
        \Cacholong\Controller\Admin\PurgeCacheController::handle_nav_bar_change($menu_id, $menu_data = array());
    }

    /**
     * Handle all stuff just before final shutdown of wordpress
     * Note: used to handle purges of actions that are called multiple times
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-11-27
     * @version     1.0.1     2021-04-07    Delete possible temp option for ips
     * @return      void
     */
    public static function handle_shutdown()
    {
        $ajax = defined('DOING_AJAX') && DOING_AJAX;
        if (!$ajax) {
            \Cacholong\Controller\Admin\PurgeCacheController::purge_navbar_changes();
        }

        //delete possible temp option
        delete_option(Entities\Identifier::CC_TEMP_OVERRIDE_IPS);
    }

    /**
     * Handle debug hook
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     Created at 2019-02-20
     *
     * @param       mixed     ...        Optional. Unlimited number of additional variables. Default none.
     *
     * @return      void
     */
    public static function handle_debug_hook()
    {
        $arguments = func_get_args();
        $argumentsList = cacholong_cc_itemlist_to_sentence($arguments);

        cacholong_cc_log_error("Debug hook is called with following arguments: {$argumentsList}.");
    }

    /**
     * Add link to settings page
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     Created at 2019-02-20
     *
     * @param       array     $Links    Links
     * @param       string    $file     file base name to check.
     *
     * @return      array     links
     */
    public static function plugin_action_links($links, $file)
    {
        if ($file == CACHOLONG_CACHE_BASENAME) {
            $links[] = '<a href="' . cacholong_cc_get_settings_page_url() . '">' . __('Settings', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN) . '</a>';
        }
        return $links;
    }
}

/**
 * Start plugin
 */
\Cacholong\CacholongCacheControl::init();
