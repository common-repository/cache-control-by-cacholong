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

namespace Cacholong\Libraries\PurgeSetting;

defined('ABSPATH') OR exit;

require_once(CACHOLONG_CACHE_DIR . '/core/libraries/purgeSetting/PurgeSetting.php');

/**
 * Default Post purge setting class
 * 
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * 
 */
class DefaultPostPurgeSetting extends PurgeSetting {

    /**
     * Default setting
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0     2016-09-09
     * 
     * @return      bool        
     */
    public function getDefault()
    {
        return (bool) get_option(\Cacholong\Entities\Identifier::CC_DEFAULT_POST_PURGE_SETTINGS_DEFAULT);
    }

    /**
     * Home setting
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0     2016-09-09
     * 
     * @return      bool        
     */
    public function getHome()
    {
        return (bool) get_option(\Cacholong\Entities\Identifier::CC_DEFAULT_POST_PURGE_SETTINGS_HOME);
    }

    /**
     * Category Wordpress setting
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0     2016-09-09
     * 
     * @return      bool        
     */
    public function getCategoriesWordpress()
    {
        return (bool) get_option(\Cacholong\Entities\Identifier::CC_DEFAULT_POST_PURGE_SETTINGS_CATEGORIES_WORDPRESS);
    }

    /**
     * Category Woocommerce setting
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0     2016-09-09
     * 
     * @return      bool        
     */
    public function getCategoriesWoocommerce()
    {
        return (bool) get_option(\Cacholong\Entities\Identifier::CC_DEFAULT_POST_PURGE_SETTINGS_CATEGORIES_WOOCOMMERCE);
    }

    /**
     * Flush all setting
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0     2016-09-09
     * 
     * @return      bool        
     */
    public function getFlushAll()
    {
        return (bool) get_option(\Cacholong\Entities\Identifier::CC_DEFAULT_POST_PURGE_SETTINGS_FLUSH_ALL);
    }
}