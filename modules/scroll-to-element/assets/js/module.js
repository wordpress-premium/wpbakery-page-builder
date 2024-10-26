(function ($) {
    'use strict';

    function scrollToElement() {
        var model = window.vc.latestAddedElement;
        if (!model || !model.view || !model.view.el) {
            return;
        }

        var element = model.view.el;
        var offset = 250;
        var elementTop = element.getBoundingClientRect().top;
        var iframe = document.getElementById('vc_inline-frame');
        var scrollTop = window.scrollY || document.documentElement.scrollTop;
        var offsetPosition = elementTop + scrollTop - offset;

        if (iframe) {
            // For frontend editor
            var iframeWindow = iframe.contentWindow;
            scrollTop =
                iframeWindow.scrollY ||
                iframeWindow.document.documentElement.scrollTop;
            offsetPosition = elementTop + scrollTop - offset;

            iframeWindow.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
        } else {
            // For backend editor
            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
        }
    }

    function initializeScrollLogic() {
        vc.events.on('afterLoadShortcode', _.debounce(scrollToElement, 300));
    }
    var isModuleEnabled = !window.vc_modules.hasOwnProperty('vc-scroll-to-element') || window.vc_modules['vc-scroll-to-element'];
    var isFrontendEditor = 'admin_frontend_editor' === window.vc_mode;
    if (isModuleEnabled) {
        if (isFrontendEditor) {
            // Initialize once on this event to prevent scroll on initial editor load
            vc.events.once('shortcodeView:ready', initializeScrollLogic);
        } else {
            vc.events.on('shortcodeView:ready', _.debounce(scrollToElement, 300));
        }
    }
})(window.jQuery);
