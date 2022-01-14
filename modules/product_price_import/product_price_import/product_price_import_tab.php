<div id="import_options" class="panel woocommerce_options_panel hidden">
	<form action="" method="post" id="import_form" class="import_form" enctype="multipart/form-data">
		<input type="hidden" name="wppimport" value="1">
		<input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
		<input type="file" id="upload" name="upload" class="custom-file-input">
		<input type="submit" value="Import" name="import" class="import_input_button">
	</form>
</div>
