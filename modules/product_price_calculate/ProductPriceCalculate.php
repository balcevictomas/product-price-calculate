<?php
namespace WPPImport\modules\product_price_calculate;
use WPPImport\services\Uploader;

class ProductPriceCalculate
{
  protected $wpdb;
  protected $product_id;
  protected $product;
  public function __construct()
  {
    global $wpdb, $post, $product;
    $this->wpdb = $wpdb;
    $this->product_id = 0;

  //  echo "labas";
  //  die();
    if(!empty($_POST['product_id'])) $this->product_id = $_POST['product_id'];

    add_filter('woocommerce_product_data_tabs', [$this, 'importTabCal']);
    add_action('woocommerce_product_data_panels', [$this, 'importTabCalContent']);
    add_action('post_edit_form_tag', [$this, 'postEditFormTag']);
    add_filter('woocommerce_get_price_html',[$this, 'customProductPriceHtml'], 10, 2);
  //  add_action('woocommerce_after_add_to_cart_button', [$this, 'productDimensionsForm'], 10);
  add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
  //  add_action( 'woocommerce_single_product_summary', [$this,'custom_single_product_summary'], 2 );
add_action('woocommerce_after_add_to_cart_button', [$this, 'calculation_form'], 10);
    add_filter('woocommerce_add_cart_item_data', [$this, 'addCartItemCustomData'], 10, 2);
   add_action('woocommerce_before_calculate_totals', [$this, 'changeCartItemPrice']);
    if(!empty($_POST['submit1'])) $this->insert_or_update($_POST,$this->product_id);

  }




  public function importTabCal( $tabs )
	{
		$tabs['import_price'] = array(
			'label'		=> 'Price Calculation',
			'target'	=> 'price_calculation',
			'class'		=> array( 'show_if_simple' ),
		);

		return $tabs;
	}
  public function postEditFormTag()
  {
    echo ' enctype="multipart/form-data"';
  }
  public function importTabCalContent()
  {
    global $post;
    $product_id = $post->ID;
  //  echo $product_id;
    	include EZ_PLUGIN_PATH.'/modules/product_price_calculate/product_price_calculate/product_price_calculate_tab.php';
  }
  public function insert_or_update($data, $product_id)
  {
    global $wpdb, $post;

  //  $product_id = $post->ID;

//  var_dump($data);
//  die();

   $data = wp_unslash($data);
   $max_width = floatval($data['max_width']);
   $min_width = floatval($data['min_width']);
   $max_height = floatval($data['max_height']);
   $min_height = floatval($data['min_height']);
   $price = floatval($data['price']);
   $product_id = floatval($data['product_id']);
   //echo $product_id;
   //die();
   $length = sanitize_text_field($data['length']);
   $pricepermeasure = sanitize_text_field($data['pricepermeasure']);
   //var_dump($length);
    if ($length!="mm")
    {
        $max_width=$this->transform_to_mm($length, $max_width);
        $min_width=$this->transform_to_mm($length, $min_width);
        $max_height=$this->transform_to_mm($length, $max_height);
        $min_height=$this->transform_to_mm($length, $min_height);

    }

    update_post_meta( $product_id, '_minwidth', $min_width );
    update_post_meta( $product_id, '_maxwidth', $max_width );
    update_post_meta( $product_id, '_minheight', $min_height );
    update_post_meta( $product_id, '_maxheight', $max_height );
    update_post_meta( $product_id, '_priceper', $price );
    update_post_meta( $product_id, '_pricepermeasure', $pricepermeasure );



  }
public function transform_to_mm($length, $num)
  {
    if ($length == "cm")
    {
      $num *= 10;
    }
    if ($length == "m")
    {
      $num *= 1000;
    }
    return $num;
  }
public function customProductPriceHtml()
{
  global $product, $wpdb;
  $product_id = $product->get_id();

  $priceper = get_post_meta($product_id, '_priceper')[0];
  $pricepermeasure = get_post_meta($product_id, '_pricepermeasure')[0];

$pricepermeasure = str_replace('2', '<sup>2</sup>', $pricepermeasure);
  echo 'Price per ' .$pricepermeasure. ': '.$priceper.'&euro;';
}
public function custom_single_product_summary(){
    global $product;

    remove_action( 'woocommerce_single_product_summary', [$this,'woocommerce_template_single_excerpt'], 20 );
    add_action( 'woocommerce_single_product_summary', [$this,'calculation_form'], 20 );
}

public function calculation_form(){
  global $post;
  $product_id = $post->ID;
//  echo $product_id;
    include EZ_PLUGIN_PATH.'/modules/product_price_calculate/product_price_calculate/product_price_calculate_form.php';
}
public function enqueueScripts($hook_suffix)
{
  wp_enqueue_script('ppimain', EZ_PLUGIN_URL.'modules/product_price_calculate/assets/js/main.js', array('jquery'));
}

public function addCartItemCustomData($cart_item_meta, $product_id)
{

  global $woocommerce;

  $cart_item_meta['width'] = $_POST['width'];
  $cart_item_meta['height'] = $_POST['height'];
  $cart_item_meta['unit'] = $_POST['unit'];
  $cart_item_meta['pricepermeasure'] = $_POST['pricepermeasure'];
  $cart_item_meta['priceper'] = $_POST['priceper'];
 var_dump($cart_item_meta);

  return $cart_item_meta;
}

public function changeCartItemPrice($cart_obj)
{

    foreach($cart_obj->cart_contents as $key => $value) {
    $product_id = $value['data']->get_id();
    $product_id = $value['data']->get_id();
    $width = $value['width'];
    $height = $value['height'];
    $unit = $value['unit'];
    $pricepermeasure = $value['pricepermeasure'];
    $priceper = $value['priceper'];


    $new_price = $this->calculatePriceByUnit($product_id, $width, $height, $unit, $pricepermeasure, $priceper);
    $value['data']->set_price($new_price);
  }
}
public function calculatePriceByUnit($product_id, $width, $height, $unit, $pricepermeasure, $priceper)
{
  $product_id = intval($product_id);
  $width = intval($width);
  $height = intval($height);
  $priceper = intval($priceper);
  $unit = sanitize_text_field($unit);
  $pricepermeasure = sanitize_text_field($pricepermeasure);
  if ($pricepermeasure == 'mm2' && $unit =='cm' ) {
    $width *= 10;
    $height *= 10;
  }
  else if ($pricepermeasure == 'mm2' && $unit == 'm')
  {
    $width *= 1000;
    $height *= 1000;
  }

  $new_price = $width * $height * $priceper;

  return $new_price;

}

}
