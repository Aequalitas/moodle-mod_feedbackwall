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
 * 
 *
 * @author  Franz Weidmann 
 * @version 10/2014
 * @package mod_courseboard
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');


header('Content-Type: text/html; charset=utf-8'); // otherwise the response would be send in JSON-Type

if (!isloggedin()) {
    echo "ERROR: " . get_string("offlineerror", "courseboard");
    die(); 
}

if (!confirm_sesskey()) {
    echo 'ERROR(sesskey)';
    die();
}

$courseid = required_param('k', PARAM_INT);  // Those three params are used in every action.
$coursemoduleid = required_param('r', PARAM_INT);
$courseboardid = required_param('b', PARAM_INT);

if (!$courseboard = $DB->get_record('courseboard', array('id' => $courseboardid), '*', MUST_EXIST)) {
    echo 'ERROR(courseid)';
    die();
}
if (!$course = $DB->get_record('course', array('id' => $courseboard->course), '*', MUST_EXIST)) {
    echo 'ERROR(course)';
    die();
}
if (!$cm = get_coursemodule_from_instance('courseboard', $courseboard->id, $course->id)) {
    echo 'ERROR(coursemodule)';
    die();
}

require_course_login($course, false, $cm);

$context = context_module::instance($cm->id);


// AJAX-Querys, 'fnc' tells which kind of query it was.
if ($fnc = required_param('fnc', PARAM_ALPHA)) {
    switch($fnc) {

        case 'postInsert':

            require_capability('mod/courseboard:write', $context);

            $post = required_param('q', PARAM_TEXT);
            $name = required_param('s', PARAM_ALPHAEXT);

            $timecreated = time();

            $entry = new stdClass();
            $entry->courseid = $courseid;
            $entry->coursemoduleid = $coursemoduleid;
            $entry->post = $post;
            $entry->name = $name;
            $entry->userid = $USER->id;
            $entry->timecreated = $timecreated;
            $entry->timemodified = $timecreated;

            $postid = $DB->insert_record('courseboard_posts', $entry, true);


            break;

        case 'courseboardRefresh':

            require_capability('mod/courseboard:view', $context);

            $sort = required_param('q', PARAM_ALPHA);

            switch ($sort) {
                case 'old' :
                    $sort = '';
                    break;
                case 'new' :
                    $sort = 'id DESC';
                    break;
                case 'averagedescending' :
                    $sort = 'ratingaverage DESC';
                    break;
                case 'averageascending' :
                    $sort = 'ratingaverage ASC';
                    break;
                case 'amountdescending' :
                    $sort = 'rating DESC';
                    break;
                case 'amountascending' :
                    $sort = 'rating ASC';
                    break;
            }

            $entry = $DB->get_records('courseboard_posts', array(
                    'courseid'       => $courseid,
                    'coursemoduleid' => $coursemoduleid),
                    $sort);

            // That we havent to go through all the comments, we fetch the
            // postids which are in this module.
            $allpostids = array();
            foreach ($entry as $post) {
                array_push($allpostids, $post->id);
            }

            // Fetch all the comments which are in this module.
            $allcommentsresult = $DB->get_records_list('courseboard_comments', 'postid', $allpostids);
            // Fetch all the rating entries for posts in this module.
            $allratingsresult = $DB->get_records_list('courseboard_ratings', 'postid', $allpostids);

            // Select needed data for the output of the posts and its comments.
            if (!empty($entry)) {

                $rend = $PAGE->get_renderer('mod_courseboard');

                foreach ($entry as $post) {

                    $comments = array();
                    // Fetch the comments for this post.
                    foreach ($allcommentsresult as $comment) {
                        if ($comment->postid == $post->id) {
                            array_push($comments, $comment);
                        }
                    }


                    $didrate = false;
                    // Checks wether the user rated this post or not.
                    foreach ($allratingsresult as $rating) {
                        if ($rating->postid == $post->id && $rating->userid == $USER->id) {
                            $didrate = true;
                            break;
                        }
                    }

                    $data = new stdclass();
                    $data->post = $post;
                    $data->comments = $comments;
                    $data->courseid = $courseid;
                    $data->coursemoduleid = $coursemoduleid;
                    $data->courseboardid = $courseboardid;
                    $data->didrate = $didrate;
                    $data->userid = $USER->id;
                    $data->sesskey = sesskey();

                    echo $rend->render_post($data);
                }


                echo html_writer::tag('h3', get_string('loadingpleasewait', 'courseboard'), 
                                        array('id' => 'postsloading', 'class' => 'courseboard_postsloading'));

            } else {
                echo html_writer::tag('h2', get_string('noposts', 'courseboard'), array(
                   'class' => 'noposts'));
            }

            break;

        case 'rate':

            require_capability('mod/courseboard:write', $context);

            $postid = required_param('q', PARAM_INT);
            $stars = required_param('h', PARAM_INT);

            $timecreated = time();

            $rateentry = new stdClass();
            $rateentry->courseid = $courseid;
            $rateentry->coursemoduleid = $coursemoduleid;
            $rateentry->postid = $postid;
            $rateentry->userid = $USER->id;
            $rateentry->didrate = $stars;
            $rateentry->timecreated = $timecreated;
            $rateentry->timemodified = $timecreated;

            $DB->insert_record('courseboard_ratings', $rateentry, false);

            // Updating the rating of the post.
            $checkpostid = $DB->get_record('courseboard_posts', array('id' => $postid), 'id', MUST_EXIST);

            $entry = $DB->get_record('courseboard_posts', array(
                'courseid'       => $courseid,
                'coursemoduleid' => $coursemoduleid,
                'id'             => $postid
            ), '*', MUST_EXIST);

            $newamountrating = 1 + $entry->rating;
            if($newamountrating != 1) {
                $newaverage = ($stars + ($entry->ratingaverage)) / 2;
            } else {
                $newaverage = $stars; // If it is the first rate for this post.
            }
            

            $updaterating = new stdClass();
            $updaterating->id = $postid;
            $updaterating->courseid = $courseid;
            $updaterating->coursemoduleid = $coursemoduleid;
            $updaterating->rating = $newamountrating;
            $updaterating->ratingaverage = $newaverage;

            $DB->update_record('courseboard_posts', $updaterating);

            break;

        case 'commentInsert':

            require_capability('mod/courseboard:write', $context);

            $comment = required_param('q', PARAM_TEXT);
            $postid = required_param('s', PARAM_INT);
            $name = required_param('o', PARAM_ALPHAEXT);

            $timecreated = time();

            $entry = new stdClass();
            $entry->courseid = $courseid;
            $entry->coursemoduleid = $coursemoduleid;
            $entry->comment = $comment;
            $entry->postid = $postid;
            $entry->name = $name;
            $entry->userid = $USER->id;
            $entry->timecreated = $timecreated;
            $entry->timemodified = $timecreated;

            $DB->insert_record('courseboard_comments', $entry, false);

            break;

        case 'commentsRefresh':

            require_capability('mod/courseboard:view', $context);

            $postid = required_param('q', PARAM_INT);

            $checkpostid = $DB->get_record('courseboard_posts', array('id' => $postid), 'id', MUST_EXIST);

            $post = $DB->get_record('courseboard_posts', array(
                'courseid'       => $courseid,
                'coursemoduleid' => $coursemoduleid,
                'id'             => $postid
            ), '*', MUST_EXIST);

            $comments = $DB->get_records('courseboard_comments', array(
                'courseid'       => $courseid,
                'coursemoduleid' => $coursemoduleid,
                'postid'         => $postid)
            );

            $rend = $PAGE->get_renderer('mod_courseboard');

            $data = new stdclass();
            $data->post = $post;
            $data->comments = $comments;
            $data->courseid = $courseid;
            $data->coursemoduleid = $coursemoduleid;
            $data->courseboardid = $courseboardid;
            $data->sesskey = sesskey();

            echo $rend->render_comment($data);

            break;

        default:
            break;
    }
}