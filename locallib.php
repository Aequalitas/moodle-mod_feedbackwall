<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library of functions used by the quiz module.
 *
 * This contains functions that are called from within the courseboard module only
 * Functions that are also called by core Moodle are in {@link lib.php}
 *
 * @package    mod_courseboard
 * @copyright  12/2015 Franz Weidmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Send a notification message to all enrolled users who are interested
 * about a new post/comment/rate.
 *
 * @param String type Tells the function which type of even
 * the notification is about. 'post' | 'comment' | 'rate'
 *
 * @param object $a Information to create the message
 *
 *
 */
function courseboard_send_eventNotifications($type, $a, $context) {
	global $DB;

    if($type == 'post' || $type == 'comment' || $type == 'rate'){

        $newEventMessage = new stdClass();
        $newEventMessage->component         = 'mod_courseboard';
        $newEventMessage->name              = 'new'.$type;
        $newEventMessage->notification      = 1;
        $newEventMessage->userfrom          = core_user::get_noreply_user();
        $newEventMessage->subject           = get_string('new'.$type.'subject', 'courseboard', $a);
        $newEventMessage->fullmessage       = get_string('new'.$type.'message', 'courseboard', $a);
        $newEventMessage->fullmessageformat = FORMAT_PLAIN;
        $newEventMessage->fullmessagehtml   = '';
        $newEventMessage->smallmessage      = get_string('new'.$type.'small', 'courseboard', $a);
        $newEventMessage->contexturl        = $a->courseboardurl;
        $newEventMessage->contexturlname    = $a->courseboardname;

        foreach(get_enrolled_users($context) as $enrolledUser){
    	   	$newEventMessage->userto = $enrolledUser;

    	 	if (has_capability('mod/courseboard:notifynew'.$type, $context, $enrolledUser))
    	    	message_send($newEventMessage);
        }
    }
}