core.control.extend('grid', function(){
	var control = this;
	var _private = {
			form: new String(),
			sandbox: new sandbox(),
			source: new String(),
			template: new String(),
			html: new Object(),
			records: new Object(),
			offset: 0,
			limit: 500,
			search: new String(),
			renderContent: function(){
				var content = $('.gridContent', this.html);
				var rows = "";
				if(this.records.body.length) {
					rows = control.render(content.html(), this.records.body);
				}
				content.html(rows);
			},
			initSort: function(){
				
			},
			initSearch: function(){
				var form = $('.gridHeaderSearch>form', this.html);
				form.submit(function(event){
					event.preventDefault();
					_private.search = $('input[name="keywords"]', form).val();
					_private.ajaxPost('search', function(){
						_private.records = jQuery.parseJSON(arguments[0].responseText);
						_private.renderContent();
						_private.renderFooter();
					});
				});
			},		
			renderFooter: function(){
				var footer = $('.gridFooter>div>span', this.html);
				var legend = control.render(footer.html(), [this.records.footer]);
				footer.html(legend);
			},
			initAdd: function(){
				var button = $('.gridHeaderAdd input[name="addButton"]', this.html);
				button.mousedown(function(event){
					_private.sandbox.fire({type: 'navigation.primary', data: _private.form});
				});
			},
			ajaxPost: function(){
				$.ajax({
					type: 'POST',
					data: this.postParameters(arguments[0]),
					url: this.source,
					complete: arguments[1],
					dataType: 'json',
					async: false
				});			
			},
			postParameters: function(){
				return {
					command: arguments[0],
					search: this.search,
					offset: this.offset,
					limit: this.limit
				};
			}
	};
	var _public = {			
			init: function(source){
				if(!source) return;
				_private.source = source;
				this.getTemplate();
				_private.html = $(_private.template);
				this.getRecords();
				_private.renderContent();
				_private.initSearch();
				_private.initAdd();
				_private.renderFooter();
			},

			setOffset: function(offset){
			_private.offset = offset;
			},
			setLimit: function(limit){
				_private.limit = limit;
			},
			setAddForm: function(source){
				_private.form = source;
			},
			getTemplate: function(){
				$.ajax({
					type: 'GET',
					url: _private.source,
					complete: function(){
						var response = arguments[0].responseText;
						_private.template = response;
					},
					async: false
				});
			},
			getRecords: function(){
				_private.ajaxPost('browse', function(){
					_private.records = jQuery.parseJSON(arguments[0].responseText);
				});
			},
			getHTML: function(){
				return _private.html;
			}
		};
	for(i in _public){
		this[i] = _public[i];
	}
	this.init(arguments[0]);
});