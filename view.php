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
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->js(new moodle_url($CFG->wwwroot.'/mod/courseboard/script.js'));

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$a  = optional_param('a', 0, PARAM_INT);  // courseboard instance ID.

if ($id) {
    if (! $cm = get_coursemodule_from_id('courseboard', $id)) {
        error('Course Module ID was incorrect');
    }

    if (! $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST)) {
        error('Course is misconfigured');
    }

    if (! $courseboard = $DB->get_record('courseboard', array('id' => $cm->instance), '*', MUST_EXIST)) {
        error('Course module is incorrect');
    }

} else if ($a) {
    if (! $courseboard = $DB->get_record('courseboard', array('id' => $a), '*', MUST_EXIST)) {
        error('Course module is incorrect');
    }
    if (! $course = $DB->get_record('course', array('id' => $courseboard->course), '*', MUST_EXIST)) {
        error('Course is misconfigured');
    }
    if (! $cm = get_coursemodule_from_instance('courseboard', $courseboard->id, $course->id)) {
        error('Course Module ID was incorrect');
    }

} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/courseboard:view', $context);

// Show some info for guests.
if (isguestuser()) {
    $PAGE->set_title($courseboard->name);
    echo $OUTPUT->header();
    echo $OUTPUT->confirm('<p>'.get_string('noguests', 'courseboard').'</p>'.get_string('liketologin'),
        get_login_url(), $CFG->wwwroot.'/course/view.php?id='.$course->id);

    echo $OUTPUT->footer();
    exit;
}

// Initialise site.
$courseshortname = format_string($course->shortname, true, array('context' => context_course::instance($course->id)));
$title = $courseshortname.': '.format_string($courseboard->name);

$rend = $PAGE->get_renderer('mod_courseboard');
$PAGE->set_url('/mod/courseboard/view.php', array('id' => $cm->id));
$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);

// Print the page header.
$strcourseboards = get_string('modulenameplural', 'courseboard');
$strcourseboard = get_string('modulename', 'courseboard');

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($courseboard->name), 2);

// Print the main part of the page.

// Topdiv, where you choose name and way of sort.

$topdiv = new stdclass();
$topdiv->sesskey = sesskey();
$topdiv->courseid = $course->id;
$topdiv->coursemoduleid = $cm->id;
$topdiv->courseboardid = $cm->instance;
$topdiv->firstname = $USER->firstname;
$topdiv->lastname = $USER->lastname;
$topdiv->usierid = $USER->id;
$topdiv->intro = $courseboard->intro;

echo $rend->render_topdiv($topdiv);

// Maindiv, show posts and its comments.

echo '<br/>';
echo '<hr>';

echo $OUTPUT->box_start('', 'maindiv');

// Getting all posts of this module from the database.
$entry = $DB->get_records('courseboard_posts', array(
    'courseid'       => $course->id,
    'coursemoduleid' => $cm->id),
'id DESC');

// That we havent to go through all the comments, we fetch the
// postids which are in this module.
$allpostids = array();
foreach ($entry as $post) {
    array_push($allpostids, $post->id);
}

$result = courseboard_user_outline($course, $USER, $cm, $courseboard);
echo $result->info;
echo $result->time;
// Fetch all the comments which are in this module.
$allcommentsresult = $DB->get_records_list('courseboard_comments', 'postid', $allpostids);
// Fetch all the rating entries for posts in this module.
$allratingsresult = $DB->get_records_list('courseboard_ratings', 'postid', $allpostids);

// Select needed data for the output of the posts and its comments.
if (!empty($entry)) {
    foreach ($entry as $post) {

        $allcomments = array();
        // Fetch the comments for this post.
        foreach ($allcommentsresult as $comment) {
            if ($comment->postid == $post->id) {
                array_push($allcomments, $comment);
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
        $data->comments = $allcomments;
        $data->didrate = $didrate;
        $data->courseid = $course->id;
        $data->coursemoduleid = $cm->id;
        $data->courseboardid = $cm->instance;
        $data->userid = $USER->id;
        $data->sesskey = sesskey();

        echo $rend->render_post($data);
    }
} else {
    echo $OUTPUT->heading(get_string('noposts', 'courseboard'), 2, 'noposts');
}
echo $OUTPUT->box_end();

// Finish the page.
echo $OUTPUT->footer();
