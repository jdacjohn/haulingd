// Declare the namespace
var fdTextareaController;

// Define anonymous function
(function() {

		// Create object private to the anonymous function
		function fdTextareaMaxlength(inp, maxlength, id) {
				this._inp       = inp;
				this._max       = Number(maxlength);
				this._id        = id || '';
				var self        = this;

				self.maxlength = function() {
						if(self._inp.disabled) return false;

						if(self._inp.value.length > self._max) {
								self._inp.value = self._inp.value.substring(0,self._max);
								return false;
						}

						return true;
				}
				self.showlength = function() {
						if(self._inp.value.length) {
								document.getElementById('maxLenCount'+self._id).innerHTML = '('+self._inp.value.length+' used)';
						}

						return true;
				}
				addEvent(self._inp, 'keyup',    self.maxlength, false);
				addEvent(self._inp, 'keyup',    self.showlength, false);
				addEvent(self._inp, 'blur',     self.maxlength, false);
				addEvent(self._inp, 'blur',     self.showlength, false);
				addEvent(self._inp, 'focus',    self.maxlength, false);
				addEvent(self._inp, 'focus',    self.showlength, false);

				// IE only event 'onpaste'

				// conditional compilation used to load only in IE win.

				/*@cc_on @*/
				/*@if (@_win32)
				addEvent(self._inp, 'paste', self.maxlength, false); //function(){ event.returnValue = false; self._inp.value = window.clipboardData.getData("Text").substring(0,self._max); }, true);
				addEvent(self._inp, 'paste', self.showlength, false); 
				/*@end @*/
		};

		// Construct the previously declared namespace
		fdTextareaController = {
				textareas: [],

				_construct: function( e ) {

						var regExp_1 = /fd_max(_[0-9]+)?_([0-9]+){1}/i;

						var textareas = document.getElementsByTagName("textarea");

						for(var i = 0, textarea; textarea = textareas[i]; i++) {
								if(textarea.className && textarea.className.search(regExp_1) != -1) {
										var matches = textarea.className.match(regExp_1);
										max = parseInt(matches[2]);
										if(max) fdTextareaController.textareas[fdTextareaController.textareas.length] = new fdTextareaMaxlength(textarea, max, matches[1]);
								}
						}

				},

				_deconstruct: function( e ) {
						/* TODO: Clean up for IE memory leaks.. */
				}
		}
// Complete the anonymous function and call it immediately.
})();

// onload events
addEvent(window, 'load', fdTextareaController._construct, false);
addEvent(window, 'unload', fdTextareaController._deconstruct, false);
