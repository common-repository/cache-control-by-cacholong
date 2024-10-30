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
defined('ABSPATH') OR exit;

use Cacholong\Libraries\WPML;

/*
 * Note: These helper functions are available and therefore prefixed with cacholong_cc_ to prevent collisions.
 */

/**
 * Redirect to settings page
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 *
 * @version     1.0.0     2015-10-26
 * @version     1.0.1     2015-11-09 Added exit, recommended.
 * @version     1.0.2     2015-11-25 cacholong_cc_redirect instead of wp_redirect + exit
 * @version     1.1.0     2018-02-23 Renamed function (larger prefix)
 *
 * @return      void
 */
function cacholong_cc_redirect_settings_page()
{
    $settings_page = cacholong_cc_get_settings_page_url();
    cacholong_cc_redirect($settings_page);
}

/**
 * Redirect to $redirect
 * Note: uses wordpress conventions for redirection
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 *
 * @version     1.0.0     2015-10-25
 * @version     1.1.0     2018-02-23 Renamed function (larger prefix)
 *
 * @param       string    $redirect     Redirect url
 *
 * @return      void
 */
function cacholong_cc_redirect($redirect)
{
    wp_safe_redirect($redirect);
    exit();
}

/**
 * Get url to settings page with optional query params
 * Note: nonce is added and can be checked if needed.
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 *
 * @version     1.0.0     2015-10-26
 * @version     1.0.1     2015-10-31 Query string
 * @version     1.1.0     2018-02-23 Renamed function (larger prefix)
 *
 * @param       array       $query      Query elements
 *
 * @return      string      URL to settings page of Cacholong Cache helper
 */
function cacholong_cc_get_settings_page_url($query = null)
{
    $queryString = build_query($query);
    $queryString = !empty($queryString) ? "&{$queryString}" : null;
    $adminUrl = admin_url('options-general.php?page=' . \Cacholong\Entities\Identifier::CC_SETTINGS_PAGE_ID) . $queryString;

    return wp_nonce_url($adminUrl);
}

/**
 * Log exceptions, wrapper around log_error
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 *
 * @version     1.0.0     2015-10-21
 * @version     1.1.0     2018-02-23 Renamed function (larger prefix)
 *
 * @return      void
 */
function cacholong_cc_log_exception($exception)
{
    cacholong_cc_log_error($exception->getMessage(), $exception->getFile(), $exception->getLine(), $exception->getCode(), $exception->getTraceAsString());
}

/**
 * Log error with optional data (will only log when WP_DEBUG is true)
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 *
 * @version     1.0.0     2015-10-21
 * @version     1.1.0     2018-02-23 Renamed function (larger prefix)
 *
 * @param       mixed       $message        Message
 * @param       string      $file           Optional. File name. Default null.
 * @param       int         $line           Optional. Line number. Default null.
 * @param       string      $code           Optional. Error code. Default null.
 * @param       string      $trace          Optional. Trace information. Default null.
 *
 * @return      void
 */
function cacholong_cc_log_error($message, $file = null, $line = null, $code = null, $trace = null)
{
    if (WP_DEBUG === true) {
        $pretty_message = (is_array($message) || is_object($message)) ? print_r($message, true) : $message;

        $message_with_info = null;
        $message_with_info .= 'Time: ' . date('d-m-Y\, H:i:s', time()) . ' (UTC)' . PHP_EOL;
        if (!empty($file)) {
            $message_with_info .= 'File: ' . $file . PHP_EOL;
        }

        if (!empty($line)) {
            $message_with_info .= 'Line: ' . $line . PHP_EOL;
        }

        if (!empty($code)) {
            $message_with_info .= 'Code: ' . $code . PHP_EOL;
        }

        $message_with_info .= 'Message: ' . $pretty_message . PHP_EOL;

        if (!empty($trace)) {
            $message_with_info .= 'Error trace: ' . PHP_EOL . $trace . PHP_EOL;
        }

        error_log($message_with_info, 3, CACHOLONG_LOG_PATH);
        //var_dump($message_with_info);
    }
}

/**
 * Log wp remote response
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 *
 * @version     1.0.0     2021-04-20
 *
 * @param       array|WP_Error  $response        Response
 *
 * @return      void
 */
function cacholong_cc_log_wp_remote_response($url, $options, $response, $dontlogKeys = ['http_response', 'cookies', 'filename'])
{
    cacholong_cc_log_error("Request to wp_remote_request:");
    cacholong_cc_log_error('url: ' . $url);
    cacholong_cc_log_error('options:');
    cacholong_cc_log_error($options);
    cacholong_cc_log_error("Result of wp_remote_request:");

    if(!is_wp_error($response))
    {
        $finalResponse = $response;

        //remove dont log information
        foreach($dontlogKeys as $dontLogKey)
        {
            if(array_key_exists($dontLogKey, $finalResponse))
            {
                unset($finalResponse[$dontLogKey]);
            }
        }

        cacholong_cc_log_error($finalResponse);
    }
    else
    {
        cacholong_cc_log_error($response->get_error_messages());
    }
}

/**
 * Get relative path to post (without the base url)
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 *
 * @version     1.0.0     2015-11-03
 * @version     1.1.0     2018-02-23 Renamed function (larger prefix)
 *
 * @return      string    Relative url starting with /
 */
function cacholong_cc_get_post_relative_url($postID)
{
    $permaLink = get_permalink($postID);
    return cacholong_cc_get_post_relative_url_from_permalink($permaLink);
}

/**
 * Get relative path for given permalink
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 *
 * @version     1.0.0     2021-03-10
 *
 * @return      string    Relative url starting with /
 */
function cacholong_cc_get_post_relative_url_from_permalink($permaLink)
{
    $home_url = cacholong_cc_get_home_url();

    $relative_path = str_replace($home_url, null, $permaLink);
    if ($relative_path[0] != '/') {
        $relative_path = '/' . $relative_path;
    }

    return $relative_path;
}

/**
 * Get host name
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 * @see         https://wordpress.stackexchange.com/questions/20294/whats-the-difference-between-home-url-and-site-url
 *
 * @version     1.0.0     2015-11-10
 * @version     1.1.0     2018-02-23 Renamed function (larger prefix)
 * @version     1.2.0     2021-03-04 WPML support
 *
 * @return      string|false    Host name or false if not available
 */
function cacholong_cc_get_host_name()
{
    $url = cacholong_cc_get_home_url();
    return parse_url($url, PHP_URL_HOST);
}

/**
 * Get post slug
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 *
 * @version     1.0.0     2015-11-16
 * @version     1.1.0     2018-02-23 Renamed function (larger prefix)
 *
 * @return      string    Post slug
 */
function cacholong_cc_post_slug($postID)
{
    $permaLink = get_permalink($postID);
    return basename($permaLink);
}

/**
 * Retrieve referer from '_wp_http_referer' or HTTP referer.
 * Note:        When used right after login, the url will contain loggedOut=true, which causes logout trouble.
 *
 * @author      Wordpress | Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Wordpress | Cacholong <info@cacholong.nl>
 * @todo        ok
 *
 * @version     1.0.0     Original
 * @version     1.1.0     2015-11-25 Based on wordpress wp_get_referer, but will try to always return referer
 * @version     1.2.0     2018-02-23 Renamed function (larger prefix)
 *
 * @return      false| string    URL or false in case of error
 */
function cacholong_cc_get_referer()
{
    $referer = false;

    if (function_exists('wp_validate_redirect')) {
        $wp_http_referer = isset($_REQUEST['_wp_http_referer']) ? $_REQUEST['_wp_http_referer'] : null;
        $http_referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;

        if (!empty($wp_http_referer) || !empty($http_referrer)) {
            if (!empty($wp_http_referer)) {
                $referer = wp_unslash($wp_http_referer);
            } else {
                $referer = wp_unslash($http_referrer);
            }

            $referer = wp_validate_redirect($referer, false);
        }
        //else donop
    }
    //else donop

    return $referer;
}

/**
 * Creates readable sentence from list of items. Output in three ways:
 * 1 item: 'item 1'
 * 2 items: 'item 1 and item 2'
 * 3+ items: 'item 1, item 2 and item 3'
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 *
 * @version     1.0.0     2016-03-28
 * @version     1.1.0     2018-02-23    Renamed function (larger prefix)
 * @version     1.2.0     2018-11-14    Handle associative (and numeric) itemList
 *
 * @var         array     $itemList             List of items
 * @var         string    $singleItemPrefix     Optional. Prefix for single item. Default null.
 * @var         string    $singleItemSuffix     Optional. Suffix for single item. Default null.
 *
 * @return      string    List of items
 */
function cacholong_cc_itemlist_to_sentence($itemList, $singleItemPrefix = null, $singleItemSuffix = null)
{
    $sentenceList = null;

    $itemCount = count($itemList);

    //handle prefix and suffix
    if ($singleItemPrefix or $singleItemSuffix) {
        foreach ($itemList as &$singleItem) {
            $singleItem = $singleItemPrefix . $singleItem . $singleItemSuffix;
        }
    }

    //create sentence
    if ($itemCount <> 0) {
        if ($itemCount == 1) {
            $sentenceList = reset($itemList);
        } elseif ($itemCount == 2) {
            $sentenceList = reset($itemList);   //first item
            $sentenceList .= __(' and ', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN);
            $sentenceList .= end($itemList);  //second item
        } else {    //3+
            $itemListWithoutLast = array_slice($itemList, 0, $itemCount - 1);
            $sentenceList = implode(', ', $itemListWithoutLast);

            $sentenceList .= __(' and ', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN) . end($itemList);
        }
    }

    return $sentenceList;
}

/**
 * Get checkbox checked attribute
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 *
 * @version     1.0.0     2016-09-07    Original
 * @version     1.1.0     2018-02-23    Renamed function (larger prefix)
 *
 * @param       mixed     $value        Everything that is considered true (checked) or everything else (not checked)
 *
 * @return      string|null     Returns checked attribute (value 1) or nothing
 */
function cacholong_cc_checkbox_checked_attribute($value)
{
    $checked = null;

    if ($value) {
        $checked = 'checked="checked"';
    }

    return $checked;
}

/**
 * Get cron settings cache(s) field key and name
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 *
 * @version     1.0.0     2019-02-19    Original
 *
 * @return      array
 */
function cacholong_cc_get_cron_settings_caches_field_key_and_name()
{
    $purgeCache = new \Cacholong\Libraries\PurgeCache();
    return $purgeCache->getCronSettingsFieldKeyAndName();
}

/**
 * Get cron settings cache(s) field key and hook purge name
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 *
 * @version     1.0.0     2019-02-21    Original
 *
 * @return      array
 */
function cacholong_cc_get_cron_settings_caches_field_key_and_hook_purge_name()
{
    $purgeCache = new \Cacholong\Libraries\PurgeCache();
    return $purgeCache->getCronSettingsFieldKeyAndHookPurgeName();
}

/**
 * Returns guaranteed string with hh:mm (24hour format) format or null in case of invalid input.
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 *
 * @version     1.0.0     2019-02-19
 *
 * @param       string      $unsafeInput  Unsafe input for a hh:mm field
 *
 * @return      string|null Sanatized hh:mm field or null if not valid format.
 */
function cacholong_cc_sanitize_time_hh_mm_24h($unsafeInput)
{

    $sanitizedHHMM = null;
    $unsafeInput = trim($unsafeInput);

    if (preg_match(Cacholong\Entities\Identifier::TIME_OF_DAY_24H_REGEX, $unsafeInput) == 1) {
        $sanitizedHHMM = $unsafeInput;
    }

    return $sanitizedHHMM;
}

/**
 * Returns option cron time (from db)
 * Note: because of sanitazion safe.
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 *
 * @version     1.0.0     2019-02-20
 *
 * @return      string    Cron time or 00:00 (if was empty)
 */
function cacholong_cc_get_option_cron_time()
{
    $value = get_option(\Cacholong\Entities\Identifier::CC_FIELD_CRON_TIME);
    return empty($value) ? '00:00' : $value;
}

/**
 * Get cron options for purge caches
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 *
 * @version     1.0.0     2019-02-19    Original
 *
 * @return      array     Key of setting and value of setting
 */
function cacholong_cc_get_options_cron_caches()
{
    $cronOptions = array();

    $cronCacheSettings = cacholong_cc_get_cron_settings_caches_field_key_and_name();
    foreach ($cronCacheSettings as $cronCacheFieldKey => $cronCacheName) {
        $cronOptions[$cronCacheFieldKey] = get_option($cronCacheFieldKey);
    }

    return $cronOptions;
}

/**
 * Get current used timezone (wordpress or php fallback)
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 *
 * @version     1.0.0     2019-02-22    Original
 *
 * @return      string    valid timezone
 */
function cacholong_cc_get_timezone()
{
    $wordpressTimeZone = get_option('timezone_string');
    return $wordpressTimeZone ? $wordpressTimeZone : date_default_timezone_get();
}

/**
 * Does woocommerce exist?
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        build when needed
 *
 * @version     1.0.0     2016-09-07    Original
 * @version     1.1.0     2018-02-23    Renamed function (larger prefix)
 *
 * @return      bool      Yes (true) or not (false)
 */
function cacholong_cc_has_woocommerce()
{
    return false; //function_exists('is_woocommerce');
}

/**
 * Check if WP_CLI is active
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 *
 * @version     1.0.0     2019-11-13
 * @return      bool
 */
function cacholong_cc_is_wp_cli()
{
    return defined('WP_CLI') && WP_CLI;
}

/**
 * Check if this is a gutenberg page
 * Note: Please use after replace_editor / admin_enqueue_scripts hook.
 *
 * @author      https://github.com/Freemius/wordpress-sdk | Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   https://github.com/Freemius/wordpress-sdk | Cacholong <info@cacholong.nl>
 * @todo        ok
 * @see         https://wordpress.stackexchange.com/questions/309862/check-if-gutenberg-is-currently-in-use
 *
 * @version     1.0.0     2019-11-29
 * @return      bool
 */
function cacholong_cc_is_gutenberg_page()
{
    //1: gutenberg plugin is used
    if (function_exists('is_gutenberg_page') && is_gutenberg_page()) {
        return true;
    }

    //2: gutenberg pagen on wordpress 5+
    $current_screen = get_current_screen();
    if (method_exists($current_screen, 'is_block_editor') && $current_screen->is_block_editor()) {
        return true;
    }

    //3: no gutenberg
    return false;
}

/**
 * URL to postid
 * Differs from original url_to_postid, because it also works with WPML and language negotiation type domain
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 * @see         https://wordpress.stackexchange.com/questions/20294/whats-the-difference-between-home-url-and-site-url
 *
 * @version     1.0.0     2021-03-08
 *
 * @return      int     Post id
 */
function cacholong_cc_url_to_postid($url)
{
    if (!cacholong_cc_is_url_absolute($url) && WPML::active() && WPML::is_language_negotiation_type_domain()) {
        $homeUrl = rtrim(WPML::getHomeUrl(), '/');
        $relativeUrl = ltrim($url, '/');

        $url = "{$homeUrl}/{$relativeUrl}";
    }

    return url_to_postid($url);
}

/**
 * Is url absolute or not? Meaning does it has a scheme or does it start like //
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 * @see         https://wordpress.stackexchange.com/questions/20294/whats-the-difference-between-home-url-and-site-url
 *
 * @version     1.0.0     2021-03-08
 *
 * @return      bool     True (absolute url) or false
 */
function cacholong_cc_is_url_absolute($url)
{
    //<scheme>:// or without scheme //
    return (strpos($url, '://') !== false)  ||
        (substr($url, 0, 2) == '//');
}

/**
 * Get WPML safe home url (without query arguments / fragments)
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 * @see         https://wordpress.stackexchange.com/questions/20294/whats-the-difference-between-home-url-and-site-url
 *
 * @version     1.0.0     2021-03-08
 *
 * @return      string  home url
 */
function cacholong_cc_get_home_url()
{
    $url = null;

    if (WPML::active() && WPML::is_language_negotiation_type_domain()) {
        $url = WPML::getHomeUrl();
    }
    else
    {
        $url = get_home_url();
    }


    return cacholong_cc_get_url_without_query_fragment($url);
}


/**
 * Get url without query argument(s) (?a=1&b=2) and/or fragment (#fragment)
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 * @see         https://wordpress.stackexchange.com/questions/20294/whats-the-difference-between-home-url-and-site-url
 *
 * @version     1.0.0     2021-03-10
 *
 * @return      string    url without query arguments / fragment
 */
function cacholong_cc_get_url_without_query_fragment($url)
{
    $pieces = explode('?', $url);
    return $pieces ? reset($pieces) : $url;
}