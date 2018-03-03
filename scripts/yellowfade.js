// This technique is a combination of a technique I used for highlighting FAQ's using anchors
// and the ever popular yellow-fade technique used by 37 Signals in Basecamp.

// Including this script in a page will automatically do two things when the page loads...
// 1. Highlight a target item from the URL (browser address bar) if one is present.
// 2. Setup all anchor tags with targets pointing to the current page to cause a fade on the target element when clicked.

// Note regarding IE: http://us3.php.net/manual/en/ref.outcontrol.php#58671

// This is the amount of time (in milliseconds) that will lapse between each step in the fade
var FadeInterval = 300;

// This is where the fade will start, if you want it to be faster and start with a lighter color, make this number smaller
// It corresponds directly to the FadeSteps below
var StartFadeAt = 7;

// This is list of steps that will be used for the color to fade out
var FadeSteps = new Array();
	FadeSteps[1] = "ff";
	FadeSteps[2] = "ee";
	FadeSteps[3] = "dd";
	FadeSteps[4] = "cc";
	FadeSteps[5] = "bb";
	FadeSteps[6] = "aa";
	FadeSteps[7] = "99";

// These are the lines that "connect" the script to the page.
var W3CDOM = (document.createElement && document.getElementsByTagName);
addEvent(window, 'load', initFades);

// This function automatically connects the script to the page so that you do not need any inline script
// See http://www.scottandrew.com/weblog/articles/cbs-events for more information
/*function addEvent(obj, eventType,fn, useCapture)
{
	if (obj.addEventListener) {
		obj.addEventListener(eventType, fn, useCapture);
		return true;
	} else {
		if (obj.attachEvent) {
			var r = obj.attachEvent("on"+eventType, fn);
			return r;
		}
	}
}*/

// The function that initializes the fade and hooks the script into the page
function initFades()
{
	if (!W3CDOM) return;

	// This section highlights targets from the URL (browser address bar)

	// Get the URL
	var currentURL = unescape(window.location);
	// If there is a '#' in the URL
	if (currentURL.indexOf('#')>-1)
		// Highlight the target
		//DoFade(StartFadeAt, currentURL.substring(currentURL.indexOf('#')+1,currentURL.length));
		Highlight(currentURL);

	// This section searches the page body for anchors and adds onclick events so that their targets get highlighted

	// Get the list of all anchors in the body
	var anchors = document.body.getElementsByTagName('a');

	// For each of those anchors
	for (var i=0;i<anchors.length;i++)

		// If there is a '#' in the anchors href
		if (
				anchors[i].href.indexOf('#')>-1
				&&
				document.location.href.indexOf(anchors[i].href.substring(0,anchors[i].href.indexOf('#')))>-1
			)

			// Add an onclick event that calls the highlight function for the target
			//anchors[i].onclick = function(){Highlight(this.href);return true};
			addEvent(anchors[i], 'click', function(){Highlight(this.href)});
}

// This function is just a small wrapper to use for the oncick events of the anchors
function Highlight(target) {

	// Get the target ID from the string that was passed to the function
	if (!target || !target.length) return;
	var targetId = target.substring(target.indexOf('#')+1,target.length);
	var target = document.getElementById(targetId);
	if (!target) return;

	//try to find an appropriate color to revert to after the fade. Fixes problem in Mozilla
	finalColor = getBGColor(target);

	DoFade(StartFadeAt, targetId, finalColor);
}

// This is the recursive function call that actually performs the fade
function DoFade(colorId, targetId, finalColor) {
    var t = document.getElementById(targetId);
    if (!t) return;

	if (!finalColor) finalColor = "#ffffff";
	else if (finalColor.length > 7)
		finalColor = finalColor[0] + //convert #def -> #ddeeff
		             finalColor[1] +
		             finalColor[1] +
		             finalColor[2] +
		             finalColor[2] +
		             finalColor[3] +
		             finalColor[3];

    if (!t.style) t.style = t;

	if (colorId >= 1) {
		t.style.backgroundColor = "#ffff" + FadeSteps[colorId];

		// If it's the last color, set it to transparent
		if (colorId==1) {
			t.style.backgroundColor = finalColor;
		}
		colorId--;

		// Wait a little bit and fade another shade
		setTimeout("DoFade("+colorId+",'"+targetId+"','"+finalColor+"')", FadeInterval);
	}
}

function getBGColor (element) {
	if (!element.nodeType || element.nodeType !== 1) return false;
	//alert(element.tagName + '::' + element.getAttribute('id'));

	var rgb = /rgb\(\s*[0-9]{1,3}\s*,\s*[0-9]{1,3}\s*,\s*[0-9]{1,3}\s*\)/i;
	var hex = /#[a-f0-9]{3,6}/i;
	var bgcolor;

	var i = 0;
	tests:
	while (++i) {
		switch (i) {
			case 1:
				//1. Look at bgcolor attribute
				bgcolor = element.getAttribute('bgcolor');
				//alert('bgcolor::' + bgcolor);
				break;
			case 2:
				//2. Look at background-color in style attribute
				if (!element.style) element.style = element;
				bgcolor = element.style.backgroundColor;
				//alert('backgroundColor::' + bgcolor);
				break;
			case 3:
				//3. Look at background-color inherited from stylesheets
				bgcolor = getStyleFromStyleSheets(element, 'background-color');
				//alert('background-color::' + bgcolor);
				break;
			case 4:
				//4. Look at background shorthand property inherited from stylesheets
				bgcolor = getStyleFromStyleSheets(element, 'background');
				//alert('background::' + bgcolor);
				break;
			case 5:
				//5. Inspect parentNode
				if (element.parentNode && element.parentNode.nodeType === 1) {
					bgcolor = getBGColor(element.parentNode);
				}
				break;
			default:
				break tests;
		}

		if (bgcolor) {
			bgcolor = new String(bgcolor);
			var _bgcolor = bgcolor.match(hex);
			//alert('match(hex)::' + _bgcolor);
			if (_bgcolor) return _bgcolor.toString();

			bgcolor = bgcolor.match(rgb);
			//alert('match(rgb)::' + bgcolor);
			if (bgcolor) {
				bgcolor = RGBtoHex(bgcolor.toString()) || false;
				if (bgcolor) return '#' + bgcolor;
			}
		}
	}

	return false;
}

/**
* getStyleFromStyleSheets v0.1
* Allows to get styles from stylesheets with :modifiers (:hover, :visited...) attributes
* author: Patrice FERLET - Metal3d (metal3d@copix.org)
* Licence: MIT
* Adapted from http://forum.mootools.net/viewtopic.php?id=3107 - 2007-06-13, Chris Bloom
*/
function getStyleFromStyleSheets (element,property,modificator) {
	var zclass = element.className || false;
	var ztag   = element.tagName.toLowerCase();
	var st= "";

	//Check every stylesheets loaded into browser and every CSS rules
	for(i in document.styleSheets){
		var rules = document.styleSheets[i];
		for (j in rules.cssRules){
			var css = new String(rules.cssRules[j].cssText);
			//tag
			if(!modificator){
				//tag -> div
				if(css.test(ztag,"i")){
					st = rules.cssRules[j].cssText;
				}
				//class -> .class{...}
				if(zclass && css.test('\.'+zclass,"i")){
					st = rules.cssRules[j].cssText;
				}
				//id -> #id{...}
				if(css.test('#'+element.getAttribute('id'),"i")){
					st = rules.cssRules[j].cssText;
				}
				//#elem div
				if(css.test('#'+element.getAttribute('id')+"\s*"+ztag,"i")){
					st = rules.cssRules[j].cssText;
				}
			}
			//modificator as hover, active, visited...
			if(modificator){
				//targ -> div:hover
				if(css.test(ztag+":"+modificator,"i")){
					st = rules.cssRules[j].cssText;
				}
				//class -> .class:hover{...}
				if(zclass && css.test('\.'+zclass+":"+modificator,"i")){
					st = rules.cssRules[j].cssText;
				}
				//id #id:hover{...}
				if(css.test('#'+element.getAttribute('id')+":"+modificator,"i")){
					st = rules.cssRules[j].cssText;
				}

				//#elem div:hover...
				if(css.test('#'+element.getAttribute('id')+"\s*"+ztag+":"+modificator,"i")){
					st = rules.cssRules[j].cssText;
				}
			}
		}
	}

	//revalidate as String
	st = new String(st)		;
	//alert(st);

	//get property if given
	if(property){
		var val = st.match(property+"\s*\:\s*([^;\}]*)","i");
		if (val){
			st = new String(val[1]);
		}
	}
	//alert(st);

	if(st=="" || st=="undefined"){
		st=false
	}

	return st;
}

function RGBtoHex(R,G,B) {
	//alert('R::' + R + '(' + typeof R + ')');
	if (!R) return false;
	if (!G && !B) {
		//R may be "rgb(nnn, nnn, nnn)" format string
		R = R.match(/[0-9]+/g);
		if (R.length > 3) return false;
		G = R[1];
		B = R[2];
		R = R[0];
	}
	return toHex(R)+toHex(G)+toHex(B)
}
function toHex(N) {
	if (N==null) return "00";
	N=parseInt(N); if (N==0 || isNaN(N)) return "00";
	N=Math.max(0,N); N=Math.min(N,255); N=Math.round(N);
	return "0123456789ABCDEF".charAt((N-N%16)/16)
		+ "0123456789ABCDEF".charAt(N%16);
}

String.prototype.test = function(regex, params) {
	return ((typeof regex == 'string') ? new RegExp(regex, params) : regex).test(this);
}
