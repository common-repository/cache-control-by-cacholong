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
 * HttpApiRequest library
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 *
 */
class HttpApiRequest {

    /**
     * Execute request with $options to given $url and return http status code
     * When \HttpApiRequest::HTTP_METHOD is not found in $options, wp_remote_request assumes GET.
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * @link        https://codex.wordpress.org/HTTP_API#Other_Arguments        Valid $options for wp_remote_request
     *
     * @version     1.0.0     2018-02-28
     *
     * @param       string    $url              Execute request url
     * @param       array     $options          Optional. Options for request. Default array().
     *
     * @return      int|null                    HTTP status code or null
     */
    public static function execute($url, $options = array()) {
        $result = null;

        try
        {
            $response = wp_remote_request($url, $options);

            if(defined('CACHOLONG_CACHE_DEBUG_HTTP_API_REQUEST') && CACHOLONG_CACHE_DEBUG_HTTP_API_REQUEST)
            {
                cacholong_cc_log_wp_remote_response($url, $options, $response);
            }

            if (!is_wp_error($response)) //ok
            {
                $httpStatusCode = wp_remote_retrieve_response_code($response);
                $result = is_int($httpStatusCode) ? $httpStatusCode : null;
            }
            else    //ok
            {
                self::handle_http_api_error($response);
            }
        }
        catch (\Exception $exception) //ok
        {
            cacholong_cc_log_exception($exception);
        }
        finally //ok
        {
            return $result;
        }
    }

    /**
     * Handle HTTP API error
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2018-02-26
     *
     * @param       WP_Error  $response   Respons from wp_remote_* request
     *
     * @return      void
     */
    private static function handle_http_api_error(\WP_Error $response)
    {
        cacholong_cc_log_error("HTT API got following error(s):");
        cacholong_cc_log_error($response->get_error_messages());
    }

}

/* End of file HttpApiRequest.php */

//HttpApiRequest::execute('invalid..url...nl');
//HttpApiRequest::execute('http://preliot.nl');