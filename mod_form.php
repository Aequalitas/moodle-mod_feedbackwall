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
 * This file defines the main courseboard configuration form
 * It uses the standard core Moodle (>1.8) formslib. For
 * more info about them, please visit:
 *
 * http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * The form must provide support for, at least these fields:
 *   - name: text element of 64cc max
 *
 * Also, it's usual to use these fields:
 *   - intro: one htmlarea element to describe the activity
 *            (will be showed in the list of activities of
 *             courseboard type (index.php) and in the header
 *             of the courseboard main page (view.php).
 *   - introformat: The format used to write the contents
 *             of the intro field. It automatically defaults
 *             to HTML when the htmleditor is used and can be
 *             manually selected if the htmleditor is not used
 *             (standard formats are: MOODLE, HTML, PLAIN, MARKDOWN)
 *             See lib/weblib.php Constants and the format_text()
 *             function for more info
 */
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}


require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_courseboard_mod_form extends moodleform_mod {
    function definition() {
        global $COURSE;
        $mform =& $this->_form;

        // Adding the 'general' fieldset, where all the common settings are showed.
            $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard 'name' field.
            $mform->addElement('text', 'name', get_string('setmodulename', 'courseboard'), array('size' => '64'));
            $mform->setType('name', PARAM_TEXT);
            $mform->addRule('name', null, 'required', null, 'client');
            $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Adding the required 'intro' field to hold the description of the instance.
           $this->add_intro_editor(true, get_string('intro', 'courseboard'));

            // Add standard elements, common to all modules.
                $features = new object();
                $features->groups           = false;
                $features->groupings        = false;
                $features->groupmembersonly = true;
                $this->standard_coursemodule_elements($features);
            // Add standard buttons, common to all modules.
            $this->add_action_buttons();

    }
}
