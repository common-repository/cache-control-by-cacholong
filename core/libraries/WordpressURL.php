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
 * WordpressURL
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 *
 * @version     1.0.0   2016-09-08
 */
class WordpressURL {

    /**
     * Get relative home url for current site
     * Note: get_home_url() returns full url, we only need part after it.
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2016-09-08
     *
     * @return      string    Home url
     */
    public static function get_home()
    {
        return '/';
    }

    /**
     * Get all relative wordpress category urls for given $postId
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2016-09-08
     *
     * @var         int       $postID       ID of post
     *
     * @return      array     URLs for all wordpress categories
     */
    public static function get_wordpress_categories_all($postID)
    {
        $finalCategories = array();

        $homeUrl = cacholong_cc_get_home_url();
        $wordpressCategories = get_the_category($postID);

        foreach ($wordpressCategories as $category)
        {
            $categoryLink = get_category_link($category->cat_ID);

            if(strpos($categoryLink, $homeUrl) === 0)
            {
                $categoryLink = substr($categoryLink, strlen($homeUrl));
            }

            $finalCategories[] = $categoryLink;
        }

        return $finalCategories;
    }

    /**
     * Get all category urls for woocommerce
     *
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        build when needed
     *
     * @version     1.0.0     2016-09-08
     *
     * @return      array     URLs for all Woocommerce categories
     */
    public static function get_woocommerce_categories($categoryId = 0)
    {
        $finalCategories = array();

        if(cacholong_cc_has_woocommerce())
        {
            //Example code to get categories
//            $args = array(
//                'hierarchical' => 1,
//                'hide_empty' => 0,
//                'parent' => $categoryId,
//                'taxonomy' => 'product_cat'
//            );
//
//            $categoryList = get_categories($args);
//
//            if(is_array($categoryList) && $categoryList)
//            {
//                foreach ($categoryList as $category)
//                {
//                    $categoryUrl = get_term_link($category->slug, 'product_cat');
//                }
//            }
//            //else empty category list
        }

        return $finalCategories;
    }
}
/* End of file WordpressURL.php */