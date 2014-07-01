function inWindow(s){
	var scrollTop = jQuery(window).scrollTop();
	var windowHeight = jQuery(window).height();
	var currentEls = jQuery(s);
	var result = [];
	currentEls.each(function(){
		var el = jQuery(this);
		var offset = el.offset();
		if(scrollTop <= offset.top && (el.height() + offset.top) < (scrollTop + windowHeight))
			result.push(this);
	});
	return jQuery(result);
}

// Count views
function countViews() {
	var countableView = (jQuery('.countable[data\-view\-id]'));
	
	if (countableView.length && jQuery(countableView[0]).attr('data-view-id')) {
		var countableViewId = jQuery(countableView[0]).attr('data-view-id');
		
		var data = {
			action: 'pdc_count_views',
			post_id: countableViewId
		};

		jQuery.post(ajax_object.ajax_url, data);
	}
}

jQuery(window).load(
	countViews
);

// Count served
var processed = new Array();

function countServed() {
	var served = inWindow('.countable[data\-served\-id]');

	served.each(function(){
		var countableServed = jQuery(this);
		
		if (countableServed.length) {
			for (var i = 0; i < countableServed.length; i++) {
				if (jQuery(countableServed[i]).attr('data-served-id')) {
					var countableServedId = jQuery(countableServed[i]).attr('data-served-id');

					if ( ! processed[ countableServedId ] ) {
						processed[ countableServedId ] = true;
						
						var data = {
							action: 'pdc_count_served',
							post_id: countableServedId
						};

						jQuery.post(ajax_object.ajax_url, data);
					}
				}
			}
		}
	});
}

jQuery(window).load(
	countServed
);
jQuery(window).resize(
	countServed
);
jQuery(window).scroll(
	countServed
);
