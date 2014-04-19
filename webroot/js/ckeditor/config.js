/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	
	//Oembed
	//config.allowedContent = true;
	//config.extraPlugins = 'oembed';
	//config.oembed_maxWidth = '560';
	//config.oembed_maxHeight = '315';

	//config.toolbar = [
	//    [ 'Source', '-', 'NewPage', 'Preview', '-', 'Templates' ],
	//    [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ],
	//    '/',
	//    [ 'Bold', 'Italic' ]
	//];

	config.toolbar = [
		['Source','-','Save'],'-',['Undo','Redo'],'-',['Scayt'],'-',['NumberedList','BulletedList','Table'],'-',['Blockquote','CreateDiv','SpecialChar','Iframe'],
		'/',
		['Styles'],['Format'],'-',['Bold','Italic','Underline','Strike','TextColor'],'-',['Link','Unlink','Image','Flash','Smiley'],'-',['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		'/',
		['oembed','MediaEmbed']
	];

	config.baseUrl = "http://we-sport.fr/";
	config.baseDir = "D:\wamp\www\weSport\webroot";
	config.enterMod = 'ENTER_BR';


};
