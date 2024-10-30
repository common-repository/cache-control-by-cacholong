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
 * Library for AdminMessage
 * Note: Because Wordpress relies on redirecting, messages must be send via the url ór flashData. Because the messages vary,
 * flashData solution was chosen.
 * 
 * @since 2015-10-29
 * @author Piet Rol <info@preliot.nl>
 * @version 1.0
 */
class AdminMessage {
    /*
     * @const     string      AJAX_MESSAGES_TARGET_AFTER       Jquery selector where messages are put after
     */

    const AJAX_MESSAGES_TARGET_AFTER = 'div.wrap > h1';

    /*
     * @const     string      FLASHDATA_KEY_ADMIN_MESSAGE       Admin message key for FlashData
     */
    const FLASHDATA_KEY_ADMIN_MESSAGE = 'admin_messages';

    /*
     * @const     string      FLASHDATA_KEY_ADMIN_CLASS       Admin message class
     */
    const FLASHDATA_KEY_ADMIN_CLASS = 'class';

    /*
     * @const     string      FLASHDATA_KEY_ADMIN_CONTENT      Admin message content
     */
    const FLASHDATA_KEY_ADMIN_CONTENT = 'content';

    /**
     * Set message for admin
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0       2015-10-28
     * @version     1.0.1       2016-03-28          Remove identical messages from session
     * @version     1.1.0       2016-09-09          Bugfix: Only remove identical messages for current message, there could be other valid duplicate messages.
     * @version     1.2.0       2017-12-15          Session > FlashData
     * 
     * @var         string      $message            Message for admin.
     * @var         string      $class              Optional. Admin notice class. Default \Cacholong\Entities\AdminNoticeClass::SUCCESS.
     * @var         bool        $removeIdentical    Optional. Remove identical messages from FlashData. Default false.
     * @return      void
     */
    public static function set_message($message, $class = \Cacholong\Entities\AdminNoticeClass::SUCCESS, $removeIdentical = false)
    {
        $messagesForFlashData = array();
        $saveMessage = true;

        //get previous messages
        $previousMessages = \Cacholong\Libraries\FlashData::get_flash(self::FLASHDATA_KEY_ADMIN_MESSAGE);
        if(!empty($previousMessages) && $previousMessages[self::FLASHDATA_KEY_ADMIN_MESSAGE])
        {
            $messagesForFlashData = $previousMessages[self::FLASHDATA_KEY_ADMIN_MESSAGE];
        }

        //detect if message is unique
        if($removeIdentical && $messagesForFlashData)
        {
            foreach ($messagesForFlashData as $key => $singleMessage)
            {
                if($class == $singleMessage[self::FLASHDATA_KEY_ADMIN_CLASS] && $message == $singleMessage[self::FLASHDATA_KEY_ADMIN_CONTENT])
                {
                    $saveMessage = false;
                    break;
                }
                //not identical
            }
        }

        //save new message
        if($saveMessage)
        {
            $messagesForFlashData[] = array(self::FLASHDATA_KEY_ADMIN_CLASS => $class, self::FLASHDATA_KEY_ADMIN_CONTENT => $message);
        }

        \Cacholong\Libraries\FlashData::set_flash(array(self::FLASHDATA_KEY_ADMIN_MESSAGE => $messagesForFlashData));
    }

    /**
     * Output admin messages (based on FlashData) directly on screen
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0     2015-10-28
     * 
     * @return      void
     */
    public static function output()
    {
        $flashData = \Cacholong\Libraries\FlashData::get_flash(self::FLASHDATA_KEY_ADMIN_MESSAGE);
        $adminMessages = isset($flashData[self::FLASHDATA_KEY_ADMIN_MESSAGE]) ? $flashData[self::FLASHDATA_KEY_ADMIN_MESSAGE] : array();

        foreach ($adminMessages as $message)
        {
            require(CACHOLONG_VIEW_DIR . '/admin/misc/admin_message.phtml');
        }
    }

    /**
     * Get message(s) as html
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0     2015-11-26
     * @version     1.0.1     2015-11-26 Merge same classes option
     * @version     1.1.0     2016-09-09 New notice classes
     * @version     1.1.1     2017-12-21 Array check for adminMessages
     * 
     * @param       array       $adminMessages      Array of arrays. Each array contains 'class' and 'content'.
     * @param       bool        $mergeSameClasses   Optional. Merge messages with same error class. Default false.
     * 
     * @return      string|null    Message as HTML or null if messages did not contain class and/or content
     */
    private static function get_messages_as_html($adminMessages, $mergeSameClasses = false)
    {
        $html = null;

        if(is_array($adminMessages))
        {
            if($mergeSameClasses)
            {
                $mergedMessages = array();

                //1: merge 
                foreach ($adminMessages as $message)
                {
                    if(isset($message['class']) && isset($message['content']))
                    {
                        $mergedMessages[$message['class']][] = $message['content'];
                    }
                }

                //2: output
                foreach ($mergedMessages as $class => $messagesSameClass)
                {
                    $html .= '<div class="notice ' . $class . ' is-dismissible">';
                    foreach ($messagesSameClass as $message)
                    {
                        $html .= '<p>' . $message . '</p>';
                    }
                    $html .= '</div>';
                }
            }
            else
            {
                //no merging, show seperatly
                foreach ($adminMessages as $message)
                {
                    if(isset($message['class']) && isset($message['content']))
                    {
                        $html .= '<div class="notice ' . $message['class'] . ' is-dismissible">';
                        $html .= '<p>' . $message['content'] . '</p>';
                        $html .= '</div>';
                    }
                }
            }
        }
        //else do nop
        
        return $html;
    }

    /**
     * Handle ajax messages
     * 
     * @author      Preliot, Piet Rol <info@preliot.nl> (commissioned by Cacholong)
     * @copyright   Cacholong <info@cacholong.nl>
     * @todo        ok
     * 
     * @version     1.0.0     2015-11-26
     * 
     * @return      void
     */
    public static function handle_ajax_messages()
    {
        $flashData = \Cacholong\Libraries\FlashData::get_flash(self::FLASHDATA_KEY_ADMIN_MESSAGE);
        $adminMessages = isset($flashData[self::FLASHDATA_KEY_ADMIN_MESSAGE]) ? $flashData[self::FLASHDATA_KEY_ADMIN_MESSAGE] : array();

        $result['value'] = self::get_messages_as_html($adminMessages, true);
        $result['type'] = !empty($result['value']) ? \Cacholong\Entities\AjaxResponseType::SUCCESS : \Cacholong\Entities\AjaxResponseType::ERROR;
        $result['targetAfter'] = self::AJAX_MESSAGES_TARGET_AFTER;

        $JSON = \Cacholong\Libraries\Json::encode($result);

        if(!empty($JSON))
        {
            echo $JSON;
        }
        wp_die();
    }

}
/* End of file AdminMessage.php */