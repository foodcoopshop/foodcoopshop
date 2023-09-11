Jodit.defaultOptions.filebrowser = {
	resizable: true,
	width: 600,
	height: 400,
	url: '/js/elfinder/php/connector.php',
	lang: 'de' // full option list {@link https://github.com/Studio-42/elFinder/wiki/Client-configuration-options|here}
};

Jodit.modules.FileBrowser = function (options) {
	this.options = options;

	var dialog,
		callbackSelect,
		$elfinderbox = this.create.fromHTML(
			'<div class="jodit_elfinder"></div>'
		);

	dialog = new Jodit.modules.Dialog({ resizable: false });

	options.getFileCallback = function (file) {
		if (callbackSelect && callbackSelect.call) {
			callbackSelect.call(editor, [file.url]);
		} else {
			editor.selection.insertNode(
				editor.create.fromHTML('<img src="' + file.url + '">')
			);
		}

		dialog.close();
	};

	$($elfinderbox).elfinder(options);
	dialog.setContent($elfinderbox);

	this.open = function (callback) {
		callbackSelect = callback;
		dialog.open();
		$($elfinderbox).resize();
	};
};
Jodit.modules.FileBrowser.prototype = new Jodit.modules.ViewWithToolbar();