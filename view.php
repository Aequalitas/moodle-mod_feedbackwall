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
     * @package mod/feedbackwall
     * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */


global $DB;


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');




$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/mod/feedbackwall/script.js') );

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$a  = optional_param('a', 0, PARAM_INT);  // feedbackwall instance ID

if ($id) {
    if (! $cm = get_coursemodule_from_id('feedbackwall', $id)) {
        error('Course Module ID was incorrect');
    }

    if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
        error('Course is misconfigured');
    }

    if (! $feedbackwall = $DB->get_record('feedbackwall', array('id' => $cm->instance))) {
        error('Course module is incorrect');
    }

} else if ($a) {
    if (! $feedbackwall = $DB->get_record('feedbackwall', array('id' => $a))) {
        error('Course module is incorrect');
    }
    if (! $course = $DB->get_record('course', array('id' => $feedbackwall->course))) {
        error('Course is misconfigured');
    }
    if (! $cm = get_coursemodule_from_instance('feedbackwall', $feedbackwall->id, $course->id)) {
        error('Course Module ID was incorrect');
    }

} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

// show some info for guests
if (isguestuser()) {
    $PAGE->set_title($feedbackwall->name);
    echo $OUTPUT->header();
    echo $OUTPUT->confirm('<p>' . get_string('noguests', 'feedbackwall') . '</p>' . get_string('liketologin'),
        get_login_url(), $CFG->wwwroot . '/course/view.php?id=' . $course->id);

    echo $OUTPUT->footer();
    exit;
}

// create date

    $date = usergetdate(time());

    if(strlen($date["mday"]) == 1) {
        $date["mday"] = 0 . $date["mday"];

    }
    if (strlen($date["mon"]) == 1) {
        $date["mon"] = 0 . $date["mon"];
    }

    $dateint = $date["mday"] . $date["mon"] . $date["year"];


// initialise site
$courseshortname = format_string($course->shortname, true, array('context' => context_course::instance($course->id)));
$title = $courseshortname . ': ' . format_string($feedbackwall->name);

$rend = $PAGE->get_renderer('mod_feedbackwall');
$PAGE->set_url('/mod/feedbackwall/view.php', array('id' => $cm->id));
$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);

// Print the page header
$strnewmodules = get_string('modulenameplural', 'feedbackwall');
$strnewmodule = get_string('modulename', 'feedbackwall');

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($feedbackwall->name), 2);

// Print the main part of the page

// Topdiv, choose name and way of sort

$topdiv = new stdclass();
$topdiv->sesskey = $USER->sesskey;
$topdiv->courseid = $course->id;
$topdiv->coursemoduleid = $cm->id;
$topdiv->dateint = $dateint;
$topdiv->firstname = $USER->firstname;
$topdiv->lastname = $USER->lastname;
$topdiv->intro = $feedbackwall->intro;

echo $rend->render_topdiv($topdiv);

// Maindiv, show Feedbacks and its comments.

echo $OUTPUT->heading(get_string("feedbackwall", "feedbackwall"), 2);
echo "<hr>";

echo $OUTPUT->box_start("", "maindiv", array("style" => "overflow:auto;"));

// getting all feedbacks of this module from the database
$entry = $DB->get_records('feedbackwall_feedbacks', array('courseid' => $course->id, "coursemoduleid" => $cm->id), $sort = 'id DESC');

if(!empty($entry)) {
    foreach($entry as $feedback) {
        $comments = $DB->get_records("feedbackwall_comments", array("feedbackid" => $feedback->id));
        $data = new stdclass();
        $data->feedback = $feedback;
        $data->comments = $comments;
        $data->courseid = $course->id;
        $data->coursemoduleid = $cm->id;
        $data->dateInt = $dateint;
        $data->userid = $USER->id;
        $data->sesskey = $USER->sesskey;

        echo $rend->render_feedback($data);
    }
}
else
{
    echo $OUTPUT->heading(get_string("noFeedbacks", "feedbackwall"), 2, "", "", array("class" => 'feedbacks', "style" => 'margin-top:10%;'));
}
echo $OUTPUT->box_end();

// Finish the page
echo $OUTPUT->footer();
