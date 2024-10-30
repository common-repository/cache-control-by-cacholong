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

require_once(CACHOLONG_CACHE_DIR . '/core/libraries/UserOptions.php');

/**
 * FlashData library
 * 
 * Flash data uses user options library to store 'flash' data
 * Flash data is data dat is used once and then deleted.
 * 
 * Explanation: Session flash data was used initially, but caused problems with some customers with session locking (session_start). 
 * Sessions are now abandoned in favor of flashData.
 * 
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * 
 */
class FlashData extends UserOptions {
    /*
     * @var string  FLASH_PREFIX    Prefix before flash data
     */

    const FLASH_PREFIX = 'flash_';

    /**
     * Set flash data. Flash data will only be available untill usage of FlashData::get_flash, then it will be deleted.
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0       2017-12-15
     * @version     1.1.0       2017-12-21 Adapted to userOptions
     * 
     * @param       array       $data     Array with keys (name of data) and values (data value)
     * 
     * @return      void
     */
    public static function set_flash($data)
    {
        if(is_array($data))
        {
            $flashData = array();

            foreach ($data as $key => $value)
            {
                $flashData[self::FLASH_PREFIX . $key] = $value;
            }

            parent::init()->set($flashData);
        }
    }

    /**
     * Get flash data and removes it.
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0       2017-12-15
     * @version     1.1.0       2017-12-21 Adapted to userOptions
     * 
     * @param       array       $keys     Array with keys to retrieve
     * 
     * @return      array       Array with session data or empty array
     */
    public static function get_flash($keys)
    {
        $keys = is_array($keys) ? $keys : (array) $keys;
        $flashKeys = array();

        $data = array();
        $flashData = array();

        //prepend flash prefix
        foreach ($keys as $key)
        {
            $flashKeys[] = self::FLASH_PREFIX . $key;
        }

        //get data from session
        $flashData = parent::init()->get($flashKeys);
        $keyFlashPrefixLength = strlen(self::FLASH_PREFIX);

        //get rid of prefix
        if(is_array($flashData))
        {
            foreach ($flashData as $key => $value)
            {
                $originalKey = substr($key, $keyFlashPrefixLength);
                $data[$originalKey] = $value;
            }
        }
        //else donop
        //
        //remove session flash data
        parent::init()->delete($flashKeys);

        return $data;
    }
    
    /**
     * Delete all flash data for current user
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0     2017-12-21
     * @version     1.1.0     2017-12-27 Forgot static
     * 
     * @return      void
     */
    public static function delete_all_flash_data()
    {
        parent::init()->deleteAllCurrentUser();
    }

}
/* End of file FlashData.php */