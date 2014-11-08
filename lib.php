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
 * @author  Franz Weidmann
 * @package mod_courseboard
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
 * 
 * This function returns the latest userdata => post, comment and rating of an instance.
 * 
 * @return sdtClass $result->info and $result->time
 */
function courseboard_user_outline($course, $user, $mod, $courseboard) {
    global $DB;
    $post = 0;
    $result = new stdClass();
    $result->time = "Time: ";

    $result->info = '<h4>Latest post </h4>';
    if ($posts = $DB->get_records('courseboard_posts', array('courseid' => $course->id, 'coursemoduleid' => $mod->id, 'userid' => $user->id), 'id DESC', 'id, post, timemodified', 0, 1)) {
        foreach ($posts as $post) { // Foreach loop has only one round to go.
            $result->info .= format_string($post->post).'<p> Postid: '.$post->id.'</p>';
            $result->time .= $post->timemodified - 1000000000000 . '(Post), '; // Substraction because all the time entries in the database beginn with 1.
        }
    } else {
        $result->info = '<p>'.get_string('notposted', 'courseboard').'</p>';
    }

    $result->info .= '<h4>Latest comment </h4>';
    if ($comments = $DB->get_records('courseboard_comments', array('courseid' => $course->id, 'coursemoduleid' => $mod->id, 'userid' => $user->id), 'id DESC', '*', 0, 1)) {
        foreach ($comments as $comment) {    
            $result->info .= format_string($comment->comment).'<p> Postid: '.$comment->postid.' Commentid: '.$comment->id.'</p>';
            $result->time .= $comment->timemodified - 1000000000000 . '(Comment), ';
        }
    } else {
        $result->info .= '<p>'.get_string('notcommented', 'courseboard').'</p>';
    }

    $result->info .= '<h4> Latest rating </h4>';
    if ($ratings = $DB->get_records('courseboard_ratings', array('courseid' => $course->id, 'coursemoduleid' => $mod->id, 'userid' => $user->id), 'id DESC', '*', 0, 1)) {
        foreach ($ratings as $rate) {    
            $result->info .= 'Rating: '.$rate->didrate.'<p> Postid: '.$rate->postid.' Rateid: '.$rate->id.'</p>';
            $result->time .= $rate->timemodified . '(Rating)';
        }
    } else {
        $result->info .= '<p>'.get_string('notrated', 'courseboard').'</p>';
    }

    return $result;

}


/**
 *
 * @return boolean
 * 
 * This function prints all userdate => posts, comments and ratings of an instance.
 */
function courseboard_user_complete($course, $user, $mod, $courseboard) {

    global $DB;

    echo '<h4>Latest posts </h4>';
    if ($posts = $DB->get_records('courseboard_posts', array('userid' => $user->id))) {
        foreach ($posts as $post) {
            echo format_string($post->post).'<p> Postid: '.$post->id.' Time: '.($post->timemodified - 1000000000000) . '</p>';

        }
    } else {
        echo '<p>'.get_string('notposted', 'courseboard').'</p>';
    }

   echo '<h4>Latest comment </h4>';
    if ($comments = $DB->get_records('courseboard_comments', array('userid' => $user->id))) {
        foreach ($comments as $comment) {
            echo format_string($comment->comment).'<p> Postid: '.$comment->postid.' Commentid: '.$comment->id.' Time: '.($comment->timemodified - 1000000000000) . '</p>';
        }
    } else {
        echo '<p>'.get_string('notcommented', 'courseboard').'</p>';
    }

    echo '<h4> Latest rating </h4>';
    if ($ratings = $DB->get_records('courseboard_ratings', array('userid' => $user->id))) {
        foreach ($ratings as $rating) {
            echo ' Rating: '.$rating->didrate.'<p> Postid: '.$rating->postid.' rateid: '.$rating->id.'Time: '.$rating->timemodified . '</p>';
        }
    } else {
        echo '<p>'.get_string('notrated', 'courseboard').'</p>';
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

/**
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function courseboard_supports($feature) {
    switch($feature) {

        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;

        default:
            return null;
    }
}

