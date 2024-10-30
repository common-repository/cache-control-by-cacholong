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

/**
 * Purge setting class abstract class
 * 
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * 
 */
abstract class PurgeSetting {
   
    /**
     * Required method for default setting
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0     2016-09-09
     * 
     * @return      bool      purge (true) or do not purge (false)  
     */
    abstract public function getDefault();

    /**
     * Required method for home setting
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0     2016-09-09
     * 
     * @return      bool      purge (true) or do not purge (false)  
     */
    abstract public function getHome();
    
     /**
     * Required method for categories wordpress
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0     2016-09-09
     * 
     * @return      bool      purge (true) or do not purge (false)  
     */
    abstract public function getCategoriesWordpress();
    
     /**
     * Required method for categories Woocommerce
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0     2016-09-09
     * 
     * @return      bool      purge (true) or do not purge (false)  
     */
    abstract public function getCategoriesWoocommerce();
    
      /**
     * Required method for flush all
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0     2016-09-09
     * 
     * @return      bool      purge (true) or do not purge (false)  
     */
    abstract public function getFlushAll();
}