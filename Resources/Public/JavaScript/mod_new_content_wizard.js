// maybe?: https://stackoverflow.com/questions/50427513/html-paste-clipboard-image-to-file-input

import {html, css, LitElement, nothing} from "lit";
import Modal from "@typo3/backend/modal.js";
import "@typo3/backend/element/icon-element.js";
import AjaxRequest from "@typo3/core/ajax/ajax-request.js";
import {lll} from "@typo3/core/lit-helper.js";
import Notification from "@typo3/backend/notification.js";
import Viewport from "@typo3/backend/viewport.js";
import RegularEvent from "@typo3/core/event/regular-event.js";
import {SeverityEnum} from "@typo3/backend/enum/severity.js";
import jQuery from"jquery";
class Item {
	constructor(identifier, label, description, icon, url, requestType, defaultValues, saveAndClose, ctype, images, filename) {
		this.identifier = identifier;
		this.label = label;
		this.description = description;
		this.icon = icon;
		this.url = url;
		this.requestType = requestType;
		this.defaultValues = defaultValues;
		this.saveAndClose = saveAndClose;
		this.visible = true;
		this.ctype = ctype;
		this.images = images;
		this.filename = filename;
	}
	static fromData(data) {
		return new Item(
			data.identifier,
			data.label,
			data.description,
			data.icon,
			data.url,
			data.requestType ?? "location",
			data.defaultValues ?? [],
			data.saveAndClose ?? false,
			data.ctype ?? "no-data",
			data.images ?? [],
			data.filename ?? false
		);
	}
	reset() {
		this.visible = true;
	}
}
class Category {
	constructor(identifier, label, items) {
		this.identifier = identifier;
		this.label = label;
		this.items = items;
		this.disabled = false;
	}

	static fromData(data) {
		return new Category(
			data.identifier,
			data.label,
			data.items.map(item => Item.fromData(item))
		);
	}

	reset() {
		this.disabled = false;
		this.items.forEach(item => {
			item.reset();
		});
	}

	activeItems() {
		return this.items.filter(item => item.visible) ?? [];
	}
}
class Categories {
	constructor(items) {
		this.items = items;
	}
	static fromData(data) {
		return new Categories(
			Object.values(data).map(category => Category.fromData(category))
		);
	}
	reset() {
		this.items.forEach(category => {
			category.reset();
		});
	}
	categoriesWithItems() {
		return this.items.filter(category => category.activeItems().length > 0) ?? [];
	}
}

// import {NewContentElementWizard} from "@typo3/backend/new-record-wizard.js";
import {NewRecordWizard} from "@typo3/backend/new-record-wizard.js";
import {NewContentElementWizardButton} from "@typo3/backend/new-content-element-wizard-button.js";

let originalRenderWizard = NewRecordWizard.prototype.renderWizard;
NewContentElementWizardButton.prototype.renderWizard = function() {
	this.url && Modal.advanced({
		content: this.url,
		title: this.subject,
		severity: SeverityEnum.notice,
		size: Modal.sizes.large,
		additionalCssClasses: ['new-content-wizard'],
		type: Modal.types.ajax
	})
}

let originalElementProperties = NewRecordWizard.elementProperties;
let converter = {converter:{fromAttribute:(e)=>{const t=JSON.parse(e);return Categories.fromData(t)}}};
originalElementProperties.set("categories",converter);
// let original_renderCategory = NewRecordWizard.prototype.renderCategory;
// NewRecordWizard.prototype.renderCategory = function(e) {
// 	debugger;
// 	return original_renderCategory.call(this,e);
// }
let originalRender = NewRecordWizard.prototype.render;
NewRecordWizard.prototype.render = function() {
	return originalRender.call(this);
};

NewRecordWizard.prototype._showDev = function() {
	this._showDevHelper = !this._showDevHelper;
	this.requestUpdate();
}


const getConfig = () => {
	const el = document.querySelector('[data-identifier="bdm_wizard_preview"]');
	const configJson = el.dataset.config;
	return JSON.parse(configJson);
}


let originalRenderCategories = NewRecordWizard.prototype.renderCategories;
NewRecordWizard.prototype.renderCategories = function() {
	let htmlResult = originalRenderCategories.call(this);
	// let bdm_wizard_preview_extension_config = window.bdm_wizard_preview_extension_config;
	let bdm_wizard_preview_extension_config = getConfig();
	const imageRootPath = bdm_wizard_preview_extension_config.previewImagePath;
	const isDevelepmentContext = bdm_wizard_preview_extension_config.isDevelepmentContext;
	let devMarkup = '';

	if(isDevelepmentContext) {
		let button = html`
			<button class="show-dev-helper ${this._showDevHelper ? 'active':''}" @click="${t => {t.preventDefault(), t.stopPropagation(),this._showDev()}}">
				<?xml version="1.0" encoding="iso-8859-1"?>
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" xml:space="preserve" viewBox="0 0 419.931 419.931">
                    <path d="M282.895 352.367c-2.176-1.324-4.072-3.099-5.579-5.25-.696-.992-1.284-2.041-1.771-3.125H28.282V100.276h335.624v159.138c7.165.647 13.177 5.353 15.701 11.797 2.235-1.225 4.726-1.982 7.344-2.213 1.771-.154 3.53-.044 5.236.293V39.561c0-12.996-10.571-23.569-23.566-23.569H23.568C10.573 15.992 0 26.565 0 39.561v309.146c0 12.996 10.573 23.568 23.568 23.568h257.179c-2.007-4.064-2.483-8.652-1.302-13.066.681-2.539 1.859-4.855 3.45-6.842zm55.13-296.798c0-4.806 3.896-8.703 8.702-8.703h8.702c4.807 0 8.702 3.896 8.702 8.703v9.863c0 4.806-3.896 8.702-8.702 8.702h-8.702c-4.807 0-8.702-3.896-8.702-8.702v-9.863zm-40.465 0c0-4.806 3.896-8.703 8.702-8.703h8.703c4.807 0 8.702 3.896 8.702 8.703v9.863c0 4.806-3.896 8.702-8.702 8.702h-8.703c-4.806 0-8.702-3.896-8.702-8.702v-9.863zm-40.466 0c0-4.806 3.897-8.703 8.702-8.703h8.702c4.807 0 8.703 3.896 8.703 8.703v9.863c0 4.806-3.896 8.702-8.703 8.702h-8.702c-4.805 0-8.702-3.896-8.702-8.702v-9.863z"/>
					<path d="m419.875 335.77-2.615-14.83c-.353-1.997-2.256-3.331-4.255-2.979l-13.188 2.324c-1.583-3.715-3.605-7.195-6.005-10.38l8.614-10.268c.626-.744.931-1.709.847-2.68-.086-.971-.554-1.867-1.3-2.494l-11.534-9.68c-.746-.626-1.713-.93-2.683-.845-.971.085-1.867.552-2.493 1.298l-8.606 10.26c-3.533-1.8-7.312-3.188-11.271-4.104V278c0-2.028-1.645-3.674-3.673-3.674h-15.06c-2.027 0-3.673 1.646-3.673 3.674v13.392c-3.961.915-7.736 2.304-11.271 4.104l-8.608-10.259c-1.304-1.554-3.62-1.756-5.175-.453l-11.535 9.679c-.746.627-1.213 1.523-1.299 2.494-.084.971.22 1.937.846 2.683l8.615 10.266c-2.396 3.184-4.422 6.666-6.005 10.38l-13.188-2.325c-1.994-.351-3.901.982-4.255 2.979l-2.614 14.83c-.169.959.05 1.945.607 2.744.561.799 1.41 1.342 2.37 1.511l13.198 2.326c.215 4.089.927 8.045 2.073 11.812l-11.6 6.695c-.844.485-1.459 1.289-1.712 2.229-.252.941-.119 1.943.367 2.787l7.529 13.041c.485.844 1.289 1.459 2.229 1.711.313.084.632.125.951.125.639 0 1.272-.166 1.836-.492l11.609-6.703c2.73 2.925 5.812 5.517 9.18 7.709l-4.584 12.593c-.332.916-.289 1.926.123 2.809s1.157 1.566 2.072 1.898l14.148 5.149c.406.148.832.224 1.257.224.53 0 1.063-.115 1.554-.345.883-.411 1.564-1.157 1.897-2.073l4.583-12.593c1.965.238 3.965.361 5.994.361s4.029-.125 5.994-.361l4.584 12.593c.332.916 1.016 1.662 1.897 2.073.49.229 1.021.345 1.554.345.424 0 .85-.074 1.256-.224l14.15-5.149c.913-.332 1.659-1.017 2.07-1.898.412-.883.456-1.893.123-2.809l-4.584-12.591c3.365-2.192 6.447-4.786 9.18-7.709l11.609 6.703c.563.324 1.197.492 1.836.492.318 0 .64-.043.951-.125.941-.252 1.743-.869 2.229-1.711l7.529-13.043c.486-.842.619-1.846.367-2.787-.253-.938-.868-1.742-1.712-2.229l-11.598-6.693c1.146-3.768 1.856-7.724 2.071-11.812l13.198-2.327c.96-.169 1.812-.712 2.37-1.511.579-.8.798-1.786.629-2.745zm-65.691 23.566c-11.155 0-20.2-9.045-20.2-20.201s9.046-20.2 20.2-20.2c11.156 0 20.201 9.044 20.201 20.2s-9.045 20.201-20.201 20.201zM164.695 235.373c0-4.752-2.785-9.117-7.096-11.119l-39.455-18.332 39.456-18.334c4.31-2.004 7.095-6.368 7.095-11.118v-.319c0-4.21-2.119-8.075-5.665-10.334-1.962-1.253-4.247-1.916-6.606-1.916-1.778 0-3.563.391-5.16 1.133l-63.078 29.333c-4.309 2.004-7.092 6.368-7.092 11.117v.877c0 4.743 2.782 9.104 7.093 11.118l63.084 29.336c1.631.755 3.368 1.138 5.162 1.138 2.338 0 4.616-.664 6.597-1.924 3.548-2.268 5.666-6.13 5.666-10.335l-.001-.321zm62.237-101.361c-2.301-3.15-6.002-5.03-9.901-5.03h-.314c-5.354 0-10.048 3.425-11.679 8.516l-41.56 128.772c-1.183 3.718-.517 7.813 1.781 10.962 2.301 3.148 6.002 5.029 9.901 5.029h.315c5.352 0 10.043-3.426 11.672-8.516l41.555-128.762c1.194-3.715.532-7.816-1.77-10.971zm81.069 60.354-63.079-29.333c-1.592-.74-3.374-1.131-5.152-1.131-2.358 0-4.644.661-6.605 1.912-3.552 2.263-5.671 6.127-5.671 10.337v.319c0 4.746 2.783 9.111 7.097 11.123l39.454 18.33-39.455 18.331c-4.311 2.002-7.096 6.367-7.096 11.119v.321c0 4.205 2.119 8.066 5.669 10.336 1.974 1.258 4.254 1.923 6.595 1.923 1.792 0 3.527-.383 5.169-1.141l63.082-29.336c4.307-2.009 7.088-6.371 7.088-11.114v-.877c-.003-4.75-2.786-9.114-7.096-11.119z"/>
				</svg>
			</button>
		`;
		if(this._showDevHelper && false) {
			let showPath = imageRootPath;
			if(imageRootPath=='' || imageRootPath==null || imageRootPath==undefined) {
				showPath = 'Pfad ist leer. Bitte Pfad konfigurieren.';
			}
			devMarkup = html`
				<div class="d-flex flex-row dev-helper form-inline">
					${button}
					<label class="d-flex flex-row">Bildpfad:</label>
					<input id="debug-filepath" class="filepath" type="text" value="${showPath}"/>
				</div>
			`;
		}else {
			devMarkup = html`
				<div class="d-flex flex-row dev-helper">
					${button}
				</div>
			`;
		}
	}
	const styleMarkup = html`
		<style>
			
			input.copy-filename, input.filepath {
				background-color: yellow;
				border: 2px solid red;
				padding: 5px;
				width: 100%;
				color: black;;
			}
			input.filepath {
				height: 2rem;
			}
			button.show-dev-helper.active svg{
				fill: red;
			}
			button.show-dev-helper{
				border:none;
				padding: 5px;
				margin-bottom: 10px;
			}
			.item {
				flex-direction: column;
				height: 100%;
				width: 100%;
			}
			.elementwizard-categories .item {
				background-color: #ececec;
				
			}
			.elementwizard-categories .item:hover {
				color: var(--typo3-component-hover-color);
				background: var(--typo3-light-active-bg);
				background: #3393eb33;
				border-color: var(--typo3-light-active-bg);
				.item-body-label, .item-body-description{
					align-items: center;
					display: flex;
					/*color: var(--typo3-text-color-base) !important;*/
					color: light-dark( 
							var(--token-color-neutral-90),
							var(--token-color-neutral-20)
					) !important;
					/*-webkit-filter: invert(100%);*/
					/*filter: invert(100%); */
				}
			}
			@container (min-width: 500px) {
				.item-list.item-list {
					grid-template-columns: repeat(3, 1fr);
				}
			}
			@container (min-width: 1000px) {
				.item-list.item-list {
					grid-template-columns: repeat(4, 1fr);
				}
			}
			@container (min-width: 1400px) {
				.item-list.item-list {
					grid-template-columns: repeat(5, 1fr);
				}
			}
			.preview-image {
				margin-top: 4px;
				max-width: 100%;
				height: auto;
				border: 2px solid #c9c9c9;
			}
			.item-body-label, .item-body-description{
				align-items: center;
				display: flex;
				/*color: var(--typo3-text-color-base) !important;*/
				color: light-dark(
						var(--token-color-neutral-90),
						var(--token-color-neutral-90)
				) !important;
				
				/*-webkit-filter: invert(100%);*/
				/*filter: invert(100%);*/
			}
			.row-label {
				gap: .5rem;
			}
		</style>
	`;
	return html `
		${ styleMarkup }
		${ devMarkup }
		${ htmlResult }`;
}

let originalRenderCategoryButton = NewRecordWizard.prototype.renderCategoryButton;
NewRecordWizard.prototype._handleInputClick = function(element) {
	element.select();
	document.execCommand('copy');
}
let originalRenderCategoryItem = NewRecordWizard.prototype.renderCategoryItem;
NewRecordWizard.prototype.renderCategoryItem = function(e) {
	// let bdm_wizard_preview_extension_config = window.bdm_wizard_preview_extension_config;
	let bdm_wizard_preview_extension_config = getConfig();
	const imageRootPath = bdm_wizard_preview_extension_config.previewImagePath;
	const isDevelepmentContext = bdm_wizard_preview_extension_config.isDevelepmentContext;
	let input = '';
	if(this._showDevHelper && isDevelepmentContext) {
		input = html`
		<div>
			
			<input class="copy-filename" @click="${t => {t.preventDefault(), t.stopPropagation(),this._handleInputClick(t.target)}}" type="text" value="${e.filename}" />
		</div>`;
	}
	return html`${e.visible ? html`
		<div>
			<div>
				${input}
			</div>
			<button
					type="button"
					class="item"
					data-identifier="${e.identifier}"
					@click="${t => {
		t.preventDefault(), this.handleItemClick(e)
	}}"
			>
				<div class="d-flex flex-column">
					<div class="d-flex flex-row row-label">
						<div class="item-icon">
							<typo3-backend-icon identifier="${e.icon}" size="medium"></typo3-backend-icon>
						</div>	
						<div class="item-body">
							<div class="item-body-label align-middle">${e.label}</div><!-- ctype:${e.ctype} / identifier:${e.identifier} -->
							<div class="item-body-description">${e.description}</div>
						</div>
					</div>
					<div class="item-images">
						${e.images.length ? e.images.map(imageData => html`
							<img width="${imageData.imageWidth}" height="${imageData.imageHeight}" src="${imageData.fileUrl}" alt="${imageData}" class="preview-image img-fluid">
						`) : nothing}
					</div>
				</div>
			</button>
		</div>
		` : nothing}`
}

