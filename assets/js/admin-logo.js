/** Native media picker backed by WordPress custom_logo. NADA theme. */
jQuery(function ($) {
    $('#nada-select-logo').on('click', function () {
        const frame = wp.media({ title: 'Choose site logo', library: { type: 'image' }, multiple: false });
        frame.on('select', function () {
            const image = frame.state().get('selection').first().toJSON();
            $('#nada-logo-id').val(image.id);
            $('#nada-logo-preview').empty().append($('<img>', { src: image.url, alt: '', width: 240 }));
        });
        frame.open();
    });
    $('#nada-remove-logo').on('click', function () {
        $('#nada-logo-id').val(0);
        $('#nada-logo-preview').empty();
    });
});
