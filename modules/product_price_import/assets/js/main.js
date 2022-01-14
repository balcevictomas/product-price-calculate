function calculatePrice() {
	var wppi_width = jQuery('.wppi_width').val();
	var wppi_height = jQuery('.wppi_height').val();
	var wppi_dimensions = jQuery('input[name="dimensions"]:checked').val();
	var wppi_qty = jQuery('.qty').val();
	var wppi_product_id = jQuery('.front_product_id').val();

	var dimensions = jQuery("input[name='dimensions']:checked").val();
	var current_width = jQuery(".wppi_width").val();
	var current_height = jQuery(".wppi_height").val();

	jQuery.post('', {
		wppi_width: wppi_width,
		wppi_height: wppi_height,
		wppi_dimensions: wppi_dimensions,
		wppi_qty: wppi_qty,
		wppi_product_id: wppi_product_id
	}, function(result) {
		jQuery(".woocommerce-Price-amount").html(result);
		jQuery("#wppi_price_from").hide();
	});

	var dimensions_arr = getDimentions();

	if(current_width < dimensions_arr['width_min']) {
	jQuery('#wppi_err').html('<span class="wppi_err">Minimum width is: '+dimensions_arr['width_min']+' '+dimensions+'</span>');
	} else if(current_width > dimensions_arr['width_max']) {
	jQuery('#wppi_err').html('<span class="wppi_err">Maximum width is: '+dimensions_arr['width_max']+' '+dimensions+'</span>');
	} else if(current_height < dimensions_arr['height_min']) {
	jQuery('#wppi_err').html('<span class="wppi_err">Minimum height is: '+dimensions_arr['height_min']+' '+dimensions+'</span>');
	} else if(current_height > dimensions_arr['height_max']) {
	jQuery('#wppi_err').html('<span class="wppi_err">Maximum height is: '+dimensions_arr['height_max']+' '+dimensions+'</span>');
	} else {
	jQuery('#wppi_err').html('');
	}
}

jQuery(document).ready(function(){
	jQuery('.dimensions').change(function(){
		var dimensions_arr = getDimentions();

		jQuery(".wppi_width").attr({
			"min" : dimensions_arr['width_min'],
			"max" : dimensions_arr['width_max']
		});

		jQuery(".wppi_height").attr({
			"min" : dimensions_arr['height_min'],
			"max" : dimensions_arr['height_max']
		});
	});
});

function getDimentions() {
	var width_min = 200;
	var width_max = 3000;

	var height_min = 50;
	var height_max = 600;

	selected_value = jQuery("input[name='dimensions']:checked").val();

	if(selected_value == 'cm') {
		width_min = width_min / 10;
		width_max = width_max / 10;

		height_min = height_min / 10;
		height_max = height_max / 10;
	}

	if(selected_value == 'in') {
		width_min = Math.ceil(width_min / 25.4);
		width_max = Math.ceil(width_max / 25.4);

		height_min = Math.ceil(height_min / 25.4);
		height_max = Math.ceil(height_max / 25.4);
		if(width_max > 118) width_max = 118;
		if(height_max > 23) height_max = 23;
	}

	var dimensions_arr = {
		'width_min': width_min,
		'width_max': width_max,
		'height_min': height_min,
		'height_max': height_max,
	};

	return dimensions_arr;
}
