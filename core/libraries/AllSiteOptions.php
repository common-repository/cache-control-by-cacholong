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
 * SiteOptions library
 *
 * Perform a add/update/delete option for a) single site or b) all network sites
 * Perform a update_network for a single network site
 *
 * Uses is_multisite() to decide if WordPress is working in single or multisite mode
 * Will use options table or prefix_x_options ( f.e. wp_2_options, wp_3_options)
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 *
 */
class AllSiteOptions
{
    /*
     * @const   string     Method to add a option
     */

    const ADD_OPTION_METHOD = 'add_option';

    /*
     * @const   string     Method to update a option
     */
    const UPDATE_OPTION_METHOD = 'update_option';

    /*
     * @const   string     Method to delete a option
     */
    const DELETE_OPTION_METHOD = 'delete_option';

    public static function add($option, $value)
    {
        self::x_option(self::ADD_OPTION_METHOD, $option, $value);
    }

    /*
     * Warning: If option is manually deleted in db or not existing, there will be no
     * update.
     */
    public static function update($option, $value)
    {
        self::x_option(self::UPDATE_OPTION_METHOD, $option, $value);
    }

    /*
     * Warning: If option is manually deleted in db or not existing, there will be no
     * update.
     */
    public static function update_network($networkID, $option, $value)
    {
        self::x_option_network($networkID, self::UPDATE_OPTION_METHOD, $option, $value);
    }

    public static function delete($option)
    {
        self::x_option(self::DELETE_OPTION_METHOD, $option);
    }

    /**
     * Create, update, delete a option / all network options
     *
     * The ...$arguments can contain zero, one or n arguments. Will be used
     * like this: method($option, ...$argumemnts)
     *
     * Arguments can contain nothing (delete_option) or most likely a value parameter in other
     * cases. See add_option, update_option, delete_option, get_option for accepted parameters.
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2016-09-08
     * @see         https://codex.wordpress.org/Function_Reference/switch_to_blog
     *
     * @param       string    $method           use one of class constants, like self::ADD_OPTION_METHOD
     * @param       string    $option           Name of option
     * @param       mixed     ...$arguments     Arguments for method after 'option'.
     *
     * @return      void
     */
    private static function x_option($method, $option, ...$arguments)
    {
        if (is_multisite()) {
            $networkIDs = self::get_network_ids();

            foreach ($networkIDs as $networkID) {
                self::x_option_network($networkID, $method, $option, ...$arguments);
            }
        } else {
            $method($option, ...$arguments);
        }
    }

    /**
     * Create, update, delete a option for one network
     */
    private static function x_option_network($networkID, $method, $option, ...$arguments)
    {
        if(is_multisite())
        {
            switch_to_blog($networkID);     //switch to other site to get options in wp_x_table
        }
        $method($option, ...$arguments);

        if(is_multisite())
        {
            restore_current_blog();         //always switch back, as recommended
        }
    }

    /*
     * @see https://developer.wordpress.org/reference/functions/get_sites/
     */
    private static function get_network_ids()
    {
        $networkIDs = get_sites(['fields' => 'ids']);
        return is_array($networkIDs) ? $networkIDs : [];
    }
}

/* End of file SiteOptions.php */