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
 * Style class
 * 
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * 
 * @version     1.0.0     2017-12-15
 */
class Style {
    
    /*
     * @var string  Regex for targeting id part of style tag
     */
    const STYLE_TAG_ID_REGEX = "/\s+id='.*-css'\s+/";

    /**
     * Remove id attribute from link tag
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0     2017-12-15
     * 
     * @param       string    $link     Full style link
     * 
     * @return      string    CSS link tag without ID or original link tag (when something went wrong)
     */
    public static function remove_id($link)
    {
        $css_no_id = preg_replace(self::STYLE_TAG_ID_REGEX, ' ', $link);
        return ($css_no_id) ? $css_no_id : $link;
    }

}
/* End of file Style.php */