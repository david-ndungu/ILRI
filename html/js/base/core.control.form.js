core.control.extend('form', function(){
	var _private = {
		html: new Object(),
		source: new String(),
		template: new String(),
		record: new Object(),
		command: new String(),
		grid: new String(),
		sandbox: new sandbox(),
		getTemplate: function(){
			var that = this;
			$.ajax({
				type: 'GET',
				url: that.source,
				complete: function(){
					that.template = '<div>'+arguments[0].responseText+'</div>';
					that.html = $(that.template);
				},
				async: false
			});			
		},
		initSubmit: function(){
			var that = this;
			var control = this.html.find('form');
			control.unbind('submit').submit(function(event){
				event.preventDefault();
				that.ajaxPost(function(){
					that.sandbox.fire({type: 'navigation.primary', data: that.grid});
				});
			});
		},
		ajaxPost: function(){
			var control = this;
			var parameters = this.html.find('form').serialize()+'&command='+this.command;; 
			$.ajax({
				type: 'POST',
				data: parameters,
				url: control.source,
				complete: arguments[0],
				dataType: 'json',
				async: true
			});			
		}		
	};
	var _public = {
			init: function(){
				if(!arguments.length) return;
				_private.source = arguments[0];
				_private.getTemplate();
				this.clearForm();
			},
			getHTML: function(){
				_private.initSubmit();
				return _private.html;
			},
			clearForm: function(){
				_private.html.find('input[type="text"], input[type="password"], textarea').val('lorem ipsum');
			},
			setGrid: function(source){
				_private.grid = source;
			},
			setCommand: function(command){
				_private.command = command;
			}						
	};
	for(i in _public){
		this[i] = _public[i];
	}
	this.init(arguments[0]);	
});