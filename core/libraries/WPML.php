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
 * WPML
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 *
 * @version     1.0.0   2016-09-08
 */
class WPML
{
    /**
     * Is WPML active?
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2021-03-04
     *
     * @return      bool
     */
    public static function active()
    {
        return defined('ICL_SITEPRESS_VERSION') && !ICL_PLUGIN_INACTIVE && class_exists('SitePress', false);
    }

    /**
     * Is language negotiation type of type 'domain' (Meaning is WPML using domains for language switching?)
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2021-03-04
     *
     * @return      bool
     */
    public static function is_language_negotiation_type_domain()
    {
        if(defined('WPML_LANGUAGE_NEGOTIATION_TYPE_DOMAIN') && function_exists('wpml_get_setting_filter'))
        {
            return WPML_LANGUAGE_NEGOTIATION_TYPE_DOMAIN === (int) wpml_get_setting_filter(false, 'language_negotiation_type');
        }
        else
        {
            cacholong_cc_log_error(__('Expected constant WPML_LANGUAGE_NEGOTIATION_TYPE_DOMAIN or global function wpml_get_setting_filter(), but one or both are missing. Language negotiation type assumed to be not of type domain. User could notice this when type was domain.', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), __FILE__);
            return false;
        }
    }

     /**
     * Get home URL (also for domains)
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2021-03-04
     *
     * @return      string
     */
    public static function getHomeUrl()
    {
        if(function_exists('icl_get_home_url'))
        {
            return icl_get_home_url();
        }
        else
        {
            cacholong_cc_log_error(__('Expected global function icl_get_home_url() was missing. Used default get_home_url(). User could notice this when type was domain.', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN), __FILE__);
            return get_home_url();
        }
    }

}

/* End of file WPML.php */