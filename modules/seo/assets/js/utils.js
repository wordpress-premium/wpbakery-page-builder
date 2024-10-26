/* =========================================================
 * Copyright 2023 Wpbakery
 *
 * WPBakery Page Builder util functions for the SEO Analysis panel in the navbar
 *
 * ========================================================= */
/* global vc */
if (!window.vc) {
	window.vc = {};
}

(function ($) {

	window.vc.seo_utils = {
		getTextContent: function (data) {
			data = data
				.replace(/\s*\bdata-vcv-[^"<>]+"[^"<>]+"+/g, '')
				.replace(/<!--\[vcvSourceHtml]/g, '')
				.replace(/\[\/vcvSourceHtml]-->/g, '')
				.replace(/<\//g, ' </');
			var range = document.createRange();
			var documentFragment = range.createContextualFragment(data);

			var helper = documentFragment.querySelector('style, script, noscript, meta, title, #vc_no-content-helper, .vc_controls');

			while (helper) {
				var parentNode = helper.parentNode;
				parentNode.removeChild(helper);
				helper = documentFragment.querySelector('style, script, noscript, meta, title, #vc_no-content-helper, .vc_controls');
			}

			return documentFragment && documentFragment.textContent && documentFragment.textContent.trim();
		},
		/**
		 * Creates a hidden element with the purpose to calculate the sizes of elements and adds these elements to the body.
		 *
		 * @returns {HTMLElement} The created hidden element.
		 */
		createMeasurementElement: function () {
			var hiddenElement = document.createElement('div');
			hiddenElement.id = 'vc-measurement-element';

			// Styles to prevent unintended scrolling in Gutenberg.
			hiddenElement.style.position = 'absolute';
			hiddenElement.style.left = '-9999em';
			hiddenElement.style.top = 0;
			hiddenElement.style.height = 0;
			hiddenElement.style.overflow = 'hidden';
			hiddenElement.style.fontFamily = 'arial, sans-serif';
			hiddenElement.style.fontSize = '20px';
			hiddenElement.style.fontWeight = '400';

			document.body.appendChild(hiddenElement);
			return hiddenElement;
		},
		/**
		 * Measures the width of the text using a hidden element.
		 *
		 * @param {string} text The text to measure the width for.
		 * @returns {number} The width in pixels.
		 */
		measureTextWidth: function (text) {
			var element = document.getElementById('vc-measurement-element');
			if (!element) {
				element = this.createMeasurementElement();
			}
			element.innerHTML = text;
			return element.offsetWidth;
		},
		/**
		 * Finds a keyphrase in a provided text string
		 *
		 * @param {string} text The text check for keyphrase.
		 * @param {string} keyphrase The keyphrase.
		 * @returns {object} The object about found keyphrase.
		 */
		findKeyphrase: function (text, keyphrase) {
			text = text.toLowerCase();
			keyphrase = keyphrase.trim().toLowerCase();
			// Escape special characters in the keyphrase
			var escapedKeyphrase = keyphrase.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

			// Create a regular expression with word boundaries and case-insensitivity
			var regex = new RegExp('\\b' + escapedKeyphrase + '\\b', 'gi');

			// Use the regular expression to find matches in the text
			var matches = Array.from(text.matchAll(regex));

			if (matches.length) {
				return {
					found: true,
					count: matches.length,
					positions: matches.map(function (match) {
						return match.index;
					}),
				};
			} else {
				return {
					found: false,
					count: 0,
					positions: [],
				};
			}
		},
		/**
		 * Slugify a string
		 *
		 * @param {string} text The text string to slugify.
		 * @returns {tring} Slugified string.
		 */
		slugify: function (text) {
			return text.toString().toLowerCase()
				.replace(/\s+/g, '-') // Replace spaces with -
				.replace(/[^\w\-]+/g, '') // Remove all non-word characters
				.replace(/\-\-+/g, '-') // Replace multiple - with single -
				.replace(/^-+/, '') // Trim - from start of text
				.replace(/-+$/, ''); // Trim - from end of text
		},
		/**
		 * Finds a keyphrase in a provided url slug string
		 *
		 * @param {string} slug The url slug string.
		 * @param {string} keyphrase The keyphrase.
		 * @returns {object} The object about found keyphrase.
		 */
		findKeyphraseInSlug: function (slug, keyphrase) {
			// Slugify the keyphrase and slug (users can use spaces in slug)
			var slugifiedKeyphrase = this.slugify(keyphrase);
			var slugifiedSlug = this.slugify(slug);

			// Escape special characters in the slugified keyphrase and slug
			var escapedKeyphrase = slugifiedKeyphrase.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
			var escapedSlug = slugifiedSlug.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

			// Create a regular expression with hyphens and word boundaries
			var regex = new RegExp('\\b' + escapedKeyphrase.split('-').join('\\b-\\b') + '\\b', 'gi');

			// Use the regular expression to find matches in the slug
			var matches = Array.from(escapedSlug.matchAll(regex));

			if (matches.length > 0) {
				return {
					found: true,
					count: matches.length,
					positions: matches.map(function (match) {
						return match.index;
					}),
				};
			} else {
				return {
					found: false,
					count: 0,
					positions: [],
				};
			}
		},
		/**
		 * Finds a keyphrase in a provided list of images
		 *
		 * @param {object} $images The jQuery object of all the images in the content.
		 * @param {string} keyphrase The keyphrase.
		 * @returns {object} The object about images that contain keyphrase.
		 */
		findKeyphraseInAltTag: function ($images, keyphrase) {
			var totalImages = $images.length;
			var imagesWithKeyphrase = 0;
			keyphrase = keyphrase.trim().toLowerCase();

			// Loop through each image in the jQuery object
			$images.each(function () {
				// Get the alt attribute of the current image
				var altText = $(this).attr('alt');

				// Check if the alt attribute contains the keyphrase
				if (altText && altText.toLowerCase().includes(keyphrase)) {
					imagesWithKeyphrase++;
				}
			});
			// Calculate the percentage of images with the keyphrase
			var percentage = (imagesWithKeyphrase / totalImages) * 100;

			return {
				percentage: percentage,
				imagesWithKeyphrase: imagesWithKeyphrase
			};
		},
		/**
		 * Finds a keyphrase density in a provided text string
		 *
		 * @param {string} text The text check for keyphrase.
		 * @param {string} keyphrase The keyphrase.
		 * @returns {object} The object about the text that contain keyphrase.
		 */
		findKeyphraseDensity: function (text, keyphrase) {
			keyphrase = keyphrase.trim().toLowerCase();
			var totalWords = text.trim().split(/\s+/).length; // Count words in the text
			var keyphraseRegExp = new RegExp('\\b' + keyphrase + '\\b', 'gi');
			var keyphraseOccurrences = text.match(keyphraseRegExp) || [];

			var advisedMinOccurrences = Math.ceil(0.005 * totalWords);
			var advisedMaxOccurrences = Math.ceil(0.03 * totalWords);

			return {
				keyphraseOccurrences: keyphraseOccurrences,
				advisedMinOccurrences: advisedMinOccurrences,
				advisedMaxOccurrences: advisedMaxOccurrences,
			};
		},
		getParagraphs: function (data) {
			var paragraphs = data.find('p');
			// Remove paragraphs that have any of the target classes or ids
			var preventedSelectors = ['.vc_ui-help-block'];
			paragraphs = paragraphs.filter(function (index, element) {
				var $paragraph = $(element);
				// Check if the paragraph contains any of the target selectors
				var containsPreventedSelectors = preventedSelectors.some(function (selector) {
					return $paragraph.is(selector);
				});
				return !containsPreventedSelectors;
			});
			// Remove paragraphs without text content
			paragraphs = paragraphs.filter(function (index, element) {
				return $(element).text().trim().length > 0;
			});
			// Remove paragraphs that only contain <a> tags
			paragraphs = paragraphs.filter(function (index, element) {
				return !(1 === $(element).contents().length && 1 === $(element).children('a').length);
			});
			// Return the text content of the first paragraph
			return paragraphs;
		},
		getSentences: function (text) {
			var sentences = text.match(/\(?[^\.\?\!]+[\.!\?]\)?/g);
			return sentences || [];
		},
		/**
		 * Checks whether the text contains three or more sentences in a row all starting with the same word.
		 *
		 * @param {string} text The text to check for consecutive sentences.
		 * @returns {Object} An object containing the analysis results.
		 *   - consecutiveCount {number} - The number of consecutive sentences with the same starting word.
		 *   - state {boolean} - True if three or more consecutive sentences start with the same word, otherwise false.
		 */
		hasConsecutiveSentences: function (text) {
			// Split the text into sentences using a simple regular expression
			var sentences = this.getSentences(text);

			// Check for consecutive sentences with the same start word
			var consecutiveCount = 1;
			for (var i = 1; i < sentences.length; i++) {
				var currentStartWord = sentences[i].split(' ')[0];
				var previousStartWord = sentences[i - 1].split(' ')[0];

				if (currentStartWord === previousStartWord) {
					consecutiveCount++;
					if (consecutiveCount >= 3) {
						return {
							consecutiveCount: consecutiveCount,
							state: true
						};
					}
				} else {
					consecutiveCount = 1;
				}
			}

			return {
				consecutiveCount: consecutiveCount,
				state: false
			};
		},
		getPassiveVoicePercentage: function (paragraphs) {
			// Initialize counters
			var totalSentences = 0;
			var passiveVoiceSentences = 0;

			if (paragraphs.length) {
				var _this = this;
				// Iterate through paragraphs
				paragraphs.each(function (index, element) {
					// Split paragraph into sentences
					var sentences = _this.getSentences($(element).text());

					// Update total sentence count
					totalSentences += sentences.length;

					// Check for passive voice in each sentence
					sentences.forEach(function (sentence) {
						if (_this.hasPassiveVoice(sentence)) {
							passiveVoiceSentences++;
						}
					});
				});
			}

			// Calculate percentage
			var percentage = totalSentences ? (passiveVoiceSentences / totalSentences) * 100 : 0;

			return percentage.toFixed(2);
		},
		hasPassiveVoice: function (text) {
			// Regular expression to identify passive voice patterns
			var passiveVoiceRegex = /\b(am|are|is|was|were|been|being)\s+[^.!?]*\b(by)\b/;
			// Check if the text contains passive voice
			return passiveVoiceRegex.test(text);
		},
		getWordsCount: function (text) {
			var punctuationRegexString = "\\–\\-\\(\\)_\\[\\]’‘“”〝〞〟‟„\"'.?!:;,¿¡«»‹›\u2014\u00d7\u002b\u0026\u06d4\u061f\u060C\u061B\u3002\uff61" +
				"\uff01\u203c\uff1f\u2047\u2049\u2048\u2025\u2026\u30fb\u30fc\u3001\u3003\u3004\u3006\u3007\u3008\u3009\u300a\u300b\u300c\u300d\u300e" +
				"\u300f\u3010\u3011\u3012\u3013\u3014\u3015\u3016\u3017\u3018\u3019\u301a\u301b\u301c\u301d\u301e\u301f\u3020\u3036\u303c\u303d\uff5b" +
				"\uff5d\uff5c\uff5e\uff5f\uff60\uff62\uff63\uff64\uff3b\uff3d\uff65\uffe5\uff04\uff05\uff20\uff06\uff07\uff08\uff09\uff0a\uff0f\uff1a" +
				"\uff1b\uff1c\uff1e\uff3c\\<>";
			var interJectionRegexString = "([ " + punctuationRegexString + "])";
			// Punctuation marks are tokenized as if they were words.
			var words = text.split(/\s/g);
			// Punctuation marks are tokenized as if they were words.
			words = words.reduce(function (result, word) {
				var newWord = word.replace(new RegExp(interJectionRegexString, 'g'), " $1 ");
				return result.concat(newWord.split(" "));
			}, []);

			words = words.filter(function (word) {
				return "" !== word.trim();
			});

			return words.length;
		},
		/**
		 * Checks the subheading distribution in an HTML string.
		 *
		 * @param {string} htmlString - The input HTML string to analyze.
		 * @returns {Array} An array of objects, each representing a section of text with its analysis results.
		 *   - wordCount {number} - The number of words in the section.
		 *   - subheadingCount {number} - The number of subheadings in the section.
		 */
		getTextSectionCount: function (htmlString) {
			var parser = new DOMParser();
			var doc = parser.parseFromString(htmlString, 'text/html');

			// Helper function to count words in a text node
			function countWords (text) {
				return text.trim().split(/\s+/).length;
			}

			// Helper function to check if a node contains subheadings
			function countSubheadings (node) {
				var subheadingElements = node.querySelectorAll('h1, h2, h3, h4, h5, h6');
				return subheadingElements.length;
			}

			// Helper function to analyze a section and return the results
			function analyzeSection (node) {
				var paragraphElements = node.querySelectorAll('p');
				var wordCount = 0;
				var subheadingCount = countSubheadings(node);

				// Count words only within paragraph elements
				paragraphElements.forEach(function (paragraph) {
					return wordCount += countWords(paragraph.textContent || '');
				});

				return {
					wordCount: wordCount,
					subheadingCount: subheadingCount
				};
			}

			// Traverse the DOM to analyze each section
			var nodes = doc.body.childNodes;
			var results = [];

			nodes.forEach(function (node) {
				if (node.nodeType === Node.ELEMENT_NODE) {
					// Analyze the section and push results to the array
					var sectionResults = analyzeSection(node);
					results.push(sectionResults);
				}
			});

			return results;
		}
	};

})(window.jQuery);
