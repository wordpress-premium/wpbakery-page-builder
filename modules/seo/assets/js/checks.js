/* global vc, i18nLocale */
if (!window.vc) {
	window.vc = {};
}

(function ($) {
	'use strict';

	var storagePrefix = 'results';
	var maxTitleLength = 600;
	var utils = vc.seo_utils;

	var checkData = {
		focusKeyphrase: {
			title: i18nLocale.focusKeywordTitle,
			checkMethod: 'checkFocusKeyphrase',
			conditions: [],
		},
		descriptionLength: {
			title: i18nLocale.seoDescription,
			checkMethod: 'checkDescriptionLength',
			conditions: [],
		},
		titleWidth: {
			title: i18nLocale.seoTitle,
			checkMethod: 'checkTitleWidth',
			conditions: [],
		},
		postTextLength: {
			title: i18nLocale.textLength,
			checkMethod: 'checkPostTextLength',
			conditions: [],
		},
		images: {
			title: i18nLocale.images,
			checkMethod: 'checkImages',
			conditions: ['content']
		},
		inboundLinks: {
			title: i18nLocale.internalLinks,
			checkMethod: 'checkInboundLinks',
			conditions: ['content']
		},
		outboundLinks: {
			title: i18nLocale.outboundLinks,
			checkMethod: 'checkOutboundLinks',
			conditions: ['content']
		},
		consecutiveSentences: {
			title: i18nLocale.consecutiveSentences,
			checkMethod: 'checkForConsecutiveSentences',
			conditions: ['text', 'content']
		},
		subheadingDistribution: {
			title: i18nLocale.subheadingDistribution,
			checkMethod: 'checkSubheadingDistribution',
			conditions: ['text', 'content']
		},
		paragraphLength: {
			title: i18nLocale.paragraphLength,
			checkMethod: 'checkParagraphLength',
			conditions: ['text', 'content']
		},
		passiveVoicePercentage: {
			title: i18nLocale.passiveVoice,
			checkMethod: 'checkPassiveVoicePercentage',
			conditions: ['text', 'content']
		},
		checkSentenceLength: {
			title: i18nLocale.sentenceLength,
			checkMethod: 'checkSentenceLength',
			conditions: ['text', 'content']
		},
		keyphraseInTitle: {
			title: i18nLocale.keyphraseInTitleText,
			checkMethod: 'checkKeyphraseInTitle',
			conditions: ['title', 'keyphrase']
		},
		keyphraseInDescription: {
			title: i18nLocale.keyphraseInDescriptionText,
			checkMethod: 'checkKeyphraseInDescription',
			conditions: ['description', 'keyphrase']
		},
		keyphraseInSlug: {
			title: i18nLocale.keyphraseInSlug,
			checkMethod: 'checkKeyphraseInSlug',
			conditions: ['slug', 'keyphrase']
		},
		keyphraseInImages: {
			title: i18nLocale.imageKeyphrase,
			checkMethod: 'checkKeyphraseInImages',
			conditions: ['content', 'keyphrase', 'images']
		},
		keyphraseDensity: {
			title: i18nLocale.keyphraseDensity,
			checkMethod: 'checkKeyphraseDensity',
			conditions: ['content', 'keyphrase', 'text']
		},
		keyphraseInIntroduction: {
			title: i18nLocale.keyphraseInIntroductionText,
			checkMethod: 'checkKeyphraseInIntroduction',
			conditions: ['content', 'keyphrase', 'text']
		},
		previouslyUsedKeyphrase: {
			title: i18nLocale.previouslyUsedKeyphrase,
			checkMethod: 'checkPreviouslyUsedKeyphrase',
			conditions: ['keyphrase']
		}
	};

	vc.seo_checks = {
		$wpbContentWrapper: null,
		analyzeContent: function ($contentWrapper) {
			this.$wpbContentWrapper = $contentWrapper;
			var $content = this.$wpbContentWrapper.find('>div:not(#vc_no-content-helper)');
			var $text = $content.find('p');
			var $images = $content.find('img');
			var formData = vc.seo_storage.get('formData');
			var conditions = {
				content: $content.length,
				text: $text.length,
				images: $images.length,
				keyphrase: formData.keyphrase,
				title: formData.title,
				description: formData.description,
				slug: formData.slug
			};
			var areConditionsAllowed = function (key) {
				return checkData[key].conditions.every(function (condition) {
					return conditions[condition];
				});
			};
			for (var key in checkData) {
				if (checkData[key]) {
					if (areConditionsAllowed(key)) {
						var method = checkData[key].checkMethod;
						this[method](checkData[key].title);
					} else {
						vc.seo_storage.resetResult(checkData[key].title);
					}
				}
			}
		},
		checkTitleWidth: function (title) {
			var state = 'problems';
			var description = i18nLocale.seoTitleWidthTooLong;
			var titleWidth = utils.measureTextWidth(vc.seo_storage.get('formData').title);

			if (!titleWidth) {
				description = i18nLocale.seoTitleEmpty;
			} else if (titleWidth < maxTitleLength) {
				state = 'success';
				description = i18nLocale.goodJob;
			}

			vc.seo_storage.updateResult(state, title, description, storagePrefix);
		},
		checkFocusKeyphrase: function (title) {
			var state = 'problems';
			var description = i18nLocale.noFocusKeyword;

			if (vc.seo_storage.get('formData').keyphrase) {
				state = 'success';
				description = i18nLocale.goodJob;
			}

			vc.seo_storage.updateResult(state, title, description, storagePrefix);
		},
		checkDescriptionLength: function (title) {
			var state = 'problems';
			var description = '';

			if (!vc.seo_storage.get('formData').description.length) {
				description = i18nLocale.seoDescriptionEmpty;
			} else if (vc.seo_storage.get('formData').description.length < 120) {
				state = 'warnings';
				description = i18nLocale.seoDescriptionTooShort;
			} else if (vc.seo_storage.get('formData').description.length > 120 && vc.seo_storage.get('formData').description.length <= 156) {
				state = 'success';
				description = i18nLocale.wellDone;
			} else if (vc.seo_storage.get('formData').description.length > 156) {
				state = 'warnings';
				description = i18nLocale.seoDescriptionTooLong;
			}

			vc.seo_storage.updateResult(state, title, description, storagePrefix);
		},
		checkKeyphraseInTitle: function (title) {
			var state = 'problems';
			var description = i18nLocale.keyphraseInTitleEmpty.replace('%1$s', vc.seo_storage.get('formData').keyphrase);
			var seoTitle = vc.seo_storage.get('formData').title.toLowerCase();
			var seoKeyphrase = vc.seo_storage.get('formData').keyphrase.trim().toLowerCase();
			var result = utils.findKeyphrase(vc.seo_storage.get('formData').title, vc.seo_storage.get('formData').keyphrase);
			var startsWithKeyphrase = 0 === seoTitle.indexOf(seoKeyphrase);

			if (result.found) {
				if(startsWithKeyphrase) {
					state = 'success';
					description = i18nLocale.goodJob;
				} else {
					state = 'warnings';
					description = i18nLocale.keyphraseInTitleWarn;
				}
			}

			vc.seo_storage.updateResult(state, title, description, storagePrefix);
		},
		checkKeyphraseInDescription: function (title) {
			var state = 'problems';
			var description = i18nLocale.keyphraseInDescriptionEmpty;
			var result = utils.findKeyphrase(vc.seo_storage.get('formData').description, vc.seo_storage.get('formData').keyphrase);

			if (result.found) {
				state = 'success';
				description = i18nLocale.keyphraseInDescriptionSuccess;
			}

			vc.seo_storage.updateResult(state, title, description, storagePrefix);
		},
		checkKeyphraseInSlug: function (title) {
			var state = 'warnings';
			var description = i18nLocale.keyphraseInSlugProblem;
			var result = utils.findKeyphraseInSlug(vc.seo_storage.get('formData').slug, vc.seo_storage.get('formData').keyphrase);

			if (result.found) {
				state = 'success';
				description = i18nLocale.greatWork;
			}

			vc.seo_storage.updateResult(state, title, description, storagePrefix);
		},
		checkKeyphraseInImages: function (title) {
			var state = 'success';
			var description = '';
			var imagesData = utils.findKeyphraseInAltTag(this.images, vc.seo_storage.get('formData').keyphrase);
			var percentage = imagesData.percentage;
			var imagesWithKeyphrase = imagesData.imagesWithKeyphrase;

			// Check if there are more than 4 images
			if (this.images.length > 4) {
				// Check if the percentage falls within the specified range (30% to 70%)
				if (percentage >= 30 && percentage <= 70) {
					description = i18nLocale.goodJob;
				} else if (percentage > 70) {
					state = 'warnings';
					description = i18nLocale.imageKeyphraseTooMuch.replace('%1$s', this.images.length).replace('%2$s', imagesWithKeyphrase);
				} else {
					state = 'warnings';
					description = i18nLocale.imageKeyphraseNotEnough.replace('%1$s', this.images.length).replace('%2$s', imagesWithKeyphrase);
				}
			} else {
				// Check if at least one image contains the keyphrase
				if (imagesWithKeyphrase > 0) {
					description = i18nLocale.goodJob;
				} else {
					state = 'warnings';
					description = i18nLocale.imageKeyphraseMissing;
				}
			}

			vc.seo_storage.updateResult(state, title, description, storagePrefix);
		},
		checkKeyphraseDensity: function (title) {
			var state = 'success';
			var description = '';
			var keyphraseInText = utils.findKeyphraseDensity(this.textContent, vc.seo_storage.get('formData').keyphrase);
			var keyphraseOccurrences = keyphraseInText.keyphraseOccurrences;
			var advisedMinOccurrences = keyphraseInText.advisedMinOccurrences;
			var advisedMaxOccurrences = keyphraseInText.advisedMaxOccurrences;

			if (keyphraseOccurrences.length < advisedMinOccurrences) {
				state = 'problems';
				description = i18nLocale.keyphraseDensityNotEnough.replace('%1$s', keyphraseOccurrences.length).replace('%2$s', advisedMinOccurrences);
			} else if (keyphraseOccurrences.length >= advisedMinOccurrences && keyphraseOccurrences.length <= advisedMaxOccurrences) {
				description = i18nLocale.keyphraseDensitySuccess.replace('%1$s', keyphraseOccurrences.length);
			} else {
				state = 'problems';
				description = i18nLocale.keyphraseDensityTooMuch.replace('%1$s', keyphraseOccurrences.length).replace('%2$s', advisedMaxOccurrences);
			}

			vc.seo_storage.updateResult(state, title, description, storagePrefix);
		},
		checkInboundLinks: function (title) {
			var inboundLinks = [];
			var goodJob = i18nLocale.goodJob;
			var noInboundLinksDescription = i18nLocale.noInternalLinks;
			var $links = this.$wpbContentWrapper.find(">div:not(#vc_no-content-helper) a:not([class*='vc_control'])");
			var inboundLinksState = 'success';
			var inboundLinksDescription = goodJob;

			$.each($links, function (i, link) {
				if (window.location.host === link.host) {
					inboundLinks.push(link);
				}
			});
			if (!inboundLinks.length) {
				inboundLinksState = 'problems';
				inboundLinksDescription = noInboundLinksDescription;
			}

			vc.seo_storage.updateResult(inboundLinksState, title, inboundLinksDescription, storagePrefix);
		},
		checkOutboundLinks: function (title) {
			var outboundLinks = [];
			var goodJob = i18nLocale.goodJob;
			var noOutboundLinksDescription = i18nLocale.noOutboundLinks;
			var $links = this.$wpbContentWrapper.find(">div:not(#vc_no-content-helper) a:not([class*='vc_control'])");
			var outboundLinksState = 'success';
			var outboundLinksDescription = goodJob;

			$.each($links, function (i, link) {
				if (window.location.host !== link.host) {
					outboundLinks.push(link);
				}
			});
			if (!outboundLinks.length) {
				outboundLinksState = 'problems';
				outboundLinksDescription = noOutboundLinksDescription;
			}

			vc.seo_storage.updateResult(outboundLinksState, title, outboundLinksDescription, storagePrefix);
		},
		checkImages: function (title) {
			var state = 'problems';
			var description = i18nLocale.noImages;
			var images = this.$wpbContentWrapper.find('>div:not(#vc_no-content-helper) img');
			this.images = images;

			if (images.length) {
				state = 'success';
				description = i18nLocale.goodJob;
			}

			vc.seo_storage.updateResult(state, title, description, storagePrefix);
		},
		checkPostTextLength: function (title) {
			var state = 'problems';
			var description = i18nLocale.textLengthLess;
			var postContent = this.$wpbContentWrapper.html();
			var textContent = utils.getTextContent(postContent);
			this.textContent = textContent;
			var textLength = textContent.split(/\s+/).length;
			var isContentEmpty = (1 === textLength) && '' === utils.getTextContent(postContent).split(/\s+/)[0];

			if (isContentEmpty) {
				textLength = 0;
			}
			if (textLength < 200) {
				description = description.replace('%1$s', textLength).replace('%2$s', 'far below');
			} else if (textLength >= 200 && textLength < 250) {
				description = description.replace('%1$s', textLength).replace('%2$s', 'below');
			} else if (textLength >= 250 && textLength < 300) {
				description = description.replace('%1$s', textLength).replace('%2$s', 'slightly below');
				state = 'warnings';
			} else if (textLength >= 300) {
				description = window.sprintf(i18nLocale.textLengthSuccess, textLength);
				state = 'success';
			}

			vc.seo_storage.updateResult(state, title, description, storagePrefix);
		},
		checkKeyphraseInIntroduction: function (title) {
			var state = 'problems';
			var description = i18nLocale.keyphraseInIntroductionEmpty;
			var firstParagraph = utils.getParagraphs(this.$wpbContentWrapper).first().text();
			var result = utils.findKeyphrase(firstParagraph, vc.seo_storage.get('formData').keyphrase);

			if (result.found) {
				state = 'success';
				description = i18nLocale.wellDone;
			}

			vc.seo_storage.updateResult(state, title, description, storagePrefix);
		},
		checkPassiveVoicePercentage: function (title) {
			var paragraphs = utils.getParagraphs(this.$wpbContentWrapper);
			var percentage = utils.getPassiveVoicePercentage(paragraphs);
			var state = 'problems';
			var description = window.sprintf(i18nLocale.passiveVoiceError, percentage + '%');

			if (percentage < 10) {
				state = 'success';
				description = i18nLocale.passiveVoiceSuccess;
			}

			vc.seo_storage.updateResult(state, title, description, storagePrefix);
		},
		checkForConsecutiveSentences: function (title) {
			var hasConsecutiveSentences = utils.hasConsecutiveSentences(this.textContent);
			var state = hasConsecutiveSentences.state ? 'problems' : 'success';
			var description = i18nLocale.consecutiveSentencesSuccess;

			if (hasConsecutiveSentences.state) {
				description = i18nLocale.consecutiveSentencesFail.replace('%1$s', hasConsecutiveSentences.consecutiveCount);
			}

			vc.seo_storage.updateResult(state, title, description, storagePrefix);
		},
		checkParagraphLength: function (title) {
			var state = 'success';
			var description = i18nLocale.paragraphLengthSuccess;
			var paragraphs = utils.getParagraphs(this.$wpbContentWrapper);
			var longParagraphsCount = 0;

			paragraphs.each(function (index, element) {
				var paragraphText = $(element).text();
				// Check the word count for each paragraph
				var wordCount = utils.getWordsCount(paragraphText);
				if (wordCount > 150) {
					longParagraphsCount++;
				}
			});
			if (longParagraphsCount > 0) {
				description = window.sprintf(i18nLocale.paragraphLengthError, longParagraphsCount);
				state = 'problems';
			}

			vc.seo_storage.updateResult(state, title, description, storagePrefix);
		},
		checkSentenceLength: function (title) {
			var state = 'success';
			var description = i18nLocale.great;
			var paragraphs = utils.getParagraphs(this.$wpbContentWrapper);
			var totalSentences = 0;
			var longSentencesCount = 0;

			paragraphs.each(function (index, paragraph) {
				var sentences = utils.getSentences($(paragraph).text());
				totalSentences += sentences.length;
				// Check the word count for each sentence
				sentences.forEach(function (sentence) {
					var wordCount = utils.getWordsCount(sentence);
					if (wordCount > 20) {
						longSentencesCount++;
					}
				});
			});
			var longSentencePercentage = (longSentencesCount / totalSentences) * 100;
			if (1 !== totalSentences && longSentencePercentage > 25) {
				description = window.sprintf(i18nLocale.sentenceLengthError, longSentencePercentage.toFixed());
				state = 'problems';
			}

			vc.seo_storage.updateResult(state, title, description, storagePrefix);
		},
		checkSubheadingDistribution: function (title) {
			var contentHtmlString = this.$wpbContentWrapper.html();
			var textSections = utils.getTextSectionCount(contentHtmlString);
			var state = 'success';
			var description = i18nLocale.goodJob;
			var isNoHeadings = textSections.filter(function (section) {
				return !section.subheadingCount && section.wordCount > 300;
			});
			var isNotEnoughHeadings = textSections.filter(function (section) {
				return 1 === section.subheadingCount && section.wordCount > 300;
			});

			if (isNoHeadings.length) {
				state = 'problems';
				description = i18nLocale.subheadingDistributionFail;
			} else if (isNotEnoughHeadings.length) {
				state = 'warnings';
				description = i18nLocale.subheadingDistributionWarn.replace('%s', isNotEnoughHeadings.length);
			}

			vc.seo_storage.updateResult(state, title, description, storagePrefix);
		},
		checkPreviouslyUsedKeyphrase: function (title) {
			var state = 'success';
			var description = i18nLocale.previouslyUsedKeyphraseSuccess;

			if (vc.seo_storage.get('formData').isUsedKeyphrase) {
				state = 'warnings';
				description = i18nLocale.previouslyUsedKeyphraseWarn;
			}
			vc.seo_storage.updateResult(state, title, description, storagePrefix);
		},
	};

})(window.jQuery);
