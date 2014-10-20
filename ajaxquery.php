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

define('AJAX_SCRIPT', true);

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/locallib.php');


if (!confirm_sesskey()) {
    echo "ERROR(sesskey)";
    die();
}

$courseid = required_param("k", PARAM_INT);  // Those two params are used in every action
$coursemoduleid = required_param("r", PARAM_INT);

$checkcourseid = $DB->get_record("feedbackwall", array("course" => $courseid), "*", MUST_EXIST);

require_login($course, false, $cm);

// AJAX-Querys, "fnc" tells which kind of query it was.
if($fnc = required_param("fnc", PARAM_ALPHA)) {
    if($fnc == "feedbackInsert"){
        $feedback = required_param("q", PARAM_TEXT);
        $name = required_param("s", PARAM_ALPHATEXT);

        $date = usergetdate(time());

        if(strlen($date["mday"]) == 1) {
            $date["mday"] = 0 . $date["mday"];
        }
        if (strlen($date["mon"]) == 1) {
            $date["mon"] = 0 . $date["mon"];
        }
        $timecreated = $date["mday"] . $date["mon"] . $date["year"];


        $entry = new stdClass();
        $entry->courseid = $courseid;
        $entry->coursemoduleid = $coursemoduleid;
        $entry->feedback = $feedback;
        $entry->name = $name;
        $entry->didrate = "0";
        $entry->timecreated = $timecreated;
        $entry->timemodified = $timecreated;

        $DB->insert_record("feedbackwall_feedbacks", $entry, false);

    } else if ($fnc == "feedbackwallRefresh") {
        $s = required_param("q", PARAM_ALPHA);

        $entry = "";
        switch ($s)
        {
            case "old" :
                $s = "";
            break;

            case "new" :
                $s = "id DESC";
            break;

            case "averagedescending" :
                $s = "ratingaverage DESC";
            break;

            case "averageascending" :
                $s = "ratingaverage ASC";
            break;

            case "amountdescending" :
                $s = "rating DESC";
            break;

            case "amountascending" :
                $s = "rating ASC";
            break;

        }

        $entry = $DB->get_records('feedbackwall_feedbacks', array(
        'courseid' => $courseid,
        "coursemoduleid" => $coursemoduleid),
        $sort = $s);

        if(!empty($entry)) {
                global $PAGE;
                $rend = $PAGE->get_renderer("mod_feedbackwall");

            foreach($entry as $feedback) {
                $comments = $DB->get_records("feedbackwall_comments", array("feedbackid" => $feedback->id));

                $data = new stdclass();
                $data->feedback = $feedback;
                $data->comments = $comments;
                $data->courseid = $courseid;
                $data->coursemoduleid = $coursemoduleid;
                $data->userid = $USER->id;
                $data->sesskey = $USER->sesskey;

                echo $rend->render_feedback ($data);

            }

                echo html_writer::tag("h3", get_string("loadingpleasewait", "feedbackwall"), array(
                "id" => 'feedbacksloading',
                "style" => 'display:none;')
                 );

        } else {
            echo html_writer::tag("h2", get_string("noFeedbacks", "feedbackwall"), array(
                 "style" => 'margin-top:20%;margin-bottom:20%;'));
        }

    } else if($fnc == "rate") {
        $feedbackid = required_param("q", PARAM_INT);
        $stars = required_param("h", PARAM_INT);

        $checkfeedbackid = $DB->get_record("feedbackwall_feedbacks", array("id" => $feedbackid), "*", MUST_EXIST);

        $entry = $DB->get_record("feedbackwall_feedbacks", array(
        "courseid" => $courseid,
        "coursemoduleid" => $coursemoduleid,
        "id" => $feedbackid)
         );

        $newamountrating = 1 + $entry->rating;
        $newaverage = ($stars + ($entry->ratingaverage)) / $newamountrating;
        $newstringdidrate .= $USER->id . "," . $entry->didrate;

        $updaterating = new stdClass();
        $updaterating->id = $feedbackid;
        $updaterating->courseid = $courseid;
        $updaterating->coursemoduleid = $coursemoduleid;
        $updaterating->rating = $newamountrating;
        $updaterating->ratingaverage = $newaverage;
        $updaterating->didrate = $newstringdidrate;

        $DB->update_record("feedbackwall_feedbacks", $updaterating);

    } else if($fnc == "commentInsert") {
        $comment = required_param("q", PARAM_TEXT);
        $feedbackid = required_param("s", PARAM_INT);
        $name = required_param("o", PARAM_ALPHATEXT);


        $date = usergetdate(time());

        if(strlen($date["mday"]) == 1) {
            $date["mday"] = 0 . $date["mday"];
        }
        if (strlen($date["mon"]) == 1) {
            $date["mon"] = 0 . $date["mon"];
        }
        $timecreated = $date["mday"] . $date["mon"] . $date["year"];

        $entry = new stdClass();
        $entry->courseid = $courseid;
        $entry->coursemoduleid = $coursemoduleid;
        $entry->comment = $comment;
        $entry->feedbackid = $feedbackid;
        $entry->name = $name;
        $entry->timecreated = $timecreated;
        $entry->timemodified = $timecreated;

        $DB->insert_record("feedbackwall_comments", $entry, false);

        $checkfeedbackid = $DB->get_record("feedbackwall_feedbacks", array("id" => $feedbackid), "*", MUST_EXIST);

        $entry = $DB->get_record("feedbackwall_feedbacks", array(
        "courseid" => $courseid,
        "coursemoduleid" => $coursemoduleid,
        "id" => $feedbackid)
        );

        $newamountrating = 1 + $entry->amountcomments;

        $updaterating = new stdClass();
        $updaterating->id = $feedbackid;
        $updaterating->courseid = $courseid;
        $updaterating->coursemoduleid = $coursemoduleid;
        $updaterating->amountcomments = $newamountrating;

        $DB->update_record("feedbackwall_feedbacks", $updaterating);

    } else if($fnc == "commentsRefresh") {
        $feedbackid = required_param("q", PARAM_INT);

        $checkfeedbackid = $DB->get_record("feedbackwall_feedbacks", array("id" => $feedbackid), "*", MUST_EXIST);

        $feedback = $DB->get_record('feedbackwall_feedbacks', array(
        'courseid' => $courseid,
        "coursemoduleid" => $coursemoduleid,
        "id" => $feedbackid)
        );

        $comments = $DB->get_records('feedbackwall_comments', array(
        'courseid' => $courseid,
        "coursemoduleid" => $coursemoduleid,
        "feedbackid" => $feedbackid)
        );

        global $PAGE;
        $rend = $PAGE->get_renderer("mod_feedbackwall");

        $data = new stdclass();
        $data->feedback = $feedback;
        $data->comments = $comments;
        $data->courseid = $courseid;
        $data->coursemoduleid = $coursemoduleid;
        $data->sesskey = $USER->sesskey;

        echo $rend->render_comment($data);
    }
}