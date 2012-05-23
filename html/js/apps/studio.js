core.register('studio', function(sandbox){
	return {
		init: function(){
			sandbox.module = this;
			sandbox.listen('navigation.primary', this.route);
		},
		kill: function(){
			
		},
		controls: {			
		},
		route: function(event){
			var href = event.data;
			if(sandbox.module.initControl(href)){
				sandbox.fire({type: 'navigation.staging', data: {stage: 'primary', control: sandbox.module.controls[href]}});
			}			
		},
		controlType: function(href){
			var controlType = false;
			if(sandbox.module.getGridRoutes().indexOf(href) != -1){
				controlType = 'grid';
			}
			if(sandbox.module.getFormRoutes().indexOf(href) != -1){
				controlType = 'form';
			}
			return controlType;				
		},
		initControl: function(href){
			var controlType = sandbox.module.controlType(href);
			if(controlType) {
				if(typeof sandbox.module.controls[href] == 'undefined'){
					var control = sandbox.createControl(controlType, href);
					if(controlType == 'grid'){
						control.setAddForm(href.replace('/grid/', '/form/'));
					}
					sandbox.module.controls[href] = control;
				}
				return true;					
			} else {
				return false;
			}
		},
		getGridRoutes: function(){
			var routes = new Array();
			routes.push('/studio/grid/assethead');
			return routes;
		},
		getFormRoutes: function(){
			var routes = new Array();
			routes.push('/studio/form/assethead');
			return routes;
		}
	};
});