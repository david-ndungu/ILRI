core.navigation = {
	sandbox: new sandbox(),
	init: function(){
		this.primary();
		this.sandbox.listen(['staging'], this.staging);
	},
	primary: function(){
		$('.pageContentNavigation a').click(function(event){
			event.preventDefault();
		});
		$('.pageContentNavigation > ul > li > a').mousedown(function(event){
			var anchor = $(this);
			anchor.siblings('ul').slideDown(function(){
				anchor.addClass('expanded');
			});
			$('.pageContentNavigation > ul > li > a.expanded').not(anchor).siblings('ul').slideUp(function(){
				$(this).removeClass('expanded');
			});
			core.navigation.sandbox.fire('navigation.primary', anchor.attr('href'));
		});			
	},
	staging: function(event){
		var stage = event.data.stage;
		switch(stage){
			case 'primary':
				$('.pageContentContent').html(event.data.content);
			break;
		}
	}
};
$(document).ready(function(){
	core.navigation.init();
});