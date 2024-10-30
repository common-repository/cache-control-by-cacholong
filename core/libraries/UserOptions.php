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
 * UserOptions library
 * 
 * Wrapper for Wordpress user options
 * 
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * 
 */
class UserOptions {
    /*
     * @const       bool        $OPTIONS_GLOBAL             If options are global (true) or not (false). False means blog specific.
     */

    const OPTIONS_GLOBAL = false;

    /*
     * @const       string      OPTION_PREFIX               Option prefix, added before all options. Usefull to seperate keys from other plugin options + deleting everything at once.
     * 
     */
    const OPTION_PREFIX = 'cch_';

    /*
     * @var int      userID      User ID
     */

    private static $_userID = 0;

    /**
     * @var UserOptions The single instance of the class
     */
    private static $_instance = null;

    /**
     * Init $_instance with singleton object
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0     Created at 2017-12-20
     * 
     * @return      UserOptions        Instance of UserOptions
     */
    protected static function init()
    {
        if(is_null(self::$_instance))
        {
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
     * 
     */
    protected function __construct()
    {
        self::$_userID = get_current_user_id(); //function only available from init hook and onwards
    }

    /**
     * Set user option data
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @param       array       $data           Array of keys (name of data) and values (value of data)
     * 
     * @version     1.0.0       2017-12-20
     * 
     * @return      void
     */
    protected function set($data)
    {
        if(is_array($data))
        {
            foreach ($data as $key => $value)
            {
                update_user_option(self::$_userID, self::OPTION_PREFIX . $key, $value, self::OPTIONS_GLOBAL);
            }
        }
    }

    /**
     * Get user option data
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0     2017-12-20
     * @version     1.1.0     2017-12-21 Check for false
     * 
     * @param       array     $keys     Keys
     * 
     * @return      mixed     Content of user options or null if nothing is set.
     */
    protected function get($keys)
    {
        $data = array();
        $keys = is_array($keys) ? $keys : (array) $keys;

        foreach ($keys as $key)
        {
            $user_option = get_user_option(self::OPTION_PREFIX . $key, self::$_userID);
            $data[$key] = ($user_option !== false) ? $user_option : null;
        }

        return $data;
    }

    /**
     * Delete user option
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0     2017-12-20
     * 
     * @param       array     $keys     Keys
     * 
     * @return      void
     */
    protected function delete($keys)
    {
        $keys = is_array($keys) ? $keys : (array) $keys;

        foreach ($keys as $key)
        {
            delete_user_option(self::$_userID, self::OPTION_PREFIX . $key, self::OPTIONS_GLOBAL);
        }
    }

    /**
     * Delete all current user options from this plugin
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0     2017-12-20
     * 
     * @return      void
     */
    protected function deleteAllCurrentUser()
    {
        global $wpdb;
        $deleteKeys = array();

        //get prefix for all user options from this plugin
        $prefix = self::OPTION_PREFIX;
        if(!self::OPTIONS_GLOBAL)
        {
            $prefix = $wpdb->get_blog_prefix() . $prefix;
        }
        $prefixLength = strLen($prefix);

        //Get all relevant meta_keys
        $userID = self::$_userID;
        $userMetaRows = $wpdb->get_results("SELECT meta_key FROM {$wpdb->usermeta} WHERE user_id = {$userID} AND meta_key LIKE '{$prefix}%'; ", ARRAY_A);

        //Prepare delete keys
        foreach ($userMetaRows as $userMetaRow)
        {
            $deleteKeys[] = substr($userMetaRow['meta_key'], $prefixLength);
        }

        $this->delete($deleteKeys);
    }

}
/* End of file UserOptions.php */