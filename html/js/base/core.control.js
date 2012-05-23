core.control  = {
	render : function(template, records){
		this.html = new String(template);
		var output = new Array();
		for(i in records){
			output.push("\t"+this.compile(records[i]));
		}
		return output.join("\r");
	},
	compile : function(record){
		var pattern = /{{([^}]*)}}/g;
		this.html = this.html.replace(pattern, function(tag){
			var key = tag.replace('{{', '').replace('}}', '');
			return record[key];
		});
		return this.html;			
	},
	getHTML: function(){
		return $('<em>It works</em>');
	},
	extend : function(name, extension){
		extension.prototype = core.control;
		extension.prototype.constructor = extension;
		core.control[name] = extension;					
	}
};