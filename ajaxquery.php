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
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/locallib.php');



// AJAX-Querys, "fnc" tells which kind of query it was.
if(isset($_POST["fnc"]))
{
	if($_POST["fnc"] == "feedbackInsert")
	{
		$feedback =  required_param("q",PARAM_TEXT);
		$name =  required_param("s",PARAM_TEXT);
		$courseid = required_param("l",PARAM_INT);
		$coursemoduleid = required_param("k",PARAM_INT);
		$timecreated =  required_param("q",PARAM_INT);
		
		$entry = new stdClass();
		$entry -> courseid = $courseid;
		$entry -> coursemoduleid = $coursemoduleid;
		$entry -> feedback = $feedback;
		$entry -> name = $name;
		$entry -> didrate = "0";
		$entry -> timecreated = $timecreated;
		$entry -> timemodified = $timecreated;
		
		$DB -> insert_record("feedbackwall_feedbacks", $entry, false);
		
	}
	else if($_POST["fnc"] == "feedbackwallRefresh")
	{
		
		$s = required_param("q",PARAM_TEXT);
		$courseid = required_param("s",PARAM_INT); 
		$coursemoduleid = required_param("l",PARAM_INT);
		$date =  required_param("d",PARAM_TEXT);
		
		
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
			
		
		
	}
	else if($_POST["fnc"]=="rate")
	{
	
		$feedbackid = required_param("q",PARAM_INT);
		$courseid = required_param("s",PARAM_INT);
		$coursemoduleid = required_param("k",PARAM_INT);
		$stars = required_param("h",PARAM_INT);
				
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
		
	}
	else if($_POST["fnc"]=="commentInsert")
	{
			
		
		$comment= required_param("q",PARAM_TEXT);
		$feedbackid = required_param("s",PARAM_INT);
		$name= required_param("o",PARAM_TEXT);
		$courseid= required_param("k",PARAM_INT);
		$coursemoduleid = required_param("r",PARAM_INT);
		$timecreated = required_param("l",PARAM_INT);

			
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
			
	}
	else if($_POST["fnc"]=="commentsRefresh")
	{
		
		$feedbackid = required_param("q",PARAM_INT);
		$courseid = required_param("k",PARAM_INT);
		$coursemoduleid = required_param("r",PARAM_INT);
		$date =  required_param("d",PARAM_INT);
		
		
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
		
	}


}
else
{
	echo "";
}

//end of AJAX-querys
