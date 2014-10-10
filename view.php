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
 * @version 9/2014
 * @package mod/feedbackwall
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


global $DB;


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');




// AJAX-Querys, "fnc" tells which kind of query it was.
if(isset($_POST["fnc"]))
{
	if($_POST["fnc"] == "feedbackInsert")
	{
		$feedback = $_POST["q"];
		$name = $_POST["s"];
		$courseid = $_POST["l"];
		$coursemoduleid = $_POST["k"];
		$timecreated = $_POST["r"]; 
		
		$entry = new stdClass();
		$entry -> courseid = $courseid;
		$entry -> coursemoduleid = $coursemoduleid;
		$entry -> feedback = $feedback;
		$entry -> name = $name;
		$entry -> didrate = "0";
		$entry -> timecreated = $timecreated;
		$entry -> timemodified = $timecreated;
		
		$DB -> insert_record("feedbackwall_feedbacks", $entry, false);
		die;
	}
	else if($_POST["fnc"] == "feedbackwallRefresh")
	{
		
		$s = $_POST["q"];
		$courseid = $_POST["s"];
		$coursemoduleid = $_POST["l"];
		$date = $_POST["d"];
		
		
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
		
		
		$entry = $DB->get_records('feedbackwall_feedbacks',
		array(
		'courseid'=>$courseid,
		"coursemoduleid"=>$coursemoduleid,
		),$sort=$s);
		
		if(!empty($entry))
			{
				foreach($entry as $feedback)
				{			
					
					$comments = $DB->get_records("feedbackwall_comments",array("feedbackid"=>$feedback -> id));	
					
					echo feedbackwall_feedbacks($feedback,$comments,$courseid,$coursemoduleid,$date,$USER -> id);														
				}
			}
			else
			{
				echo "<h2 style='margin-top:20%;margin-bottom:20%;'>". get_string("noFeedbacks","feedbackwall") . "</h2>";
			}
			
		die;
		
	}
	else if($_POST["fnc"]=="rate")
	{
	
		$feedbackid = $_POST["q"];
		$courseid = $_POST["s"];
		$coursemoduleid = $_POST["k"];
		$stars = $_POST["h"];
		
		$entry = $DB -> get_record("feedbackwall_feedbacks", array(
		"courseid"=>$courseid,
		"coursemoduleid"=>$coursemoduleid,
		"id"=>$feedbackid)
		);
		
		$newamountRating= 1 + $entry -> rating;
		
		$newAverage = ($stars + ($entry -> ratingaverage)) / $newamountRating;
		
		$newStringDidRate .= $USER -> id . "," . $entry -> didrate;
		
		$updateRating = new stdClass();
		$updateRating -> id = $feedbackid;
		$updateRating -> courseid = $courseid;
		$updateRating -> coursemoduleid = $coursemoduleid;
		$updateRating -> rating = $newamountRating;
		$updateRating -> ratingaverage = $newAverage;
		$updateRating -> didrate = $newStringDidRate;
		
		$DB->update_record("feedbackwall_feedbacks",$updateRating);
		
		die;
		
	}
	else if($_POST["fnc"]=="commentInsert")
	{
			
		
		$comment=$_POST["q"];
		$feedbackid = $_POST["s"];
		$name=$_POST["o"];
		$courseid=$_POST["k"];
		$coursemoduleid =$_POST["r"];
		$timecreated =$_POST["l"]; 

			
			$entry = new stdClass();
			$entry -> courseid = $courseid;
			$entry -> coursemoduleid = $coursemoduleid;
			$entry -> comment = $comment;
			$entry -> feedbackid = $feedbackid;
			$entry -> name = $name;
			$entry -> timecreated = $timecreated;
			$entry -> timemodified = $timecreated;
			
			$DB->insert_record("feedbackwall_comments",$entry,false);
			
			
			$entry = $DB->get_record("feedbackwall_feedbacks", array(
			"courseid"=>$courseid,
			"coursemoduleid"=>$coursemoduleid,
			"id"=>$feedbackid)
			);
		
			$newamountRating= 1 + $entry -> amountcomments;
			
			$updateRating = new stdClass();
			$updateRating -> id = $feedbackid;
			$updateRating -> courseid = $courseid;
			$updateRating -> coursemoduleid = $coursemoduleid;
			$updateRating -> amountcomments = $newamountRating;
			
			$DB->update_record("feedbackwall_feedbacks",$updateRating);
			die;
	}
	else if($_POST["fnc"]=="commentsRefresh")
	{
		
		$feedbackid = $_POST["q"];
		$courseid = $_POST["k"];
		$coursemoduleid = $_POST["r"];
		$date = $_POST["d"];
		
		
		$feedback = $DB->get_record('feedbackwall_feedbacks', array(
		'courseid'=>$courseid,
		"coursemoduleid"=>$coursemoduleid,
		"id"=>$feedbackid)
		);
		
		$comments = $DB->get_records('feedbackwall_comments', array(
		'courseid'=>$courseid,
		"coursemoduleid"=>$coursemoduleid,
		"feedbackid"=>$feedbackid)
		);
		
		echo feedbackwall_comments($feedback,$comments,$courseid,$coursemoduleid,$date);
		die;
	}


}

//end of AJAX-querys

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/mod/feedbackwall/script.js') );

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$a  = optional_param('a', 0, PARAM_INT);  // feedbackwall instance ID

if ($id) {
    if (! $cm = get_coursemodule_from_id('feedbackwall', $id)) {
        error('Course Module ID was incorrect');
    }

    if (! $course = $DB->get_record('course',array('id'=>$cm->course))) {
        error('Course is misconfigured');
    }

    if (! $feedbackwall = $DB->get_record('feedbackwall', array('id'=>$cm->instance))) {
        error('Course module is incorrect');
    }

} else if ($a) {
    if (! $feedbackwall = $DB->get_record('feedbackwall', array('id'=>$a))) {
        error('Course module is incorrect');
    }
    if (! $course =$DB->get_record('course', array('id'=>$feedbackwall->course))) {
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
    echo $OUTPUT->confirm('<p>'.get_string('noguests', 'feedbackwall').'</p>'.get_string('liketologin'),
            get_login_url(), $CFG->wwwroot.'/course/view.php?id='.$course->id);

    echo $OUTPUT->footer();
    exit;
}

//create date

	$date = usergetdate(time());
	
	if(strlen($date["mday"])==1)
	{
		$date["mday"]= 0 . $date["mday"];
	}
	if(strlen($date["mon"])==1)
	{
		$date["mon"]= 0 . $date["mon"];
	}
	
	$dateInt = $date["mday"] . $date["mon"] . $date["year"];


//initialise site
$courseshortname = format_string($course -> shortname, true, array('context' => context_course::instance($course -> id)));
$title = $courseshortname . ': ' . format_string($feedbackwall -> name);


$PAGE -> set_url('/mod/feedbackwall/view.php', array('id' => $cm -> id));
$PAGE -> set_title($title);
$PAGE -> set_heading($course -> fullname);

/// Print the page header
$strnewmodules = get_string('modulenameplural', 'feedbackwall');
$strnewmodule  = get_string('modulename', 'feedbackwall');

echo $OUTPUT -> header();
echo $OUTPUT -> heading(format_string($feedbackwall -> name), 2);

// Print the main part of the page

// Topdiv, choose name and way of sort
$table = new html_table();
$table -> data = array(
array("<h3>". $feedbackwall -> intro . "</h3>"),
array("<select  id='name'>
<option value='" . get_string("anonymous","feedbackwall") ."' >" . get_string("anonymous","feedbackwall") ."</option>  
<option value='" . $USER -> username . "' >" . $USER -> username . "</option>
</select>
<label style='font-size: 11.9px;color: #999;'>" . get_string("nameinputdescription","feedbackwall") . "</label>"),

array('<textarea style="margin-top:1%;" id="feedbackinputfield"  rows="4" cols="90" placeholder="' . get_string("writeaFeedback","feedbackwall") .'"></textarea>
	<input type="button" id="feedbackbutton" onClick="feedbackInsert(' . $course -> id .',' . $cm -> id . ',' . $dateInt . ');" value="' . get_string("send","feedbackwall") . '">
	<p style="font-size: 11.9px;color: #999;">' . get_string("infoReturn","feedbackwall") . '</p>
	<label style="display:none; color:red;" id="emptyFieldWarning">' . get_string("emptyFeedbackinput","feedbackwall") . '</label>
	')
);


echo $OUTPUT -> box(html_writer::table($table),"","topdiv");


//Maindiv, show Feedbacks and its comments.


echo $OUTPUT -> box_start();
echo "<input type='button'  id='refreshlistbtn' value='" . get_string("refreshfeedbacklist","feedbackwall") . "' onClick='feedbackwallRefresh(" . $course -> id . "," . $cm -> id . "," . $dateInt . ");'>

	<select id='sortmenu' onChange='feedbackwallRefresh(" . $course -> id . "," . $cm -> id . "," . $dateInt . ");' >
		<option value='new'>" . get_string("newsortdescription","feedbackwall") .  "</option>
		<option value='old'>" . get_string("oldsortdescription","feedbackwall") .  "</option>
		<option value='averagedescending'>" . get_string("ratingdescending","feedbackwall") .  "</option>
		<option value='averageascending'>" . get_string("ratingascending","feedbackwall") .  "</option>
		<option value='amountdescending'>" . get_string("amountdescending","feedbackwall") .  "</option>
		<option value='amountascending'>" . get_string("amountascending","feedbackwall") .  "</option>
	</select>
		
";

echo $OUTPUT->box_end();

echo "<h2>". get_string("feedbackwall","feedbackwall") . "</h2>";
echo "<hr>";

echo $OUTPUT->box_start("","maindiv",array("style"=>"overflow:auto;"));

// getting all feedbacks of this module from the database
$entry = $DB -> get_records('feedbackwall_feedbacks', array('courseid'=>$course -> id,"coursemoduleid"=>$cm -> id,),$sort='id DESC');
if(!empty($entry))
{
	foreach($entry as $feedback)
	{				
		
		$comments = $DB-> get_records("feedbackwall_comments",array("feedbackid"=>$feedback -> id));	
		
		echo feedbackwall_feedbacks($feedback,$comments,$course -> id,$cm -> id,$dateInt,$USER -> id);														
	}
}
else
{
	echo "<h2 class='feedbacks' style='margin-top:10%;'>". get_string("noFeedbacks","feedbackwall") . "</h2>";
}
echo $OUTPUT->box_end();



	

// Finish the page
echo $OUTPUT->footer();

