/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
// require('./componentes/new-memoria.js');
// require('./componentes/new-procesos.js');
// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// const $ = require('jquery');
jQuery(document).ready(function ($) {
    let countPopover = 0;
    const colaListosDisplay = $('#cola-listos-display');
    const colaNuevosDisplay = $('#cola-nuevos-display');
    const colaBloqueadosDisplay = $('#cola-bloqueados-display');
    const finalizadosDisplay = $('#cola-finalizados-display');

    $("[data-toggle=popover_procesador]").on('click', function (e) {
        const ganttBody = $('.gantt-body.procesador');
        const target = $(this).attr('objetivo');
        const flecha = $(this).attr('flecha');
        const objetivoTag = $('#'+target);
        const flechaTag = $('#'+flecha);
        const objetivoDisplay = objetivoTag.css('display');

        if (objetivoDisplay === 'none') {
            const height = parseInt(ganttBody.css('height')) + parseInt(objetivoTag.css('height')) +20;
            objetivoTag.show();
            flechaTag.show();
            countPopover = countPopover +1;

            if (countPopover === 1) {
                const originalHeight = ganttBody.css('height');

                ganttBody.attr('altura', originalHeight);
                ganttBody.css('height', height);
            }
        } else {
            const altura = parseInt(ganttBody.attr('altura'));
            objetivoTag.hide();
            flechaTag.hide();
            countPopover = countPopover -1;

            if (countPopover === 0) {
                ganttBody.css('height', altura);
                ganttBody.attr('altura', 'false');
            }
        }

        e.preventDefault();
    });
    $("[data-toggle=popover_bloqueo]").on('click', function (e) {
        const ganttBody = $('.gantt-body.bloqueo');
        const target = $(this).attr('objetivo');
        const flecha = $(this).attr('flecha');
        const objetivoTag = $('#'+target);
        const flechaTag = $('#'+flecha);
        const objetivoDisplay = objetivoTag.css('display');

        if (objetivoDisplay === 'none') {
            const height = parseInt(ganttBody.css('height')) + parseInt(objetivoTag.css('height')) +20;
            objetivoTag.show();
            flechaTag.show();
            countPopover = countPopover +1;

            if (countPopover === 1) {
                const originalHeight = ganttBody.css('height');

                ganttBody.attr('altura', originalHeight);
                ganttBody.css('height', height);
            }
        } else {
            const altura = parseInt(ganttBody.attr('altura'));
            objetivoTag.hide();
            flechaTag.hide();
            countPopover = countPopover -1;

            if (countPopover === 0) {
                ganttBody.css('height', altura);
                ganttBody.attr('altura', 'false');
            }
        }

        e.preventDefault();
    });
    $('.mapa-memoria').on('click', '.particion',function (e) {
        const $this = $(this);
        const mensaje = $this.find('.mensaje');
        const displayMensaje = mensaje.css('display');

        if (displayMensaje === "none") {
            mensaje.show()
        } else {
            mensaje.hide()
        }

        e.preventDefault();

    });

    function revelarGanttProcesadorLine() {
        $(".gantt-body.procesador .gantt-line").each(function(index) {
            const that = this;
            const colaListos = $(that).find('.cola.listos .cola-listos-mostrar').html();
            const colaNuevos = $(that).find('.cola.nuevos .cola-nuevos-mostrar').html();
            const finalizado = $(that).find('.finaliza-proceso .proceso-name');
            const lineaBloquea = $(that).find('.proceso-bloquea');
            const flechaBloquea = $(that).find('.flecha-bloquea');
            const lineaSale = $(that).find('.proceso-sale');
            const flechaSale = $(that).find('.flecha');
            const t = setTimeout(function() {
                $(that).show();
                lineaBloquea.show();
                flechaBloquea.show();
                lineaSale.show();
                flechaSale.show();
                if (colaListos) {
                    colaListosDisplay.html(colaListos);
                } else {
                    colaListosDisplay.html('');
                }

                if (colaNuevos) {
                    colaNuevosDisplay.html(colaNuevos);
                } else {
                    colaNuevosDisplay.html('');
                }
                finalizadosDisplay.append(finalizado);

            }, 1000 * index);
        });
    }
    function revelarGanttBloqueoLine() {
        $(".gantt-body.bloqueo .gantt-line").each(function(index) {
            const that = this;
            const colaBloqueados = $(that).find('.cola.bloqueados .cola-bloqueados-mostrar').html();
            const lineaBloquea = $(that).find('.proceso-bloquea');
            const flechaBloquea = $(that).find('.flecha-bloquea');
            const lineaSale = $(that).find('.proceso-sale');
            const flechaSale = $(that).find('.flecha');
            const t = setTimeout(function() {
                $(that).show();
                lineaBloquea.show();
                flechaBloquea.show();
                lineaSale.show();
                flechaSale.show();

                if (colaBloqueados) {
                    colaBloqueadosDisplay.html(colaBloqueados);
                } else {
                    colaBloqueadosDisplay.html('');
                }
            }, 1000 * index);
        });
    }
    function revelarMemoriaBloque() {
        $(".mapa-memoria .memoria-bloque").each(function(index) {
            let that = this;
            let t = setTimeout(function() {
                $(that).show();
            }, 1000 * index);
        });
    }
    function revelarNumeroProcesador() {
        $(".numero-procesador").each(function(index) {
            let that = this;
            let t = setTimeout(function() {
                $(that).show();
            }, 1000 * index);
        });
    }
    function revelarNumeroBloqueo() {
        $(".numero-bloqueo").each(function(index) {
            let that = this;
            let t = setTimeout(function() {
                $(that).show();
            }, 1000 * index);
        });
    }

    revelarGanttProcesadorLine();
    revelarNumeroProcesador();
    revelarGanttBloqueoLine();
    revelarNumeroBloqueo();
    revelarMemoriaBloque();
    // $('.gantt-line').each(function(i){
        // console.log(i);
        // $(this).delay((i+100)*1000).show();
        // setTimeout(function(){
        //     $('.gantt-line').show();
        // },1000);
    // });
    // $("[data-toggle=popover]").popover({
    //     html: true,
    //     content: function () {
    //         const target = $(this).attr('objetivo');
    //         return $('#'+target).html();
    //     }
    // });
});