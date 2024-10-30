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
 * File
 * 
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * 
 * @version     1.0.1     2015-11-02
 */
class File {

    /**
     * Fingerprint any asset like javascript, css (etc) with file last modified. 
     * If this fails somehow, it will replace the fingerprint with a random number.
     * 
     * Note: With this you can set aggresive caching on these assets. 
     * Note: Based on idea of Kevin Hale, see link
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * @link        http://www.particletree.com/notebook/automatically-version-your-css-and-javascript-files/
     * 
     * @version     1.0.0     2018-02-28
     * 
     * @return      string    Fingerprint / version number   
     */
    public static function fingerprint($assetPath) {
        $version = null;

        if (file_exists($assetPath)) { //ok
            $version = filemtime($assetPath);
        } else {    //ok
            cacholong_cc_log_error("Unable to fingerprint asset ({$assetPath}), because the file does not exist.");
        }

        return $version;
    }

}

/* End of file File.php */