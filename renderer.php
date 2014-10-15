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
 * Chat module rendering methods
 *
 * @package    mod_feedbackwall
 * @copyright  2014 Franz Weidmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_feedbackwall_renderer extends plugin_renderer_base {



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


	public function render_comment($data)
	{
		$fID= $data -> feedback -> id;

		$comments = "";
		
		if( $data -> feedback -> amountcomments > 0)
		{														
			foreach( $data -> comments as $comment)
			{
				$comments .= $this -> box_start("",s($comment -> id) . "comment" . $fID );
				$comments .= html_writer::tag("h4",s($comment -> name));
				$comments .= format_text($comment -> comment,$format = FORMAT_MOODLE) . "</br>";
				$comments .= $this -> box_end() . "</br>";
			}														
		}	
		else
		{
			$comments .= $this -> box(get_string("noComments","feedbackwall"),"",array("class"=>'commShow' . $fID));
		}		

		$comments .= "<hr>" .  $this -> container_start('commanShow'. $fID,"",array("style"=>'margin-top:3%;'));	

		$areaID = "'commtxtarea" . $fID . "'";		

		$sesskey = '"' .  $data -> sesskey . '"';

		$comments .= '<textarea  onclick="clearArea(' . $areaID . ');"';
		$comments .= "' id='commtxtarea" . $fID . "' cols='90' rows='3' placeholder='" . get_string("writeaComment","feedbackwall") . "'>";
		$comments .= "</textarea>";

		$comments .= "<input type='button'  onClick='commInsert(" . $fID . "," . s($data -> courseid) . "," . s($data -> coursemoduleid) . "," . s($data -> dateInt) ."," . $sesskey . ");";
		$comments .= "' class='commentarbtn' id='commbtn" . $fID . "' value='" . get_string("send","feedbackwall") . "'>";

		$comments .= html_writer::tag('label',get_string("emptyCommentinput","feedbackwall"),array("style"=>"display:none; color:red;","id"=>"emptyCommFieldwarning"));														
		
		$comments .=  $this -> container_end();

		
		return $comments;
	}	
	
	
	/**
	 * This function loads a feedback which belongs to 
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
	
	public function render_feedback(stdclass $data)
	{

		$fID = $data -> feedback -> id;	
		
		$ratingAverage = $data -> feedback -> ratingaverage;

		$alreadyrated = $data -> feedback -> didrate;
						
		$alreadyratedArray = explode(",",$alreadyrated);
		$canRate=1;
		$i=0;
		
		while($alreadyratedArray[$i]!="0")
		{
			if($alreadyratedArray[$i] == $data -> userid)
			{
				$canRate = 0;
			}
			$i++;
		}				

		$feedback = $this -> output -> container_start("feedbacks",$fID);  									
		$feedback .= html_writer::tag("h4",s($data -> feedback -> name));
		$feedback .=  $this -> output -> box(format_text($data -> feedback -> feedback,$format = FORMAT_MOODLE),"","",array("style"=>'margin-left:5%;margin-top:2%;')) . "</br>";									
		
		
		
		
		$feedback .=  html_writer::start_tag('table');											
		$feedback .=  html_writer::start_tag('tr');		
	
		
		for($i=0;$i<5;$i++)
		{							
		
			if($ratingAverage - 1 >= 0 )
			{
				$feedback .= html_writer::tag('td',html_writer::tag("img","",array("src"=>"pix/fullStar.jpg","alt"=>"fullStar")));
				$ratingAverage -= 1;
			}
			else if($ratingAverage - 0.5 >= 0 )
			{
				$feedback .= html_writer::tag('td',html_writer::tag("img","",array("src"=>"pix/halfStar.jpg","alt"=>"halfStar")));
				$ratingAverage -= 0.5;
			}
			else
			{
				$feedback .= html_writer::tag('td',html_writer::tag("img","",array("src"=>"pix/emptyStar.jpg","alt"=>"emptyStar")));
			}												
		}		
	

		$feedback .=  html_writer::tag("td",html_writer::tag("label","(" . s($data -> feedback -> rating) .  ")",array("title"=>get_string("rating","feedbackwall"))));
		
		$feedback .=  html_writer::end_tag('tr');											
		$feedback .=  html_writer::end_tag('table');					


		if($canRate==1)
		{	
		
			
			$feedback .= '
				<select id="selectStar'  . $fID . '">
					<option value="noStar">' . get_string("rateFeedback","feedbackwall") . '</option>
					<option value="oneStar">' . get_string("rateoneStar","feedbackwall") . '</option>
					<option value="twoStars">' . get_string("ratetwoStars","feedbackwall") . '</option>
					<option value="threeStars">' . get_string("ratethreeStars","feedbackwall") . '</option>
					<option value="fourStars">' . get_string("ratefourStars","feedbackwall") . '</option>
					<option value="fiveStars">' . get_string("ratefiveStars","feedbackwall") . '</option>
				</select>
			';
			
			$sesskeyoutput = '"' . $data -> sesskey . '"';
			$feedback .=  "<input type='button' onClick='rate(" . $fID . "," . s($data -> courseid) . "," . s($data -> coursemoduleid) . "," .  s($data -> dateInt) . "," . $sesskeyoutput .");'";
			$feedback .=" id='rate" . $fID . "' value='" . get_string("rate","feedbackwall") . "'></br>";
		}
		else
		{
			$feedback .=  html_writer::tag('label',get_string("alreadyrated","feedbackwall"),array("id"=>"alreadyrated"));
		}

		$feedback .=  "<input type='button' onClick='commShow(" . $fID . ");' class='commShow' id='commShow" . $fID . "' value='";		

		if($data -> feedback -> amountcomments > 0)
		{
			$feedback .=  $data -> feedback -> amountcomments . " ". get_string("showComments","feedbackwall") . "'>";
		}
		else
		{
			$feedback .=  get_string("writeaComment","feedbackwall") . "'>";
		}

		$feedback .=  "<input style='display:none;'  onClick='commHide(" . $fID . ");' class='commHide' type='button'";
		$feedback .=  "id='commHide"  . $fID . "' value='" . get_string("hideComments","feedbackwall") . "'>";					
		$feedback .=  "<hr>";					
		$feedback .=  $this -> output -> box_start("comments",'commfield'. $fID,array("style"=>'display:none;margin-left:15%;'));
	
		$commentdata = new stdclass();
		$commentdata -> feedback = $data -> feedback;
		$commentdata -> comments = $data -> comments;
		$commentdata -> courseid = $data -> courseid;
		$commentdata -> coursemoduleid = $data -> coursemoduleid;
		$commentdata -> dateInt = $data -> dateInt;
		$commentdata -> sesskey = $data -> sesskey;
		
		
		global $PAGE;
		$rend = $PAGE -> get_renderer("mod_feedbackwall");
	
		$feedback .= $rend -> render_comment($commentdata);			

		$feedback .=  $this -> output -> box_end();				
		$feedback .=  $this -> output -> container_end();
		
		
		return $feedback;
	}
}

