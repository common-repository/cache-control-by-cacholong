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

namespace Cacholong\Controller\Admin;

use Cacholong\Entities\Identifier;

defined('ABSPATH') OR exit;

/**
 * Controller class for toolbar on admin page
 *
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @link        https://codex.wordpress.org/Toolbar
 *
 * @version     1.0.0     Created at 2015-10-31
 */
class ToolbarController
{

    /**
     * Create node 'purge all' for admin toolbar

     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2015-10-31
     * @version     1.0.1     2016-02-16 cacholong_cc_get_settings_page_url($queryAction) removed, added current page + query vars + wp_nonce
     * @version     1.1.0     2019-02-21 ab-icon class added for icon
     * @version     2.0.0     2020-10-16 single link -> multiple links in dropdown menu
     *
     * @param       \WP_Admin_Bar   $wpAdminBar     Instance of wp_admin_bar
     * @return      void
     */
    public static function create_toolbar_node_purge(\WP_Admin_Bar $wpAdminBar)
    {
        $hrefPurgeAll = static::getHrefForAction(Identifier::CC_REQUEST_ACTION_PURGE_ALL_ADMIN_BAR);
        $iconAdminBar = '<span class="ab-icon"></span>';
        $wpAdminBar->add_node([
            'id' => \Cacholong\Entities\Identifier::CC_TOOLBAR_NODE_PURGE,
            'title' => $iconAdminBar . __('Purge caches', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN),
            'href' => $hrefPurgeAll,
        ]);

        $wpAdminBar->add_node([
            'id' => \Cacholong\Entities\Identifier::CC_TOOLBAR_NODE_PURGE_ALL,
            'title' => __('Purge all', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN),
            'href' => $hrefPurgeAll,
            'parent' => \Cacholong\Entities\Identifier::CC_TOOLBAR_NODE_PURGE
        ]);

        $wpAdminBar->add_node([
            'id' => \Cacholong\Entities\Identifier::CC_TOOLBAR_NODE_PURGE_PAGESPEED,
            'title' => __('Purge pagespeed', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN),
            'href' => static::getHrefForAction(Identifier::CC_REQUEST_ACTION_PURGE_ALL_NGINX_PAGESPEED),
            'parent' => \Cacholong\Entities\Identifier::CC_TOOLBAR_NODE_PURGE
        ]);

        $wpAdminBar->add_node([
            'id' => \Cacholong\Entities\Identifier::CC_TOOLBAR_NODE_PURGE_FASTCGI,
            'title' => __('Purge fastCGI', \Cacholong\Entities\Identifier::CC_TEXT_DOMAIN),
            'href' => static::getHrefForAction(Identifier::CC_REQUEST_ACTION_PURGE_ALL_NGINX_FASTCGI),
            'parent' => \Cacholong\Entities\Identifier::CC_TOOLBAR_NODE_PURGE
        ]);
    }

    /**
     * Get href fo given action

     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     *
     * @version     1.0.0     2020-10-16
     *
     * @param       string    $action   Action (use Identifier enum)
     *
     * @return      string    relative url with query argument Identifier::CC_REQUEST_ACTION_KEY => $action
     */
    private static function getHrefForAction($action)
    {
        $href_without_nonce = add_query_arg([Identifier::CC_REQUEST_ACTION_KEY => $action]);
        return wp_nonce_url($href_without_nonce);
    }
}
