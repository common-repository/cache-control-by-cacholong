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

require_once(CACHOLONG_CACHE_DIR . '/core/designpattern/observer/CacheHelperSubject.php');
require_once(CACHOLONG_CACHE_DIR . '/core/designpattern/observer/NginxPageSpeedCacheHelperObserver.php');
require_once(CACHOLONG_CACHE_DIR . '/core/designpattern/observer/NginxFastCGICacheHelperObserver.php');
require_once(CACHOLONG_CACHE_DIR . '/core/libraries/Hosts.php');
require_once(CACHOLONG_CACHE_DIR . '/core/libraries/purgeSetting/CustomtPostPurgeSetting.php');
require_once(CACHOLONG_CACHE_DIR . '/core/libraries/purgeSetting/DefaultPostPurgeSetting.php');
require_once(CACHOLONG_CACHE_DIR . '/core/libraries/WordpressURL.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');       //needed to access get_home_path();

use \Cacholong\Libraries\WordpressURL;

/**
 * Purge Cache Class
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 *
 * @version     1.0.0     Created at 2015-10-28
 */
class PurgeCache {
    /*
     * @var CacheHelperSubject      Cache helper subject
     */

    private $cacheHelperSubject;

    /*
     * @var array                   Array of CacheHelperObserver objects
     */
    private $cacheHelperObservers;

    /**
     * Constructor
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-28
     *
     * @return      void
     */
    public function __construct() {

    }

    /**
     * Handle purge all request
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-26
     * @version     1.1.0     2016-09-08 Abstracted code to start_purge_all
     * @return      void
     */
    public function handle_purge_all() {
        $this->start_purge_all();
    }

    /**
     * Handle purge all request for specific cache
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2019-02-21
     *
     * @param       string    $hookPurgeCache      Hook name of cache helper
     *
     * @return      void
     */
    public function handle_purge_all_specific($hookPurgeCache) {
        try {
            $hosts = $this->get_hosts();
            if (is_array($hosts)) {
                $this->cacheHelperSubject = new \Cacholong\Designpattern\Observer\CacheHelperSubject();
                $this->cacheHelperSubject->set_hosts_info($hosts);

                $this->create_all_observers($this->cacheHelperSubject);

                foreach ($this->cacheHelperObservers as $cacheHelperObserver) {
                    $cacheHelperObserverHookName = \Cacholong\Entities\Identifier::CC_HOOK_PURGE_CACHE_PREFIX . $cacheHelperObserver::getCacheHelperHookName();

                    if ($hookPurgeCache == $cacheHelperObserverHookName) {
                        $cacheHelperObserver->purgeAll($this->cacheHelperSubject);
                        break;
                    }
                }
            }
            //else do nop
        } catch (\Exception $ex) {
            cacholong_cc_log_exception($ex);
        }
    }

    /**
     * Handle purge single request
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok + todo woocommerce path
     *
     * @version     1.0.0     2015-10-26
     * @version     1.1.0     2016-08-29 Phpdoc update
     * @version     2.0.0     2016-09-07
     * @version     2.0.1     2020-12-14    bugfix: home page was ignored by url_to_postid
     * @version     2.0.2     2021-03-08    bugfix: url_to_postid didnt work with WPML + domain setting
     *
     * @param       $relative_path  string      Relative path to purge
     * @param       $postId         int         Optional. Post ID. Without it, post id is guessed based on relative path. Default 0.
     *
     * @return      void
     */
    public function handle_purge_single($relative_path, $postId = 0) {

        $postId = $this->getPostId($relative_path, $postId);

        if ($postId !== 0) {
            $postType = get_post_type($postId);
            $postTypeIsDefault = \Cacholong\Entities\PostType::is_wordpress_default_type($postType);

            //Create correct purgeSetting object
            if ($postTypeIsDefault) {
                $purgeSetting = new \Cacholong\Libraries\PurgeSetting\DefaultPostPurgeSetting();
            } else {
                $purgeSetting = new \Cacholong\Libraries\PurgeSetting\CustomPostPurgeSetting();
            }

            //Get all purge options
            $purgeSettingDefault = $purgeSetting->getDefault();
            $purgeSettingHome = $purgeSetting->getHome();
            $purgeSettingCategoriesWordpress = $purgeSetting->getCategoriesWordpress();
            $purgeSettingCategoriesWoocommerce = $purgeSetting->getCategoriesWoocommerce();
            $purgeSettingFlushAll = $purgeSetting->getFlushAll();

            //Decide which purge fits
            if ($purgeSettingFlushAll && !($purgeSettingDefault || $purgeSettingHome || $purgeSettingCategoriesWordpress || $purgeSettingCategoriesWoocommerce)) {    //ok
                //perform complete flush
                $this->start_purge_all();
            } elseif ($purgeSettingDefault && !($purgeSettingHome || $purgeSettingCategoriesWordpress || $purgeSettingCategoriesWoocommerce || $purgeSettingFlushAll )) {    //ok
                //perform single purge
                $this->start_purge_single($relative_path);
            } else {
                $multipleUrls = array();

                //perform multiple purge
                if ($purgeSettingDefault) {    //ok
                    $multipleUrls[] = $relative_path;
                }

                if ($purgeSettingHome) {    //ok
                    $multipleUrls[] = WordpressURL::get_home();
                }

                if ($purgeSettingCategoriesWordpress) {    //ok
                    $categoriesWordpress = WordpressURL::get_wordpress_categories_all($postId);
                    $multipleUrls = array_merge($multipleUrls, $categoriesWordpress);
                }

                if ($purgeSettingCategoriesWoocommerce) {
                    $categoriesWoocommerce = WordpressURL::get_woocommerce_categories();
                    $multipleUrls = array_merge($multipleUrls, $categoriesWoocommerce);
                }

                $this->start_purge_multiple($multipleUrls);
            }
        } else {    //ok
            // scenarios:
            // - url has changed -> there is no post attached anymore -> nothing needs to happen -> ok
            // - wrong url in purge single item on settings page
            // - something went wrong -> then nothing happens
        }
    }

    public function hasMinimalOneCacheEnabled()
    {
        $minimalOneCache = false;

        try {
            $hosts = $this->get_hosts();
            if (is_array($hosts)) {
                $minimalOneCache = \Cacholong\Libraries\Hosts::hasMinimalOneCacheEnabled($hosts);
            }
            //else do nop
        } catch (\Exception $ex) {
            cacholong_cc_log_exception($ex);
        }

        return $minimalOneCache;
    }
    /**
     * Get hosts from json file
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-11-01
     * @version     1.0.1     2015-11-10 Also url
     * @version     1.0.2     2016-01-04 Bugfix with empty. Before php 5.5 empty does not work with temp result from function.
     * @version     1.0.3     2019-02-21 Extra check on empty hosts file field
     *
     * @return      array|false     Array of hosts or false in case of error
     */
    private function get_hosts() {
        $pathToHostsFile = get_option(\Cacholong\Entities\Identifier::CC_FIELD_HOSTS_FILE);
        $hosts = false;

        if ($pathToHostsFile) {
            if (parse_url($pathToHostsFile, PHP_URL_SCHEME) && parse_url($pathToHostsFile, PHP_URL_HOST)) {
                //url
                $fullPathHostsFile = $pathToHostsFile;
                $hosts = \Cacholong\Libraries\Hosts::get_hosts($fullPathHostsFile, false);
            } else {
                //file path
                $fullPathHostsFile = get_home_path() . "{$pathToHostsFile}";
                $hosts = \Cacholong\Libraries\Hosts::get_hosts($fullPathHostsFile, true);
            }
        }

        return $hosts;
    }

    /**
     * Get relative path from form field
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-29
     *
     * @return      string|null    Relative path or null when not present / error
     */
    public function get_relative_path_from_form_field() {
        if (isset($_POST[\Cacholong\Entities\Identifier::CC_FIELD_SINGLE_PURGE]) &&
                !empty($_POST[\Cacholong\Entities\Identifier::CC_FIELD_SINGLE_PURGE])) {
            $unsafeSingleField = $_POST[\Cacholong\Entities\Identifier::CC_FIELD_SINGLE_PURGE];
            $unsafeSingleField = htmlspecialchars($unsafeSingleField);

            $singleField = trim($unsafeSingleField);
            return $singleField;
        } else {
            return null;
        }
    }

    /**
     * Get cron settings field key and name
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2019-02-19
     *
     * @return      array     Array of cron settings. Key is cache helper cron setting name, value is cache helper name.
     */
    public function getCronSettingsFieldKeyAndName() {
        try {
            $this->cacheHelperSubject = new \Cacholong\Designpattern\Observer\CacheHelperSubject();
            $this->create_all_observers($this->cacheHelperSubject);
            return $this->cacheHelperSubject->getCronSettingsFieldKeyAndName();
        } catch (\Exception $ex) {
            cacholong_cc_log_exception($ex);
        }
    }

    /**
     * Get cron settings field key and hook purge name
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2019-02-19
     *
     * @return      array     Array of cron settings. Key is cache helper cron setting name, value is cache helper hook name.
     */
    public function getCronSettingsFieldKeyAndHookPurgeName() {
        try {
            $this->cacheHelperSubject = new \Cacholong\Designpattern\Observer\CacheHelperSubject();
            $this->create_all_observers($this->cacheHelperSubject);
            return $this->cacheHelperSubject->getCronSettingsFieldKeyAndHookPurgeName();
        } catch (\Exception $ex) {
            cacholong_cc_log_exception($ex);
        }
    }

    /**
     * Create all observers and bind to $subject.
     * Note: If a new cache helper is introduced, it should be added here (and require_once at start of this file).
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-28
     *
     * @return      void
     */
    public function create_all_observers($subject) {
        $this->cacheHelperObservers[] = new \Cacholong\Designpattern\Observer\NginxPageSpeedCacheHelperObserver($subject);
        $this->cacheHelperObservers[] = new \Cacholong\Designpattern\Observer\NginxFastCGICacheHelperObserver($subject);
    }

    /**
     * Start purge single request
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-26
     * @version     1.1.0     2016-08-29 Phpdoc update
     * @version     2.0.0     2016-09-07
     * @version     2.1.0     2016-09-08 Private, abstracted from handle_purge_single
     *
     * @param       $relative_path  string      Relative path to purge
     *
     * @return      void
     */
    private function start_purge_single($relative_path) {
        try {
            $hosts = $this->get_hosts();
            if (is_array($hosts)) {
                if (!empty($relative_path)) {
                    $this->cacheHelperSubject = new \Cacholong\Designpattern\Observer\CacheHelperSubject();
                    $this->cacheHelperSubject->set_hosts_info($hosts);
                    $this->cacheHelperSubject->set_single_relative_path_url($relative_path);

                    $this->create_all_observers($this->cacheHelperSubject);

                    $this->cacheHelperSubject->notifyPurgeSingleRequest();
                } else {
                    $message = __("Could not purge single file, because no valid path was given. Please try again.", \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN);
                    \Cacholong\Libraries\AdminMessage::set_message($message, \Cacholong\Entities\AdminNoticeClass::ERROR);
                }
            }
            //else do nop
        } catch (\Exception $ex) {
            cacholong_cc_log_exception($ex);
        }
    }

    /**
     * Start purge multiple request
     * Note: it is possible that the request is valid, but there are no urls. This can happen f.e.
     * when purge setting 'Wordpress categories' is selected, but the post is not connected to a single category.
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2016-09-08
     *
     * @param       $relative_paths     array       Relative paths to purge
     *
     * @return      void
     */
    private function start_purge_multiple($relative_paths) {
        try {
            $hosts = $this->get_hosts();
            if (is_array($hosts)) {
                if (is_array($relative_paths) && $relative_paths) {
                    $this->cacheHelperSubject = new \Cacholong\Designpattern\Observer\CacheHelperSubject();
                    $this->cacheHelperSubject->set_hosts_info($hosts);
                    $this->cacheHelperSubject->set_multiple_relative_path_urls($relative_paths);

                    $this->create_all_observers($this->cacheHelperSubject);

                    $this->cacheHelperSubject->notifyPurgeMultipleRequest();
                } else {
                    $message = __("Nothing is purged. If you expected a purge, please check settings page from Cache control by Cacholong plugin.", \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN);
                    \Cacholong\Libraries\AdminMessage::set_message($message, \Cacholong\Entities\AdminNoticeClass::WARNING);
                }
            }
            //else do nop
        } catch (\Exception $ex) {
            cacholong_cc_log_exception($ex);
        }
    }

    /**
     * Start purge all request
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-26
     * @version     2.1.0     2016-09-08 Private, abstracted from handle_purge_all
     *
     * @return      void
     */
    private function start_purge_all() {
        try {
            $hosts = $this->get_hosts();
            if (is_array($hosts)) {
                $this->cacheHelperSubject = new \Cacholong\Designpattern\Observer\CacheHelperSubject();
                $this->cacheHelperSubject->set_hosts_info($hosts);

                $this->create_all_observers($this->cacheHelperSubject);

                $this->cacheHelperSubject->notifyPurgeAllRequest();
            }
            //else do nop
        } catch (\Exception $ex) {
            cacholong_cc_log_exception($ex);
        }
    }

    /*
     * Returns $postId if not zero or else tries to get post ID based on relative path.
     *
     * @param       $relative_paths     string      relative path to post
     * @param       $postId             int         Optional. Post ID. Default 0.
     */
    private function getPostId($relative_path, $postId = 0)
    {
        if($postId)
        {
            return $postId;
        }

        $postId = cacholong_cc_url_to_postid($relative_path);

        //fix: home page doesn't return postId
        if($postId === 0 && ($relative_path === WordpressURL::get_home()))
        {
            $postId = get_option( 'page_on_front' );
        }

        return $postId;
    }
}
