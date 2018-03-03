// Tabs
	var ActivateTabs=function(){
		if(typeof(MT)=='undefined'){
			var MyTabs= new mt('tabs','div.my_tab');
			MyTabs.removeTabTitles('h5.tab_title');
			MyTabs.addTab('t1','<br /><img src="images/one.gif" alt />');
			MyTabs.addTab('t2','<br /><img src="images/two.gif" alt />');
			MyTabs.makeActive('t1');
			}	
	}
	
// curvyCorners
  cCorners = function()
  {
      settings = {
          tl: { radius: 0 },
          tr: { radius: 0 },
          bl: { radius: 20 },
          br: { radius: 20 },
          antiAlias: true,
          autoPad: true,
          validTags: ["div"]
      }

      var myBoxObject = new curvyCorners(settings, "frame");
      myBoxObject.applyCornersToAll();
  }

//*** This code is copyright 2003 by Gavin Kistner, gavin@refinery.com
//*** It is covered under the license viewable at http://phrogz.net/JS/_ReuseLicense.txt

//***Cross browser attach event function. For 'evt' pass a string value with the leading "on" omitted
AttachEvent(window,'load',cCorners,false);
AttachEvent(window,'load',ActivateTabs,false);

function AttachEvent(obj,evt,fnc,useCapture){
	if (!useCapture) useCapture=false;
	if (obj.addEventListener){
		obj.addEventListener(evt,fnc,useCapture);
		return true;
	} else if (obj.attachEvent) return obj.attachEvent("on"+evt,fnc);
	else{
		MyAttachEvent(obj,evt,fnc);
		obj['on'+evt]=function(){ MyFireEvent(obj,evt) };
	}
} 

//The following are for browsers like NS4 or IE5Mac which don't support either
//attachEvent or addEventListener
function MyAttachEvent(obj,evt,fnc){
	if (!obj.myEvents) obj.myEvents={};
	if (!obj.myEvents[evt]) obj.myEvents[evt]=[];
	var evts = obj.myEvents[evt];
	evts[evts.length]=fnc;
}
function MyFireEvent(obj,evt){
	if (!obj || !obj.myEvents || !obj.myEvents[evt]) return;
	var evts = obj.myEvents[evt];
	for (var i=0,len=evts.length;i<len;i++) evts[i]();
}
