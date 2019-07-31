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
 * Define the accessibility block's class
 *
 * @package    block_accessibility
 * @author      Mark Johnson <mark.johnson@tauntons.ac.uk>
 * @author      Angela Castro Jimenez
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/blocks/accessibility/lib.php');

/**
 * accessibility Block's class
 */
class block_accessibility extends block_base {

    const JS_URL = '/blocks/accessibility/module.js';
    const CSS_URL = '/blocks/accessibility/userstyles.php';
    const FONTSIZE_URL = '/blocks/accessibility/changesize.php';
    const COLOUR_URL = '/blocks/accessibility/changecolour.php';
    const DB_URL = '/blocks/accessibility/database.php';

    /*
    * Constantes para el bloque personalizado
    */
    const FILTER_COLOUR_URL = '/blocks/accessibility/colourfilter.php';
    const CONTRAST = '/blocks/accessibility/changecontrast.php';

    /**
     * Set the title and include the stylesheet
     *
     * We need to include the stylesheet here rather than in {@see get_content()} since get_content
     * is sometimes called after $OUTPUT->heading(), e.g. such as /user/index.php where the middle
     * region is hard-coded.
     * However, /admin/plugins.php calls init() for each plugin after $OUTPUT->heading(), so the
     * sheet is not included at all on that page.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_accessibility');
    }

    /**
     * Called after init(). Here we have instance id so we can use config for specific instance
     * The function will include CSS declarations into Moodle Page
     * CSS declarations will be generated according to user settings and instance configuration
     *
     */
    public function specialization() {
        $instanceid = $this->instance->id;

        if (!$this->page->requires->is_head_done()) {

            // Link default/saved settings to a page.
            // Each block instance has it's own configuration form, so we need instance id.
            $cssurl = self::CSS_URL . '?instance_id=' . $instanceid;
            $this->page->requires->css($cssurl);
        }
    }

    /**
     * instance_allow_multiple explicitly tells there cannot be multiple
     * block instance on the same page
     *
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Set where the block should be allowed to be added
     *
     * @return array
     */
    public function applicable_formats() {
        return array('all' => true);
    }

    /**
     * Generate the contents for the block
     *
     * @return object Block contents and footer
     */
    public function get_content() {
        global $USER;
        global $FULLME;
        global $DB;

        // Until Issue #63 is fixed, we don't want to display block for unauthenticated users.
        if (!isloggedin()) {
            return null;
        }

        if ($this->content !== null) {
            return $this->content;
        }

        // Get the current page url (redirection after action when no Javascript).
        $params = array('redirect' => $FULLME);

        // Set block services paths: changesize.php, changecolour.php and database.php.
        $sizeurl = new moodle_url(self::FONTSIZE_URL, $params);
        $coloururl = new moodle_url(self::COLOUR_URL, $params);

        $params['op'] = 'save';
        $params['size'] = true;
        $params['scheme'] = true;
        $dburl = new moodle_url(self::DB_URL, $params);

        // Initialization of increase_font, decrease_font and save button.
        $incattrs = array(
                'title' => get_string('inctext', 'block_accessibility'),
                'id' => "block_accessibility_inc",
                'href' => $sizeurl->out(false, array('op' => 'inc')),
                'tabindex'=>'0'

        );
        $decattrs = array(
                'title' => get_string('dectext', 'block_accessibility'),
                'id' => "block_accessibility_dec",
                'href' => $sizeurl->out(false, array('op' => 'dec')),
                'tabindex'=>'0'
        );
        $saveattrs = array(
                'title' => get_string('save', 'block_accessibility'),
                'id' => "block_accessibility_save",
                'href' => $dburl->out(false),
                'tabindex'=>'0'
        );

        // Initialization of reset button.
        $resetattrs = array(
                'id' => 'block_accessibility_reset',
                'title' => get_string('resettext', 'block_accessibility'),
                'href' => $sizeurl->out(false, array('op' => 'reset')),
                'tabindex'=>'0'
        );

        // If any of increase/decrease buttons reached maximum size, disable it.
        if (isset($USER->fontsize)) {
            if ($USER->fontsize == MIN_FONTSIZE) {
                $decattrs['class'] = 'disabled';
                unset($decattrs['href']);
            }
            if ($USER->fontsize == MAX_FONTSIZE) {
                $incattrs['class'] = 'disabled';
                unset($incattrs['href']);
            }
        } else {
            // Or disable reset button.
            $resetattrs['class'] = 'disabled';
        }

        // Initialization of scheme profiles buttons.
        $c1attrs = array(
                'title' => get_string('col1text', 'block_accessibility'),
                'id' => 'block_accessibility_colour1',
                'href' => $coloururl->out(false, array('scheme' => 1)),
                'tabindex'=>'0'
        );
        $c2attrs = array(
                'title' => get_string('col2text', 'block_accessibility'),
                'id' => 'block_accessibility_colour2',
                'href' => $coloururl->out(false, array('scheme' => 2)),
                'tabindex'=>'0'
        );
        $c3attrs = array(
                'title' => get_string('col3text', 'block_accessibility'),
                'id' => 'block_accessibility_colour3',
                'href' => $coloururl->out(false, array('scheme' => 3)),
                'tabindex'=>'0'
        );
        $c4attrs = array(
                'title' => get_string('col4text', 'block_accessibility'),
                'id' => 'block_accessibility_colour4',
                'href' => $coloururl->out(false, array('scheme' => 4)),
                'tabindex'=>'0'
        );

        if (!isset($USER->colourscheme) && !isset($USER->colourfondo) && !isset($USER->colourletra)) {
            $c1attrs['class'] = 'disabled';
        }

        // The display:inline-block CSS declaration is not applied to block's buttons because IE7 doesn't support, float is
        // used instead for IE7 only.
        $clearfix = '';
        if (preg_match('/(?i)msie [1-7]/', $_SERVER['HTTP_USER_AGENT'])) {
            $clearfix = html_writer::tag('div', '', array('style' => 'clear:both')); // Required for IE7.
        }

        // Render block HTML.
        $content = '';

        $strchar = get_string('char', 'block_accessibility');
        $resetchar = "R";
        $divattrs = array('id' => 'accessibility_controls', 'class' => 'content');
        $listattrs = array('id' => 'block_accessibility_textresize', 'class' => 'button_row');



        $content .= html_writer::start_tag('div', $divattrs);
        $content .= html_writer::start_tag('ul', $listattrs);

        $content .= html_writer::start_tag('li', array('class' => 'access-button'));
        $content .= html_writer::tag('a', $strchar . '-', $decattrs);
        $content .= html_writer::end_tag('li');

        $content .= html_writer::start_tag('li', array('class' => 'access-button'));
        $content .= html_writer::tag('a', $strchar,  $resetattrs);
        $content .= html_writer::end_tag('li');

        $content .= html_writer::start_tag('li', array('class' => 'access-button'));
        $content .= html_writer::tag('a', $strchar . '+', $incattrs);
        $content .= html_writer::end_tag('li');

        $content .= html_writer::start_tag('li', array('class' => 'access-button'));
        $content .= html_writer::tag('a', '&nbsp', $saveattrs);
        $content .= html_writer::end_tag('li');

        $content .= html_writer::end_tag('ul');

        $content .= $clearfix;

        // Colour change buttons.
        $content .= html_writer::start_tag('ul', array('id' => 'block_accessibility_changecolour'));

        $content .= html_writer::start_tag('li', array('class' => 'access-button'));
        $content .= html_writer::tag('a', $resetchar, $c1attrs);
        $content .= html_writer::end_tag('li');

        $content .= html_writer::start_tag('li', array('class' => 'access-button'));
        $content .= html_writer::tag('a', $strchar, $c2attrs);
        $content .= html_writer::end_tag('li');

        $content .= html_writer::start_tag('li', array('class' => 'access-button'));
        $content .= html_writer::tag('a', $strchar, $c3attrs);
        $content .= html_writer::end_tag('li');

        $content .= html_writer::start_tag('li', array('class' => 'access-button'));
        $content .= html_writer::tag('a', $strchar, $c4attrs);
        $content .= html_writer::end_tag('li');

        $content .= html_writer::end_tag('ul');


        /*
         * Bloque personalizado
         */

        //Inicialización del botón de selección del color de fondo
        $attr_colorpicker_fondo = array(
            'name'=> get_string('cpbackground', 'block_accessibility'),
            'title'=> get_string('cpbackground', 'block_accessibility'),
            'id' => 'boton-color-picker-fondo',
            'type' => 'color',
        );

        //Inicialización del botón de selección del color de letra
        $attr_colorpicker_letra = array(
            'name'=> get_string('cpfont', 'block_accessibility'),
            'title'=> get_string('cpfont', 'block_accessibility'),
            'id' => 'boton-color-picker-letra',
            'type' => 'color',
        );

        //Se establece el color que aparece en los botones
        if (isset($USER->colourfondo)) {
            $attr_colorpicker_fondo['value'] = "#".$USER->colourfondo;
        }else{
            $attr_colorpicker_fondo['class'] = 'startEmpty';
            $attr_colorpicker_fondo['value'] = "#FFFFFF";
        }

        if (isset($USER->colourletra)) {
            $attr_colorpicker_letra['value'] = "#".$USER->colourletra;
        }else{
            $attr_colorpicker_letra['class'] = 'startEmpty';
            $attr_colorpicker_letra['value'] = "#000000";
        }


        //Botón de selección de color de fondo
        $content .= html_writer::start_tag('ul', array('id' => 'block_accessibility_filter'));
        $content .= html_writer::start_tag('li', array('class' => 'access-button'));
        $content .= html_writer::tag('input', null, $attr_colorpicker_fondo);
        $content .= html_writer::end_tag('li');

        //Botón de selección de color de letra
        $content .= html_writer::start_tag('li', array('class' => 'access-button'));
        $content .= html_writer::tag('input', null, $attr_colorpicker_letra);
        $content .= html_writer::end_tag('li');


        $filterurl = new moodle_url(self::FILTER_COLOUR_URL, $params);
        $options = $DB->get_record('block_accessibility', array('userid' => $USER->id));


        //Inicializacion del botón de filtro de color
        $attr_colorpicker = array(
               // 'name'=> get_string('cfilter', 'block_accessibility'),
                //'title'=> 'Establecer filtro de color',
                'id' => 'boton-color-picker',
                'type' => 'text'
        );



        if (!isset($USER->colourfilter)) {
            if(!empty($options->colourfilter)){
                $colorfilter = unserialize($options->colourfilter);
                $USER->colourfilter = $colorfilter;
            }
        }

        //Se establece el aspecto del botón en base a la configuración
        if(isset($USER->colourfilter)) {
            $stringTransparencia = "opacity:".$USER->colourfilter['t'].";";
            $stringBackgroundColor = "background-color:rgb(".$USER->colourfilter["r"].",".$USER->colourfilter["g"].",".$USER->colourfilter["b"].")!important;";
            $content .= html_writer::tag('div',"",
                array(
                'style' => 'position:fixed; width:100%;height:100%; z-index: 22147483640; pointer-events:none; top:0; left:0;'.$stringTransparencia.$stringBackgroundColor,
                'id' => "colourfilterpersonalizado"
            ));
            $attr_colorpicker['value'] = "rgba(".$USER->colourfilter['r'].",".$USER->colourfilter['g'].",".$USER->colourfilter['b'].",".$USER->colourfilter['t'].")";
        }else{
            $attr_colorpicker['class'] = 'startEmpty';
            $attr_colorpicker['value'] = "";
        }

        //Botón de selección de filtro de color
        $content .= html_writer::start_tag('li', array('class' => 'access-button'));
        $content .= html_writer::tag('input', null, $attr_colorpicker);
        $content .= html_writer::end_tag('li');

        $content .= html_writer::end_tag('ul');
        $this->page->requires->css('/blocks/accessibility/colourpicker/spectrum.css', true);


       //Inicialización del selector de fuente
        $attr_selectfont = array(
            'title'=> get_string('setfuente', 'block_accessibility'),
            'id' => 'select-family-custom-accessibility',
            'name'=> get_string('listafuentes', 'block_accessibility')
        );

        //Botón de selección de fuente
        $content .= html_writer::start_tag('ul', array('id' => 'block_accessibility_family'));
        $content .= html_writer::start_tag('li', array('class' => 'access-button'));
        $content .= html_writer::start_tag('select', $attr_selectfont);

        $attr_font_family = [
          'Defecto', 'Dyslexic', 'Open Sans', 'sans-serif', 'serif',
        ];
        foreach($attr_font_family as $key){
            if(isset($USER->fontfamily) && $USER->fontfamily == $key){
                $selected = "selected";
            }else{
                $selected = "";
            }
            $content .= html_writer::tag('option', $key,['value' => $key, $selected => true] );
        }

        $content .= html_writer::end_tag('select');
        $content .= html_writer::end_tag('li');
        $content .= html_writer::end_tag('ul');


        //Inicialización del selector de interlineado
        $attr_selectlineheight = array(
            'title'=> get_string('setEspaciado', 'block_accessibility'),
            'id' => 'select-line-height-custom-accessibility',
            'name'=> get_string('listaEspaciados', 'block_accessibility')
        );


        //Botón interlineado
        $content .= html_writer::start_tag('ul', array('id' => 'block_accessibility_line_height'));
        $content .= html_writer::start_tag('li', array('class' => 'access-button'));
        $content .= html_writer::start_tag('select', $attr_selectlineheight);

        $attr_line_height = [
                'Defecto','1.5','2', '3',
        ];
        foreach($attr_line_height as $key){
            if(isset($USER->lineheight) && $USER->lineheight == $key){
                $selected = "selected";
            }else{
                $selected = "";
            }
            $content .= html_writer::tag('option', $key,['value' => $key, $selected => true] );
        }

        $content .= html_writer::end_tag('select');
        $content .= html_writer::end_tag('li');
        $content .= html_writer::end_tag('ul');


        //Inicialización del selector de interlineado
        $attr_selectWordSpacing = array(
            'title'=> get_string('setEspaciadoPalabra', 'block_accessibility'),
            'id' => 'select-word-spacing-custom-accessibility',
            'name'=> get_string('listaEspaciadosPalabra', 'block_accessibility')
        );

        //Botón espaciado entre letras
        $content .= html_writer::start_tag('ul', array('id' => 'block_accessibility_word_spacing'));
        $content .= html_writer::start_tag('li', array('class' => 'access-button'));
        $content .= html_writer::start_tag('select', $attr_selectWordSpacing);

        $attr_word_spacing = [
                'Defecto','0.16','0.32', '0.5',
        ];

        foreach($attr_word_spacing as $key){
            if(isset($USER->wordspacing) && $USER->wordspacing == $key){
                $selected = "selected";
            }else{
                $selected = "";
            }
            $content .= html_writer::tag('option', $key,['value' => $key, $selected => true] );
        }

        $content .= html_writer::end_tag('select');
        $content .= html_writer::end_tag('li');
        $content .= html_writer::end_tag('ul');


        //Apoyo lectura
        if(isset($USER->readerline) && $USER->readerline){
            $texto_button_reader_line = "Apoyo Lectura";
            $class_button_reader_line = "reader-line-active";
        }else{
            $texto_button_reader_line = "Apoyo Lectura";
            $class_button_reader_line = "";
        }

        //Inicialización del botón apoyo lectura
        $attr_reader_line = array(
            'title'=> get_string('setReaderLine', 'block_accessibility'),
            'id' => 'reader-line-custom-accessibility',
            'name'=> get_string('ReaderLine', 'block_accessibility'),
            'class'=> $class_button_reader_line
        );

        //Botón apoyo de lectura
        $content .= html_writer::start_tag('ul', array('id' => 'block_accessibility_reader_line'));
        $content .= html_writer::start_tag('li', array('class' => 'access-button'));
        $content .= html_writer::tag('button',$texto_button_reader_line, $attr_reader_line);

        $content .= html_writer::end_tag('li');
        $content .= html_writer::end_tag('ul');

        //Cambiar cursor
        if(isset($USER->changecursor) && $USER->changecursor){
            $texto_button_change_cursor = "Cursor Normal";
            $class_button_change_cursor = "cursor-activo";
        }else{
            $texto_button_change_cursor = "Cursor Grande";
            $class_button_change_cursor = "";
        }

        //Inicialización del botón modo cine
        $attr_cursor = array(
                'title'=> get_string('cambiarCursor', 'block_accessibility'),
                'id' => 'change-cursor-custom-accessibility',
                'name'=> get_string('cursor', 'block_accessibility'),
                'class'=> $class_button_change_cursor
        );

        $content .= html_writer::start_tag('ul', array('id' => 'block_accessibility_change_cursor'));
        $content .= html_writer::start_tag('li', array('class' => 'access-button'));
        $content .= html_writer::tag('button',$texto_button_change_cursor, $attr_cursor);

        $content .= html_writer::end_tag('li');
        $content .= html_writer::end_tag('ul');


        $attr_alarm=array('id'=> 'alarm-custom-accessibility');


        if(isset($USER->alarm) && $USER->alarm){
            $attr_alarm['value'] = date("H : i",$USER->alarm);
            $attr_alarm['disabled'] = "true";
            $texto_button_alarm = "Borrar Alarma";
            $clase_alarm = "alarm-activo";

        }else{
            $attr_alarm['value'] = "";
            $clase_alarm ="";
            $texto_button_alarm = "Fijar Alarma";
        }

        $attr_alarma=array(
                'title'=> get_string('setAlarma', 'block_accessibility'),
                'id' => 'alarm-button-custom-accessibility',
                'name'=> get_string('alarma', 'block_accessibility'),
                'class'=> $clase_alarm
        );
        $this->page->requires->css('/blocks/accessibility/hourpicker/wickedpicker.css', true);

        //Botón de alarma
        $content .= html_writer::start_tag('ul', array('id' => 'block_accessibility_alarm'));
        $content .= html_writer::start_tag('li', array('class' => 'access-button'));
        $content .= html_writer::tag('input',null, $attr_alarm);

        $content .= html_writer::tag('button',$texto_button_alarm, $attr_alarma);

        $content .= html_writer::end_tag('li');
        $content .= html_writer::end_tag('ul');


        //Efecto Cine (modo foco)

        $content .= html_writer::tag('div',"",array(
            'style' => 'position:fixed; width:100%;height:100%; z-index: 99998; top:0; left:0;background-color:rgb(133,133,133)!important;opacity:0.8',
            'id' => "modo-cinema"
        ));
        if(isset($USER->cinema) && $USER->cinema){
            $texto_button_cinema = "Modo Cine";
            $clase_cinema = "cinema-activo";
        }else{
            $attr_alarm['value'] = "";
            $clase_cinema ="";
            $texto_button_cinema = "Modo Cine";
        }

        //Inicialización del botón modo cine
        $attr_cine = array(
            'title'=> get_string('modoCine', 'block_accessibility'),
            'id' => 'cinema-button-custom-accessibility',
            'name'=> get_string('cine', 'block_accessibility'),
            'class'=> $clase_cinema
        );

        //Botón modo cine
        $content .= html_writer::start_tag('ul', array('id' => 'block_accessibility_cinema'));
        $content .= html_writer::start_tag('li', array('class' => 'access-button'));

        $content .= html_writer::tag('button',$texto_button_cinema, $attr_cine);

        $content .= html_writer::end_tag('li');
        $content .= html_writer::end_tag('ul');

        //Chequeo de accesibilidad (tota11y)

        //Inicialización del botón chequeo accesibilidad
        $attr_tota11y = array(
            'title'=> get_string('setTota11y', 'block_accessibility'),
            'id' => 'tota11y-button-custom-accessibility',
            'name'=> get_string('tota11y', 'block_accessibility'),
            'value'=> get_string('accesstota11y', 'block_accessibility')
        );
        $texto_tota11y='Accesibilidad';

        //Botón tota11y (modo chequeo de accesibilidad)
        $content .= html_writer::start_tag('ul', array('id' => 'block_accessibility_tota11y'));
        $content .= html_writer::start_tag('li', array('class' => 'access-button'));

        $content .= html_writer::tag('button',$texto_tota11y, $attr_tota11y);

        $content .= html_writer::end_tag('li');
        $content .= html_writer::end_tag('ul');


        //FIN BLOQUE PERSONALIZADO

        $content .= $clearfix;

        // Display "settings saved" or etc.
        if (isset($USER->accessabilitymsg)) {
            $message = $USER->accessabilitymsg;
            unset($USER->accessabilitymsg);
        } else {
            $message = '';
        }
        $messageattrs = array('id' => 'block_accessibility_message', 'class' => 'clearfix');
        $content .= html_writer::tag('div', $message, $messageattrs);

        // Data to pass to module.js.
        $jsdata['autoload_atbar'] = false;
        $jsdata['instance_id'] = $this->instance->id;

 /*       // Render ATBar.
        $showatbar = DEFAULT_SHOWATBAR;
        if (isset($this->config->showATbar)) {
            $showatbar = $this->config->showATbar;
        }

        if ($showatbar) {

            // Load database record for a current user.
            $options = $DB->get_record('block_accessibility', array('userid' => $USER->id));

            // Initialize ATBar.
            $checkboxattrs = array(
                    'type' => 'checkbox',
                    'value' => 1,
                    'id' => 'atbar_auto',
                    'name' => 'atbar_auto',
                    'class' => 'atbar_control',
            );

            $labelattrs = array(
                    'for' => 'atbar_auto',
                    'class' => 'atbar_control'
            );

            if ($options && $options->autoload_atbar) {
                $checkboxattrs['checked'] = 'checked';
                $jsdata['autoload_atbar'] = true;
            }

            // ATbar launch button (if javascript is enabled).
            $launchattrs = array(
                    'type' => 'button',
                    'value' => get_string('launchtoolbar', 'block_accessibility'),
                    'id' => 'block_accessibility_launchtoolbar',
                    'class' => 'atbar_control access-button'
            );

            // Render ATBar.
            $content .= html_writer::empty_tag('input', $launchattrs);

            $spanattrs = array('class' => 'atbar-always');
            $content .= html_writer::start_tag('span', $spanattrs);
            $content .= html_writer::empty_tag('input', $checkboxattrs);
            $strlaunch = get_string('autolaunch', 'block_accessibility');
            $content .= html_writer::tag('label', $strlaunch, $labelattrs);
            $content .= html_writer::end_tag('span');
        }

        $content .= html_writer::end_tag('div');
 */

        // Loader icon.
        $spanattrs = array('id' => 'loader-icon');
        $content .= html_writer::start_tag('span', $spanattrs);
        $content .= html_writer::end_tag('span');

        $content .= $clearfix;

        $this->content = new stdClass;
        $this->content->footer = '';
        $this->content->text = $content;

        // Keep in mind that dynamic AJAX mode cannot work properly with IE <= 8 (for now), so javascript will not even be loaded.
        if (!preg_match('/(?i)msie [1-8]/', $_SERVER['HTTP_USER_AGENT'])) {
            // Language strings to pass to module.js.
            $this->page->requires->string_for_js('saved', 'block_accessibility');
            $this->page->requires->string_for_js('jsnosave', 'block_accessibility');
            $this->page->requires->string_for_js('reset', 'block_accessibility');
            $this->page->requires->string_for_js('jsnosizereset', 'block_accessibility');
            $this->page->requires->string_for_js('jsnocolourreset', 'block_accessibility');
            $this->page->requires->string_for_js('jsnosize', 'block_accessibility');
            $this->page->requires->string_for_js('jsnocolour', 'block_accessibility');
            $this->page->requires->string_for_js('jsnosizereset', 'block_accessibility');
            $this->page->requires->string_for_js('jsnotloggedin', 'block_accessibility');
            $this->page->requires->string_for_js('launchtoolbar', 'block_accessibility');

            //BLOQUE PERSONALIZADO
            $this->page->requires->string_for_js('escogerColorFilter', 'block_accessibility');
            $this->page->requires->string_for_js('cancelarColorFilter', 'block_accessibility');
            $this->page->requires->string_for_js('errorColorFilter', 'block_accessibility');


            $this->page->requires->string_for_js('errorChangeFamily', 'block_accessibility');
            $this->page->requires->string_for_js('errorContrast', 'block_accessibility');
            $this->page->requires->string_for_js('WCAGaaaSI', 'block_accessibility');
            $this->page->requires->string_for_js('WCAGaaaNO', 'block_accessibility');
            $this->page->requires->string_for_js('errorContrastR', 'block_accessibility');
            $this->page->requires->string_for_js('errorChangeLineHeight', 'block_accessibility');
            $this->page->requires->string_for_js('errorChangeWordSpacing', 'block_accessibility');
            $this->page->requires->string_for_js('errorReaderline', 'block_accessibility');
            $this->page->requires->string_for_js('errorCursor', 'block_accessibility');
            $this->page->requires->string_for_js('errorCinema', 'block_accessibility');
            //$this->page->requires->string_for_js('errorTota11y', 'block_accessibility');
            $this->page->requires->string_for_js('errorAlarma', 'block_accessibility');
            //FIN BLOQUE PERSONALIZADO

            $jsmodule = array(
                    'name' => 'block_accessibility',
                    'fullpath' => self::JS_URL,
                    'requires' => array('base', 'node', 'stylesheet')
            );

            // Include js script and pass the arguments.
            $this->page->requires->js_init_call('M.block_accessibility.init', $jsdata, false, $jsmodule);
            $this->page->requires->js_call_amd('block_accessibility/zaccesibilidad_personalida','initialise');
            $this->page->requires->js('/blocks/accessibility/tota11y.min.js', false);
        }

        return $this->content;
    }

}
