define(function(require){

	var ace = require('ace');
	var ace_markdown = require('ace/mode/markdown');

	var theme = document.createElement('link');
	theme.href = '/css/ace.css';
	theme.type = 'text/css';
	theme.rel = 'stylesheet';
	document.head.appendChild(theme);

	var GfmEditor = (function(ace) {
		var _editor = null;
		var _session = null;

		var _setEditor = function(div_name) {
			_editor = ace.edit(div_name);
			_session = _editor.getSession();

			// _editor.setTheme('ace/theme/tomorrow_night_eighties');
			_session.setMode(new ace_markdown.Mode());

			// free wrap mode
			_session.setUseWrapMode(true);
			_session.setWrapLimitRange();

			// use soft tabs
			_session.setUseSoftTabs(true);
			_session.setTabSize(4);

			_editor.setShowFoldWidgets(false); // useless in Markdown
		};

		return {
			init: function(div_id) {
				_setEditor(div_id);
			},
			getContent: function() {
				return _editor.getValue();
			},
			setContent: function(content) {
				_editor.setValue(content, -1); // cursor at document start
			},
			resize: function() {
				_editor.resize();
			}
		};
	})(ace);

	GfmEditor.init(note_content);

});