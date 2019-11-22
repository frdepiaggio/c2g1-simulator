jQuery(document).ready(function ($) {

    function adaptColor(selector) {
        var rgb = $(selector).css("background-color");

        if (rgb.match(/^rgb/)) {
            var a = rgb.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+(?:\.\d+)?))?\)$/),
                r = a[1],
                g = a[2],
                b = a[3];
        }
        var hsp = Math.sqrt(
            0.299 * (r * r) +
            0.587 * (g * g) +
            0.114 * (b * b)
        );
        if (hsp > 127.5) {
            $(selector).css('color', 'black');
        } else {
            $(selector).css('color', 'white');
        }
    }

    const section = $('#section');
    const bloquePrincipal = $('#bloque-principal');
    const procesosErrorGeneral = $('#procesos-error');
    const algoritmoPlanificacionShow = $('#algoritmo-planificacion-show');
    const algoritmoPlanificacionSelect = $('#algoritmo-planificacion');
    const algoritmoPlanificacionError = $('#algoritmo-planificacion-error');
    const quantumContainer = $('.quantum-container');
    const quantumInput = $('#quantum-input');
    const quantumError = $('#quantum-error');
    const quantumShow = $('#quantum-show');
    const taInput = $('#ta-input');
    const taError = $('#ta-error');
    const irrupcion1Input = $('#irrupcion1-input');
    const irrupcion1Error = $('#irrupcion1-error');
    const irrupcion2Input = $('#irrupcion2-input');
    const irrupcion2Error = $('#irrupcion2-error');
    const esInput = $('#entradas-salidas-input');
    const esError = $('#entradas-salidas-error');
    const procesoSizeInput = $('#tamanio-input');
    const procesoSizeError = $('#tamanio-error');
    const procesoPrioridadSelect = $('#proceso-prioridad');
    const procesoPrioridadError = $('#prioridad-error');
    const procesoPrioridadClass = $('.prioridad');
    const procesosTableRow = $('#procesos-table-body');
    const procesosTableNull = $('#procesos-table-null');
    const maximoSizeParticion = $('#maxima-particion-size');
    const procesosError = $('#procesos-500-error');
    const procesosCheck = $('#procesos-check');
    const procesosDataTitle = $('#datos-procesos-titulo');
    const simuladorCheck = $('#simulador-check');
    const simuladorDataTitle = $('#datos-simulador-titulo');
    const colasContainer = $('.colas-multinivel');
    const quantumColaAlta = $('.cola-alta-quantum-container');
    const quantumColaAltaError = $('#cola-alta-quantum-error');
    const quantumColaMediaError = $('#cola-media-quantum-error');
    const quantumColaBajaError = $('#cola-baja-quantum-error');
    const quantumColaMedia = $('.cola-media-quantum-container');
    const quantumColaBaja = $('.cola-baja-quantum-container');
    const colaAltaInput = $('#cola-alta-input');
    const colaAltaError = $('#cola-alta-error');
    const colaAltaQuantumInput = $('#cola-alta-quantum-input');
    const colaMediaInput = $('#cola-media-input');
    const colaMediaError = $('#cola-media-error');
    const colaMediaQuantumInput = $('#cola-media-quantum-input');
    const colaBajaInput = $('#cola-baja-input');
    const colaBajaError = $('#cola-baja-error');
    const colaBajaQuantumInput = $('#cola-baja-quantum-input');
    let hasProcesos = false;

    section
        .on('change', '#cola-alta-input',function () {
            const $this = $(this);
            if ($this.val() === 'rr') {
                quantumColaAlta.show();
            } else {
                quantumColaAlta.hide()
            }
        })
        .on('change', '#cola-media-input',function () {
            const $this = $(this);
            if ($this.val() === 'rr') {
                quantumColaMedia.show();
            } else {
                quantumColaMedia.hide()
            }
        })
        .on('change', '#cola-baja-input',function () {
            const $this = $(this);
            if ($this.val() === 'rr') {
                quantumColaBaja.show();
            } else {
                quantumColaBaja.hide()
            }
        })
        .on('change', '#algoritmo-planificacion', function () {
            const $this = $(this);

            if($this.val() === 'rr') {
                quantumContainer.show();
            } else {
                quantumContainer.hide();
            }
            if($this.val() === 'multinivel') {
                colasContainer.show();
                procesoPrioridadClass.show();
            } else {
                colasContainer.hide();
                procesoPrioridadClass.hide();
            }
        })
        .on('change','#ta-input', function () {
            taError.html('');
            taInput.css('border', '1px solid #ced4da');
        })
        .on('change','#irrupcion1-input', function () {
            irrupcion1Error.html('');
            irrupcion1Input.css('border', '1px solid #ced4da');
        })
        .on('change','#irrupcion2-input', function () {
            irrupcion2Error.html('');
            irrupcion2Input.css('border', '1px solid #ced4da');
        })
        .on('change','#entradas-salidas-input', function () {
            esError.html('');
            esInput.css('border', '1px solid #ced4da');
        })
        .on('change','#tamanio-input', function () {
            procesoSizeError.html('');
            procesoSizeInput.css('border', '1px solid #ced4da');
        })
        .on('change','#proceso-prioridad', function () {
            procesoPrioridadError.html('');
            procesoPrioridadSelect.css('border', '1px solid #ced4da');
        })
        .on('keyup', '#tamanio-input',function(e){
            if (e.keyCode === 13) {
                $('#btn-new-proceso').trigger('click');
            }
        })
        .on('click', '#btn-new-proceso', function (e) {
            const $this = $(this);
            const loadingIcon = $('.loading-proceso');
            const simuladorId = $('#simulador-id-span').html();
            const ta = parseInt(taInput.val());
            const ti1 = parseInt(irrupcion1Input.val());
            const ti2 = parseInt(irrupcion2Input.val());
            const es = parseInt(esInput.val());
            const size = parseInt(procesoSizeInput.val());
            const prioridad = parseInt(procesoPrioridadSelect.val());
            const url = $this.attr('url');

            const data = {
                'proceso': {
                    'id_simulador': parseInt(simuladorId),
                    'algoritmo_planificacion': algoritmoPlanificacionSelect.val(),
                    'maximo-size-particion': parseInt(maximoSizeParticion.html()),
                    'ta': ta,
                    'ti1': ti1,
                    'es': es,
                    'ti2': ti2,
                    'size': size,
                    'prioridad': prioridad
                }
            };

            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                success: function(res){
                    if (res.code === 400) { //Si hubo algun error de validación por parte del usuario
                        const errorInput = res.error;
                        if (errorInput.includes('ta')) {
                            taError.html('Este campo no puede estar vacío ni ser negativo');
                            taInput.css('border', 'solid 2px #dc3545');
                        }
                        if (errorInput.includes('ti1')) {
                            irrupcion1Error.html('Este campo no puede estar vacío ni ser menor o igual a cero');
                            irrupcion1Input.css('border', 'solid 2px #dc3545');
                        }
                        if (errorInput.includes('ti2')) {
                            irrupcion2Error.html('Este campo no puede estar vacío ni ser menor o igual a cero');
                            irrupcion2Input.css('border', 'solid 2px #dc3545');
                        }
                        if (errorInput.includes('es')) {
                            esError.html('Este campo no puede estar vacío ni ser menor o igual a cero');
                            esInput.css('border', 'solid 2px #dc3545');
                        }
                        if (errorInput.includes('size_null')) {
                            procesoSizeError.html('Este campo no puede estar vacío');
                            procesoSizeInput.css('border', 'solid 2px #dc3545');
                        }
                        if (errorInput.includes('size_max')) {
                            procesoSizeError.html('No puede superar el tamaño de la particion más grande ('+
                                parseInt(maximoSizeParticion.html()) + 'KB)' );
                            procesoSizeInput.css('border', 'solid 2px #dc3545');
                        }
                        if (errorInput.includes('prioridad')) {
                            procesoPrioridadError.html('Este campo no puede estar vacío');
                            procesoPrioridadSelect.css('border', 'solid 2px #dc3545');
                        }
                    }
                    else if(res.code === 500) { //Si hubo algún error en el server
                        procesosError.show();
                        procesosError.html('Hubieron errores inesperados al guardar, ' +
                            'por favor recargue la página e intentelo nuevamente');
                    } else { // Si salió bien la response
                        const tr = $('<tr></tr>');
                        const tdId = $('<td></td>').html(res.newProcesoId);
                        const tdTa = $('<td></td>').html(ta);
                        const tdTi1 = $('<td></td>').html(ti1);
                        const tdEs = $('<td></td>').html(es);
                        const tdTi2 = $('<td></td>').html(ti2);
                        const tdSize = $('<td></td>').html(size);
                        const tdPrioridad = $('<td></td>').html(prioridad);

                        hasProcesos = true;
                        procesosTableNull.remove();

                        tr.append(tdId,tdTa,tdTi1,tdEs,tdTi2,tdSize,tdPrioridad);
                        procesosTableRow.append(tr);

                        taInput.val(null);
                        irrupcion1Input.val(null);
                        irrupcion2Input.val(null);
                        esInput.val(null);
                        procesoSizeInput.val(null);
                        procesoPrioridadSelect.val(null);
                        taInput.focus();
                        procesosErrorGeneral.html('');
                    }
                },
                beforeSend: function(){
                    loadingIcon.show();
                    $this.prop("disabled", true);
                    $('button').prop("disabled", true);
                    $('input').prop("disabled", true);
                },
                complete: function(){
                    loadingIcon.hide();
                    $this.prop("disabled", false);
                    $('button').prop("disabled", false);
                    $('input').prop("disabled", false);
                    taInput.focus();
                }
            });

            e.preventDefault();
        })
        .on('click', '#btn-simulador-save', function (e) {
            const $this = $(this);
            const loadingIcon = $('.loading-simulador');
            const simuladorId = $('#simulador-id-span').html();
            const algoritmoPlanificacion = algoritmoPlanificacionSelect.val();
            const quantumValue = quantumInput.val();
            const url = $this.attr('url');
            const tituloFinal = '<h2 class="final">Finalizar Simulador</h2>';
            const mensajeFinal = '<div class="finalizar"><p>El simulador ha sido cargado correctamente con los datos presentes en las ' +
                'secciones de la derecha y está listo para ejecutarse.</p><p>Para continuar haga click en el siguiente' +
                ' botón:</p></div>';
            const buttonFinal = '<div class="text-center"><a href="/simular/'+ simuladorId +'" class="btn btn-primary fin">' +
                '<i class="fas fa-power-off"></i> Iniciar simulación' +
                '</a></div>';
            const colaAltaValue = $('#cola-alta-input').val();
            const colaAltaQuantumValue = $('#cola-alta-quantum-input').val();
            const colaMediaValue = $('#cola-media-input').val();
            const colaMediaQuantumValue = $('#cola-media-quantum-input').val();
            const colaBajaValue = $('#cola-baja-input').val();
            const colaBajaQuantumValue = $('#cola-baja-quantum-input').val();
            const data = {
                'simulador': {
                    'id': simuladorId,
                    'algoritmo_planificacion': algoritmoPlanificacion,
                    'quantum': quantumValue,
                    'cola_alta': colaAltaValue,
                    'cola_alta_quantum': colaAltaQuantumValue,
                    'cola_media': colaMediaValue,
                    'cola_media_quantum': colaMediaQuantumValue,
                    'cola_baja': colaBajaValue,
                    'cola_baja_quantum': colaBajaQuantumValue,
                }
            };
            console.log(data);
            if (hasProcesos) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    success: function(res){
                        console.log(res);
                        if (res.code === 400) { //Si hubo algun error de validación por parte del usuario
                            const errorInput = res.error;
                            if (errorInput.includes('algoritmo_planificacion')) {
                                algoritmoPlanificacionError.html('Este campo no puede estar vacío');
                                algoritmoPlanificacionSelect.css('border', 'solid 2px #dc3545');
                            }
                            if (errorInput.includes('quantum')) {
                                quantumError.html('Este campo no puede estar vacío ni ser menor o igual a cero');
                                quantumInput.css('border', 'solid 2px #dc3545');
                            }
                            if (errorInput.includes('cola_alta_quantum')) {
                                quantumColaAltaError.html('Este campo no puede estar vacío');
                                colaAltaQuantumInput.css('border', 'solid 2px #dc3545');
                            }
                            if (errorInput.includes('cola_media_quantum')) {
                                quantumColaMediaError.html('Este campo no puede estar vacío');
                                colaMediaQuantumInput.css('border', 'solid 2px #dc3545');
                            }
                            if (errorInput.includes('cola_baja_quantum')) {
                                quantumColaBajaError.html('Este campo no puede estar vacío');
                                colaBajaQuantumInput.css('border', 'solid 2px #dc3545');
                            }
                            if (errorInput.includes('cola_alta')) {
                                colaAltaError.html('Este campo no puede estar vacío');
                                colaAltaInput.css('border', 'solid 2px #dc3545');
                            }
                            if (errorInput.includes('cola_media')) {
                                colaMediaError.html('Este campo no puede estar vacío');
                                colaMediaInput.css('border', 'solid 2px #dc3545');
                            }
                            if (errorInput.includes('cola_baja')) {
                                colaBajaError.html('Este campo no puede estar vacío');
                                colaBajaInput.css('border', 'solid 2px #dc3545');
                            }
                        }
                        else if(res.code === 200) { // Si salió bien la response
                            if (algoritmoPlanificacion === 'fcfs') {
                                algoritmoPlanificacionShow.html('FCFS');
                                quantumShow.html('No corresponde');
                            } else if (algoritmoPlanificacion === 'rr') {
                                algoritmoPlanificacionShow.html('Round-Robin');
                                quantumShow.html(quantumValue);
                            } else if (algoritmoPlanificacion === 'prioridades') {
                                algoritmoPlanificacionShow.html('Prioridades');
                                quantumShow.html('No corresponde');
                            } else if  (algoritmoPlanificacion === 'multinivel') {
                                algoritmoPlanificacionShow.html('Colas multinivel');
                                quantumShow.html('No corresponde');
                            }

                            bloquePrincipal.html(tituloFinal);
                            bloquePrincipal.append(mensajeFinal);
                            bloquePrincipal.append(buttonFinal);
                            simuladorCheck.show(); //Se muestra un check en el bloque de datos del simulador
                            simuladorDataTitle.css('background-color', '#20c997'); //Fondo bloque datos memoria en verde
                            procesosCheck.show(); //Se muestra un check en el bloque de datos de procesos
                            procesosDataTitle.css('background-color', '#20c997'); //Fondo bloque datos memoria en verde


                        } else { //Si hubo algún error en el server
                            procesosError.show();
                            procesosError.html('Hubieron errores inesperados al guardar, ' +
                                'por favor recargue la página e intentelo nuevamente');
                        }
                    },
                    beforeSend: function(){
                        loadingIcon.show();
                        $this.prop("disabled", true);
                        $('button').prop("disabled", true);
                        $('input').prop("disabled", true);
                    },
                    complete: function(){
                        loadingIcon.hide();
                        $this.prop("disabled", false);
                        $('button').prop("disabled", false);
                        $('input').prop("disabled", false);
                    }
                });
            } else {
                procesosErrorGeneral.html('Se necesita cargar por lo menos un proceso')
            }
        })
});