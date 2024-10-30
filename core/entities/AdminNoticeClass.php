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

defined( 'ABSPATH' ) OR exit;

/**
 * Entity AdminNoticeClass, use as enum
 *
 * @since 2015-10-28
 * @author Piet Rol <info@preliot.nl>
 * @version 1.0
 * @version 1.1 2016-09-09 New notice classes
 * @link https://digwp.com/2016/05/wordpress-admin-notices/
 */
abstract class AdminNoticeClass {

    //const INFO = 'notice-info';   //not used yet
    const SUCCESS = 'notice-success';
    const WARNING = 'notice-warning';
    const ERROR = 'notice-error';
}
/* End of file AdminNoticeClass.php */