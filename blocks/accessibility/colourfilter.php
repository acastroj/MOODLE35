<?php

require_once('../../config.php');
require_once($CFG->dirroot . '/blocks/accessibility/lib.php');
require_login();

$r = required_param('r', PARAM_INT);
$g = required_param('g', PARAM_INT);
$b = required_param('b', PARAM_INT);
$a = required_param('a', PARAM_FLOAT);

if($r == 255 && $g == 255 && $b == 255 && $a = 1){
    unset($USER->colourfilter);

    // Clear user records in database.
    $urlparams = array(
            'op' => 'reset',
            'colourfilter' => true,
            'userid' => $USER->id
    );

    if (!accessibility_is_ajax()) {
        $redirect = required_param('redirect', PARAM_TEXT);
        $urlparams['redirect'] = safe_redirect_url($redirect);
    }
    $redirecturl = new moodle_url('/blocks/accessibility/database.php', $urlparams);
    redirect($redirecturl);
}else{
    $USER->colourfilter = array("r" => $r, "g" => $g, "b" => $b, "t" => $a);

    $urlparams = array(
            'op' => 'save',
            'colourfilter' => true,
    );

    if (!accessibility_is_ajax()) {
        $redirect = required_param('redirect', PARAM_TEXT);
        $urlparams['redirect'] = safe_redirect_url($redirect);
    }
    $redirecturl = new moodle_url('/blocks/accessibility/database.php', $urlparams);
    redirect($redirecturl);
}

if (!accessibility_is_ajax()) {
    $redirect = required_param('redirect', PARAM_TEXT);
    $redirecturl = new moodle_url($redirect);
    redirect($redirecturl);
}