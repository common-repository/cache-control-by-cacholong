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

require_once(CACHOLONG_CACHE_DIR . '/core/libraries/PurgeCache.php');

use Cacholong\Designpattern\Observer;

/**
 * Controller class for attaching admin post actions to purge cache
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 *
 * @version     1.0.0     Created at 2015-10-21
 * @version     1.1.0     2017-12-15 From Session -> FlashData
 */
class PurgeCacheController
{
    /*
     * @const     string         FLASHDATA_KEY_PURGE_NAVBAR_CHANGES  Purge navbar changes key for FlashData (can't use bool, because of new UserOptions lib)
     */
    const FLASHDATA_KEY_PURGE_NAVBAR_CHANGES = 'purge_navbar_changes';

    /**
     * Add admin actions to methods
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     Created at 2015-10-21
     * @version     1.1.0     2019-02-21 Cron cache settings
     * @version     1.2.0     2019-11-12 Factory reset
     */
    public static function init()
    {
        do_action('cacholong_cache_control_purge_cache_before_init');

        //purge all / single
        add_action(\Cacholong\Entities\AdminPostAction::get_action_hook_name(\Cacholong\Entities\AdminPostAction::PURGE_ALL), array('\Cacholong\Controller\Admin\PurgeCacheController', 'handle_purge_all_settings_page'));
        add_action(\Cacholong\Entities\AdminPostAction::get_action_hook_name(\Cacholong\Entities\AdminPostAction::PURGE_SINGLE), array('\Cacholong\Controller\Admin\PurgeCacheController', 'handle_purge_single_settings_page'));

        //factory reset
        add_action(\Cacholong\Entities\AdminPostAction::get_action_hook_name(\Cacholong\Entities\AdminPostAction::FACTORY_RESET), array('\Cacholong\Controller\Admin\PurgeCacheController', 'handle_factory_reset'));

        //cron purge hook names
        $cronCacheKeyAndHooks = cacholong_cc_get_cron_settings_caches_field_key_and_hook_purge_name();
        foreach ($cronCacheKeyAndHooks as $cronCacheFieldKey => $cronCacheHookName) {
            add_action($cronCacheHookName, function() use ($cronCacheHookName) {
                self::handle_purge_all_specific($cronCacheHookName);
            });
        }

        do_action('cacholong_cache_control_purge_cache_after_init');
    }

    /**
     * Handle purge all request from settings page
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-26
     * @version     1.0.1     2015-11-27 Moved purge all to handle_purge_all and renamed function
     *
     * @return      void
     */
    public static function handle_purge_all_settings_page()
    {
        self::handle_purge_all();
        cacholong_cc_redirect_settings_page();
    }

    /**
     * Handle factory reset from settings page
     * Strange: Expected get_current_network_id() to give network id, but it always returns 1. Use get_current_blog_id works.
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2019-11-12
     * @version     1.1.0     2019-11-13 Fixed: get_current_blog_id to reset single page
     * @return      void
     */
    public static function handle_factory_reset()
    {
        $site_id = get_current_blog_id();
        \Cacholong\Libraries\CacholongCacheControlSetup::reset_options_to_default($site_id);

        $message = __("Factory settings restored. All settings restored to original default settings.", \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN);
        \Cacholong\Libraries\AdminMessage::set_message($message, \Cacholong\Entities\AdminNoticeClass::SUCCESS);

        cacholong_cc_redirect_settings_page();
    }

    /**
     * Handle purge all request
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-11-27
     *
     * @return      void
     */
    public static function handle_purge_all()
    {
        $purgeCache = new \Cacholong\Libraries\PurgeCache();
        $purgeCache->handle_purge_all();
    }

    /**
     * Handle purge all request for specific cache
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @param       string    $hookPurgeCache      Hook name of cache helper
     *
     * @return      void
     */
    public static function handle_purge_all_specific($hookPurgeCache)
    {
        $purgeCache = new \Cacholong\Libraries\PurgeCache();
        $purgeCache->handle_purge_all_specific($hookPurgeCache);
    }

    /**
     * Handle purge all request from admin bar node
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-31
     * @version     1.0.1     2015-11-09 Added exit, recommended.
     * @version     1.0.2     2015-11-25 wp_get_referer replaced by cacholong_cc_get_referer
     * @version     1.0.3     2015-11-25 cacholong_cc_redirect instead of wp_safe_redirect + exit
     * @version     1.1.0     2020-10-19 abstracted redirect code to redirect_to_referer_or_fallback_to_settings_page()
     * @return      void
     */
    public static function handle_purge_all_from_admin_bar()
    {
        $purgeCache = new \Cacholong\Libraries\PurgeCache();
        $purgeCache->handle_purge_all();
        static::redirect_to_referer_or_fallback_to_settings_page();
    }

    /**
     * Handle purge all nginx fastcgi from admin bar node
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2020-10-19
     *
     * @return      void
     */
    public static function handle_purge_nginx_fastcgi_from_admin_bar()
    {
        static::handle_purge_specific_cache_from_admin_bar(Observer\NginxFastCGICacheHelperObserver::class);
    }

    /**
     * Handle purge all nginx pagespeed from admin bar node
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2020-10-19
     *
     * @return      void
     */
    public static function handle_purge_nginx_pagespeed_from_admin_bar()
    {
        static::handle_purge_specific_cache_from_admin_bar(Observer\NginxPageSpeedCacheHelperObserver::class);
    }

    /**
     * Handle purge for specific cache from admin bar node
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2020-10-19
     *
     * @param       string    $cacheHelperObserver  Valid (sub)class name of Observer\CacheHelperObserver::class
     *
     * @return      void
     */
    public static function handle_purge_specific_cache_from_admin_bar($cacheHelperObserver)
    {
        if (is_subclass_of($cacheHelperObserver, Observer\CacheHelperObserver::class, true)) { //ok
            $hookName = \Cacholong\Entities\Identifier::CC_HOOK_PURGE_CACHE_PREFIX . $cacheHelperObserver::getCacheHelperHookName();
            \Cacholong\Controller\Admin\PurgeCacheController::handle_purge_all_specific($hookName);

            $cacheHelperName = $cacheHelperObserver::getCacheHelperName();
            $message = sprintf(__("Purged %s cache."), $cacheHelperName);
            \Cacholong\Libraries\AdminMessage::set_message($message, \Cacholong\Entities\AdminNoticeClass::SUCCESS);
        } else {    //ok
            $message = __("Tried to handle purge for single cache from admin bar, but cacheHelperObserver ({$cacheHelperObserver}) is invalid.");
            cacholong_cc_log_error($message, __FILE__);
        }

        static::redirect_to_referer_or_fallback_to_settings_page();
    }

    /**
     * Redirect to referer or fallback to settings page
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-19
     *
     * @return      void
     */
    private static function redirect_to_referer_or_fallback_to_settings_page()
    {
        $referer = cacholong_cc_get_referer();

        //always refer. In no referer is available, redirect to options page.
        $redirect = empty($referer) ? cacholong_cc_get_settings_page_url() : $referer;

        cacholong_cc_redirect($redirect);
    }

    /**
     * Handle purge single request
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-26
     *
     * @return      void
     */
    public static function handle_purge_single_settings_page()
    {
        $purgeCache = new \Cacholong\Libraries\PurgeCache();
        $relative_path = $purgeCache->get_relative_path_from_form_field();

        $purgeCache->handle_purge_single($relative_path);
        cacholong_cc_redirect_settings_page();
    }

    /**
     * Handle purge for post create or update from many sources.
     * Will only activate when it's an update & no revision & status publish. Different outcomes depending on ajax  or not.
     * When a post is updated or saved, this function is still called.
     *
     * Note: when inline_save ajax action used, ajax message system cannot function. Messages will be shown in next request.
     * Note: when post is published in future, it wil: 1) have post_status future and 2) when scheduled time arrives have post_status publish
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * @link        https://codex.wordpress.org/Plugin_API/Action_Reference/save_post
     *
     * @version     1.0.0     2015-11-03
     * @version     1.0.1     2015-11-09 Added exit, recommended.
     * @version     1.1.0     2015-11-16 Bugfix. Was not static. Multiple checks and routes.
     * @version     1.1.1     2015-11-25 wp_get_referer replaced by cacholong_cc_get_referer
     * @version     1.2.0     2015-11-26 redirects and ajax/normal route removed. All messages are handled in ajax now.
     * @version     1.2.1     2015-11-26 Check post type if purge is needed
     * @version     1.2.2     2015-11-27 Moved call to purge_slug_changes elsewhere
     * @version     1.3.0     2016-08-29 Added 'future' branch
     * @version     1.3.1     2016-09-07 Use PostType entity
     * @version     1.4.0     2018-11-14 Use get_purge_is_required
     *
     * @param       $postID     int         Id op post
     * @param       $wp_post    WP_Post     WP Post object
     * @param       $is_update  bool        Existing post being updated (true) or not (false). Is_update is false when new post is created.
     *
     * @return      void
     */
    public static function handle_purge_post_save($postID, $wp_post, $is_update)
    {
        if ($is_update) {
            //must be an update

            if (\Cacholong\Entities\PostType::get_purge_is_required($postID)) {
                $post_status = get_post_status($postID);
                switch ($post_status) {
                    case 'publish':
                        $relative_path = cacholong_cc_get_post_relative_url($postID);
                        $purgeCache = new \Cacholong\Libraries\PurgeCache();
                        $purgeCache->handle_purge_single($relative_path, $postID);

                        //no redirect, normal flow, messages handled in ajax
                        break;
                    case 'future':
                        //do nop -> when post is finally published, it will follow the case 'publish'
                        //because it is new, no purge is needed
                        //$scheduledTimestamp = get_post_time('U', true, $postID);
                        break;
                    default:
                    //draft/etc, donop
                }
            }
            //else not allowed type, donop
        }
        // else is new, donop
    }

    /**
     *
     * @param       $postID             int         Id op post
     *
     * @return      void
     */
    public static function handle_purge_post_trash($postID)
    {
        static::handle_purge_post_trash_or_untrash($postID);
    }

    /**
     * Purge untrash will trigger a purge twice. Once for the untrash and
     * once because save_post is also triggered.
     *
     * @param       $postID             int         Id op post
     * @param       $previous_status    string      The status of the post at the point where it was trashed.
     *
     * @return      void
     */
    public static function handle_purge_post_untrash($postID, $previous_status = null)
    {
        static::handle_purge_post_trash_or_untrash($postID);
    }

    /**
     * @param       $postID             int         Id op post
     *
     * @return      void
     */
    private static function handle_purge_post_trash_or_untrash($postID)
    {
        if (\Cacholong\Entities\PostType::get_purge_is_required($postID)) {
            $relative_path = cacholong_cc_get_post_relative_url($postID);
            $purgeCache = new \Cacholong\Libraries\PurgeCache();
            $purgeCache->handle_purge_single($relative_path, $postID);
        }
        //else not allowed type, donop
    }



    /**
     * Handle slug changes
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-11-17
     * @version     1.1.0     2017-12-15 Session > FlashData
     * @version     1.2.0     2018-11-15 Use original_slug from wordpress
     * @version     2.0.0     2021-03-10 Rebuild to work without flashdata
     *
     * @var         int     $post_id     Post ID.
     * @var         array   $data        Data that has changed
     */
    public static function handle_slug_change($postID, $data)
    {
        //only continue when post is not a revision and poststatus = publish
        if(!(wp_is_post_revision($postID) === false && get_post_status($postID) == 'publish')) {
            return;
        }

        //slugs equal? do not continue
        if(get_post_field( 'post_name', $postID ) === $data['post_name'])
        {
            return;
        }

        $relative_path = cacholong_cc_get_post_relative_url($postID);
        $purgeCache = new \Cacholong\Libraries\PurgeCache();
        $purgeCache->handle_purge_single($relative_path, $postID);
    }

    /**
     * Handle nav bar change by setting FlashData switch
     * Note: can't handle purge here, because this function might be called multiple times
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-11-27
     * @version     1.1.0     2017-12-15    Session > FlashData
     * @version     1.2.0     2017-12-21    bool true > int 1
     *
     * @param       string      $menu_id        Id of menu
     * @param       array       $menu_data      Optional. Array with menu data. Default array().
     *
     *
     * @return      void
     */
    public static function handle_nav_bar_change($menu_id, $menu_data = array())
    {
        \Cacholong\Libraries\FlashData::set_flash(array(self::FLASHDATA_KEY_PURGE_NAVBAR_CHANGES => 1));
    }

    /**
     * Purge navbar changes
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-11-27
     * @version     1.1.0     2017-12-15    Session > FlashData
     * @return      void
     */
    public static function purge_navbar_changes()
    {
        $purgeNavbarChanges = \Cacholong\Libraries\FlashData::get_flash(self::FLASHDATA_KEY_PURGE_NAVBAR_CHANGES);
        $purgeNavbarChanges = !empty($purgeNavbarChanges) ? $purgeNavbarChanges[self::FLASHDATA_KEY_PURGE_NAVBAR_CHANGES] : false;

        if ($purgeNavbarChanges == 1) {
            self::handle_purge_all();
        }
        //else donop
    }
}
