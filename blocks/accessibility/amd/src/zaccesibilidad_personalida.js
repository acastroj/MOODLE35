//Cambiar el filtro de color
define(['jquery','block_accessibility/spectrum','block_accessibility/wickedpicker'], function($,spectrum,wickedpicker) {
    return {
        initialise: function (){
            $('#boton-color-picker').spectrum({
                showAlpha: true,
                showInitial: true,
                showPalette: true,
                allowEmpty: true,
                palette: [
                    ['rgba(186, 186, 112, 0.3);'],
                    ['rgba(255, 102, 153, 0.3);'],
                    ['rgba(51, 153, 204, 0.3);'],
                    ['rgba(0, 204, 102, 0.3);'],
                ],
                maxSelectionSize: 1,
                cancelText: M.util.get_string('cancelarColorFilter', 'block_accessibility'),
                chooseText: M.util.get_string('escogerColorFilter', 'block_accessibility'),
                change: changeFilterColour,
            });
            function changeFilterColour(color){
                if(!color){
                    $('#colourfilterpersonalizado').remove();
                    var colorRgb = tinycolor("white").toRgb();
                    var stringTransparencia = "";
                    var stringBackgroundColor = "";
                    transparencia = 1;
                }else{
                    var colorRgb = color.toRgb();
                    var transparencia = colorRgb.a;
                    if(transparencia > 0.8){
                        transparencia = 0.8;
                    }
                    var stringTransparencia = "opacity:"+transparencia+";";
                    var stringBackgroundColor = "background-color:rgb("+colorRgb.r+","+colorRgb.g+","+colorRgb.b+")!important;";
                }
                $.ajax({
                    url: M.cfg.wwwroot+'/blocks/accessibility/colourfilter.php',
                    data: {r:colorRgb.r,g:colorRgb.g,b:colorRgb.b,a:transparencia},
                    beforeSend: M.block_accessibility.show_loading,
                    complete: M.block_accessibility.hide_loading,
                    success: function(){
                        $('#colourfilterpersonalizado').remove();
                        if(color){
                            $('body').prepend('<div id="colourfilterpersonalizado" style="position:fixed; width:100%;height:100%; z-index: 22147483640; pointer-events:none; top:0; left:0; '+stringBackgroundColor+stringTransparencia+'"></div>');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown){
                        alert(M.util.get_string('errorColorFilter', 'block_accessibility')+': ' + textStatus +' ' + errorThrown);
                    }
                });
            }

            //Cambiar la fuente
            $('#select-family-custom-accessibility').on('change',function(){
                var fuente = this.value;
                $.ajax({
                    url: M.cfg.wwwroot+'/blocks/accessibility/changefamily.php',
                    data: {fuente:fuente},
                    beforeSend: M.block_accessibility.show_loading,
                    complete: function(){
                        if(fuente!="Defecto"){
                            M.block_accessibility.hide_loading();
                        }
                    },
                    success: function(){
                        M.block_accessibility.reload_stylesheet();
                        if(fuente == "Defecto"){
                            location.reload();
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown){
                        alert(M.util.get_string('errorChangeFamily', 'block_accessibility')+': ' + textStatus +' ' + errorThrown);
                    }
                });
            });

            // Cambiar color de fondo y color de letra
            $('#accessibility_controls').on('change','#boton-color-picker-fondo, #boton-color-picker-letra',function(){
                $(this).removeClass('startEmpty');
                if($(this).attr('id') == "boton-color-picker-fondo"){
                    var color = this.value;
                    var color2 = $("#boton-color-picker-letra").val();
                }else{
                    var color2 = this.value;
                    var color = $("#boton-color-picker-fondo").val();
                }
                if(!$("#boton-color-picker-letra").hasClass('startEmpty') && !$("#boton-color-picker-fondo").hasClass('startEmpty')) {
                    color = tinycolor(color);
                    color2 = tinycolor(color2);
                    if (tinycolor.isReadable(color, color2, {level: "AAA", size: "small"})) {
                        $.ajax({
                            url: M.cfg.wwwroot + '/blocks/accessibility/changecontrast.php',
                            data: {
                                option: "change", colorFondo: color.toHex(), colorLetra: color2.toHex() },
                            beforeSend: M.block_accessibility.show_loading,
                            complete: M.block_accessibility.hide_loading,
                            success: function () {
                                M.block_accessibility.reload_stylesheet();
                                M.block_accessibility.toggle_textsizer('colour1', 'on');
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                alert(M.util.get_string('errorContrast', 'block_accessibility') + ': ' + textStatus + ' ' + errorThrown);
                            }
                        });
                        M.block_accessibility.show_message(M.util.get_string('WCAGaaaSI', 'block_accessibility'));
                    } else {
                        M.block_accessibility.show_message(M.util.get_string('WCAGaaaNO', 'block_accessibility'));
                    }
                }
            });
            $('#block_accessibility_colour1').on('click',function(){
                $.ajax({
                    url: M.cfg.wwwroot + '/blocks/accessibility/changecontrast.php',
                    data: {option: "reset" },
                    beforeSend: M.block_accessibility.show_loading,
                    success: function () {
                        location.reload();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert(M.util.get_string('errorContrastR', 'block_accessibility') + ': ' + textStatus + ' ' + errorThrown);
                    }
                });
            });

            $('#block_accessibility_colour1').on('keypress',function(){
                $.ajax({
                    url: M.cfg.wwwroot + '/blocks/accessibility/changecontrast.php',
                    data: {option: "reset" },
                    beforeSend: M.block_accessibility.show_loading,
                    success: function () {
                        location.reload();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert(M.util.get_string('errorContrastR', 'block_accessibility') + ': ' + textStatus + ' ' + errorThrown);
                    }
                });
            });

            //Cambiar Interlineado
            $('#select-line-height-custom-accessibility').on('change',function(){
                var lineheight = this.value;
                $.ajax({
                    url: M.cfg.wwwroot+'/blocks/accessibility/changelineheight.php',
                    data: {line_height:lineheight},
                    beforeSend: M.block_accessibility.show_loading,
                    complete: function(){
                        if(lineheight!="Defecto"){
                            M.block_accessibility.hide_loading();
                        }
                    },
                    success: function(){
                        M.block_accessibility.reload_stylesheet();
                        if(lineheight == "Defecto"){
                            location.reload();
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown){
                        alert(M.util.get_string('errorChangeLineHeight', 'block_accessibility')+': ' + textStatus +' ' + errorThrown);
                    }
                });
            });

            //Cambiar espaciado de palabra
            $('#select-word-spacing-custom-accessibility').on('change',function(){
                var wordspacing = this.value;
                $.ajax({
                    url: M.cfg.wwwroot+'/blocks/accessibility/changewordspacing.php',
                    data: {word_spacing:wordspacing},
                    beforeSend: M.block_accessibility.show_loading,
                    complete: function(){
                        if(wordspacing!="Defecto"){
                            M.block_accessibility.hide_loading();
                        }
                    },
                    success: function(){
                        M.block_accessibility.reload_stylesheet();
                        if(wordspacing == "Defecto"){
                            location.reload();
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown){
                        alert(M.util.get_string('errorChangeWordSpacing', 'block_accessibility')+': ' + textStatus +' ' + errorThrown);
                    }
                });
            });

            //Línea de apoyo para lectura
            if($('#reader-line-custom-accessibility').hasClass('reader-line-active')){
                $('body').prepend('<div id="reader-line-custom" style="background: #000;width: 100% !important;min-width: 100% !important;top: 20px;box-sizing: border-box;height: 12px !important;border: solid 3px #fff300;border-radius: 5px;position: absolute !important;z-index: 2147483647;"></div>');
            }

            $('#reader-line-custom-accessibility').on('click',function(){

                var activar = true;
                var boton = $(this)
                if(boton.hasClass('reader-line-active')){
                    activar = false;
                }
                $.ajax({
                    url: M.cfg.wwwroot+'/blocks/accessibility/readerline.php',
                    data: {reader_line:activar},
                    beforeSend: M.block_accessibility.show_loading,
                    complete: M.block_accessibility.hide_loading,
                    success: function(){
                        if(activar){
                            $('body').prepend('<div id="reader-line-custom" style="background: #000;width: 100% !important;min-width: 100% !important;top: 20px;box-sizing: border-box;height: 12px !important;border: solid 3px #fff300;border-radius: 5px;position: absolute !important;z-index: 2147483647;"></div>');
                            boton.addClass('reader-line-active');
                            boton.html('Apoyo Lectura');
                        }else{
                            $('#reader-line-custom').remove();
                            boton.removeClass('reader-line-active');
                            boton.html('Apoyo Lectura');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown){
                        alert(M.util.get_string('errorReaderline', 'block_accessibility')+': ' + textStatus +' ' + errorThrown);
                    }
                });
            });


            $(document).on('mousemove',function(e){
                if($('#reader-line-custom').length > 0){
                    $('#reader-line-custom').css('top', e.pageY - 26 + 'px' );
                }
            });

            //Cambiar estilo de cursor

            $('#change-cursor-custom-accessibility').on('click',function(){
                var activar = true;
                var boton = $(this)
                if(boton.hasClass('cursor-activo')){
                    activar = false;
                }
                $.ajax({
                    url: M.cfg.wwwroot+'/blocks/accessibility/changecursor.php',
                    data: {change_cursor:activar},
                    beforeSend: M.block_accessibility.show_loading,
                    complete: function(){
                        if(activar){
                            M.block_accessibility.hide_loading();
                            boton.addClass('cursor-activo');
                            boton.html('Cursor Defecto');
                        }
                    },
                    success: function(){
                        M.block_accessibility.reload_stylesheet();
                        if(!activar){
                            location.reload();
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown){
                        alert(M.util.get_string('errorCursor', 'block_accessibility')+': ' + textStatus +' ' + errorThrown);
                    }
                });
            });

            //Alarma
            var sound = new Audio( M.cfg.wwwroot+'/blocks/accessibility/alarm.mp3');
            function comprobar_alarma(){
                var ahora = Math.floor(new Date().getTime()/1000);
                if(ahora >= time){
                    clearInterval(interval);
                    $.ajax({
                        url: M.cfg.wwwroot+'/blocks/accessibility/alarm.php',
                        data: {alarm:false},
                        beforeSend: M.block_accessibility.show_loading,
                        success: function(){
                            sound.play();
                            $('#alarm-button-custom-accessibility').removeClass('alarm-activo');
                            $('#alarm-custom-accessibility').prop( "disabled", false );
                            $('#alarm-button-custom-accessibility').html('Poner Alarma');
                            M.block_accessibility.hide_loading();
                            alert("¡Alarma!");
                        },
                        error: function(jqXHR, textStatus, errorThrown){
                            alert(M.util.get_string('errorAlarma', 'block_accessibility')+': ' + textStatus +' ' + errorThrown);
                        }
                    });

                }
            }
            var time = "";
            var interval = "";
            if($('#alarm-button-custom-accessibility').hasClass('alarm-activo')){
                $('#alarm-custom-accessibility').wickedpicker({now:$('#alarm-custom-accessibility').val(),twentyFour: true});
                var today = new Date();
                var hora = $('#alarm-custom-accessibility').wickedpicker('time').split(" : ");
                today.setHours(hora[0], hora[1], 0 ,0);
                time = Math.floor(today.getTime()/1000);
                interval = setInterval(comprobar_alarma,1000);
            }else{
                $('#alarm-custom-accessibility').wickedpicker({twentyFour: true});
            }
            $('#alarm-button-custom-accessibility').on('click',function(){
                var activar = true;
                var boton = $(this);

                var tiempo = $('#alarm-custom-accessibility').wickedpicker('time');
                if(boton.hasClass('alarm-activo')){
                    activar = false;
                }else{
                    var today = new Date();
                    var hora = tiempo.split(" : ");
                    today.setHours(hora[0], hora[1], 0 ,0);
                    today = Math.floor(today.getTime()/1000);
                    var ahora = Math.floor(new Date()/1000);
                    if(ahora <= today){
                        tiempo = today;
                    }else{
                        tiempo = today + 24*60*60;
                    }

                }
                if(!activar || tiempo != ""){
                    $.ajax({
                        url: M.cfg.wwwroot+'/blocks/accessibility/alarm.php',
                        data: {alarm:activar,tiempo: tiempo},
                        beforeSend: M.block_accessibility.show_loading,
                        success: function(){
                            clearInterval(interval);
                            if(activar){
                                time = tiempo;
                                interval = setInterval(comprobar_alarma,1000);
                                boton.addClass('alarm-activo');
                                $('#alarm-custom-accessibility').prop( "disabled", true );
                                boton.html('Borrar Alarma');
                            }else{
                                boton.removeClass('alarm-activo');
                                $('#alarm-custom-accessibility').prop( "disabled", false );
                                boton.html('Fijar Alarma');
                            }
                            M.block_accessibility.hide_loading();
                        },
                        error: function(jqXHR, textStatus, errorThrown){
                            alert(M.util.get_string('errorAlarma', 'block_accessibility')+': ' + textStatus +' ' + errorThrown);
                        }
                    });
                }else{
                    M.block_accessibility.show_message("Selecciona una hora");
                }
            });

            //Modo cine
            $('#cinema-button-custom-accessibility').on('click',function(){
                var activar = true;
                var boton = $(this);
                if(boton.hasClass('cinema-activo')){
                    activar = false;
                }
                $.ajax({
                    url: M.cfg.wwwroot+'/blocks/accessibility/cinema.php',
                    data: {cinema:activar},
                    beforeSend: M.block_accessibility.show_loading,
                    complete: M.block_accessibility.hide_loading,
                    success: function(){
                        M.block_accessibility.reload_stylesheet();
                        if(activar){
                            boton.addClass('cinema-activo');
                            boton.html('Modo cine');
                        }else{
                            boton.removeClass('cinema-activo');
                            boton.html('Modo cine');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown){
                        alert(M.util.get_string('errorCinema', 'block_accessibility')+': ' + textStatus +' ' + errorThrown);
                    }
                });
            });

            $('#modo-cinema').on('click',function(){
                $.ajax({
                    url: M.cfg.wwwroot+'/blocks/accessibility/cinema.php',
                    data: {cinema:false},
                    beforeSend: M.block_accessibility.show_loading,
                    complete: M.block_accessibility.hide_loading,
                    success: function(){
                        M.block_accessibility.reload_stylesheet();
                        $('#cinema-button-custom-accessibility').removeClass('cinema-activo');
                        $('#cinema-button-custom-accessibility').html('Modo cine');

                    },
                    error: function(jqXHR, textStatus, errorThrown){
                        alert(M.util.get_string('errorCinema', 'block_accessibility')+': ' + textStatus +' ' + errorThrown);
                    }
                });
            });

            //Chequeo accesibilidad (Tota11y)
            $('#tota11y-button-custom-accessibility').on('click',function(){
                if($(this).hasClass('tota11y-activo')){
                    $('#tota11y-toolbar').hide();
                    $('.tota11y-toolbar-toggle').trigger('click');
                    $(this).removeClass('tota11y-activo');
                }else{
                    $('.tota11y-toolbar-toggle').trigger('click');
                    $('#tota11y-toolbar').show();
                    $(this).addClass('tota11y-activo');

                }

            });
        }
    };

});