core.control.extend('grid', function(){
	var properties = {
		template : "",
		records: []
	};
	var options = arguments.length ? arguments[0] : {};
	for(j in options){
		properties[j] = options[j];
	}
	var methods = {
		init: function(){
			
		},
		setTemplate: function(template){
			properties.template = template;
		},
		setRecords: function(records){
			properties.records = records;
		}
	};
	for(i in methods){
		this[i] = methods[i];
	}
	methods.init();
});