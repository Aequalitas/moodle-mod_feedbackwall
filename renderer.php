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
  * @copyright  10/2014 Franz Weidmann
  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */

defined('MOODLE_INTERNAL') || die();

class mod_feedbackwall_renderer extends plugin_renderer_base {

    /**
     * This function loads the top component of the mainpage 
     * as HTML-Code
     *
     *
     * @param stdclass $data has this data->
     * String sesskey Sessionkey
     * int $courseid courseid
     * int coursemoduleid moduleid of the plugin within the course
     * String firstname firstname of the account
     * String lastname lastname of the account
     * String intro introtext about this module
     *
     * @return String $topdiv top part of the page as HTML-Code
     */
    public function render_topdiv(stdclass $data) {

        $topdiv = "";
        $sesskey = "'" . $data->sesskey . "'"; // Make the sesskey to a string so javascript can use it.

        $inputdesc = html_writer::tag("label",
        get_string("nameinputdescription",
        "feedbackwall"),
        array("style" => 'font-size:11.9px;color:#999;')
        );

        $textarea = html_writer::tag('textarea', "", array(
            "style" => "margin-top:1%;",
            "id" => "feedbackinputfield",
            "rows" => "4",
            "cols" => "90",
            "placeholder" => get_string("writeaFeedback", "feedbackwall"))
        );

        $inputsend = html_writer::tag('input', "", array(
           "type" => "button",
           "id" => "feedbackbutton",
           "onClick" => "feedbackwall_feedbackInsert(" .
                    $data->courseid  .','.
                    $data->coursemoduleid  . ',' .
                    $sesskey . ");",
            "value" => get_string("send", "feedbackwall")
        ));

        $warnlabel = html_writer::tag('label', get_string("emptyFeedbackinput", "feedbackwall"), array(
            "style" => "display:none;color:red;",
            "id" => "emptyFieldWarning"
        ));

        $table = new html_table();
        $table->data = array(
        array(
               $data->intro
        ),
        array(html_writer::select(array(
               get_string("anonymous", "feedbackwall") => get_string("anonymous", "feedbackwall"),
               $data->firstname . "_" . $data->lastname => $data->firstname . "_" . $data->lastname
            ), "" , 0, "", array("id" => "name")) . $inputdesc
        ),
        array(
            $textarea . $inputsend . $warnlabel)
        );

        $topdiv .= $this->box(html_writer::table($table), "", "topdiv");

        $sesskey = '"' . $data->sesskey . '"';
        $topdiv .= $this->box_start();
        $topdiv .= get_string("sortfor", "feedbackwall") . "&nbsp&nbsp";

            // Selectmenu to sort.
            $topdiv .= html_writer::select(array(
            'new' => get_string("newsortdescription", "feedbackwall"),
            'old' => get_string("oldsortdescription", "feedbackwall"),
            'averagedescending' => get_string("ratingdescending" , "feedbackwall"),
            'averageascending' => get_string("ratingascending" , "feedbackwall"),
            'amountdescending' => get_string("amountdescending" , "feedbackwall"),
            'amountascending' => get_string("amountascending" , "feedbackwall"),
        ), 'sort', 0, "", array(
            "id" => "sortmenu",
            "onchange" => "feedbackwall_feedbackwallRefresh(" .
                        $data->courseid . "," .
                        $data->coursemoduleid  . "," .
                        $sesskey . ");"
            )
        );

            $topdiv .= $this->box_end();
            return $topdiv;
    }


    /**
     * This function loads all comments of a feedback 
     * into its comment section 
     *
     * @param stdclass $data has this data->
     * object feedback database entry of a feedback
     * object commentsentry database entry of the comments of the feedback
     * int $courseid courseid
     * int coursemoduleid moduleid of the plugin within the course
     * String sesskey Sessionkey
     *
     * @return string $comments all the comments of a feedback as HTML-Code
     */


    public function render_comment(stdclass $data) {

        $date = $data->feedback->timecreated;

        $datestring = $date[0] . $date[1] . "." . $date[2] . $date[3] . "."
        . $date[4] . $date[5] . $date[6] . $date[7] . "&nbsp"
        . $date[8] . $date[9] . ":" . $date[10] . $date[11];

        $fid = $data->feedback->id;
        $comments = "";

        if ($data->feedback->amountcomments > 0) {
            foreach ($data->comments as $comment) {
                $comments .= $this->box_start("", s($comment->id) . "comment" . $fid );
                
                $comments .= format_text($comment->comment, $format = FORMAT_MOODLE) . "</br>";
                $comments .= html_writer::tag("p",
                s($comment->name)
                . " - " .
                $datestring
                );
                $comments .= $this->box_end() . "<hr></br>";
            }

        } else {
               $comments .= $this->box(get_string("noComments", "feedbackwall"), "", array("class" => 'commShow' . $fid));

        }
            $comments .= $this->container_start('commanShow'. $fid, "", array("style" => 'margin-top:3%;'));
            $areaid = "'commtxtarea" . $fid . "'";

            $comments .= html_writer::tag("textarea", "", array(
                "onclick" => "feedbackwall_clearArea(" . $areaid . ");",
                "id" => 'commtxtarea' . $fid,
                "cols" => '90',
                "rows" => '3',
                "placeholder" => get_string("writeaComment", "feedbackwall"))
            );

            $sesskey = '"' .  $data->sesskey . '"';
            // Button to send a comment.
            $comments .= html_writer::tag("input", "", array(
                "type" => 'button',
                "onClick" => 'feedbackwall_commInsert('
                    . $fid . "," .
                    s($data->courseid) . "," .
                    s($data->coursemoduleid) . "," .
                    $sesskey . ');',
                "class" => 'commentarbtn',
                "id" => 'commbtn' . $fid,
                "value" => get_string("send", "feedbackwall"))
            );

            $comments .= html_writer::tag('label', get_string("emptyCommentinput", "feedbackwall"), array(
                "style" => "display:none; color:red;",
                "id" => "emptyCommFieldwarning". $fid
            ));

            $comments .= $this->container_end();

            return $comments;
    }


    /**
     * This function loads a feedback which belongs to 
     * this module from the database. 
     *
     * @param stdclass $data has this data->
     * object feedback database entry of a feedback
     * object comments database entry of the comments of the feedback
     * int courseid courseid
     * int coursemoduleid moduleid of the plugin within the course
     * int userid userid
     * String sesskey Sessionkey
     *
     * @return string $feedbacks all the feedbacks of the module, with its comments, as HTML-Code
     */

    public function render_feedback(stdclass $data) {

        $fid = $data->feedback->id;
        $ratingaverage = $data->feedback->ratingaverage;
        $alreadyrated = $data->feedback->didrate;
        $alreadyratedarray = explode(",", $alreadyrated);
        $canrate = 1;
        $i = 0;

        while ($alreadyratedarray[$i] != "0") {

            if ($alreadyratedarray[$i] == $data->userid) {
                $canrate = 0;
            }

            $i++;
        }

        $feedback = $this->output->box_start("feedbacks", $fid);
        

        $feedback .= $this->output->box(
        format_text($data->feedback->feedback,
        $format = FORMAT_MOODLE),
        "", "", array("style" => 'margin-left:5%;margin-top:2%;')
        ) . "</br>";

        $date = $data->feedback->timecreated;

        $datestring = $date[0] . $date[1] . "." . $date[2] . $date[3] . "."
        . $date[4] . $date[5] . $date[6] . $date[7] . "&nbsp"
        . $date[8] . $date[9] . ":" . $date[10] . $date[11];

        $feedback .= html_writer::tag("p",
        s($data->feedback->name)
        . " - " . 
        $datestring
        );

        $startable = new html_table();

        for ($i = 0; $i < 5; $i++) {
            if ($ratingaverage - 1 >= 0 ) {
                $startable->data[0][$i] = html_writer::tag("img", "", array("src" => "pix/fullStar.jpg", "alt" => "fullStar"));
                $ratingaverage -= 1;

            } else if ($ratingaverage - 0.5 >= 0 ) {
                $startable->data[0][$i] = html_writer::tag("img", "", array("src" => "pix/halfStar.jpg", "alt" => "halfStar"));
                $ratingaverage -= 0.5;

            } else {
                $startable->data[0][$i] = html_writer::tag("img", "", array("src" => "pix/emptyStar.jpg", "alt" => "emptyStar"));
            }
        }

        $startable->data[0][5] = html_writer::tag("label",
        "(" . s($data->feedback->rating) .  ")",
        array("title" => get_string("rating", "feedbackwall"))
        );

        $startable->attributes["class"] = "empty"; // Otherwise the cells are too big.
        $feedback .= html_writer::table($startable);

        if ($canrate == 1) {
            $feedback .= html_writer::select(array(
                "noStar" => get_string("rateFeedback" , "feedbackwall"),
                "oneStar" => get_string("rateoneStar" , "feedbackwall"),
                "twoStars" => get_string("ratetwoStars" , "feedbackwall"),
                "threeStars" => get_string("ratethreeStars" , "feedbackwall"),
                "fourStars" => get_string("ratefourStars", "feedbackwall"),
                "fiveStars" => get_string("ratefiveStars", "feedbackwall")
            ), "", 0, "", array("id" => "selectStar"  . $fid));

            $sesskeyoutput = '"' . $data->sesskey . '"';

            $feedback .= html_writer::tag("input", "", array(
                "type" => 'button',
                "onClick" => 'feedbackwall_rate('
                    . $fid . "," .
                    s($data->courseid) . "," .
                    s($data->coursemoduleid) . "," .
                    $sesskeyoutput . ');',
            "id" => 'rate' . $fid,
            "value" => get_string("rate", "feedbackwall"))
            );

            $feedback .= "</br>";
        } else {
            $feedback .= html_writer::tag('label', get_string("alreadyrated", "feedbackwall"), array("id" => "alreadyrated"));

        }

        $combtn = "";

        if ($data->feedback->amountcomments > 0) {
            $combtn .= $data->feedback->amountcomments . " ". get_string("showComments", "feedbackwall");
        } else {
            $combtn .= get_string("writeaComment", "feedbackwall");

        }

        // Button which shows the comments.

        $feedback .= html_writer::tag("input", "", array(

            "type" => 'button',
            "onClick" => 'feedbackwall_commShow(' . $fid . ');',
            "class" => 'commShow',
            "id" => 'commShow' . $fid,
            "value" => $combtn )
        );

        // Button which hides the comments.
        $feedback .= html_writer::tag("input", "", array(

            "style" => 'display:none;',
            "onClick" => 'feedbackwall_commHide(' . $fid . ');',
            "class" => 'commHide',
            "type" => 'button',
            "id" => 'commHide' . $fid,
            "value" => get_string("hideComments", "feedbackwall"))
        );

        $feedback .= "<hr>";
        $feedback .= $this->output->box_start("comments", 'commfield'. $fid, array("style" => 'display:none;margin-left:15%;'));

        $commentdata = new stdclass();
        $commentdata->feedback = $data->feedback;
        $commentdata->comments = $data->comments;
        $commentdata->courseid = $data->courseid;
        $commentdata->coursemoduleid = $data->coursemoduleid;
        $commentdata->sesskey = $data->sesskey;

        global $PAGE;
        $rend = $PAGE->get_renderer("mod_feedbackwall");
        $feedback .= $rend->render_comment($commentdata);

        $feedback .= $this->output->box_end();
        $feedback .= $this->output->box_end();

        return $feedback;
    }
}