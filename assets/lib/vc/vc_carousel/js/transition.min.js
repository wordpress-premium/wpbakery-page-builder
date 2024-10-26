/*!
* WPBakery Page Builder v7.6.0 (https://wpbakery.com)
* Copyright 2011-2024 Michael M, WPBakery
* License: Commercial. More details: http://go.wpbakery.com/licensing
*/
	// jscs:disable
	// jshint ignore: start 
(o=>{o.fn.emulateTransitionEnd=function(n){var t=!1,i=this;return o(this).one(o.support.transition.end,function(){t=!0}),setTimeout(function(){t||o(i).trigger(o.support.transition.end)},n),this},o(function(){o.support.transition=(()=>{var n,t=document.createElement("bootstrap"),i={WebkitTransition:"webkitTransitionEnd",MozTransition:"transitionend",OTransition:"oTransitionEnd otransitionend",transition:"transitionend"};for(n in i)if(void 0!==t.style[n])return{end:i[n]}})()})})(window.jQuery);