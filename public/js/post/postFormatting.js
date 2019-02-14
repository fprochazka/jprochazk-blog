(function() {

	var fieldSelection = {

		getSelection: function() {

			var e = (this.jquery) ? this[0] : this;

			return (

				/* mozilla / dom 3.0 */
				('selectionStart' in e && function() {
					var l = e.selectionEnd - e.selectionStart;
					return { start: e.selectionStart, end: e.selectionEnd, length: l, text: e.value.substr(e.selectionStart, l) };
				}) ||

				/* exploder */
				(document.selection && function() {

					e.focus();

					var r = document.selection.createRange();
					if (r === null) {
						return { start: 0, end: e.value.length, length: 0 }
					}

					var re = e.createTextRange();
					var rc = re.duplicate();
					re.moveToBookmark(r.getBookmark());
					rc.setEndPoint('EndToStart', re);

					return { start: rc.text.length, end: rc.text.length + r.text.length, length: r.text.length, text: r.text };
				}) ||

				/* browser not supported */
				function() { return null; }

			)();

		},

		replaceSelection: function() {

			var e = (typeof this.id == 'function') ? this.get(0) : this;
			var text = arguments[0] || '';

			return (

				/* mozilla / dom 3.0 */
				('selectionStart' in e && function() {
					e.value = e.value.substr(0, e.selectionStart) + text + e.value.substr(e.selectionEnd, e.value.length);
					return this;
				}) ||

				/* exploder */
				(document.selection && function() {
					e.focus();
					document.selection.createRange().text = text;
					return this;
				}) ||

				/* browser not supported */
				function() {
					e.value += text;
					return jQuery(e);
				}

			)();

		}

	};

	jQuery.each(fieldSelection, function(i) { jQuery.fn[i] = this; });
})();

var textformatting = {
	selection: {},

	getSelection: function() {
		textformatting.selection = getSelection();
	},

	addTag: function(start_tag, end_tag) {
		if(textformatting.selection.rangeCount > 0) {
			var str_selection = textformatting.selection.toString();
			var str_node = '<'+start_tag+'>'+str_selection+'</'+end_tag+'>';
			var textarea_content = $('textarea').val();
			textarea_content = textarea_content.replace(str_selection, str_node);
			$('textarea').val(textarea_content);
		}
	},

	strong: function() {
		textformatting.addTag('b', 'b')
	},

	italic: function() {
		textformatting.addTag('i', 'i')
	},

	underline: function() {
		textformatting.addTag('span style="text-decoration: underline"', 'span')
	},

	strikethrough: function() {
		textformatting.addTag('span style="text-decoration: line-through"', 'span')
	},

	init: function() {

		//find textarea inside new post form
		textarea = $('form.newpost').find('textarea');

		//insert text formatting div
		$('<div class="text-formatting"></div>').insertBefore(textarea);

		//create buttons in text formatting div
		$('div.text-formatting').append('<button type="button" class="text-formatting strong">B</button> ');
		$('div.text-formatting').append('<button type="button" class="text-formatting italic">I</button> ');
		$('div.text-formatting').append('<button type="button" class="text-formatting underline">U</button> ');
		$('div.text-formatting').append('<button type="button" class="text-formatting strikethrough">abc</button> ');

		//set button onclick functions
		$('button.text-formatting.strong').click(textformatting.strong);
		$('button.text-formatting.italic').click(textformatting.italic);
		$('button.text-formatting.underline').click(textformatting.underline);
		$('button.text-formatting.strikethrough').click(textformatting.strikethrough);
	}
}

$(document).ready(function(){
	textformatting.init();

	document.onselectionchange = textformatting.getSelection;
});