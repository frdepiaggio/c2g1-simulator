jQuery(document).ready(function ($) {
    const elementosLink = $('.elementos');

    elementosLink.on('click', function (e) {
        const $this = $(this);
        const icon = $this.find('.icon');
        const collapseBody = $('#collapseElementos');
        let isOpen = $this.attr('isOpen');

        if (isOpen === 'true') {
            icon.addClass('fa-plus-square');
            icon.removeClass('fa-minus-square');
            $this.attr('isOpen', 'false');
        } else {
            icon.removeClass('fa-plus-square');
            icon.addClass('fa-minus-square');
            $this.attr('isOpen', 'true');
        }
    })
});