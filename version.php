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
 * Code fragment to define the version of newmodule
 * This fragment is called by moodle_needs_upgrading() and /admin/index.php
 *
 * @author  Franz Weidmann 
 * @package mod/feedbackwall
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$plugin -> version  = 2014101500;  
$plugin -> requires = 2014041100;          
$plugin -> component = 'mod_feedbackwall';
$plugin -> release = "0.84";
$plugin -> maturity = "BETA";
?>
