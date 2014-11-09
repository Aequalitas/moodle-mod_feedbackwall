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

require_once($CFG->dirroot . '/mod/courseboard/backup/moodle2/backup_courseboard_stepslib.php');

/**
 * courseboard backup task that provides all the settings and steps to perform one
 * complete backup of the activity.
 */
class backup_courseboard_activity_task extends backup_activity_task {

    /**
     * There are no settings.
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have.
     */
    protected function define_my_steps() {
        $this->add_step(new backup_courseboard_activity_structure_step('courseboard_structure', 'courseboard.xml'));
    }

    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links.
     */
    static public function encode_content_links($content) {

        global $CFG;

        $base = preg_quote($CFG->wwwroot, '/');

        // Link to the list of courseboards.
        $search = '/('.$base.'\/mod\/courseboard\/index.php\?id\=)([0-9]+)/';
        $content = preg_replace($search, '$@COURSEBOARDINDEX*$2@$', $content);

        // Link to courseboard view by moduleid.
        $search = '/('.$base.'\/mod\/courseboard\/view.php\?id\=)([0-9]+)/';
        $content = preg_replace($search, '$@COURSEBOARDVIEWBYID*$2@$', $content);

        return $content;
    }
}

