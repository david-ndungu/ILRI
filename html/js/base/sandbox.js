var sandbox = window.sandbox || function(module){
	return {
		listen : function(types, listener) {
			types = typeof types == "string" ? [ types ] : types;
			for (i in types) {
				var type = types[i];
				core.events[type] = typeof core.events[type] == 'undefined' ? [] : core.events[type];
				core.events[type].push(listener);
			}
		},
		fire : function(event) {
			event = typeof event == "string" ? {type : event, data : new Object()} : event;
			event.data = typeof event.data == "undefined" ? new Object() : event.data;
			if (core.events[event.type] instanceof Array) {
				var listeners = core.events[event.type];
				var i = listeners.length - 1;
				do {
					if (typeof listeners[i] == 'function') {
						try {
							listeners[i](event);
						} catch (e) {
							core.log(e, 2);
						}
					}
				} while (i--);
			}
		}		
	};
};