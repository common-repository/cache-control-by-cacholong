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


/**
 * Auto execute when jquery is ready
 * 
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 * 
 * @version     1.0.0     2015-11-25
 * @version     1.0.1     2016-09-07 enforceAdminSettingsSectionUniqueCheckboxes
 * @return      void
 */
jQuery(document).ready(function ()
{
    getAdminMessages();
    enforceAdminSettingsSectionUniqueCheckboxes();
});

/**
 * Get admin messages (if any) and display them in the backend
 * 
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 * 
 * @version     1.0.0     2015-11-25
 * @version     1.1.0     2018-02-23 cc_plugin_data -> cacholong_cc_plugin_data
 * 
 * @return      void
 */
function getAdminMessages()
{
    jQuery.ajax({
        type: "post",
        dataType: "json",
        url: cacholong_cc_plugin_data.ajaxUrl,
        data: {action: cacholong_cc_plugin_data.ajaxActionGetMessages},
        success: function (response) {
            if (typeof response.type != 'undefined' && response.type == cacholong_cc_plugin_data.ajaxResponseTypeSuccess)
            {
                jQuery(response.targetAfter).after(response.value);
                ajaxMessageIsDismissible();
            }
            // else default response (0) or error is given
        }
    });
}

/**
 * Make ajax messages dismissible. Function is from Wordpress core function, but already excuted when ajax messages are called.
 * Will check if button.notice-dismiss is not already added.
 * 
 * Original code found in wp-admin/js/common.js > part of adjustSubmenu
 * 
 * @author      Wordpress | Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Wordpress | Cacholong <info@cacholong.nl>
 * 
 * @todo        ok
 * 
 * @version     1.0.0     Original
 * @version     1.0.1     2015-11-26 not selector 
 * 
 * @return      void
 */
function ajaxMessageIsDismissible()
{
    // Make notice dismissible, but only if there is not already a button.notice-dismiss inside (safety first!)
    jQuery('.notice.is-dismissible:not(:has(>button.notice-dismiss))').each(function ()
    {
        var $this = jQuery(this),
                $button = jQuery('<button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button>'),
                btnText = commonL10n.dismiss || '';

        // Ensure plain text
        $button.find('.screen-reader-text').text(btnText);

        $this.append($button);

        $button.on('click.wp-dismiss-notice', function (event) {
            event.preventDefault();
            $this.fadeTo(100, 0, function () {
                jQuery(this).slideUp(100, function () {
                    jQuery(this).remove();
                });
            });
        });
    });
}

/**
 * Enforce unique checkbox checks in admin settings section
 * Some checkboxes do not tolerate other checkboxes.
 * 
 * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
 * @copyright   Cacholong <info@cacholong.nl>
 * @todo        ok
 * 
 * @version     1.0.0     2015-11-25
 * @version     1.1.0     2018-02-23 cc_plugin_data -> cacholong_cc_plugin_data
 * 
 * @return      void
 */
function enforceAdminSettingsSectionUniqueCheckboxes()
{
    var settingsSectionFieldSetWithUniqueCheckboxes = cacholong_cc_plugin_data.settingsSectionFieldSetWithUniqueCheckboxes;

    if (settingsSectionFieldSetWithUniqueCheckboxes)
    {
        //each fieldset
        jQuery.each(settingsSectionFieldSetWithUniqueCheckboxes, function (fieldSetId, uniqueCheckboxes) 
        {
            if (Array.isArray(uniqueCheckboxes))
            {
                //each checkbox
                jQuery.each(uniqueCheckboxes, function (index, checkBoxId)
                {
                    //unique checkbox > disable all other
                    jQuery('#' + fieldSetId + ' input#' + checkBoxId).on('change', function ()
                    {
                        if (this.checked)
                        {
                            jQuery('#' + fieldSetId + ' input:not("#' + checkBoxId + '")').prop('checked', false);
                        }
                    });
                    
                    //all other > disable unique
                    jQuery('#' + fieldSetId + ' input:not("#' + checkBoxId + '")').on('change', function ()
                    {
                        if (this.checked)
                        {
                            jQuery('#' + fieldSetId + ' input#' + checkBoxId).prop('checked', false);
                        }
                    });
                });
            }
            //else do nop
        });
    }
    //else do nop
}