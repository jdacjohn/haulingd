/**
* Released under the terms or MIT license:
*
* Copyright (c) Bajcic Dragan
* SOFTWARE.
*/

var mt = Class.create();
mt.prototype = {
  initialize: function(tabs_container,tabs_class) {
		
		this.bgcolor='#FFFFFF';
		this.activeColor='#FFFFFF';
		this.tc=tabs_container;
		this.tbClass=tabs_class;
		this.elm  = $(tabs_container);
		
		new Insertion.Top(this.elm,'<div id="tab-bar"></div>');

		this.tabs = $$(tabs_class);
		this.clear(this.tabs);

		MT=this;
  },
	
	clear: function(tabs){
		tabs.each(function(elm){
			elm.setStyle({display:'none'});
		});
	},
	
	makeActive: function(tab_id){
		
		$(tab_id).setStyle({display:'block'});
		$('mt-'+tab_id).setStyle({backgroundColor:MT.activeColor});
		$$('div.mt-tab').each(function(elm){
		if(elm.hasClassName('mt-tab-active')){

				elm.removeClassName('mt-tab-active');
				elm.setStyle({backgroundColor:MT.bgcolor});
			}
		
		});
		$('mt-'+tab_id).addClassName('mt-tab-active');
	},
	
	addTab: function(tab_id,text){
		
		new Insertion.Bottom($('tab-bar'),'<div id="mt-'+tab_id+'" class="mt-tab">'+text+'</div>');
		var elmnt=$('mt-'+tab_id);
		elmnt.setStyle({backgroundColor:this.bgcolor});
		
		Event.observe(elmnt, 'click', function() {

			window.mt.prototype.clear(MT.tabs);
			window.mt.prototype.makeActive(tab_id,MT.tbClass);
		});
		
		Event.observe(elmnt, 'mouseover', function(nn) {
			
			if(!elmnt.hasClassName('mt-tab-active')){
			elmnt.setStyle({backgroundColor:MT.activeColor});
			}
		});
		
		Event.observe(elmnt, 'mouseout', function(nn) {

			if(!elmnt.hasClassName('mt-tab-active')){
			elmnt.setStyle({backgroundColor:MT.bgcolor});
			
			}
		
		});
	
	},
	
	
	removeTabTitles: function(tabTitlesClass){
			this.tabsTitles = $$(tabTitlesClass);
			this.tabsTitles.each(function(elm){
				elm.setStyle({display:'none'});
			});
	}
};





	