<?php
namespace WPPImport\modules\product_price_calculate;
use WPPImport\services\Uploader;

class ProductPriceCalculate
{
  protected $wpdb;
  protected $product_id;
  protected $product;
  protected $post;
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
    add_filter('woocommerce_get_price_html',[$this, 'customProductPriceHtml'], 10, 2);
  //  add_action('woocommerce_after_add_to_cart_button', [$this, 'productDimensionsForm'], 10);
    add_action( 'woocommerce_single_product_summary', [$this,'custom_single_product_summary'], 2 );
    add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
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
  //$query1 = 'SELECT meta_value from wp_postmeta where meta_key="_priceper" and post_id='.$product_id.'';
  //$query2 = 'SELECT meta_value from wp_postmeta where meta_key="_pricepermeasure" and post_id='.$product_id.'';
  $priceper = get_post_meta($product_id, '_priceper')[0];
  $pricepermeasure = get_post_meta($product_id, '_pricepermeasure')[0];

/*  $result1 = $wpdb ->get_results($query1);
  $result2 = $wpdb ->get_results($query2);
  foreach ( $result1 as $page )
{
    $priceper = $page->meta_value;

}
foreach ( $result2 as $page )
{
    $pricepermeasure = $page->meta_value;

}*/
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



}
