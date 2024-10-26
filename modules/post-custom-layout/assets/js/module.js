jQuery( document ).ready( function ( $ ) {

    $('.vc_post-custom-layout').on('click', selectLayout);

    function selectLayout(e) {
        var selected_layout = $(e.currentTarget);
        var layout_name = selected_layout.attr('data-post-custom-layout');
        var editor_wrapper = $('#wpb_wpbakery');

        // add class that help us to hide some elements on a page that should not
        // be visible when layout is selected
        if(editor_wrapper) {
            var settings_layout = $('#vc_ui-panel-post-settings .vc_post-custom-layout[data-post-custom-layout=' + layout_name + ']');
            selected_layout = settings_layout;

            editor_wrapper.find('.vc_navbar').addClass('vc_post-custom-layout-selected');
            editor_wrapper.find('.metabox-composer-content').addClass('vc_post-custom-layout-selected');
        }

        selected_layout.addClass('vc-active-post-custom-layout');
        selected_layout.siblings().removeClass('vc-active-post-custom-layout');

        // set input that help us save layout values to post meta
        $('input[name=vc_post_custom_layout]').val(layout_name);
    }
});