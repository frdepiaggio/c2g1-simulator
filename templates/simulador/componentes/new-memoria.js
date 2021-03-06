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

    const memoriaId = $('#memory-id-span');
    const simuladorId = $('#simulador-id-span');
    const section = $('#section');
    const algoritmoIntercambioShow = $('#algoritmo-intercambio-show');
    const memoryError = $('#memoria-500-error');
    const memoryCheck = $('#memoria-check');
    const memoryDataTitle = $('#datos-memoria-titulo');
    const memoryQtyInput = section.find('#memoria-size');
    const memoryQtyError = section.find('#memoria-size-error');
    const soQtyInput = section.find('#so-size');
    const soQtyError = section.find('#so-size-error');
    const tipoError = section.find('#tipo-error');
    const tipoShow = $('#tipo-particiones-show');
    const partitionInput = $('#new-part');
    const partitionError = $('#new-part-error');
    const newPartitionSection = $('.items-particiones');
    const partitionFormSection = $('.form-particiones');
    const partitionContainer = $('#nav-partitions');
    const newPartitionBtn = partitionContainer.find('#add-part-btn');
    const memoryDisplay = $('#memory-size-span');
    const maximoSizeMemoria = $('#maxima-particion-size');
    const allProcesosInput = section.find('.form-procesos input');
    const allProcesosSelect = section.find('.form-procesos select');
    const allProcesosButton = section.find('.form-procesos button');
    let partitionsCount = 0;
    let tipoMemoria = null;
    let particionesArray = [];

    memoryDisplay.html(0);

    allProcesosInput.prop('disabled', true);
    allProcesosSelect.prop('disabled', true);
    allProcesosButton.prop('disabled', true);

    section
        //Cuando se ingresa el tamaño de memoria
        .on('change', '#memoria-size', function(){
            let memoryTotalSize = $('#memoria-size').val();
            let memorySoSize = $('#so-size').val();
            let availableMemory = memoryTotalSize - memorySoSize;

            memoryDisplay
                .html(availableMemory)
                .css('font-weight','bold')
                .attr('total', availableMemory)
            ;
            //$('#nav-config-tab').prop('aria-disabled', true).addClass('disabled');
            $('#add-part-btn').prop("disabled", true);
            $('#cant-part-span').html(partitionsCount);

            memoryQtyInput.css('border', '1px solid #ced4da');
            memoryQtyError.html('');
        })
        //Cuando se ingresa el tamaño del SO
        .on('change', '#so-size', function(){
            let partitionContainer = $('#partitions-container');
            let partitionDisplay = $("<div class='progress-bar'></div>");
            let memoryTotalSize = $('#memoria-size').val();
            let memorySoSize = $('#so-size').val();
            let availableMemory = memoryTotalSize - memorySoSize;
            let partitionPercent = parseFloat(memorySoSize * 100 / memoryTotalSize);

            partitionDisplay
                .css({
                    'width':partitionPercent+'%',
                    'background-image':'linear-gradient(to right, #434343 0%, black 100%)',
                    'color': 'white',
                    'font-weight': 'bold'
                })
                .html('<span>S.O.</span><span>'+memorySoSize+' KB</span>')
            ;
            partitionContainer.append(partitionDisplay);

            $('#nav-partitions-tab').tab('show');
            memoryDisplay
                .html(availableMemory)
                .css('font-weight','bold')
                .attr('total', availableMemory)
            ;
            //$('#nav-config-tab').prop('aria-disabled', true).addClass('disabled');
            $('#add-part-btn').prop("disabled", true);
            $('#cant-part-span').html(partitionsCount);

            soQtyInput.css('border', '1px solid #ced4da');
            soQtyError.html('');
        })
        //Cuando se elige el tipo de particiones en VARIABLES
        .on('change', '#part-variables', function () {
            const $this = $(this);
            const partitionContainer = $('#partitions-container');
            const memorySize = $('#memoria-size').val();
            const memoryTotalSize = $('#memory-size-span').html();
            const memoryPercent = parseInt(memoryTotalSize) * 100 / memorySize;
            const bestFit = $('#bf');
            let partitionDisplay = $("<div class='progress-bar'></div>");
            let random_colour =  'rgb('+ (Math.floor(Math.random() * 256)) + ','+ (Math.floor(Math.random() * 256)) + ','+ (Math.floor(Math.random() * 256)) + ')';

            if($this.is(':checked')) {
                const partitionArray = {
                    'size': memoryTotalSize,
                    'color': random_colour
                };

                bestFit.remove();
                partitionDisplay
                    .css({'width':memoryPercent + '%', 'background-color':random_colour})
                    .html('<span>P1</span><span>'+memoryTotalSize+' KB</span>')
                ;

                partitionContainer.append(partitionDisplay);
                $('#part-fijas').prop('disabled', true);
                newPartitionSection.hide();
                partitionFormSection.css('width', '100%');
                tipoMemoria = 'variables';
                particionesArray.push(partitionArray);
                memoryQtyInput.prop('disabled', true);
                soQtyInput.prop('disabled', true);
                tipoError.html('');
                tipoShow.html('Variables');
            }
        })
        //Cuando se elige el tipo de particiones en FIJAS
        .on('change', '#part-fijas', function () {
            const $this = $(this);
            const worstFit = $('#wf');
            if($this.is(':checked')) {
                worstFit.remove();
                $('#part-variables').prop('disabled', true);
                newPartitionSection.show();
                partitionFormSection.css('width', '50%');
                tipoMemoria = 'fijas';
                tipoError.html('');
                tipoShow.html('Fijas');
            }
        })
        //Cuando no hay nada en el input de nueva partición
        .on('change keyup paste', '#new-part', function(){

            if ($(this).val()) {
                $('#add-part-btn').prop("disabled", false);
            } else {
                $('#add-part-btn').prop("disabled", true);
            }
        })
        //Cuando se hace enter en el input de nueva partición
        .on('keyup', '#new-part',function(e){
            if (e.keyCode === 13) {
                $('#add-part-btn').trigger('click');
            }
        })
        //Cuando se hace click en el botón de agregar partición
        .on('click', '#add-part-btn', function(e){
            let memoryTotalSize = $('#memoria-size').val();
            let partitionContainer = $('#partitions-container');
            let partitionDisplay = $("<div class='progress-bar'></div>");
            let random_colour =  'rgb('+ (Math.floor(Math.random() * 256)) + ','+ (Math.floor(Math.random() * 256)) + ','+ (Math.floor(Math.random() * 256)) + ')';
            let availableSize = parseInt($('#memory-size-span').attr('total'));
            let partitionSize = parseInt($('#new-part').val());
            let partitionPercent = parseFloat(partitionSize * 100 / memoryTotalSize);
            let memorySize = parseInt(memoryDisplay.html());

            e.preventDefault();
            if (partitionSize > memorySize){
                alert('No hay suficiente espacio');
                partitionInput.val(null);
                $(this).prop('disabled', true);

            } else {
                const partitionArray = {
                    'size': partitionSize,
                    'color': random_colour
                };
                // Como se muestra
                partitionsCount = partitionsCount + 1;
                if (partitionsCount > 0) {
                    memoryQtyInput.prop('disabled', true);
                    soQtyInput.prop('disabled', true);
                }
                partitionDisplay
                    .css({'width':partitionPercent+'%', 'background-color':random_colour})
                    .html('<span>P'+partitionsCount+'</span><span>'+partitionSize+' KB</span>')
                ;
                adaptColor(partitionDisplay);
                partitionContainer.append(partitionDisplay);
                memoryDisplayValue = memoryDisplay.html() - partitionSize;
                partitionInput.val(null);
                $(this).prop('disabled', true);
                memoryDisplay.html(memoryDisplayValue);

                // Guardar particion en el array de particiones
                particionesArray.push(partitionArray);

                partitionError.html('');
                partitionInput.css('border', '1px solid #ced4da');
            }

            $('#cant-part-span').html(partitionsCount);
            if (memoryDisplay.html() === 0) {
                partitionInput.prop({'disabled':true, 'placeholder':'No hay mas espacio'});
                partitionContainer.css('background-color', random_colour);
                $(this).prop('disabled', true);
            }
        })
        //Cuando se hace click en el botón para Guardar
        .on('click', '#btn-partitions', function(e){
            const $this = $(this);
            const loadingIcon = $('.loading-memoria');
            const memorySize = parseInt($('#memoria-size').val());
            const soSize = parseInt($('#so-size').val());
            const algIntercambio = $('#algoritmo-intercambio').val();
            const url = $this.attr('url');
            const data = {
                'memoria': {
                    'totalSize': memorySize,
                    'soSize': soSize,
                    'tipo': tipoMemoria,
                    'particiones': particionesArray
                },
                'simulador': {
                    'algoritmo_intercambio': algIntercambio
                }
            };
            e.preventDefault();
            console.log(url);

            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                success: function(res){
                    console.log(res);
                    if (res.code === 400) { //Si hubo algun error de validación por parte del usuario
                        const errorInput = res.error;
                        if (errorInput.includes('totalSize')) {
                            memoryQtyError.html('Este campo no puede estar vacío ni ser negativo');
                            memoryQtyInput.css('border', 'solid 2px #dc3545');
                        }
                        if (errorInput.includes('soSize')) {
                            soQtyError.html('Este campo no puede estar vacío ni ser negativo');
                            soQtyInput.css('border', 'solid 2px #dc3545');
                        }
                        if (errorInput.includes('tipo')) {
                            tipoError.html('Debe elegir el tipo de particiones que tendrá la memoria');
                        }
                        if (errorInput.includes('particiones_null')) {
                            partitionError.html('Debe ingresar por lo menos una particion');
                            partitionInput.css('border', 'solid 2px #dc3545');
                        }
                        if (errorInput.includes('particiones_size')) {
                            partitionError.html('Debe completar el total de la memoria disponible');
                            partitionInput.css('border', 'solid 2px #dc3545');
                        }
                    } else if(res.code === 500) { //Si hubo algún error en el server
                        memoryError.show();
                        memoryError.html('Hubieron errores inesperados al guardar, ' +
                            'por favor recargue la página e intentelo nuevamente');
                    } else { // Si salió bien la response
                        memoriaId.html(res.newMemoriaId); //Se muestra el ID de la memoria creada
                        simuladorId.html(res.newSimuladorId); //Se muestra el ID del simulador creado
                        //Se muestra el algoritmo de intercambio
                        if (algIntercambio === 'ff') {
                            algoritmoIntercambioShow.html('First-fit');
                        } else if (algIntercambio === 'bf') {
                            algoritmoIntercambioShow.html('Best-fit');
                        } else if (algIntercambio === 'wf') {
                            algoritmoIntercambioShow.html('Worst-fit');
                        }
                        maximoSizeMemoria.html(res.maximaParticionSize);
                        $this.hide(); // Se oculta el botón
                        memoryCheck.show(); //Se muestra un check en el bloque de datos de memoria
                        memoryDataTitle.css('background-color', '#20c997'); //Fondo bloque datos memoria en verde
                        allProcesosInput.prop('disabled', false); //Se habilitan los input para carga de procesos
                        allProcesosSelect.prop('disabled', false);//Se habilitan los select para carga de procesos
                        allProcesosButton.prop('disabled', false);//Se habilitan los botones para carga de procesos
                        $('#nav-work-tab').tab('show'); //Se muestra la pestaña para la carga de procesos
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
        });
});