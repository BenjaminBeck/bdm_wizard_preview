define(
	[
		'require',
		'exports',
		'TYPO3/CMS/Backend/Enum/Severity',
		'TYPO3/CMS/Backend/Modal',
		'jquery'
	],
	function (
		e,
		n,
		t,
		i,
		$
	) {
	'use strict';
		let bdm_wizard_preview_extension_config = window.bdm_wizard_preview_extension_config;
		const imageRootPath = bdm_wizard_preview_extension_config.previewImagePath;
		let wizardItemSelector = '.t3-new-content-element-wizard-window .t3js-media-new-content-element-wizard [data-identifier]';
		const allPreviewImagePathObjects = bdm_wizard_preview_extension_config.allPreviewImagePaths;
		const isDevelepmentContext = bdm_wizard_preview_extension_config.isDevelepmentContext;
		const allPreviewImageFilenamesArray = [];
		$.each(allPreviewImagePathObjects, function (key, path){
			const filename = path.split('/').pop();
			allPreviewImageFilenamesArray.push(filename);
		});
		const fileExistsAsPreviewImage = function (filename){
			return allPreviewImageFilenamesArray.includes(filename);
		};
		const getCTypeFromButton = function ($button){
			const target = $button.data('target');
			const params = getAllUrlParams(target);
			const CType = params['defvals%5btt_content%5d%5bctype%5d'];
			return CType;
		};

		const checkInterval = setInterval(function () {
			wizardItemSelector = wizardItemSelector + ':not(.modded)';
			const $els = $('body').find(wizardItemSelector);
			if ($els.length) {
				//clearInterval(checkInterval);
				$.each($els, function (){
					const $el = $(this);
					$el.addClass('modded');
					const data = $el.data();
					if(data){
						const $container = $el.closest('.t3js-media-new-content-element-wizard');
						const $button = $container.find('button');
						const CType = getCTypeFromButton($button);
						const filenameWoExtension = CType;
						const filename = filenameWoExtension+ '.png';
						const loadAndAppendPreviewImage = function (filename, isVariant = false){
							const $dfdLoaded = $.Deferred();
							let src = imageRootPath;
							src = src + filename;
							const img  = new Image();
							img.src = src;
							img.className = 'preview-image';
							img.onload = function (){
								$(img).closest('.icon-markup').css({
									position: 'relative'
								});
								$container.append(img);
								$dfdLoaded.resolve();
							};
							img.onerror = function (){
								$(this).addClass('d-none');
							};
							return $dfdLoaded.promise();
						};
						if(fileExistsAsPreviewImage(filename)) {
							const $promiseLoaded = loadAndAppendPreviewImage(filename);
							let variantNumber = 1;
							let variantFilename = filenameWoExtension+ '-variant-' + variantNumber + '.png';
							$.when($promiseLoaded).then(function (){
								while(fileExistsAsPreviewImage(variantFilename)) {
									const $promiseLoaded = loadAndAppendPreviewImage(variantFilename, true);
									variantNumber++;
									variantFilename = filenameWoExtension+ '-variant-' + variantNumber + '.png';
								}
							});
						} else {
							if(isDevelepmentContext){
								const $input = $('<input type="text" value="' + filename + '"/>');
								$input.css({
									backgroundColor: 'yellow',
									border: '2px solid red',
									padding: '5px',
									width: '80%',
								});
								$input.on('click', function (e){
									e.stopPropagation();
									e.preventDefault();
									$(this).select();
									document.execCommand('copy');
								});
								$container.append($input);
							}
						}
						$container.on('click', function (e){
							// prevent infinite loop: if the button is clicked, don't click it again
							if($(e.target).is($button)){
								return;
							}
							$button.click();
						});
						$container.closest('.t3js-modal-content.modal-content').addClass('new-ce-wizard-modal-content');
					}
				});
			}
		}, 100);

		function getAllUrlParams(url) {

			// get query string from url (optional) or window
			var queryString = url ? url.split('?')[1] : window.location.search.slice(1);

			// we'll store the parameters here
			var obj = {};

			// if query string exists
			if (queryString) {

				// stuff after # is not part of query string, so get rid of it
				queryString = queryString.split('#')[0];

				// split our query string into its component parts
				var arr = queryString.split('&');

				for (var i = 0; i < arr.length; i++) {
					// separate the keys and the values
					var a = arr[i].split('=');

					// set parameter name and value (use 'true' if empty)
					var paramName = a[0];
					var paramValue = typeof (a[1]) === 'undefined' ? true : a[1];

					// (optional) keep case consistent
					paramName = paramName.toLowerCase();
					if (typeof paramValue === 'string') paramValue = paramValue.toLowerCase();

					// if the paramName ends with square brackets, e.g. colors[] or colors[2]
					if (paramName.match(/\[(\d+)?\]$/)) {

						// create key if it doesn't exist
						var key = paramName.replace(/\[(\d+)?\]/, '');
						if (!obj[key]) obj[key] = [];

						// if it's an indexed array e.g. colors[2]
						if (paramName.match(/\[\d+\]$/)) {
							// get the index value and add the entry at the appropriate position
							var index = /\[(\d+)\]/.exec(paramName)[1];
							obj[key][index] = paramValue;
						} else {
							// otherwise add the value to the end of the array
							obj[key].push(paramValue);
						}
					} else {
						// we're dealing with a string
						if (!obj[paramName]) {
							// if it doesn't exist, create property
							obj[paramName] = paramValue;
						} else if (obj[paramName] && typeof obj[paramName] === 'string'){
							// if property does exist and it's a string, convert it to an array
							obj[paramName] = [obj[paramName]];
							obj[paramName].push(paramValue);
						} else {
							// otherwise add the property
							obj[paramName].push(paramValue);
						}
					}
				}
			}

			return obj;
		}


	return {};
});
