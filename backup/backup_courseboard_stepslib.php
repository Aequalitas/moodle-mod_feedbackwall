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
 * @package mod_courseboard
 * @subpackage backup
 * @copyright 2010 onwards Franz Weidmann {https://github.com/Aequalitas}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define the complete courseboard structure for backup, with file and id annotations.
 */
class backup_courseboard_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.

        $courseboard = new backup_nested_element('courseboard', array('id'), array(
            'course', 'name', 'intro', 'introformat', 'timecreated'));

        $posts = new backup_nested_element('posts');
        $post = new backup_nested_element('post', array('id'), array(
            'courseid', 'coursemoduleid', 'post', 'name', 'userid',
            'timecreated', 'timemodified'));

        $comments = new backup_nested_element('comments');
        $comment = new backup_nested_element('comment', array('id'), array(
            'courseid', 'coursemoduleid', 'postid', 'name', 'userid',
            'comment', 'timecreated'));

        $ratings = new backup_nested_element('ratings');
        $rating = new backup_nested_element('rating', array('id'), array(
            'postid', 'courseid', 'coursemoduleid', 'userid'));

        // Build the tree.

        $courseboard->add_child($posts);
                      $posts->add_child($post);

                      $posts->add_child($comments);
                          $comments->add_child($comment);
                      $posts->add_child($ratings);
                          $ratings->add_child($rating);
        // Define sources.

        $courseboard->set_source_table('courseboard', array('id' => backup::VAR_ACTIVITYID));

        // All the rest of elements only happen if we are including user info.
        if ($userinfo) {

            $post->set_source_sql('
                SELECT *
                FROM {courseboard_posts}
                WHERE coursemoduleid = ?
                AND courseid = ?',
                array(backup::VAR_MODID, backup::VAR_COURSEID));

            $comment->set_source_sql('
               SELECT *
               FROM {courseboard_comments}
               WHERE postid = ?',
               array(backup::VAR_PARENTID));

            $rating->set_source_sql('
               SELECT *
               FROM {courseboard_ratings}
               WHERE postid = ?',
               array(backup::VAR_PARENTID));
        }
        // Define id annotations.

        $post->annotate_ids('user', 'userid');
        $comment->annotate_ids('user', 'userid');
        $rating->annotate_ids('user', 'userid');

        // Define file annotations.

        $courseboard->annotate_files('mod_courseboard', 'intro', null); // This file area hasn't itemid.

        // Return the root element (courseboard), wrapped into standard activity structure.
        return $this->prepare_activity_structure($courseboard);
    }
}