/* global vc, Backbone */
if (!window.vc) {
	window.vc = {};
}

(function ($) {
	'use strict';

	// Store and manage state for
	// SEO fields data and analysis results data
	// via Backbone Model
	var SeoStorage = Backbone.Model.extend({
		defaults: {
			formData: {
				keyphrase: '',
				title: '',
				description: '',
				slug: '',
				isUsedKeyphrase: ''
			},
			results: []
		},
		setResults: function (item, type, state) {
			var currentState = this.get(state);
			if ('focus-keyphrase' === type) {
				type = 'keyphrase';
				if (item && item !== this.get(state).keyphrase) {
					var _this = this;
					var data = {
						action: 'wpb_seo_check_key_phrase',
						key_phrase: item,
						post_id: window.vc_post_id,
						_vcnonce: window.vcAdminNonce
					};
					$.ajax({
						type: 'POST',
						url: window.ajaxurl,
						data: data
					}).done(function (response) {
						if (response.success) {
							currentState.isUsedKeyphrase = response.data;
							_this.set(state, currentState);
							_this.trigger('formData', 'change', currentState);
						}
					}).fail(function (response) {
						console.error('Failed to get the previously used keyphrase response: ' + response);
					});
				}
			}
			currentState[type] = item;
			this.set(state, currentState);
			if ('formData' === state) {
				this.trigger('formData', 'change', currentState);
			}
		},
		// Custom method to add, update, or remove an item in the results array
		updateResult: function (state, title, description) {
			var item = {
				state: state,
				title: title,
				description: description
			};
			var currentResults = this.get('results').slice(); // Create a shallow copy
			// Find the index of the item in the array
			var indexToModify = currentResults.findIndex(function (result) {
				return result.title === item.title;
			});
			// If the item was found, update it in the array
			if (-1 !== indexToModify) {
				currentResults[indexToModify] = item;
				this.trigger('resultChanged', 'update', item);
			} else {
				// If the item was not found, add it to the array
				currentResults.push(item);
				this.trigger('resultChanged', 'add', item);
			}

			// Update the result array in the model
			this.set('results', currentResults);
		},
		resetResult: function (title) {
			var currentResults = this.get('results');
			var resetResults = currentResults.filter(function (result) {
				return result.title !== title;
			});
			this.set('results', resetResults);
		}
	});

	vc.seo_storage = new SeoStorage();

})(window.jQuery);
