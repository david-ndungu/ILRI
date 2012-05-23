"use strict";
var core = window.core || {
	events: [],
	modules: {},
	register : function(moduleId, creator) {
		this.modules[moduleId] = {
			creator : creator,
			instance : null
		};
	},
	start : function(moduleId) {
		var module = this.modules[moduleId];
		module.instance = module.creator(new sandbox());
		try {
			module.instance.init();
		} catch (e) {
			if (typeof console === 'object') {
				console.error(e.message);
			}
		}
	},
	stop : function(moduleId) {
		var data = this.modules[moduleId];
		if (data.instance) {
			data.instance.kill();
			data.instance = null;
		}
	},
	boot : function() {
		for (var moduleId in this.modules) {
			if (this.modules.hasOwnProperty(moduleId)) {
				this.start(moduleId);
			}
		}
	},
	halt : function() {
		for ( var moduleId in this.modules) {
			if (this.modules.hasOwnProperty(moduleId)) {
				this.stop(moduleId);
			}
		}
	},
	include : function(url, callback) {
		var script = document.createElement("script");
		if(typeof callback == 'function') {
			this.onScriptReady(script, callback);
		}
		script.type = 'text/javascript';
		script.async = true;
		script.src = url;
		var scripts = document.getElementsByTagName('script');
		scripts[0].parentNode.insertBefore(script, scripts[0]);
	},
	onScriptReady : function(script, callback) {
		if (script.readyState) {
		    script.onreadystatechange = function(){
		      if(script.readyState == "loaded" || script.readyState == "complete"){
		        script.onreadystatechange = null;
		        callback();
		      }
		    };        
		} else {
			script.onload = function(){
			    callback();
			};      
		}		
	}
};