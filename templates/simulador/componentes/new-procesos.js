jQuery(document).ready(function(){

    const proccessTable = $('#proccess-table');
    let proccessesCount = 0;
    let proccessQty = $('#proccess-qty');
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