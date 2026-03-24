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
		let wizardItemSelector = '.t3-new-content-element-wizard-window .t3js-media-new-content-element-wizard';
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
			const CType = params['defvals%5btt_content%5d%5bctype%5d'] ?? '';
			return CType;
		};
		const getListTypeFromButton = function ($button){
			const target = $button.data('target');
			const params = getAllUrlParams(target);
			const Type = params["defvals%5btt_content%5d%5blist_type%5d"] ?? '';
			return Type;
		};

		const checkInterval = setInterval(function () {
			// wizardItemSelector = wizardItemSelector + ':not(.modded)';
			const $els = $('body', window.parent.document).find(wizardItemSelector + ':not(.modded)');
			// $('#parentPrice', window.parent.document).html();
			//window.parent.document.querySelector('.t3-new-content-element-wizard-window .t3js-media-new-content-element-wizard');

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
						const ListType = getListTypeFromButton($button);
						let filenameWoExtension = CType;
						if(ListType){
							filenameWoExtension += '-'+ListType;
						}
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

								let svgCopyToClipboard = '<svg style="width: 20px;height:auto;" width="551" height="600" viewBox="0 0 551 600" fill="none" xmlns="http://www.w3.org/2000/svg">\n' +
									'<path fill-rule="evenodd" clip-rule="evenodd" d="M500.737 400L500.475 525.062C500.475 566.425 466.887 600 425.537 600H75.6747C34.3122 600 0.737305 566.425 0.737305 525.062L0.999756 131.687C0.999756 90.3249 34.5747 56.75 75.9372 56.75H131.925C131.925 56.75 131.925 58.9124 131.925 49.9999C131.925 46.6374 133.262 43.4001 135.649 41.0251C138.024 38.6376 141.262 37.3 144.625 37.3C167.787 37.3 214.5 37.3 214.5 37.3C214.5 37.3 215.375 0.350098 250.737 0.350098C286.1 0.350098 286.624 37.3 286.624 37.3H355.562C362.575 37.3 368.262 42.9874 368.262 49.9999C368.262 58.9124 368.262 56.75 368.262 56.75H425.8C467.162 56.75 500.737 90.3249 500.737 131.687V265.687H450.762L450.75 144.112C450.75 123.5 434.013 106.763 413.388 106.75H368.262V118.912C368.262 125.937 362.575 131.612 355.562 131.612C314.512 131.612 185.675 131.612 144.625 131.612C137.612 131.612 131.925 125.937 131.925 118.912C131.925 108.087 131.925 106.75 131.925 106.75H88.3495C67.7245 106.763 50.9875 123.5 50.9875 144.112L50.7251 512.625C50.7251 533.25 67.462 549.987 88.087 549.987H413.124C433.737 549.987 450.487 533.25 450.487 512.625L450.75 400H500.737ZM250.737 17.2748C261.225 17.2748 269.738 25.8001 269.738 36.2876C269.738 46.7751 261.225 55.2875 250.737 55.2875C240.25 55.2875 231.725 46.7751 231.725 36.2876C231.725 25.8001 240.25 17.2748 250.737 17.2748Z" fill="black"/>\n' +
									'<path d="M269.751 187.5H100.996V212.5H269.751V187.5Z" fill="black"/>\n' +
									'<path d="M231.711 253.047H100.996V278.047H231.711V253.047Z" fill="black"/>\n' +
									'<path d="M200.994 318.596H100.732V343.596H200.994V318.596Z" fill="black"/>\n' +
									'<path d="M231.711 384.144H100.996V409.144H231.711V384.144Z" fill="black"/>\n' +
									'<path d="M269.751 449.691H100.996V474.691H269.751V449.691Z" fill="black"/>\n' +
									'<path d="M551 306.05V356.125H312.138L404.8 448.787L369.4 484.188L216.325 331.112L216.338 331.1L216.325 331.087L369.4 178.012L404.8 213.412L312.163 306.05H551Z" fill="black"/>\n' +
									'</svg>\n';

								// const $buttonCopyToClipboard = $('<button class="btn btn-default btn-sm" title="Copy to clipboard">' + svgCopyToClipboard + '</button>');
								// $buttonCopyToClipboard.css({
								// });

								$input.on('click', function (e){
									e.stopPropagation();
									e.preventDefault();
									$(this).select();
									document.execCommand('copy');
								});
								// $buttonCopyToClipboard.on('click', function (e){
								// 	e.stopPropagation();
								// 	e.preventDefault();
								// 	$(this).select();
								// 	document.execCommand('copy');
								// });
								$container.append($input);
								// $container.append($buttonCopyToClipboard);
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
