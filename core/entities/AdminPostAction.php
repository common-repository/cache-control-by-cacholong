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

namespace Cacholong\Entities;

defined('ABSPATH') OR exit;

/**
 * Entity AdminPostAction, use as enum
 *
 * @since 2015-10-26
 * @author Piet Rol <info@preliot.nl>
 * @version 1.0
 */
abstract class AdminPostAction {

    const WORDPRESS_ADMIN_POST_ACTION_PREFIX = 'admin_post_';
    const PURGE_ALL = 'purge_all';
    const PURGE_SINGLE = 'purge_single';
    const FACTORY_RESET = 'factory_reset';

    /**
     * Get admin action hook name
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-26
     *
     * @return      string    Action hook
     */
    public static function get_action_hook_name($action) {
        return self::WORDPRESS_ADMIN_POST_ACTION_PREFIX . $action;
    }

}

/* End of file AdminPostAction.php */