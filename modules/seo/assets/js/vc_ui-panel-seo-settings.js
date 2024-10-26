/* =========================================================
 * Copyright 2023 Wpbakery
 *
 * WPBakery Page Builder SEO panel in the navbar
 *
 * ========================================================= */
/* global vc, i18nLocale, Backbone */
if (!window.vc) {
	window.vc = {};
}

(function ($) {
	'use strict';

	var storagePrefix = 'formData';

	window.vc.PostSettingsSeoUIPanel = vc.PostSettingsSeoUIPanelView
		.vcExtendUI(vc.HelperPanelViewHeaderFooter)
		.vcExtendUI(vc.HelperPanelViewResizable)
		.vcExtendUI(vc.HelperPanelViewDraggable)
		.extend({
			el: '#vc_ui-panel-post-seo',
			panelName: 'post_seo',
			events: {
				'click [data-vc-ui-element="button-close"]': 'hide',
				'click [data-vc-ui-element="panel-tab-control"]': 'changeTab',
				'click [data-vc-ui-element="button-save"]': 'save',
				'click [data-vc-ui-element="button-minimize"]': 'toggleOpacity',
				'click .vc_icon-remove': 'removeImage',
				'change #vc_ui-seo-social .gallery_widget_attached_images_ids': 'updateImagePreview',
				'input #social-title-x, #social-title-facebook': 'updateTitlePreview',
				'input #social-description-x, #social-description-facebook': 'updateDescriptionPreview',
				'click #preview-dots, #vc_seo-title, #vc_description-container': 'focusTarget',
				'change .vc-preview-radio input[type="radio"]': 'changePreviewMode',
				'input #vc_seo-title-field, #vc_seo-description-field, #vc_seo-slug-field': 'updateGeneralPreviewText',
				'blur #vc_seo-title-field, #vc_seo-description-field': 'fillSocialInputs',
				'change #vc_focus-keyphrase-field, #vc_seo-title-field, #vc_seo-description-field, #vc_seo-slug-field, #social-title-facebook, #social-description-facebook, #social-title-x, #social-description-x': 'handleInputChange'
			},
			initialize: function () {
				_.bindAll(this,
					'fixElContainment',
					'setSize');
				this.on('setSize', this.setResize, this);
				this.setFormDataState();
			},
			render: function (model, prepend) {
				if (this.$el.is(':hidden')) {
					vc.closeActivePanel();
				}
				vc.active_panel = this;
				this.show();
			},
			show: function () {
				if (this.$el.hasClass('vc_active')) {
					return;
				}
				this.$el.addClass('vc_active');
				if (!this.draggable) {
					this.initDraggable();
				}
				this.fixElContainment();
				this.trigger('show');
				var $tabs = this.$el.find('.vc_panel-tab');
				if ($tabs.length) {
					this.$tabs = $tabs;
				}
			},
			changeTab: function (e) {
				e.preventDefault();
				var $control = $(e.currentTarget);
				var $parent = $control.parent();

				$('[data-vc-ui-element="panel-add-element-tab"].vc_active').removeClass('vc_active');
				$parent.addClass('vc_active');

				this.$tabs.filter('.vc_active').removeClass('vc_active');
				var activeIndex = $parent.data('tabIndex');
				this.$tabs.filter('[data-tab-index="' + activeIndex + '"]').addClass('vc_active');
			},
			removeImage: function (e) {
				var $control = $(e.currentTarget);
				var wrapper = $control.closest('.edit_form_line');
				var socialNetSlug = wrapper.attr('data-social-net-preview-slug');
				var preview = $('#' + socialNetSlug);
				preview.find('.wpb-social-placeholder-image').show();
				var image = preview.find('img');
				image.attr('src', '');
				wrapper.find('.gallery_widget_attached_images_ids').val('');

				if (e && e.preventDefault) {
					e.preventDefault();
				}
				$control.parent().remove();

			},
			updateImagePreview: function (e) {
				var $control = $(e.currentTarget);
				var wrapper = $control.closest('.edit_form_line');
				var src = wrapper.find('.inner img').attr('src');
				var socialNetSlug = wrapper.attr('data-social-net-preview-slug');

				if (socialNetSlug && src) {
					src = src.replace('-150x150', '');
					var preview = $('#' + socialNetSlug);
					var image = preview.find('img');
					image.attr('src', src);
					image.show();
					preview.find('.wpb-social-placeholder-image').hide();
				}
			},
			updateTitlePreview: function (e) {
				var $control = $(e.currentTarget);
				var wrapper = $control.closest('.vc_seo-social-block');
				wrapper.find('.wpb-social-net-preview .vc_social-title').text($control.val());
			},
			updateDescriptionPreview: function (e) {
				var $control = $(e.currentTarget);
				var wrapper = $control.closest('.vc_seo-social-block');
				var value = $control.val();
				wrapper.find('.wpb-social-net-preview .vc_social-description').text(value);
				wrapper.find('.vc_social-description-counter').text(value.length);
			},
			focusTarget: function (e) {
				var target = $(e.currentTarget).data('focus');
				$("#" + target).focus();
			},
			changePreviewMode: function (e) {
				var pagePreview = this.$el.find('.page-preview');
				if ('mobile' === $(e.currentTarget).val()) {
					pagePreview.removeClass('desktop-view');
				} else {
					pagePreview.addClass('desktop-view');
				}
			},
			createSlug: function (inputString) {
				var slug = inputString.toLowerCase();
				//	Remove spaces and replace with hyphens
				slug = slug.replace(/\s+/g, '-');
				//	Remove special characters
				slug = slug.replace(/[^a-zA-Z0-9\-]/g, '');
				//	Remove trailing and leading punctuation
				slug = slug.replace(/^[.,!?()[]{}<>:;]+|[.,!?()[]{}<>:;]+$/g, '');

				return slug;
			},
			updateGeneralPreviewText: function (e) {
				var value = $(e.currentTarget).val();
				if ('vc_seo-slug-field' === $(e.currentTarget).attr('id')) {
					value = this.createSlug(value);
				}
				var previewElementId = $(e.currentTarget).data('preview');
				var previewElement = this.$el.find('#' + previewElementId);
				if (previewElement) {
					previewElement.text(value);
				}
			},
			handleInputChange: function (e) {
				var trimmedValue = e.target.value.trim();
				vc.seo_storage.setResults(trimmedValue, e.target.name, storagePrefix);
			},
			setFormDataState: function () {
				this.$el.find('#vc_ui-seo-general').find('input[type="text"], textarea').each(function (index, input) {
					var inputName = $(input).attr('name');
					var inputValue = $(input).val();
					vc.seo_storage.setResults(inputValue, inputName, storagePrefix);
				});
				this.$el.find('#vc_ui-seo-social').find('input[type="text"], textarea').each(function (index, input) {
					var inputName = $(input).attr('name');
					var inputValue = $(input).val();
					vc.seo_storage.setResults(inputValue, inputName, storagePrefix);
				});
			},
			// If empty fills social inputs  with general tab data
			fillSocialInputs: function (e) {
				var value = $(e.currentTarget).val();
				var name = $(e.currentTarget).attr('name');
				var formData = vc.seo_storage.get('formData');
				var fieldMap = {
					'title': ['social-title-x', 'social-title-facebook'],
					'description': ['social-description-x', 'social-description-facebook']
				};

				if (fieldMap[name]) {
					fieldMap[name].forEach(function (field) {
						if (!formData[field]) {
							$('#' + field).val(value).trigger('input');
						}
					});
				}
			}
		});
})(window.jQuery);
