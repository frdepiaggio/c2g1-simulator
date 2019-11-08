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
    // $("[data-toggle=popover]").popover({
    //     html: true,
    //     content: function () {
    //         const target = $(this).attr('objetivo');
    //         return $('#'+target).html();
    //     }
    // });
});