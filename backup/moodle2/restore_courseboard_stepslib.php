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
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Franz Weidmann {https://github.com/Aequalitas}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Structure step to restore one courseboard activity.
 */
class restore_courseboard_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('courseboard', '/activity/courseboard');

        if ($userinfo) {
            $paths[] = new restore_path_element('courseboard_post', '/activity/courseboard/posts/post');
            $paths[] = new restore_path_element('courseboard_comment', '/activity/courseboard/posts/post/comments/comment');
            $paths[] = new restore_path_element('courseboard_rating', '/activity/courseboard/posts/post/ratings/rating');
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    protected function process_courseboard($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // Insert the courseboard record.
        $newitemid = $DB->insert_record('courseboard', $data);
        // Immediately after inserting 'activity' record, call this.
        $this->apply_activity_instance($newitemid);
    }

    protected function process_courseboard_post($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $oldcmid = $data->coursemoduleid;

        $data->courseid = $this->get_courseid();
        $data->coursemoduleid = $this->get_mappingid('course_module', $oldcmid);

        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('courseboard_posts', $data);

        $this->set_mapping('courseboard_post', $oldid, $newitemid, true);
    }

    protected function process_courseboard_comment($data) {
        global $DB;

        $data = (object)$data;
        $oldcmid = $data->coursemoduleid;

        $data->courseid = $this->get_courseid();
        $data->coursemoduleid = $this->get_mappingid('course_module', $oldcmid);

        $data->postid = $this->get_new_parentid('courseboard_post');
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $DB->insert_record('courseboard_comments', $data);

    }

    protected function process_courseboard_rating($data) {
        global $DB;

        $data = (object)$data;
        $oldcmid = $data->coursemoduleid;

        $data->courseid = $this->get_courseid();
        $data->coursemoduleid = $this->get_mappingid('course_module', $oldcmid);

        $data->postid = $this->get_new_parentid('courseboard_post');
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $DB->insert_record('courseboard_ratings', $data);

    }

    protected function after_execute() {
        // Add courseboard related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_courseboard', 'intro', null);
    }
}