<div id="price_calculation_form" class="panel woocommerce_options_panel hidden">

    <label class="customized-labels" for="">Ivedimo vienetas</label>
    <select name="unit" id="unit">
  <option value="mm">mm</option>
  <option value="cm">cm</option>
  <option value="m">m</option>

</select>
<br>
<br>

    <label class="customized-labels" for="">Width:</label>
    <input type="hidden" name="product_id" class="front_product_id" value="<?php echo $product_id; ?>">
    <input type="hidden" name="priceper" class="front_priceper" value="<?php echo get_post_meta($product_id, '_priceper')[0] ?>">
    <input type="hidden" name="pricepermeasure" class="front_pricepermeasure" value="<?php echo get_post_meta($product_id, '_pricepermeasure')[0] ?>">
    <input type="hidden" name="minwidth" class="front_minwidth" value="<?php echo get_post_meta($product_id, '_minwidth')[0] ?>">
    <input type="hidden" name="maxwidth" class="front_maxwidth" value="<?php echo get_post_meta($product_id, '_maxwidth')[0] ?>">
    <input type="hidden" name="minheight" class="front_minheight" value="<?php echo get_post_meta($product_id, '_minheight')[0] ?>">
    <input type="hidden" name="maxheight" class="front_maxheight" value="<?php echo get_post_meta($product_id, '_maxheight')[0] ?>">

    <input type="hidden" name="product_id" class="front_product_id" value="<?php echo $product_id; ?>">
		<input type="number" class="ppc_width"  name="width" pattern="[0-9]+([\.,][0-9]+)?"  step="0.01" required >
	  <br>
    <br>
    <label class="customized-labels" for="">Height:</label>
    <input type="number" class="ppc_height" name="height" pattern="[0-9]+([\.,][0-9]+)?" step="0.01" required>
    <br>
    <br>


    <button class="button-secondary" onclick="priceCalculate();return false;">Calculate</button>


  <div id="ppc_after"></div>
</div>
<?php

    //Or:
    foreach ($_POST as $key => $value)
        echo $key.'='.$value.'<br />';
?>
<style>
.customized-labels {
  margin:0!important;
  width: 100px!important;
}
#price_label {
  margin:0!important;
  width:33px!important;
}
</style>
