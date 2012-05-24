core.register('studio', function(sandbox){
	return {
		init: function(){
			sandbox.module = this;
			sandbox.listen('navigation.primary', this.route);
		},
		kill: function(){
			
		},
		route: function(event){
			var href = event.data;
			var controlType = sandbox.module.controlType(href);
			var control = sandbox.module.initControl(href);
			if(!control) return;
			sandbox.fire({type: 'navigation.staging', data: {"stage": "primary", "control": control}});
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
				var control = sandbox.createControl(controlType, href);
				if(controlType == 'grid'){
					control.setForm(href.replace('/grid/', '/form/'));
				}
				if(controlType == 'form'){
					control.setGrid(href.replace('/form/', '/grid/'));
					control.setCommand('create');
				}				
				return control;					
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