<?php
namespace WPPImport\modules\product_price_import;
use WPPImport\services\Uploader;

class ProductPriceImport
{
	protected $wpdb;
	protected $import_message;
	protected $product_id;
	protected $table;
	protected $import_date_table;

	public function __construct()
	{
		global $wpdb, $post;
		$this->wpdb = $wpdb;
		$this->import_message = '';
		$this->product_id = 0;
		$this->table = 'wppi_product_price';
		$this->import_date_table = 'wppi_product_price_import_date';

		if(!empty($_POST['product_id'])) $this->product_id = $_POST['product_id'];
		add_action('post_edit_form_tag', [$this, 'postEditFormTag']);
		add_action('admin_enqueue_scripts', [$this, 'adminEnqueueScripts']);
		add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
		add_action('woocommerce_after_add_to_cart_button', [$this, 'productDimensionsForm'], 10);
		add_filter('woocommerce_product_data_tabs', [$this, 'importTab']);
		add_action('woocommerce_product_data_panels', [$this, 'importTabContent']);
		add_filter('woocommerce_add_cart_item_data', [$this, 'addCartItemCustomData'], 10, 2);
		add_action('woocommerce_before_calculate_totals', [$this, 'changeCartItemPrice']);

		if(isset($_POST['wppi_width']) && isset($_POST['wppi_height'])) {
			$price = $this->calculatePriceByDimentions($_POST['wppi_product_id'], $_POST['wppi_width'], $_POST['wppi_height'], $_POST['wppi_dimensions']);

			$new_price = $price * intval($_POST['wppi_qty']);
			$new_price = number_format($new_price, 2);

			wp_send_json('&#163;'.$new_price);
		}

		add_filter('woocommerce_get_price_html',[$this, 'customProductPriceHtml'], 10, 2);
		add_filter('woocommerce_get_item_data',[$this, 'displayCartItemCustomMetaData'], 10, 2);
		add_filter('woocommerce_is_purchasable',[$this, 'disablePurchasable'], 10, 2);
		add_action('woocommerce_add_order_item_meta',[$this, 'addValuesToOrderItemMeta'], 10, 3);
		add_filter('manage_edit-product_columns', [$this, 'addImportDateColumn'], 20);
		add_action('manage_posts_custom_column', [$this, 'importDateColumnContent']);
		//add_filter('pre_get_posts', [$this, 'importDateSortable']);

		if(!empty($_POST) && $_POST['wppimport'] == 1) $this->upload($_POST, $_FILES);
		add_action('admin_notices', [$this, 'importNotice']);

		register_activation_hook(EZ_PLUGIN_PATH . 'product-price-import.php', [$this, 'createTable']);
		register_activation_hook(EZ_PLUGIN_PATH . 'product-price-import.php', [$this, 'createImportDateTable']);
	}

	public function postEditFormTag()
	{
		echo ' enctype="multipart/form-data"';
	}

	public function adminEnqueueScripts($hook_suffix)
	{
		wp_enqueue_script('wppimport', EZ_PLUGIN_URL.'modules/product_price_import/assets/js/import.js', array('jquery'));
		wp_enqueue_style('wppimport_css', EZ_PLUGIN_URL.'modules/product_price_import/assets/css/style.css', null, false, 'all' );
	}

	public function enqueueScripts($hook_suffix)
	{
		wp_enqueue_script('wppimain', EZ_PLUGIN_URL.'modules/product_price_import/assets/js/main.js', array('jquery'));
	}

	public function productDimensionsForm()
	{
		global $post;
		$product_id = $post->ID;

		include EZ_PLUGIN_PATH.'/modules/product_price_import/product_price_import/product_price_import_form.php';
	}

	public function importTab( $tabs )
	{
		$tabs['import'] = array(
			'label'		=> 'Import price',
			'target'	=> 'import_options',
			'class'		=> array( 'show_if_simple' ),
		);

		return $tabs;
	}

	public function importTabContent()
	{
		global $post;
		$product_id = $post->ID;

		include EZ_PLUGIN_PATH.'/modules/product_price_import/product_price_import/product_price_import_tab.php';
	}

	public function addCartItemCustomData($cart_item_meta, $product_id)
	{
		global $woocommerce;

		$cart_item_meta['width'] = $_POST['width'];
		$cart_item_meta['height'] = $_POST['height'];
		$cart_item_meta['dimensions'] = $_POST['dimensions'];

		return $cart_item_meta;
	}

	public function changeCartItemPrice($cart_obj)
	{
		foreach($cart_obj->cart_contents as $key => $value) {
			$product_id = $value['data']->get_id();
			$width = $value['width'];
			$height = $value['height'];
			$dimensions = $value['dimensions'];

			$new_price = $this->calculatePriceByDimentions($product_id, $width, $height, $dimensions);
			$value['data']->set_price($new_price);
		}
	}

	public function calculatePriceByDimentions($product_id, $width, $height, $dimensions)
	{
		$product_id = intval($product_id);
		$width = intval($width);
		$height = intval($height);
		$dimensions = sanitize_text_field($dimensions);
		$x = 1;

		if($dimensions == 'cm') {
			$width = $width * 10;
			$height = $height * 10;
		} else if($dimensions == 'in') {
			$width = ceil($width * 25.4);
			$height = ceil($height * 25.4);
		}

		do {
			$exist_width = $this->wpdb->get_var("
				SELECT width
				FROM {$this->table}
				WHERE product_id = {$product_id} AND width = {$width}
			");

			$exist_height = $this->wpdb->get_var("
				SELECT height
				FROM {$this->table}
				WHERE product_id = {$product_id} AND height = {$height}
			");

			if(empty($exist_width)) $width += 1;
			if(empty($exist_height)) $height += 1;

			$new_price = $this->wpdb->get_var("
				SELECT price
				FROM {$this->table}
				WHERE product_id = {$product_id} AND width = {$width} AND height = {$height}
			");

			if(!empty($new_price)) return $new_price;

			$x++;
		} while ($x < 100);

		return 0;
	}

	public function customProductPriceHtml($price, $instance)
	{
		global $product;
		$product_id = $product->get_id();

		$min_price = $this->wpdb->get_var("
			SELECT MIN(price) as min_price
			FROM {$this->table}
			WHERE product_id = {$product_id}
		");

		if ( '' !== $product->get_price() && ! $product->is_on_sale() && !empty($min_price) ) {
			$price = '<span id="wppi_price_from">From: </span>'.wc_price( $min_price ) . $product->get_price_suffix();
		} else {
			$price = '';
		}


		return $price;
	}

	public function displayCartItemCustomMetaData($item_data, $cart_item)
	{
		if(isset($cart_item['width']) && isset($cart_item['width'])) {
			$item_data[] = array(
				'key'       => 'width',
				'value'     => $cart_item['width'],
			);
		}

		if(isset($cart_item['height']) && isset($cart_item['height'])) {
			$item_data[] = array(
				'key'       => 'height',
				'value'     => $cart_item['height'],
			);
		}

		if(isset($cart_item['dimensions']) && isset($cart_item['dimensions'])) {
			$item_data[] = array(
				'key'       => 'dimensions',
				'value'     => $cart_item['dimensions'],
			);
		}

		return $item_data;
	}

	public function disablePurchasable($purchasable, $product)
	{
		global $post;
		$exist_data = $this->isExistData($post->ID);

		if($exist_data != $post->ID) {
			$purchasable = false;
		}

		return $purchasable;
	}

	public function addValuesToOrderItemMeta($item_id, $meta_key, $meta_value)
	{
		global $woocommerce;

		wc_add_order_item_meta($item_id, 'width', $meta_key['width']);
		wc_add_order_item_meta($item_id, 'height', $meta_key['height']);
		wc_add_order_item_meta($item_id, 'dimensions', $meta_key['dimensions']);
	}

	public function upload($data, $files)
	{
		$path = EZ_WPPI_UPLOADS_PATH;
		$input = 'upload';
		$file_name = null;
		$mimes = ['csv'=>'text/csv'];

		$uploader = new Uploader( $input, $path, $file_name, $mimes );

		$error_messages = [
			'type'		=> 'Wrong file type!',
			'file_name' => 'Wrong file name!',
			'error'		=> 'There was an error while uploading file.',
			'size'		=> 'File is too big. Max size is: 5mb',
			'empty'		=> 'File is not chosen!',
			'empty_data'=> 'Empty data. Check file lines!'
		];

		$success_messages = [
			'import_complete' => 'Import complete successfully!'
		];

		$uploader->setErrorMessages($error_messages);
		$uploader->setSuccessMessages($success_messages);
		$uploader->upload();

		if($uploader->upload()) {
			$open = fopen($path.$uploader->getName(), "r");
			$data_arr = array();
			$row = 1;

			if(($handle = $open) !== FALSE) {
				while(($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
					$num = count($data);
					for($c=0; $c < $num; $c++) {
						if($row == 1) {
							$data_arr['width'][$c] = $data[$c];
						} else {
							if($c == 0) {
								$data_arr['height'][$row] = $data[$c];
							} else {
								$data_arr['price'][$row][$c] = $data[$c];
							}
						}
					}
					$row++;
				}
			}

			$this->insertRow($data_arr, $this->product_id);
			fclose($open);
			unlink($path.$uploader->getName());

			if($path.$uploader->getName()) $uploader->success();
			$this->import_message = $uploader->getSuccessNotification();
			add_filter('redirect_post_location', [$this, 'addNoticeQueryVar'], 99);
		} else {
			$this->import_message = $uploader->getError();
			add_filter('redirect_post_location', [$this, 'addNoticeQueryVar'], 99);
		}
	}

	public function insertRow($data_arr, $product_id)
	{
		if(is_array($data_arr) === false) return;

		$product_id = intval($product_id);
		if($product_id < 1) return;

		$width_data = $data_arr['width'];
		$height_data = $data_arr['height'];

		$price_data = $data_arr['price'];

		$exist_data = $this->isExistData($product_id);
		if(!empty($exist_data)) $this->wpdb->delete($this->table, array('product_id' => $product_id));

		for($i=2; $i <= 13; $i++) {
			foreach($width_data as $width_key => $width) {
				if($width_key > 0) {
					$this->insert($product_id, $width, $height_data[$i], $price_data[$i][$width_key]);
				}
			}
		}
	}

	public function insert($product_id, $width, $height, $price)
	{
		$product_id = intval($product_id);
		$width  = intval($width);
		$height = intval($height);
		$price  = floatval($price);

		$this->wpdb->insert($this->table,
			array(
				'product_id' => $product_id,
				'width' 	 => intval($width),
				'height' 	 => intval($height),
				'price'		 => floatval($price)
			),
			array('%d', '%d', '%d', '%f')
		);

		if($this->wpdb->insert_id > 0) $this->insertImportDate($product_id);
	}

	public function insertImportDate($product_id)
	{
		$product_id = intval($product_id);

		$imported = $this->wpdb->get_var("SELECT product_id FROM {$this->import_date_table} WHERE product_id = {$product_id}");
		if(empty($imported)) {
			$this->wpdb->insert($this->import_date_table,
			array(
				'product_id'  => $product_id,
				'import_date' => date('Y-m-d')
			),
			array('%d', '%s')
			);
		} else {
			$this->wpdb->update($this->import_date_table,
			array(
				'import_date' => date('Y-m-d')
			),
			array( 'product_id' => $product_id ), // WHERE
			array( '%s' )
			);
		}
	}

	public function addNoticeQueryVar($location)
	{
		remove_filter('redirect_post_location', [$this, 'addNoticeQueryVar'], 99);
		return add_query_arg(array('import_notice' => $this->import_message), $location);
	}

	public function importNotice()
	{
		if(!isset($_GET['import_notice'])) return;
		$notice_class = $_GET['import_notice'] == 'Import complete successfully!' ? 'notice notice-success notice-style-green' : 'error notice notice-style-red'; ?>

		<div class="<?php echo $notice_class; ?>">
			<p class="easy-seo-notice-style"><?php _e($_GET['import_notice'], 'my_plugin_textdomain'); ?></p>
		</div><?php
	}

	public function isExistData($product_id)
	{
		$product_id = intval($product_id);

		$exist_data = $this->wpdb->get_var("SELECT product_id FROM {$this->table} WHERE product_id = {$product_id} LIMIT 1");
		return $exist_data;
	}

	public function addImportDateColumn($columns_array)
	{
		return array_slice( $columns_array, 0, 7, true )
		+ array( 'import_date' => 'Import date' )
		+ array_slice( $columns_array, 7, NULL, true );
	}

	public function importDateColumnContent($column_name)
	{
		$product_id = get_the_ID();

		if($column_name  == 'import_date') {
			$import_date = $this->wpdb->get_var("
				SELECT import_date
				FROM {$this->import_date_table}
				WHERE product_id = {$product_id}
			");

			if(!empty($import_date)) echo $import_date;
		}
	}

	public function createTable()
	{
		$sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
		id int(11) NOT NULL AUTO_INCREMENT,
		product_id bigint NOT NULL,
		width int(4) NOT NULL,
		height int(4) NOT NULL,
		price float,
		PRIMARY KEY (id),
		FOREIGN KEY (product_id) REFERENCES ".$this->wpdb->prefix."posts(ID)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta($sql);
	}

	public function createImportDateTable()
	{
		$sql = "CREATE TABLE IF NOT EXISTS {$this->import_date_table} (
		id int(11) NOT NULL AUTO_INCREMENT,
		product_id bigint NOT NULL,
		import_date date NOT NULL,
		PRIMARY KEY (id),
		FOREIGN KEY (product_id) REFERENCES ".$this->wpdb->prefix."posts(ID)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta($sql);
	}
}
