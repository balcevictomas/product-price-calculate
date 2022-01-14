function priceCalculate()
{
  var ppc_width = jQuery('.ppc_width').val();
	var ppc_height = jQuery('.ppc_height').val();
  var priceper = jQuery('.front_priceper').val();
  var width_min = jQuery('.front_minwidth').val();
  var width_max = jQuery('.front_maxwidth').val();

  var height_min = jQuery('.front_minheight').val();
  var height_max = jQuery('.front_maxheight').val();
  selected_value = jQuery("#unit option:selected").val();
  var unit = jQuery('.front_pricepermeasure').val();

  jQuery.post('', {
		ppc_width: ppc_width,
		ppc_height: ppc_height,
    priceper: priceper,
    width_min: width_min,
    width_max: width_max,
    height_min: height_min,
    height_max: height_max
	}, function(result) {
		jQuery(".woocommerce-Price-amount").html(result);
		jQuery("#wppi_price_from").hide();
    //jQuery('#ppc_after').html('');
	});

if (unit  == 'mm2' && selected_value == 'cm') {
  ppc_width*=10;
  ppc_height*=10;
}
else if (unit  == 'mm2' && selected_value == 'm') {
  ppc_width*=1000;
  ppc_height*=1000;
}
if (parseInt(ppc_width) < parseInt(width_min)) {
  jQuery('#ppc_after').html('<span class="ppc_after_info">Minimum width is ' + width_min +'</span>');
}
else if (parseInt(ppc_width) > parseInt(width_max)){
  jQuery('#ppc_after').html('<span class="ppc_after_info">Maximum width is ' + width_max +'</span>');
}
else if (parseInt(ppc_height) < parseInt(height_min)){
  jQuery('#ppc_after').html('<span class="ppc_after_info">Minimum height is ' + height_min +'</span>');
}
else if (parseInt(ppc_height) > parseInt(height_max)){
  jQuery('#ppc_after').html('<span class="ppc_after_info">Maximum height is ' + height_max +'</span>');
}
else if (!ppc_width && !ppc_height) {
  jQuery('#ppc_after').html('Enter value');
}
else if (!ppc_width) {
  jQuery('#ppc_after').html('Enter width value');
}
else if (!ppc_height) {
  jQuery('#ppc_after').html('Enter height value');
}

else {
  var price = ppc_width * ppc_height * priceper;
  jQuery('#ppc_after').html('<span class="ppc_after_info">'+price+'€</span>');
}


}
