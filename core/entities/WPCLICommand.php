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

/**
 * Entity WPCLICommand, use as enum
 *
 * @since 2020-10-16
 * @author Piet Rol <info@preliot.nl>
 * @version 1.0.0
 */
abstract class WPCLICommand {

    const CC_COMMAND_OUTPUT_EXIT_CODE = 'cacholong_cc';                         //output exit code (default)
}
/* End of file WPCLICommand.php */