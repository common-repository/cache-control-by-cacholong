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

use Cacholong\Entities;

/**
 * Hosts library is used to handle all hosts
 * Note: hosts file should also contain localhost information!
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 *
 * @version     1.0.0     2015-10-28
 */
class Hosts
{
    /*
     * @var string  Key for servers
     */

    const SERVERS_KEY = 'servers';

    /*
     * @var string  Key for host (ip address or url)
     */
    const HOST_KEY = 'ip';

    /*
     * @var string  Key for host name
     */
    const NAME_KEY = 'name';

    /*
     * @var string  Key for nginx fastcgi
     */
    const NGINX_FASTCGI_KEY = 'fastcgi';

    /*
     * @var string  Key for nginx pagespeed
     */
    const NGINX_PAGESPEED_KEY = 'pagespeed';

    /*
     * @var string  Default hosts content if no file is found
     */
    const HOSTS_DEFAULT_CONTENT = '{"servers":[{"name": "localhost", "ip": "127.0.0.1", "pagespeed": true, "fastcgi": true}]}';

    /**
     * Get hosts from given location
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-11-02
     * @version     1.0.1     2016-03-28 else change (return false -> return self::HOSTS_DEFAULT_CONTENT
     * @version     1.1.0     2021-04-07 override ips implemented
     *
     * @param       string    $locationHostsFile        Relative path to file with locations for hosts
     * @param       bool      $isFilePath               File path (true) or url (false)
     *
     * @return      array|false     Array with hosts or false  in case of error
     */
    public static function get_hosts($locationHostsFile, $isFilePath)
    {
        $overrideIps = get_option(Entities\Identifier::CC_TEMP_OVERRIDE_IPS);
        delete_option(Entities\Identifier::CC_TEMP_OVERRIDE_IPS);
        
        if ($overrideIps) {
            return self::convert_ips_to_hosts_array($overrideIps);
        }

        $fileContent = self::read_file($locationHostsFile, $isFilePath);
        if ($fileContent !== FALSE && $fileContent) {
            return self::convert_json_hosts_to_array($fileContent);
        } else {    //ok
            return self::convert_json_hosts_to_array(self::HOSTS_DEFAULT_CONTENT);
        }
    }

    /**
     * Read file with JSON hosts. Path can be filepath or url.
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-11-02
     * @version     1.0.1     2015-11-25 (Matthijs) When no hosts file is present, default JSON is returned
     * @version     1.0.2     2016-03-28 Small changes to invalid path part
     * @version     1.1.0     2017-12-21 Removed message with missing JSON file (not needed)
     *
     * @param       string    $path                     File or url path
     * @param       bool      $isFilePath               File path (true) or url (false)
     *
     * @return      string|false        String with file content or false in case of error
     */
    private static function read_file($path, $isFilePath)
    {
        if (self::is_valid_path($path, $isFilePath)) {
            $fileContent = @file_get_contents($path);
            if ($fileContent !== FALSE) {
                return $fileContent;
            } else {    //ok
                $message = __('Failed to read JSON file with hosts.', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN);
                \Cacholong\Libraries\AdminMessage::set_message($message, \Cacholong\Entities\AdminNoticeClass::ERROR);

                cacholong_cc_log_error(__("Failed to read JSON file {$path}.", \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), __FILE__);

                return false;
            }
        } else {    //ok
            return false;
        }
    }

    /**
     * Convert JSON hosts to PHP array
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-11-02
     *
     * @param       string      $JSON       JSON string
     *
     * @return      array|false     Array with information about hosts or false in case of error
     */
    private static function convert_json_hosts_to_array($JSON)
    {
        $rawHosts = \Cacholong\Libraries\Json::decode($JSON);
        $rawHosts = isset($rawHosts[self::SERVERS_KEY]) ? $rawHosts[self::SERVERS_KEY] : null;

        if (is_array($rawHosts)) {
            $cleanHosts = array();

            foreach ($rawHosts as $host) {
                $cleanHost = array();
                $cleanHost[self::HOST_KEY] = isset($host[self::HOST_KEY]) ? rtrim($host[self::HOST_KEY], '/') : null;
                $cleanHost[self::NAME_KEY] = isset($host[self::NAME_KEY]) ? $host[self::NAME_KEY] : null;
                $cleanHost[self::NGINX_FASTCGI_KEY] = isset($host[self::NGINX_FASTCGI_KEY]) ? $host[self::NGINX_FASTCGI_KEY] : null;
                $cleanHost[self::NGINX_PAGESPEED_KEY] = isset($host[self::NGINX_PAGESPEED_KEY]) ? $host[self::NGINX_PAGESPEED_KEY] : null;

                $cleanHosts[] = $cleanHost;
            }

            return $cleanHosts;
        } else {    //ok
            $message = __("JSON file with hosts contains errors.", \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN);
            \Cacholong\Libraries\AdminMessage::set_message($message, \Cacholong\Entities\AdminNoticeClass::ERROR);

            cacholong_cc_log_error(__("JSON hosts contained errors.", \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), __FILE__);

            return false;
        }
    }

    /**
     * Convert ip addresses to hosts array
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2021-04-07
     *
     * @param       array     $ips  One or more ip addresses for a hosts array
     *
     * @return      array     Array with information about hosts (based on given ips)
     */
    private static function convert_ips_to_hosts_array($ips)
    {
        $hosts = [];

        foreach ($ips as $ip) {
            $host = [];

            $host[self::HOST_KEY] = $ip;
            $host[self::NAME_KEY] = null;
            $host[self::NGINX_FASTCGI_KEY] = true;
            $host[self::NGINX_PAGESPEED_KEY] = true;

            $hosts[] = $host;
        }

        return $hosts;
    }

    /**
     * Check if minimal one cache is enabled
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2019-11-13
     *
     * @param       array       $cleanHosts     Array with host info
     *
     * @return      bool        True (minimal one cache enabled) or false (none)
     */
    public static function hasMinimalOneCacheEnabled($cleanHosts)
    {
        foreach ($cleanHosts as $host) {
            if ($host[self::NGINX_FASTCGI_KEY] || $host[self::NGINX_PAGESPEED_KEY]) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if path to file (filepath / url) is valid
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-11-10
     *
     * @param       string    $path                     File or url path
     * @param       bool      $isFilePath               File path (true) or url (false)
     *
     * @return      bool      Valid path (true) or not (false)
     */
    private static function is_valid_path($path, $isFilePath)
    {
        if ($isFilePath) {
            //file path
            return file_exists($path);
        } else {
            //url
            $headers = @get_headers($path);
            if (isset($headers[0])) {
                $header_contains_404 = strpos($headers[0], '404');
                return ($header_contains_404 === false);
            } else {
                return false;
            }
        }
    }
}

/* End of file Hosts.php */