jQuery(document).ready(function(){

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
            let memorySoSize = $('#so-size').val()/100;
            let availableMemory = memoryTotalSize - memoryTotalSize*memorySoSize;

            memoryDisplay
                .html(availableMemory)
                .css('font-weight','bold')
                .attr('total', availableMemory)
            ;
            //$('#nav-config-tab').prop('aria-disabled', true).addClass('disabled');
            $('#add-part-btn').prop("disabled", true);
            $('#cant-part-span').html(partitionsCount);
        })

        .on('change', '#so-size', function(){
            let memoryTotalSize = $('#memoria-size').val();
            let memorySoSize = $('#so-size').val()/100;
            let availableMemory = memoryTotalSize - memoryTotalSize*memorySoSize;

            $('#nav-partitions-tab').tab('show');
            memoryDisplay
                .html(availableMemory)
                .css('font-weight','bold')
                .attr('total', availableMemory)
            ;
            //$('#nav-config-tab').prop('aria-disabled', true).addClass('disabled');
            $('#add-part-btn').prop("disabled", true);
            $('#cant-part-span').html(partitionsCount);
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
            }
        })

        .on('change', '#part-fijas', function () {
            const $this = $(this);
            if($this.is(':checked')) {
                $('#part-variables').prop('disabled', true);
                newPartitionSection.show();
                partitionFormSection.css('width', '50%');
                tipoMemoria = 'fijas';
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
            let partitionInput = $('#new-part');
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
                    const memoryQtyInput = section.find('#memoria-size');
                    const soQtyInput = section.find('#so-size');

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
            }

            $('#cant-part-span').html(partitionsCount);
            if (memoryDisplay.html() == 0) {
                partitionInput.prop({'disabled':true, 'placeholder':'No hay mas espacio'});
                $(this).prop('disabled', true);
            }
        })

        .on('click', '#btn-partitions', function(){
            const memorySize = parseInt($('#memoria-size').val());
            const soSize = (parseInt($('#so-size').val()) * memorySize)/100;
            const algIntercambio = $('#algoritmo-planificacion').val();
            const url = '{{ path() }}'
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

            $.ajax({
                url: $url,
                type: 'POST',
                data: $formData,
                success: function(res){
                    // const $responseCode = res.code;
                    // if ($responseCode === 200) {
                    //     const $li = $('<li class="lado-right"></li>');
                    //     const $container = $('<div class="mensaje-container">');
                    //     const $emisor = $('<div>Usted dice:</div>');
                    //     const $bloque = $('<div class="bloque-mensaje">');
                    //     const $fecha = $('<div class="fecha">justo ahora</div>');
                    //     const $mensajeResponse = res.respuesta.mensaje;
                    //     const $mensaje = $('<div class="mensaje"></div>').html($mensajeResponse);
                    //     const $lineaMensaje =
                    //         $li
                    //             .html($container
                    //                 .append($bloque
                    //                     .append($emisor)
                    //                     .append($mensaje)
                    //                     .append($fecha)
                    //                 )
                    //             );
                    //     if ($respuestaBlock.css('display') === 'none') {
                    //         $respuestaBlock.show();
                    //     }
                    //     $respuestaList.append($lineaMensaje);
                    //     $textarea.val('');
                    //     $textarea.removeClass('withError');
                    //     $respuestaError.html('');
                    // } else if ($responseCode === 404) {
                    //     $respuestaError.html('*Ingrese un mensaje');
                    //     $textarea.addClass('withError');
                    // } else if ($responseCode === 500) {
                    //     $respuestaError.html('*Ha ocurrido un error, intente nuevamente');
                    //     $textarea.addClass('withError');
                    // }
                }
            });


            console.log(data);
            // $('#nav-work-tab').tab('show');
        });
});