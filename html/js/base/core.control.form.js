core.control.extend('form', function(){
	var control = this;
	var _private = {
		
	};
	var _public = {
			init: function(){
				if(!arguments[0]) return;
			}
	};
	for(i in _public){
		this[i] = _public[i];
	}
	this.init(arguments[0]);	
});