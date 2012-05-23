core.navigation = {
	sandbox: new sandbox(),
	init: function(){
		this.primary();
		this.sandbox.listen(['navigation.staging'], this.staging);
	},
	primary: function(){
		var extension = this;
		$('.pageContentNavigation a').click(function(event){
			var href = $(this).attr('href');
			extension.sandbox.fire({type: 'navigation.primary', data: href});
			event.preventDefault();
		});
		$('.pageContentNavigation > ul > li > a').mousedown(function(event){
			var anchor = $(this);
			if(anchor.siblings('ul').children('li').length){
				anchor.siblings('ul').slideDown(function(){
					anchor.addClass('expanded');
				});
			}
			$('.pageContentNavigation > ul > li > a.expanded').not(anchor).siblings('ul').slideUp(function(){
				$(this).removeClass('expanded');
			});
		});			
	},
	staging: function(event){
		var stage = event.data.stage;
		var control = event.data.control;
		switch(stage){
			case 'primary':
				$('.pageContentContent').html(control.getHTML());
			break;
		}
	}
};
$(document).ready(function(){
	core.navigation.init();
});