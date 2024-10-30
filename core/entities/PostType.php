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
 * Entity PostType, use as enum
 *
 * @since 2016-09-07
 * @author Piet Rol <info@preliot.nl>
 * @version 1.0.0   2016-09-07
 * @version 1.1.0   2018-11-14  Upgrade -> use get_post_types to get builtin and custom post types
 * 
 */
abstract class PostType {
    /*
     * @const     string      POST_TYPE_ATTACHMENT       Wordpress magic constant for attachment
     */

    const POST_TYPE_ATTACHMENT = 'attachment';

    /*
     * @const     string      POST_TYPES_DONT_FLUSH      Array of post types that don't need a flush
     */
    const POST_TYPES_DONT_FLUSH = array(self::POST_TYPE_ATTACHMENT);

    /**
     * Get Wordpress default post types
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * @link        https://codex.wordpress.org/Post_Types
     * 
     * @version     1.0.0     2016-09-07
     * @version     2.0.0     2018-11-14    Use self::get_post_types to get all public builtin post types
     * 
     * @return      array     Array with post types
     */
    static function get_wordpress_default() {

        return self::get_post_types(true, true);
    }

    /**
     * Checks if purge is required (if post type is public wordpress default or custom type). If it isn't required it is a private type like revision, nav_menu_item, etc.
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0     2018-11-14
     * 
     * @return      bool      Purge is required (true) or not (false)
     * 
     */
    static function get_purge_is_required($postID) {
        $allowed_types = self::get_all_public();
        $post_type = get_post_type($postID);

        return in_array($post_type, $allowed_types);
    }

    /**
     * Get Custom post types
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0     2016-09-07
     * @version     2.0.0     2018-11-14    Use self::get_post_types to get all public builtin post types
     * 
     * @return      array     Array with post types
     */
    static function get_custom() {

        return self::get_post_types(true, false);
    }

    /**
     * Get all public post types
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0     2018-11-14    Get all public post types
     * 
     * @return      array     Array with post types
     */
    static function get_all_public() {

        return self::get_post_types(true);
    }

    /**
     * Has Custom post types?
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0     2016-09-07
     * 
     * @return      bool     True (yes) or false (no)
     */
    static function has_custom() {
        return (bool) self::get_custom();
    }

    /**
     * Is give post type a wordpress default type
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0    2016-09-07
     * @version     1.1.0    2018-11-15 Usage of self::get_post_type
     * @return      bool     True (yes) or false (no)
     */
    static function is_wordpress_default_type($postType) {
        $defaultWordpress = self::get_post_types(null, true);       //all wordpress types
        return in_array($postType, $defaultWordpress);
    }

    /**
     * Get post types
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0     2018-11-15    
     *
     * @param       bool        $public                     Optional. Public post type (true) or not (false). Default null, meaning either.
     * @param       bool        $builtIn                    Optional. Builtin post type (true) or not (false). Default null, meaning either.
     * @param       bool        $removeUnwantedPostTypes    Optional. Remove unwanted post types (true) or not (false). Default true.
     * @param        
     * @return      array     Array with post types
     */
    private static function get_post_types($public = null, $builtIn = null, $removeUnwantedPostTypes = true) {

        $args = array();
        
        if(is_bool($public))
        {
            $args['public'] = $public;
        }
        if(is_bool($builtIn))
        {
            $args['_builtin'] = $builtIn;
        }

        $postTypes = get_post_types($args);
        
        if ($removeUnwantedPostTypes) {
            $postTypes = self::remove_unwanted_post_types($postTypes);
        }

        return $postTypes;
    }

    /**
     * Remove unwanted post types from postTypes
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0     2018-11-15
     * 
     * @return      array     Array with wanted post types
     */
    private static function remove_unwanted_post_types($postTypes) {

        $wantedPostTypes = array_diff($postTypes, self::POST_TYPES_DONT_FLUSH);
        return array_values($wantedPostTypes);
    }

}

/* End of file PostType.php */