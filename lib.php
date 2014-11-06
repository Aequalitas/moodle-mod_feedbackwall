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
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $courseboard An object from the form in mod_form.php
 * @return int The id of the newly inserted courseboard record
 */

// ...$Id: lib.php,v 1.7.2.5 2009/04/22 21:30:57 skodak Exp $.
defined('MOODLE_INTERNAL') || die();

function courseboard_add_instance($courseboard) {

    global $DB;

    $courseboard->timecreated = time();

    // You may have to add extra stuff in here.
    $returnid = $DB->insert_record('courseboard', $courseboard);
    return $returnid;
}


/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $courseboard An object from the form in mod_form.php.
 * @return boolean Success/Fail.
 */
function courseboard_update_instance($courseboard) {

    global $DB;

    $courseboard->timemodified = time();
    $courseboard->id = $courseboard->instance;

    // You may have to add extra stuff in here.

    $DB->update_record('courseboard', $courseboard);
    return true;

}


/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function courseboard_delete_instance($id) {

    global $DB;

    if (! $courseboard = $DB->get_record('courseboard', array('id' => $id), $id)) {
        return false;
    }

    $result = true;

    // Delete any dependent records here.

    if (! $DB->delete_records('courseboard', array('id' => $id), $courseboard->id)) {
        $result = false;
    }

    return $result;
}


/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * 
 * This function prints the latest post, comment and ratings of an instance.
 */
function courseboard_user_outline($course, $user, $mod, $courseboard) {
    global $DB;
    $post = 0;
    $result = new stdClass();

    if ($post = $DB->get_record('courseboard_posts', array('course' => $course->id, 'coursemoduleid' => $mod->id, 'userid' => $user->id))) {
        $result->post->info = format_string($post->post).'\n Postid: '.$post->id;
        $result->post->time = $post->timemodified - 10000000000000;
        echo $result->info;

    } else {
        print_string('notposted', 'courseboard');
    }

    if ($comment = $DB->get_record('courseboard_comments', array('course' => $course->id, 'coursemoduleid' => $mod->id, 'userid' => $user->id))) {
            $result->comment->info = format_string($comment->comment).'\n Postid: '.$comment->postid.'Commentid: '.$comment->id;
            $result->comment->time = $comment->timemodified - 10000000000000;
            echo $result->info;

    } else {
        print_string('notcommented', 'courseboard');
    }

    if ($rate = $DB->get_record('courseboard_ratings', array('course' => $course->id, 'coursemoduleid' => $mod->id, 'userid' => $user->id))) {
            $result->rating->info = 'Rating: '.$rate->didrate.' Postid: '.$rate->postid.'Rateid: '.$rate->id;
            $result->rating->time = $rate->timemodified;
            echo $result->info;

    } else {
        print_string('notrated', 'courseboard');
    }

return $result;

}


/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * 
 * This function prints all posts, comments and ratings.
 */
function courseboard_user_complete($course, $user, $mod, $courseboard) {

    global $DB;

    if ($posts = $DB->get_records('courseboard_posts', array('userid' => $user->id))) {
        foreach ($posts as $post) {
            echo format_string($post->post).'\n Postid: '.$post->id.' Time: '.$post->timemodified - 10000000000000;

        }
    } else {
        print_string('notposted', 'courseboard');
    }

    if ($comments = $DB->get_records('courseboard_comments', array('userid' => $user->id))) {
        foreach ($comments as $comment) {
            echo format_string($comment->comment).'\n Postid: '.$comment->postid.' Commentid: '.$comment->commentid.' Time: '.$comment->timemodified - 10000000000000;
        }
    } else {
        print_string('notcommented', 'courseboard');
    }

    if ($ratings = $DB->get_records('courseboard_comments', array('userid' => $user->id))) {
        foreach ($ratings as $rating) {
            echo '\n Rating: '.$rating->didrate.' Postid: '.$rating->postid.' rateid: '.$rating->id.'Time: '.$rating->timemodified;
        }
    } else {
        print_string('notrated', 'courseboard');
    }
   
return true;
}


/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in courseboard activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function courseboard_print_recent_activity($course, $isteacher, $timestart) {
    return false;  // True if anything was printed, otherwise false.
}


/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function courseboard_cron () {
    return true;
}


/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of courseboard. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $courseboardid ID of an instance of this module
 * @return mixed boolean/array of students
 */
function courseboard_get_participants($courseboardid) {
    return false;
}

/**
 * Checks if scale is being used by any instance of courseboard.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any courseboard
 */
function courseboard_scale_used_anywhere($scaleid) {
    if ($scaleid and record_exists('courseboard', 'grade', -$scaleid)) {
        return true;
    } else {
        return false;
    }
}


/**
 * Execute post-install custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function courseboard_install() {
    return true;
}


/**
 * Execute post-uninstall custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function courseboard_uninstall() {
    return true;
}



// Any other courseboard functions go here.  Each of them must have a name that
// starts with courseboard_
// Remember (see note in first lines) that, if this section grows, it's HIGHLY
// recommended to move all funcions below to a new 'localib.php' file.
