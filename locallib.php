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
//
//
//
// @author  Franz Weidmann 
// @version 9/2014
// @package mod/feedbackwall
// @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 


if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page
}


/**
 * This function loads all comments of a feedback 
 * into its comment section
 *
 * @param object $feedback database entry of a feedback
 * @param object $commentsentry database entry of the comments of the feedback
 * @param int $courseid courseid
 * @param int $coursemoduleid moduleid of the plugin within the course
 * @param int $dateInt date of comment
 * @return string $comments all the comments of a feedback as HTML-Code
 */

function feedbackwall_comments($feedback,$commentsentry,$courseid,$coursemoduleid,$dateInt,$sesskey)
{

	$fID=$feedback -> id;				
	$comments = "<div style='margin-left:15%;' id='comments". $fID ."' style='display:none;'>";		

	if($feedback -> amountcomments > 0)
	{														
		foreach($commentsentry as $comment)
		{
			$comments .= "<div  id='". s($comment -> id) ."comment" . $fID . "'>";
			$comments .= "<h4>" . s($comment -> name) . "</h4>" . format_text($comment -> comment,$format = FORMAT_MOODLE) . "</br>";
			$comments .= "</div><br>";
		}														
	}	
	else
	{
		$comments .= "<div class='commShow". $fID . "'>". get_string("noComments","feedbackwall") ."</div>";
	}		

	$comments .= "<hr><div style='margin-top:3%;' class='commanShow". $fID . "'>";	

	$areaID = "'commtxtarea" . $fID . "'";		

	$sesskey = '"' . $sesskey . '"';
	$comments .= '<textarea  onkeyup="textjump(event,' . $fID . ');" onclick="clearArea(' . $areaID . ');"';
	$comments .= "' id='commtxtarea" . $fID . "' cols='90' rows='3' placeholder='" . get_string("writeaComment","feedbackwall") . "'></textarea>";
	$comments .= "<input type='button'  onClick='commInsert(" . $fID . "," . s($courseid) . "," . s($coursemoduleid) . "," . s($dateInt) ."," . $sesskey . ");";
	$comments .= "' class='commentarbtn' id='commbtn" . $fID . "' value='" . get_string("send","feedbackwall") . "'>";
	$comments .= '<label style="display:none; color:red;" id="emptyCommFieldwarning">' . get_string("emptyCommentinput","feedbackwall") . '</label>';														
	$comments .= "</div></div>";

	
	return $comments;
}


/**
 * This function loads every feedback which belongs to 
 * this module from the database.
 *
 *
 * @param object $feedback database entry of a feedback
 * @param object $comments database entry of the comments of the feedback
 * @param int $courseid courseid
 * @param int $coursemoduleid moduleid of the plugin within the course
 * @param int $dateInt date of comment
 * @param int $userid userid
 * @return string $feedbacks all the feedbacks of the module, with its comments, as HTML-Code
 */
function feedbackwall_feedbacks($feedback,$comments,$courseid,$coursemoduleid,$dateInt,$userid,$sesskey)
{
	$fID=$feedback -> id;	
	$ratingAverage = $feedback -> ratingaverage;

	$alreadyrated = $feedback -> didrate;
					
	$alreadyratedArray = explode(",",$alreadyrated);
	$canRate=1;
	$i=0;
	
	while($alreadyratedArray[$i]!="0")
	{
		if($alreadyratedArray[$i] == $userid)
		{
			$canRate = 0;
		}
		$i++;
	}				

	$feedbacks =  "<div class='feedbacks' id='" . $fID. "'>";  									
	$feedbacks .=  '<h4> ' . s($feedback -> name) . '</h4>';
	$feedbacks .=   "<p style='margin-left:5%;margin-top:2%;' >" . format_text($feedback -> feedback,$format = FORMAT_MOODLE) . "</p>";									
	$feedbacks .=  '	<table>';											
	$feedbacks .=  '<tr>';		

	for($i=0;$i<5;$i++)
	{												
		if($ratingAverage - 1 >= 0 )
		{
			$feedbacks .=  '<td><img src="pix/fullStar.jpg" alt="fullStar"></td>';
			$ratingAverage -= 1;
		}
		else if($ratingAverage - 0.5 >= 0 )
		{
			$feedbacks .=  '<td><img src="pix/halfStar.jpg" alt="halfStar"></td>';
			$ratingAverage -= 0.5;
		}
		else
		{
			$feedbacks .=  '<td><img src="pix/emptyStar.jpg" alt="emptyStar"></td>';
		}												
	}		

	$feedbacks .=  "<td><label title=" . get_string("rating","feedbackwall") . " >(" . s($feedback -> rating) .")</label></td>";
	$feedbacks .=  '</tr>';											
	$feedbacks .=   '</table>';								


	if($canRate==1)
	{
		$feedbacks .= '
			<select id="selectStar'  . $fID . '">
				<option value="noStar">' . get_string("rateFeedback","feedbackwall") . '</option>
				<option value="oneStar">' . get_string("rateoneStar","feedbackwall") . '</option>
				<option value="twoStars">' . get_string("ratetwoStars","feedbackwall") . '</option>
				<option value="threeStars">' . get_string("ratethreeStars","feedbackwall") . '</option>
				<option value="fourStars">' . get_string("ratefourStars","feedbackwall") . '</option>
				<option value="fiveStars">' . get_string("ratefiveStars","feedbackwall") . '</option>
			</select>
		';
		
		$sesskeyoutput = '"' . $sesskey . '"';
		$feedbacks .=  "<input type='button' onClick='rate(" . $fID . "," . s($courseid) . "," . s($coursemoduleid) . "," .  s($dateInt) . "," . $sesskeyoutput .");'";
		$feedbacks .=" id='rate" . $fID . "' value='" . get_string("rate","feedbackwall") . "'></br>";
	}
	else
	{
		$feedbacks .=  '<label id="alreadyrated">' . get_string("alreadyrated","feedbackwall") . '</label>';
	}

	$feedbacks .=  "<input type='button' onClick='commShow(" . $fID . ");' class='commShow' id='commShow" . $fID . "' value='";		

	if($feedback -> amountcomments > 0)
	{
		$feedbacks .=  $feedback -> amountcomments . " ". get_string("showComments","feedbackwall") . "'>";
	}
	else
	{
		$feedbacks .=  get_string("writeaComment","feedbackwall") . "'>";
	}

	$feedbacks .=  "<input style='display:none;'  onClick='commHide(" . $fID . ");' class='commHide' type='button'";
	$feedbacks .=  "id='commHide"  . $fID . "' value='" . get_string("hideComments","feedbackwall") . "'>";					
	$feedbacks .=  "<hr>";					
	$feedbacks .=  "<div class='comments' id='commfield". $fID ."' style='display:none;'>";						
	$feedbacks .=  feedbackwall_comments($feedback,$comments,$courseid,$coursemoduleid,$dateInt,$sesskey);									
	$feedbacks .=  "</div>";					
	$feedbacks .=  "</div>";
	
	
	return $feedbacks;
}