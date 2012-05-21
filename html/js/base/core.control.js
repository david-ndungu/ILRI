core.control  = {
	execute : function(template, records){
		var output = new Array();
		for(i in records){
			output.push("\t"+this.compile(records[i]));
		}
		return output.join("\r");
	},
	compile : function(record){
		var pattern = /{([^}]*)}/g;
		html = html.replace(pattern, function(tag){
			var key = tag.replace('{', '').replace('}', '');
			return record[key];
		});
		return html;			
	},
	extend : function(name, extension){
		extension.prototype = core.control;
		extension.prototype.constructor = extension;
		core.control[name] = extension;					
	}
};