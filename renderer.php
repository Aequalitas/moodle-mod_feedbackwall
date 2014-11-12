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
  * @package    mod_courseboard
  * @copyright  10/2014 Franz Weidmann
  * @package mod_courseboard
  * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */

defined('MOODLE_INTERNAL') || die();

class mod_courseboard_renderer extends plugin_renderer_base {

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

        $topdiv = '';
        $sesskey = '"'.$data->sesskey.'"'; // Make the sesskey to a string so javascript can use it.

        $inputdesc = html_writer::tag('label',
                get_string('nameinputdescription', 'courseboard'),
                array('class' => 'nameinputdescription')
        );

        $textarea = html_writer::tag('textarea', '', array(
                'id'          => 'postinputfield',
                'rows'        => '4',
                'cols'        => '90',
                'placeholder' => get_string('writeapost', 'courseboard'))
        );

        $inputsend = html_writer::tag('input', '', array(
           'type'    => 'button',
           'id'      => 'postbutton',
           'onClick' => 'courseboard_postInsert('.
                    $data->courseid.','.
                    $data->coursemoduleid.','.
                    $data->courseboardid.','.
                    $sesskey.');',
            'value' => get_string('send', 'courseboard')
        ));

        $warnlabel = html_writer::tag('label', get_string('emptypostinput', 'courseboard'), array(
            'id'    => 'emptyFieldWarning',
            'class' => 'emptyFieldWarning'
        ));

        $table = new html_table();
        $table->data = array(
        array(
               $data->intro
        ),
        array(html_writer::select(array(
               get_string('anonymous', 'courseboard') => get_string('anonymous', 'courseboard'),
               $data->firstname.'_'.$data->lastname => $data->firstname.'_'.$data->lastname
            ), '' , 0, '', array('id' => 'name')).$inputdesc
        ),
        array(
            $textarea.$inputsend.$warnlabel)
        );

        $topdiv .= $this->box(html_writer::table($table), '', 'topdiv');

        $sesskey = '"'.$data->sesskey.'"';
        $topdiv .= $this->box_start();
        $topdiv .= get_string('sortfor', 'courseboard').'&nbsp&nbsp';

            // Selectmenu to sort.
            $topdiv .= html_writer::select(array(
            'new'               => get_string('newsortdescription', 'courseboard'),
            'old'               => get_string('oldsortdescription', 'courseboard'),
            'averagedescending' => get_string('ratingdescending' , 'courseboard'),
            'averageascending'  => get_string('ratingascending' , 'courseboard'),
            'amountdescending'  => get_string('amountdescending' , 'courseboard'),
            'amountascending'   => get_string('amountascending' , 'courseboard'),
        ), 'sort', 0, '', array(
            'id'       => 'sortmenu',
            'onchange' => 'courseboard_courseboardRefresh('.
                        $data->courseid.','.
                        $data->coursemoduleid.','.
                        $data->courseboardid.','.
                        $sesskey.');'
            )
        );

            $topdiv .= $this->box_end();
            return $topdiv;
    }


    /**
     * This function loads all comments of a post 
     * into its comment section 
     *
     * @param stdclass $data has this data->
     * object post database entry of a post
     * object commentsentry database entry of the comments of the post
     * int $courseid courseid
     * int coursemoduleid moduleid of the plugin within the course
     * String sesskey Sessionkey
     *
     * @return string $comments all the comments of a post as HTML-Code
     */


    public function render_comment(stdclass $data) {

        $date = $data->post->timecreated;
        $pid = $data->post->id;
        $comments = '';

        if (count($data->comments) > 0) {
            foreach ($data->comments as $comment) {
                $comments .= $this->box_start('', s($comment->id).'comment'.$pid );

                $comments .= format_text($comment->comment, $format = FORMAT_MOODLE).'</br>';
                $comments .= html_writer::tag('p', s($comment->name).' - '.userdate($date), array('class'=>'authoranddate'));
                $comments .= $this->box_end().'<hr>';
            }

        } else {
               $comments .= $this->box(get_string('noComments', 'courseboard').'<hr>');

        }

        $comments .= $this->box_start('commanShow'.$pid, '', array('class' => 'commanShow'));
        $areaid = 'commtxtarea'.$pid."'";

        $comments .= html_writer::tag('textarea', '', array(
                'id'          => 'commtxtarea'.$pid,
                'cols'        => '90',
                'rows'        => '3',
                'placeholder' => get_string('writeaComment', 'courseboard'))
        );

        $sesskey = '"'. $data->sesskey.'"';
        // Button to send a comment.
        $comments .= html_writer::tag('input', '', array(
                'type'    => 'button',
                'onClick' => 'courseboard_commInsert('.
                $pid.','.
                s($data->courseid).','.
                s($data->coursemoduleid).','.
                $data->courseboardid.','.
                $sesskey.');',
                'class' => 'commentarbtn',
                'id'    => 'commbtn'.$pid,
                'value' => get_string('send', 'courseboard'))
        );

        $comments .= html_writer::tag('label', get_string('emptyCommentinput', 'courseboard'), array(
            'id'    => 'emptyCommFieldwarning'.$pid,
            'class' => 'emptyFieldWarning'
        ));

        // If there are more than 6 comments then there is
        // a button,next to the sendbutton, which hides the comments.
        if (count($data->comments) > 5) {

            $comments .= html_writer::tag('input', '', array(
                    'onClick' => 'courseboard_commHide('.$pid.');',
                    'class'   => 'commHide',
                    'type'    => 'button',
                    'id'      => 'commHide'.$pid,
                    'value'   => get_string('hideComments', 'courseboard'))
            );
        }
        $comments .= $this->box_end();

        return $comments;
    }


    /**
     * This function loads a post which belongs to 
     * this module from the database. 
     *
     * @param stdclass $data has this data->
     * object post database entry of a post
     * object comments database entry of the comments of the post
     * int courseid courseid
     * int coursemoduleid moduleid of the plugin within the course
     * int userid userid
     * String sesskey Sessionkey
     *
     * @return string $posts all the posts of the module, with its comments, as HTML-Code
     */

    public function render_post(stdclass $data) {

        $pid = $data->post->id;
        $ratingaverage = $data->post->ratingaverage;

        $post = $this->output->box_start('posts', $pid);

        $post .= $this->output->box(
                format_text($data->post->post,
                $format = FORMAT_MOODLE),
                '', 'post').'</br>';

        $date = $data->post->timecreated;
        $post .= html_writer::tag('p', s($data->post->name).' - '.userdate($date), array('class'=>'authoranddate'));

        $startable = new html_table();

        for ($i = 0; $i < 5; $i++) {
            if ($ratingaverage - 1 >= 0 ) {
                $startable->data[0][$i] = html_writer::tag('img', '', array('src' => 'pix/fullStar.jpg', 'alt' => 'fullStar'));
                $ratingaverage -= 1;

            } else if ($ratingaverage - 0.5 >= 0 ) {
                $startable->data[0][$i] = html_writer::tag('img', '', array('src' => 'pix/halfStar.jpg', 'alt' => 'halfStar'));
                $ratingaverage -= 0.5;

            } else {
                $startable->data[0][$i] = html_writer::tag('img', '', array('src' => 'pix/emptyStar.jpg', 'alt' => 'emptyStar'));
            }
        }

        $startable->data[0][5] = html_writer::tag('p',
                '('.s($data->post->rating). ')',
                array('title' => get_string('rating', 'courseboard'))
        );

        $startable->attributes['class'] = 'empty'; // Otherwise the cells are too big.
        $post .= html_writer::table($startable);

        if (!$data->didrate) {
            $post .= html_writer::select(array(
                'noStar'     => get_string('ratepost' , 'courseboard'),
                'oneStar'    => get_string('rateoneStar' , 'courseboard'),
                'twoStars'   => get_string('ratetwoStars' , 'courseboard'),
                'threeStars' => get_string('ratethreeStars' , 'courseboard'),
                'fourStars'  => get_string('ratefourStars', 'courseboard'),
                'fiveStars'  => get_string('ratefiveStars', 'courseboard')
            ), '', 0, '', array('id' => 'selectStar'  . $pid));

            $sesskeyoutput = '"'.$data->sesskey.'"';

            $post .= html_writer::tag('input', '', array(
                        'type'    => 'button',
                        'onClick' => 'courseboard_rate('.
                        $pid.','.
                        s($data->courseid).','.
                        s($data->coursemoduleid).','.
                        s($data->courseboardid).','.
                        $sesskeyoutput.');',
                'id' => 'rate'.$pid,
                'value' => get_string('rate', 'courseboard'))
            );

            $post .= '</br>';
        } else {
            $post .= html_writer::tag('p', get_string('alreadyrated', 'courseboard'), array('id' => 'alreadyrated'));

        }

        $combtn = '';     // Text for the commentbutton which shows the comment.
        if (count($data->comments) > 0) {
            $combtn .= get_string('showComments', 'courseboard').' ('.count($data->comments).')';
        } else {
            $combtn .= get_string('writeaComment', 'courseboard');
        }

        // Will be used when someone writes a comment and there was no comment before.
        // Then the text will be taken from the attribute data.
        // Otherwise it will be used to increase the number in the brackets(number of comments).
        $combtndata = get_string('showComments', 'courseboard');

        // Button which shows the comments.

        $post .= html_writer::tag('input', '', array(
                'type'    => 'button',
                'onClick' => 'courseboard_commShow('.$pid.');',
                'class'   => 'commShowbtns',
                'id'      => 'commShow'.$pid,
                'data'    => $combtndata,
                'cn'      => count($data->comments),
                'value'   => $combtn )
        );

        // Button which hides the comments.
        $post .= html_writer::tag('input', '', array(
                'style'   => 'display:none;',
                'onClick' => 'courseboard_commHide('.$pid.');',
                'class'   => 'commHidebtns',
                'type'    => 'button',
                'id'      => 'commHide'.$pid,
                'value'   => get_string('hideComments', 'courseboard'))
        );

        $post .= '<hr>';
        $post .= $this->output->box_start('comments', 'commfield'.$pid, array('class' => 'commfield'));

        // Get data which will be needed to create the comments for this post.
        $commentdata = new stdclass();
        $commentdata->post = $data->post;
        $commentdata->comments = $data->comments;
        $commentdata->courseid = $data->courseid;
        $commentdata->coursemoduleid = $data->coursemoduleid;
        $commentdata->courseboardid = $data->courseboardid;
        $commentdata->sesskey = $data->sesskey;

        $rend = $this->page->get_renderer('mod_courseboard');
        $post .= $rend->render_comment($commentdata);

        $post .= $this->output->box_end();
        $post .= $this->output->box_end();

        return $post;
    }
}