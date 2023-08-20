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
						const filename = data.identifier+ '.png';
						const $container = $el.closest('.t3js-media-new-content-element-wizard');
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
							let variantFilename = data.identifier+ '-variant-' + variantNumber + '.png';
							$.when($promiseLoaded).then(function (){
								while(fileExistsAsPreviewImage(variantFilename)) {
									const $promiseLoaded = loadAndAppendPreviewImage(variantFilename, true);
									variantNumber++;
									variantFilename = data.identifier+ '-variant-' + variantNumber + '.png';
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
						const $button = $container.find('button.btn.btn-link');
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
	return {};
});
