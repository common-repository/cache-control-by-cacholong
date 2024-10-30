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
 * JSON
 * 
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * 
 * @version     1.0.0     2011-06-09
 * @version     1.0.1     2015-10-28 Adjusted for Cacholong
 */
class Json {

    /**
     * Encodes given value for json output
     * 
     * Note: associative array will be auto-converted to javascript-object, because javascript doesn't support associative arrays
     * Note: $bitmaskOptions are supported from php 5.3.0 and forward.
     * Note: when $bitmaskOptions = 0 : will escape double quote ", backslash \ and leave single quote ' un-escaped     
     * 
     * @since 2011-06-09
     * @author Piet Rol, Preliot
     * @version 1.0
     * 
     * @todo ok
     * @link http://www.php.net/manual/en/function.json-encode.php 
     * 
     * @param   mixed       $value              Can be all type (int, string, bool, float, array, object, etc) except resource. Must be utf-8.
     * @param   int         $bitmaskOptions     Optional. Bitmask to influence encoding. Default 0. See link for details.
     * 
     * @return  mixed       String with json values or NULL in case of error.
     */
    public static function encode($value, $bitmaskOptions = 0)
    {
        $JSON_value = null;
        if($bitmaskOptions == 0)
        {
            //bugfix: pre php 5.3.0
            $JSON_value = json_encode($value);
        }
        else
        {
            $JSON_value = json_encode($value, $bitmaskOptions);
        }

        if(!self::json_error())
        {
            return $JSON_value;
        }
        else
        {
            cacholong_cc_log_error(_x('Failed to encode json string, because of json_encode error.', 'Internal error message JSON class', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), __FILE__);
            return NULL;
        }
    }

    /**
     * Decodes given json value for php output
     *           
     * @since 2011-06-09
     * @author Piet Rol <info@preliot.nl>
     * @version 1.0
     * @todo ok
     * 
     * @param   string       $jsonValue      Valid json string.
     * 
     * @return  mixed        Will output associative array (converted json object) or NULL in case of error.
     */
    public static function decode($jsonValue)
    {
        $phpValue = json_decode($jsonValue, TRUE);

        if(!self::json_error())
        {
            return $phpValue;
        }
        else    //ok
        {
            cacholong_cc_log_error(_x('Failed to decode json string, because of json_encode error.', 'Internal error message JSON class', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), __FILE__);
            return NULL;
        }
    }

    /**
     * json_error
     *
     * Handle possible json errors (make log-entry) and returns boolean
     *           
     * @since 2011-06-09
     * @author Piet Rol <info@preliot.nl>
     * @version 1.0
     * @version 1.01 Check if json_last_error exists (only supported >= php 5.3.0)
     * @todo ok
     * 
     * @return  bool        Will return true (error) or false(no error)
     */
    private static function json_error()
    {
        $hasError = TRUE;

        if(function_exists('json_last_error'))
        {
            $jsonError = json_last_error();

            switch ($jsonError)
            {
                case JSON_ERROR_NONE:   //ok
                    $hasError = FALSE;
                    break;
                case JSON_ERROR_DEPTH:   //ok
                    cacholong_cc_log_error(_x('The maximum stack depth has been exceeded.', 'Internal error message JSON class', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), __FILE__);
                    break;
                case JSON_ERROR_CTRL_CHAR:   //ok
                    cacholong_cc_log_error(_x('Control character error, possibly incorrectly encoded.', 'Internal error message JSON class', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), __FILE__);
                    break;
                case JSON_ERROR_STATE_MISMATCH: //ok
                    cacholong_cc_log_error(_x('Invalid or malformed JSON.', 'Internal error message JSON class', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), __FILE__);
                    break;
                case JSON_ERROR_SYNTAX: //ok
                    cacholong_cc_log_error(_x('Syntax error in JSON file.', 'Internal error message JSON class', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), __FILE__);
                    break;
                case JSON_ERROR_UTF8:   //ok
                    cacholong_cc_log_error(_x('Malformed UTF-8 characters, possibly incorrectly encoded.', 'Internal error message JSON class', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), __FILE__);
                    break;
                default:    //ok
                    cacholong_cc_log_error(_x("Unknown json error-code ({$jsonError}).", 'Internal error message JSON class', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), __FILE__);
                    break;
            }
        }
        else    //ok
        {
            cacholong_cc_log_error(_x('Unable to check for json_last_error, because function does not exist. Presumed "no json error" and continued flow.', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), __FILE__);
            $hasError = FALSE;
        }
        return $hasError;
    }

}
/* End of file Json.php */