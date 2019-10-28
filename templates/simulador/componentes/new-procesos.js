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
    const algoritmoPlanificacionSelect = $('#algoritmo-planificacion');
    const quantumContainer = $('.quantum-container');
    const quantumInput = $('#quantum-input');
    const quantumError = $('#quantum-error');
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
    const procesosTableRow = $('#procesos-table-body');
    const procesosTableNull = $('#procesos-table-null');
    const maximoSizeParticion = $('#maxima-particion-size');
    const procesosError = $('#procesos-500-error');

    section
        .on('change', '#algoritmo-planificacion', function () {
            const $this = $(this);

            if($this.val() === 'rr') {
                quantumContainer.show();
            } else {
                quantumContainer.hide();
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
        .on('keyup', '#tamanio-input',function(e){
            if (e.keyCode === 13) {
                $('#btn-new-proceso').trigger('click');
            }
        })
        .on('click', '#btn-new-proceso', function (e) {
            const $this = $(this);
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
                    console.log(res);
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

                        // memoriaId.html(res.newMemoriaId); //Se muestra el ID de la memoria creada
                        // simuladorId.html(res.newSimuladorId); //Se muestra el ID del simulador creado
                        // //Se muestra el algoritmo de intercambio
                        // if (algIntercambio === 'ff') {
                        //     algoritmoIntercambioShow.html('First-fit');
                        // } else if (algIntercambio === 'bf') {
                        //     algoritmoIntercambioShow.html('Best-fit');
                        // } else if (algIntercambio === 'wf') {
                        //     algoritmoIntercambioShow.html('Worst-fit');
                        // }
                        // maximoSizeMemoria.html(res.maximaParticionSize);
                        // $this.hide(); // Se oculta el botón
                        // memoryCheck.show(); //Se muestra un check en el bloque de datos de memoria
                        // memoryDataTitle.css('background-color', '#20c997'); //Fondo bloque datos memoria en verde
                        // $('#nav-work-tab').tab('show'); //Se muestra la pestaña para la carga de procesos
                    }
                }
            });

            e.preventDefault();
        })
});