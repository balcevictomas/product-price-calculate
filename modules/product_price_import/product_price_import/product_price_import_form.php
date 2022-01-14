<div class="meta_checkbox_container">
	<div class="meta_checkbox_box">
		<label class="meta_check_mini_box">mm
			<input type="radio" checked="checked" name="dimensions" class="dimensions" value="mm">
			<span class="meta_checkmark"></span>
		</label>
		<label class="meta_check_mini_box">cm
			<input type="radio" name="dimensions" class="dimensions" value="cm">
			<span class="meta_checkmark"></span>
		</label>
		<label class="meta_check_mini_box">in
			<input type="radio" name="dimensions" class="dimensions" value="in">
			<span class="meta_checkmark"></span>
		</label>
	</div>

	<div class="meta_select_box">
		<input type="hidden" name="product_id" class="front_product_id" value="<?php echo $product_id; ?>">
		<label for="quantity">Width:
			<input type="number" id="width" name="width" class="wppi_width" min="200" max="3000" required />
		</label>
		<label for="quantity">Height:
			<input type="number" id="height" name="height" class="wppi_height" min="50" max="600" required />
		</label>
		<div id="wppi_err"></div>
	</div>
	<button class="button-secondary" onclick="calculatePrice();return false;">Calculate price</button>
</div>
