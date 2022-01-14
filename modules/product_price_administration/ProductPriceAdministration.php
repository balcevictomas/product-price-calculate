<?php
namespace WPPImport\modules\product_price_administration;
use WPPImport\services\Uploader;

class ProductPriceAdministration
{

  protected $wpdb;
  protected $product_id;

  public function __construct()
  {

    global $wpdb, $post;
    $this->wpdb = $wpdb;
    $this->import_message = '';
    $this->product_id = 0;
    //add_action( 'admin_menu', [$this,'addProductsSubmenu'], 100 );
  // add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
//  var_dump($this);
  //die();





  }
public function addProductsSubmenu()
 {
   add_submenu_page(
        'edit.php?post_type=product',
        __( 'Product Price Administration' ), // Prodct Price Administration
        __( 'Product Price Administration' ),
        'manage_woocommerce', // Required user capability
        'ppa',
         array( $this, 'PpaInside' )
    );
  }


public function PpaInside() {

		include EZ_PLUGIN_PATH.'/modules/product_price_administration/product_price_administration/product_price_administration_form.php';
	}


public function enqueueScripts($hook_suffix)
	{
		wp_enqueue_script('wppimain', EZ_PLUGIN_URL.'modules/product_price_administration/assets/js/main.js', array('jquery'));
	}


}
