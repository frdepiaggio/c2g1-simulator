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
    const memoryQtyInput = section.find('#memoria-size');
    const memoryQtyError = section.find('#memoria-size-error');
    const soQtyInput = section.find('#so-size');
    const soQtyError = section.find('#so-size-error');
    const tipoError = section.find('#tipo-error');
    const partitionInput = $('#new-part');
    const partitionError = $('#new-part-error');
    const newPartitionSection = $('.items-particiones');
    const partitionFormSection = $('.form-particiones');
    const partitionContainer = $('#nav-partitions');
    const newPartitionBtn = partitionContainer.find('#add-part-btn');
    const memoryDisplay = $('#memory-size-span');
    let partitionsCount = 0;
    let tipoMemoria = null;
    let particionesArray = [];

    section
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

        .on('change', '#so-size', function(){
            let memoryTotalSize = $('#memoria-size').val();
            let memorySoSize = $('#so-size').val();
            let availableMemory = memoryTotalSize - memorySoSize;

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

        .on('change', '#part-variables', function () {
            const $this = $(this);
            const partitionContainer = $('#partitions-container');
            const memoryTotalSize = $('#memory-size-span').html();
            let partitionDisplay = $("<div class='progress-bar'></div>");
            if($this.is(':checked')) {
                partitionDisplay
                    .css({'width':'100%', 'background-color':'rgba(63, 81, 181, 0.9)'})
                    .html('<span>P1</span><span>'+memoryTotalSize+' KB</span>')
                ;
                partitionContainer.html(partitionDisplay);
                $('#part-fijas').prop('disabled', true);
                newPartitionSection.hide();
                partitionFormSection.css('width', '100%');
                tipoMemoria = 'variables';
                particionesArray.push(memoryTotalSize);
                memoryQtyInput.prop('disabled', true);
                soQtyInput.prop('disabled', true);
                tipoError.html('');
            }
        })

        .on('change', '#part-fijas', function () {
            const $this = $(this);
            if($this.is(':checked')) {
                $('#part-variables').prop('disabled', true);
                newPartitionSection.show();
                partitionFormSection.css('width', '50%');
                tipoMemoria = 'fijas';
                tipoError.html('');
            }
        })

        .on('change keyup paste', '#new-part', function(){

            if ($(this).val()) {
                $('#add-part-btn').prop("disabled", false);
            } else {
                $('#add-part-btn').prop("disabled", true);
            }
        })
        .on('keyup', '#new-part',function(e){
            if (e.keyCode === 13) {
                $('#add-part-btn').trigger('click');
            }
        })
        .on('click', '#add-part-btn', function(e){
            let partitionContainer = $('#partitions-container');
            let partitionDisplay = $("<div class='progress-bar'></div>");
            let random_colour =  'rgb('+ (Math.floor(Math.random() * 256)) + ','+ (Math.floor(Math.random() * 256)) + ','+ (Math.floor(Math.random() * 256)) + ')';
            let availableSize = parseInt($('#memory-size-span').attr('total'));
            let partitionSize = parseInt($('#new-part').val());
            let partitionPercent = parseFloat(partitionSize * 100 / availableSize);
            let memorySize = parseInt($('#memory-size-span').html());

            e.preventDefault();
            console.log($(this).html());
            if (partitionSize > memorySize){
                alert('No hay suficiente espacio');
                partitionInput.val(null);
                $(this).prop('disabled', true);

            } else {

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
                particionesArray.push(partitionSize);

                partitionError.html('');
                partitionInput.css('border', '1px solid #ced4da');
            }

            $('#cant-part-span').html(partitionsCount);
            if (memoryDisplay.html() == 0) {
                partitionInput.prop({'disabled':true, 'placeholder':'No hay mas espacio'});
                partitionContainer.css('background-color', random_colour);
                $(this).prop('disabled', true);
            }
        })

        .on('click', '#btn-partitions', function(e){
            const $this = $(this);
            const memorySize = parseInt($('#memoria-size').val());
            const soSize = parseInt($('#so-size').val());
            const algIntercambio = $('#algoritmo-planificacion').val();
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
                    if (res.code === 400) {
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
                    }
                }
            });
            // $('#nav-work-tab').tab('show');
        });
});