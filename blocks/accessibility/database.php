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
 * Interacts with the database to save/reset font size settings        (1)
 *
 * This file handles all the blocks database interaction. If saving,
 * it will check if the current user already has a saved setting, and
 * create/update it as appropriate. If resetting, it will delete the
 * user's setting from the database. If responding to AJAX, it responds
 * with suitable HTTP error codes. Otherwise, it sets a message to
 * display, and redirects the user back to where they came from.       (2)
 *
 * @package   block_accessibility                                      (3)
 * @copyright 2009 &copy; Taunton's College                            (4)
 * @author Mark Johnson <mark.johnson@taunton.ac.uk>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later (5)
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/blocks/accessibility/lib.php');
require_login();

$op = required_param('op', PARAM_TEXT);
$size = optional_param('size', false, PARAM_BOOL);
$scheme = optional_param('scheme', false, PARAM_BOOL);
$atbar = optional_param('atbar', false, PARAM_BOOL);
$colourfilter = optional_param('colourfilter', false, PARAM_BOOL);
$fontfamily = optional_param('fontfamily', false, PARAM_BOOL);
$contrast = optional_param('contrast', false, PARAM_BOOL);
$lineheight = optional_param('lineheight', false, PARAM_BOOL);
$wordspacing = optional_param('wordspacing', false, PARAM_BOOL);
$readerline = optional_param('readerline', false, PARAM_BOOL);
$changecursor = optional_param('changecursor', false, PARAM_BOOL);
$alarm = optional_param('alarm', false, PARAM_BOOL);
$cinema = optional_param('cinema', false, PARAM_BOOL);

if (!accessibility_is_ajax()) {
    $redirect = required_param('redirect', PARAM_TEXT);
    $redirecturl = safe_redirect_url($redirect);
}

switch ($op) {
    case 'save':

        if ($setting = $DB->get_record('block_accessibility', array('userid' => $USER->id))) {
            // Check if the user's already got a saved setting. If they have, just update it.
            if ($size && isset($USER->fontsize)) {
                $setting->fontsize = $USER->fontsize;
            }
            if ($scheme && isset($USER->colourscheme)) {
                $setting->colourscheme = $USER->colourscheme;
            }
            if ($atbar) {
                $setting->autoload_atbar = 1;
            }
            if($colourfilter && isset($USER->colourfilter)){
                $setting->colourfilter = serialize($USER->colourfilter);
            }
            if($fontfamily && isset($USER->fontfamily)){
                $setting->fontfamily = $USER->fontfamily;
            }
            if($contrast && isset($USER->colourfondo) && isset($USER->colourletra)){
                $setting->colourfondo = $USER->colourfondo;
                $setting->colourletra = $USER->colourletra;
            }
            if($lineheight && isset($USER->lineheight)){
                $setting->lineheight = $USER->lineheight;
            }
            if($wordspacing && isset($USER->wordspacing)){
                $setting->wordspacing = $USER->wordspacing;
            }
            if ($readerline) {
                $setting->readerline = 1;
            }
            if ($changecursor) {
                $setting->changecursor = 1;
            }
            if($alarm && isset($USER->alarm)){
                $setting->alarm = $USER->alarm;
            }
            if ($cinema) {
                $setting->cinema = 1;
            }

            $DB->update_record('block_accessibility', $setting);
        } else {
            $setting = new stdClass;
            // Otherwise, create a new record for them.
            if ($size && isset($USER->fontsize)) {
                $setting->fontsize = $USER->fontsize;
            }
            if ($scheme && isset($USER->colourscheme)) {
                $setting->colourscheme = $USER->colourscheme;
            }
            if ($atbar) {
                $setting->autoload_atbar = 1;
            }
            if($colourfilter && isset($USER->colourfilter)){
                $setting->colourfilter = serialize($USER->colourfilter);
            }
            if($fontfamily && isset($USER->fontfamily)){
                $setting->fontfamily = $USER->fontfamily;
            }
            if($contrast && isset($USER->colourfondo) && isset($USER->colourletra)){
                $setting->colourfondo = $USER->colourfondo;
                $setting->colourletra = $USER->colourletra;
            }
            if($lineheight && isset($USER->lineheight)){
                $setting->lineheight = $USER->lineheight;
            }
            if($wordspacing && isset($USER->wordspacing)){
                $setting->wordspacing = $USER->wordspacing;
            }
            if ($readerline) {
                $setting->readerline = 1;
            }
            if ($changecursor) {
                $setting->changecursor = 1;
            }
            if($alarm && isset($USER->alarm)){
                $setting->alarm = $USER->alarm;
            }
            if ($cinema) {
                $setting->cinema = 1;
            }

            $setting->userid = $USER->id;
            $DB->insert_record('block_accessibility', $setting);
        }
        if (!accessibility_is_ajax()) {
            // If not responding to AJAX, set a message to display and redirect.
            $USER->accessabilitymsg = get_string('saved', 'block_accessibility');
            redirect($redirecturl);
        }
        break;

    case 'reset':
        if ($setting = $DB->get_record('block_accessibility', array('userid' => $USER->id))) {
            // If they've got a record, delete it and redirect the user if appropriate.
            if ($size) {
                $setting->fontsize = null;
            } else {
                if (!empty($USER->fontsize)) {
                    $setting->fontsize = $USER->fontsize;
                }
            }
            if ($scheme) {
                $setting->colourscheme = null;
            } else {
                if (!empty($USER->colourscheme)) {
                    $setting->colourscheme = $USER->colourscheme;
                }
            }
            if ($atbar) {
                $setting->autoload_atbar = 0;
            }

            if($colourfilter){
                $setting->colourfilter = null;
            }else {
                if (!empty($USER->colourfilter)) {
                    $setting->colourfilter = serialize($USER->colourfilter);
                }
            }

            if($fontfamily){
                $setting->fontfamily = null;
            }else {
                if (!empty($USER->fontfamily)) {
                    $setting->fontfamily = $USER->fontfamily;
                }
            }

            if($contrast){
                $setting->colourfondo = null;
                $setting->colourletra = null;
            }else {
                if (!empty($USER->colourfondo)) {
                    $setting->colourfondo = $USER->colourfondo;
                }
                if (!empty($USER->colourletra)) {
                    $setting->colourletra = $USER->colourletra;
                }
            }

            if($lineheight){
                $setting->lineheight = null;
            }else {
                if (!empty($USER->lineheight)) {
                    $setting->lineheight = $USER->lineheight;
                }
            }

            if($wordspacing){
                $setting->wordspacing = null;
            }else {
                if (!empty($USER->wordspacing)) {
                    $setting->wordspacing = $USER->wordspacing;
                }
            }

            if ($readerline) {
                $setting->readerline = 0;
            }

            if ($changecursor) {
                $setting->changecursor = 0;
            }

            if($alarm){
                $setting->alarm = null;
            }else {
                if (!empty($USER->alarm)) {
                    $setting->alarm = $USER->alarm;
                }
            }

            if ($cinema) {
                $setting->cinema = 0;
            }

            if (
                    empty($setting->fontsize)
                    && empty($setting->colourscheme)
                    && empty($setting->atbar)
                    && empty($setting->colourfilter)
                    && empty($setting->fontfamily)
                    && empty($setting->colourfondo)
                    && empty($setting->colourletra)
                    && empty($setting->lineheight)
                    && empty($setting->wordspacing)
                    && empty($setting->readerline)
                    && empty($setting->changecursor)
                    && empty($setting->alarm)
                    && empty($setting->cinema)
            ) {
                $DB->delete_records('block_accessibility', array('userid' => $USER->id));
            } else {
                $DB->update_record('block_accessibility', $setting);
            }
            if (!accessibility_is_ajax()) {
                $USER->accessabilitymsg = get_string('reset', 'block_accessibility');
            }
        }
        if (!accessibility_is_ajax()) {
            redirect($redirecturl);
        }
        break;

    default:
        header("HTTP/1.0 400 Bad Request");
}
