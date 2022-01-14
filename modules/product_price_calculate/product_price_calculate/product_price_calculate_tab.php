<div id="price_calculation" class="panel woocommerce_options_panel hidden">
  <form action="" method="post" id="import_form" class="import_form" enctype="multipart/form-data">
    <label class="customized-labels" for="">Ivedimo vienetas</label>
    <select name="length" id="cars">
  <option value="mm">mm</option>
  <option value="cm">cm</option>
  <option value="m">m</option>

</select>
<br>
<br>

    <label class="customized-labels" for="">Min width:</label>
    <input type="hidden" name="product_id" class="front_product_id" value="<?php echo $product_id; ?>">
		<input type="number" id="upload" name="min_width" class="custom-file-input"  pattern="[0-9]+([\.,][0-9]+)?" step="0.01" value="<?php echo get_post_meta($product_id, '_minwidth')[0] ?>" required >
	  <br>
    <br>
    <label class="customized-labels" for="">Max width:</label>
    <input type="number" id="upload" name="max_width" class="custom-file-input"  pattern="[0-9]+([\.,][0-9]+)?" step="0.01" value="<?php echo get_post_meta($product_id, '_maxwidth')[0] ?>" required>
    <br>
    <br>
    <label class="customized-labels" for="">Min height:</label>
    <input type="number" id="upload" name="min_height" class="custom-file-input"  pattern="[0-9]+([\.,][0-9]+)?" step="0.01" value="<?php echo get_post_meta($product_id, '_minheight')[0] ?>" required>
    <br>
    <br>
    <label class="customized-labels" for="">Max height:</label>
    <input type="number" id="upload" name="max_height" class="custom-file-input"  pattern="[0-9]+([\.,][0-9]+)?" step="0.01" value="<?php echo get_post_meta($product_id, '_maxheight')[0] ?>" required>
    <br>
    <br>
    <label id="price_label" for="">Price</label>
    <select name="pricepermeasure" id="cars">
  <option value="mm2">mm2</option>
  <option value="cm2">cm2</option>
  <option value="m2">m2</option>

</select>
    <input type="number" id="upload" name="price" class="custom-file-input" pattern="[0-9]+([\.,][0-9]+)?" step="0.01" value="<?php echo get_post_meta($product_id, '_priceper')[0] ?>"  required>
    <input type="submit" value="Submit" name="submit1" class="">
	</form>
</div>

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
