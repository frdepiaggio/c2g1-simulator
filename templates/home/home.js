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

    const
        section = $('#section'),
        proccessTable = $('#proccess-table'),
        partitionContainer = $('#nav-partitions'),
        newPartitionBtn = partitionContainer.find('#add-part-btn'),
        memoryDisplay = $('#memory-size-span')
    ;
    let partitionsCount = 0;

    section
        .on('click', '#nav-partitions-tab', function(){
            let
                memoryTotalSize = $('#memoria-size').val(),
                memorySoSize = $('#so-size').val()/100,
                availableMemory = memoryTotalSize - memoryTotalSize*memorySoSize
            ;
            memoryDisplay
                .html(availableMemory)
                .css('font-weight','bold')
                .attr('total', availableMemory)
            ;
            //$('#nav-config-tab').prop('aria-disabled', true).addClass('disabled');
            $('#add-part-btn').prop("disabled", true);
            $('#cant-part-span').html(partitionsCount);
        })

        .on('click', '#btn-config', function(){
            let
                memoryTotalSize = $('#memoria-size').val(),
                memorySoSize = $('#so-size').val()/100,
                availableMemory = memoryTotalSize - memoryTotalSize*memorySoSize
            ;
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
            let
                partitionInput = $('#new-part'),
                partitionContainer = $('#partitions-container'),
                partitionDisplay = $("<div class='progress-bar'></div>"),
                random_colour =  'rgb('+ (Math.floor(Math.random() * 256)) + ','+ (Math.floor(Math.random() * 256)) + ','+ (Math.floor(Math.random() * 256)) + ')',
                availableSize = parseInt($('#memory-size-span').attr('total')),
                partitionSize = parseInt($('#new-part').val()),
                partitionPercent = parseFloat(partitionSize * 100 / availableSize),
                memorySize = parseInt($('#memory-size-span').html())
            ;
            e.preventDefault();
            console.log($(this).html());
            if (partitionSize > memorySize){
                alert('No hay suficiente espacio');
                partitionInput.val(null);
                $(this).prop('disabled', true);

            } else {

                partitionsCount = partitionsCount + 1;
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
            }

            $('#cant-part-span').html(partitionsCount);
            if (memoryDisplay.html() == 0) {
                partitionInput.prop({'disabled':true, 'placeholder':'No hay mas espacio'});
                $(this).prop('disabled', true);
            }
        })

        .on('click', '#btn-partitions', function(){

            $('#nav-work-tab').tab('show');
        });
    let proccessesCount = 0,
        proccessQty = $('#proccess-qty')
    ;
    proccessQty.html(proccessesCount);
    proccessTable
        .on('click', '#new-proccess-btn', function(e){
            e.preventDefault();
            const
                ta = $('#proccess-ta').val(),
                ti = $('#proccess-ti').val(),
                size = $('#proccess-size').val(),
                container = $('#list-proccesses'),
                tr = $('<tr></tr>'),
                idTh = $('<th class="text-center" scope="row"></th>'),
                taTd = $('<td class="text-center"></td>'),
                tiTd = $('<td class="text-center"></td>'),
                sizeTd = $('<td colspan="2" class="text-center"></td>')
            ;
            console.log(ta);
            if (!ti || ti === 0) {
                alert('El tiempo de irrupcion del proceso no puede ser nulo o igual a cero')
            } else if (!ta) {
                alert('El tiempo de arribo del proceso no puede ser nulo')
            } else if (!size || size === 0) {
                alert('El tamaño del proceso no puede ser nulo o igual a cero')
            } else {
                proccessesCount = proccessesCount +1;
                idTh.html(proccessesCount);
                taTd.html(ta);
                tiTd.html(ti);
                sizeTd.html(size);

                tr.append(idTh,taTd,tiTd,sizeTd);
                container.append(tr);
                $('#proccess-ta').val(null);
                $('#proccess-ti').val(null);
                $('#proccess-size').val(null);
                proccessQty.html(proccessesCount);
            }

        });
});