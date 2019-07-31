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
 * Cambia el estilo del cursor via PHP or AJAX                               (1)
 *
 *                                         (2)
 *
 * @package   block_accessibility                                      (3)
 * @author      Angela Castro Jimenez
 * @copyright   2019, UGR, Granada
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later (5)
 */

header('Cache-Control: no-cache');

require_once('../../config.php');
require_once($CFG->dirroot . '/blocks/accessibility/lib.php');
require_login();


$op = required_param('change_cursor', PARAM_BOOL);
if(!$op){
    unset($USER->changecursor);

    // Clear user records in database.
    $urlparams = array(
            'op' => 'reset',
            'changecursor' => true,
            'userid' => $USER->id
    );

    if (!accessibility_is_ajax()) {
        $redirect = required_param('redirect', PARAM_TEXT);
        $urlparams['redirect'] = safe_redirect_url($redirect);
    }
    $redirecturl = new moodle_url('/blocks/accessibility/database.php', $urlparams);
    redirect($redirecturl);
}else {
    $USER->changecursor = $op;

    $urlparams = array(
            'op' => 'save',
            'changecursor' => true,
    );

    if (!accessibility_is_ajax()) {
        $redirect = required_param('redirect', PARAM_TEXT);
        $urlparams['redirect'] = safe_redirect_url($redirect);
    }
    $redirecturl = new moodle_url('/blocks/accessibility/database.php', $urlparams);
    redirect($redirecturl);

}

if (!accessibility_is_ajax()) {
    // Otherwise, redirect the user
    // if action is not achieved through ajax, redirect back to page is required.
    $redirect = required_param('redirect', PARAM_TEXT);
    $redirecturl = new moodle_url($redirect);
    redirect($redirecturl);
}
