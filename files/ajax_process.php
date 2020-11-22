<?php
// BOF Barcode Cart
?>
<?php
require('includes/application_top.php');

$action = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';

$result = array('result_content'=>'', 'result_success'=> false);
switch($action)
{
    case "lookupProductModel":
        $product_model = isset($_REQUEST['product_model']) ? $_REQUEST['product_model'] : '';
        if (trim($product_model)=='')
        {
              $result['result_content'] = 'Please enter Product Model field';                 
              break;
        }
        else
        {
              $pro_id = zen_bc_lookup_product_by_model($product_model);

              if ($pro_id != '')
              {
                  $result['result_content'] = $pro_id;
                  $result['result_success'] = true;
              }       
              else
              {
                  $product_attr = zen_bc_lookup_product_attr_by_model($product_model);
                  if ($product_attr != null && $product_attr != "")
                  {
                      $result['result_content'] = $product_attr;
                      $result['result_success'] = true;
                  }
                  else
                  {
                      $result['result_content'] = 'Product model '. $product_model. ' not found';
                      $result['result_success'] = false;             
                  }
              }  
        }
        break;
     case "lookupProductBarcode":
        $product_barcode = isset($_REQUEST['product_barcode']) ? $_REQUEST['product_barcode'] : '';
        if (trim($product_barcode)=='')
        {
              $result['result_content'] = 'Please enter Product Barcode field';
              break;
        }
        else
        {
              $pro_id = zen_bc_lookup_product_by_barcode($product_barcode);

              if ($pro_id != '')
              {
                  $result['result_content'] = $pro_id;
                  $result['result_success'] = true;
              }       
              else
              {
                  $product_attr = zen_bc_lookup_product_attr_by_barcode($product_barcode);
                  if ($product_attr != null && $product_attr != "")
                  {
                      $result['result_content'] = $product_attr;
                      $result['result_success'] = true;
                  }
                  else
                  {
                      $result['result_content'] = 'Product Barcode '. $product_barcode. ' not found';
                      $result['result_success'] = false;
                  }

              }  
        }
        break;   
        
     case "addProductToCart":
        $_GET['action']= 'add_product';
        $_POST['securityToken'] = $_REQUEST['securityToken'];
        $_POST['products_id'] = $_POST['proId'];
        $_POST['cart_quantity'] = $_POST['qty'];
        require(DIR_WS_INCLUDES . 'main_cart_actions.php');
        getCartContent();
        break; 
     case "updateProductCart":
        $_GET['action']= 'update_product';

        $products_id = isset($_POST['proId']) ? $_POST['proId'] : $_REQUEST['products_id'];

        $_POST['securityToken'] = $_REQUEST['securityToken'];
        $_POST['products_id'] = array($products_id);
        $_POST['cart_quantity'] = array($_POST['qty']);
        if (strpos($products_id, ":")!== false)
        {
            $cartContent = $_SESSION['cart']->contents;
            if (isset($cartContent[$products_id]) && isset($cartContent[$products_id]['attributes']))
            $_POST['id'][$products_id] = $cartContent[$products_id]['attributes'];
        }

        require(DIR_WS_INCLUDES . 'main_cart_actions.php');
        getCartContent();
        break; 
     case "delProductCart":
        $_GET['action']= 'remove_product';
         
        if (isset($_POST['proId']) && zen_not_null($_POST['proId'])) $_SESSION['cart']->remove($_POST['proId']);
        //$_POST['cart_quantity'] = array($_POST['qty']);
        //require(DIR_WS_INCLUDES . 'main_cart_actions.php');
        getCartContent();
        break; 
     case "addProductAttrToCart":
        $_GET['action']= 'add_product';
        $_POST['securityToken'] = $_REQUEST['securityToken'];
        $_POST['products_id'] = $_REQUEST['products_id'];
        $_POST['cart_quantity'] = $_REQUEST['qty'];
        $_POST['stock_id'] = $_REQUEST['stock_id'];
        $attribs = explode(',', $_REQUEST['stock_attributes']);
        $_POST['id'] = zen_bc_lookup_product_attr_pro_id($attribs, $_REQUEST['products_id']);
        require(DIR_WS_INCLUDES . 'main_cart_actions.php');
        getCartContent();
        break;     
     case "discount":
	    $_GET['action']= 'discount';
		$discount = $_POST['discount'];
		
		$coupon_code = create_coupon_code();
        $sql_data_array = array(
          'coupon_code' => $coupon_code,
          'coupon_amount' => $discount,
          'coupon_start_date' => date('Y-m-d'),
          'coupon_expire_date' => date('Y-m-d', strtotime(date('Y-m-d') . ' + 1 day')),
          'date_created' => date('Y-m-d')
        );
        zen_db_perform(TABLE_COUPONS, $sql_data_array);
        
        // add coupon description
        $coupon_id = $db->Insert_ID();
        $sql_data_array = array(
          'coupon_id' => $coupon_id,
          'language_id' => 1,
          'coupon_name' => 'POS Discount'
        );
        zen_db_perform(TABLE_COUPONS_DESCRIPTION, $sql_data_array);
		
		// apply coupon to cart
		$discount_coupon_query = "SELECT coupon_id, coupon_amount, coupon_type, coupon_minimum_order, uses_per_coupon, uses_per_user,
              						 restrict_to_products, restrict_to_categories, coupon_zone_restriction
							  FROM " . TABLE_COUPONS . "
							  WHERE coupon_code = :couponID";
		$discount_coupon_query = $db->bindVars($discount_coupon_query, ':couponID', $coupon_code, 'string');
		$discount_coupon = $db->Execute($discount_coupon_query);
		
		$total = (float)$_SESSION['cart']->total - $discount;
		$_SESSION['discount_in_cart'] = $currencies->format($discount);
		$_SESSION['cc_id'] = $coupon_id;
		if($discount > (float)$_SESSION['cart']->total) {
			$_SESSION['total_in_cart'] = $currencies->format(0);
		} else {
			$_SESSION['total_in_cart'] = $currencies->format($total);
		}
		require(DIR_WS_INCLUDES . 'main_cart_actions.php');
		getCartContent();
}


function getCartContent()
{
    global $result, $currencies, $messageStack, $db;
    
    $language_page_directory = DIR_WS_LANGUAGES . $_SESSION['language'] . '/';
    require_once($language_page_directory . 'barcode_cart.php');

    // Validate Cart for checkout
    $_SESSION['valid_to_checkout'] = true;
    $_SESSION['cart_errors'] = '';
    $_SESSION['cart']->get_products(true);

    // used to display invalid cart issues when checkout is selected that validated cart and returned to cart due to errors
    if (isset($_SESSION['valid_to_checkout']) && $_SESSION['valid_to_checkout'] == false) {
      $messageStack->add('shopping_cart', ERROR_CART_UPDATE . $_SESSION['cart_errors'] , 'caution');
    }

    // build shipping with Tare included
    $shipping_weight = $_SESSION['cart']->show_weight();
    $totalsDisplay = '';
	
	$cart_total = $currencies->format($_SESSION['cart']->show_total());
	if(isset($_SESSION['discount_in_cart']) && $_SESSION['discount_in_cart'] != '0') {
		$cart_total = $_SESSION['total_in_cart'];
	}
    
    switch (true) {
      case (SHOW_TOTALS_IN_CART == '1'):
      $totalsDisplay .= '<div class="bc_total_items">'.TEXT_TOTAL_ITEMS . $_SESSION['cart']->count_contents() .'</div><div class="bc_total_weight">'. TEXT_TOTAL_WEIGHT . $shipping_weight . TEXT_PRODUCT_WEIGHT_UNIT . '</div><div class="bc_total_amount">'.TEXT_TOTAL_AMOUNT . $cart_total.'</div>';
      break;
      case (SHOW_TOTALS_IN_CART == '2'):
      $totalsDisplay .= '<div class="bc_total_items">'.TEXT_TOTAL_ITEMS . $_SESSION['cart']->count_contents() .'</div>'. ($shipping_weight > 0 ? '<div class="bc_total_weight">'.TEXT_TOTAL_WEIGHT . $shipping_weight . TEXT_PRODUCT_WEIGHT_UNIT .'</div>' : '') . '<div class="bc_total_amount">'.TEXT_TOTAL_AMOUNT . $cart_total.'</div>';
      break;
      case (SHOW_TOTALS_IN_CART == '3'):
      $totalsDisplay .= '<div class="bc_total_items">'.TEXT_TOTAL_ITEMS . $_SESSION['cart']->count_contents() .'</div><div class="bc_total_amount">'. TEXT_TOTAL_AMOUNT . $cart_total.'</div>';
      break;
    }


    $flagHasCartContents = ($_SESSION['cart']->count_contents() > 0);
    $cartShowTotal = $currencies->format($_SESSION['cart']->show_total());

    $flagAnyOutOfStock = false;
    $products = $_SESSION['cart']->get_products();

    $productArray = array();
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      $attributeHiddenField = "";
      $attrArray = false;
      $productsName = $products[$i]['name'];
      // Push all attributes information in an array
      if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
        if (PRODUCTS_OPTIONS_SORT_ORDER=='0') {
          $options_order_by= ' ORDER BY LPAD(popt.products_options_sort_order,11,"0")';
        } else {
          $options_order_by= ' ORDER BY popt.products_options_name';
        }
        foreach ($products[$i]['attributes'] as $option => $value) {
          $attributes = "SELECT popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix
                         FROM " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                         WHERE pa.products_id = :productsID
                         AND pa.options_id = :optionsID
                         AND pa.options_id = popt.products_options_id
                         AND pa.options_values_id = :optionsValuesID
                         AND pa.options_values_id = poval.products_options_values_id
                         AND popt.language_id = :languageID
                         AND poval.language_id = :languageID " . $options_order_by;

          $attributes = $db->bindVars($attributes, ':productsID', $products[$i]['id'], 'integer');
          $attributes = $db->bindVars($attributes, ':optionsID', $option, 'integer');
          $attributes = $db->bindVars($attributes, ':optionsValuesID', $value, 'integer');
          $attributes = $db->bindVars($attributes, ':languageID', $_SESSION['languages_id'], 'integer');
          $attributes_values = $db->Execute($attributes);
          //clr 030714 determine if attribute is a text attribute and assign to $attr_value temporarily
          if ($value == PRODUCTS_OPTIONS_VALUES_TEXT_ID) {
            $attributeHiddenField .= zen_draw_hidden_field('id[' . $products[$i]['id'] . '][' . TEXT_PREFIX . $option . ']',  $products[$i]['attributes_values'][$option]);
            $attr_value = htmlspecialchars($products[$i]['attributes_values'][$option], ENT_COMPAT, CHARSET, TRUE);
          } else {
            $attributeHiddenField .= zen_draw_hidden_field('id[' . $products[$i]['id'] . '][' . $option . ']', $value);
            $attr_value = $attributes_values->fields['products_options_values_name'];
          }

          $attrArray[$option]['products_options_name'] = $attributes_values->fields['products_options_name'];
          $attrArray[$option]['options_values_id'] = $value;
          $attrArray[$option]['products_options_values_name'] = $attr_value;
          $attrArray[$option]['options_values_price'] = $attributes_values->fields['options_values_price'];
          $attrArray[$option]['price_prefix'] = $attributes_values->fields['price_prefix'];
        }
      } //end foreach [attributes]
      if (STOCK_CHECK == 'true') {
        $flagStockCheck = zen_check_stock($products[$i]['id'], $products[$i]['quantity']);
    // bof: extra check on stock for mixed YES
        if ($flagStockCheck != true) {
    //echo zen_get_products_stock($products[$i]['id']) - $_SESSION['cart']->in_cart_mixed($products[$i]['id']) . '<br>';
          if ( zen_get_products_stock($products[$i]['id']) - $_SESSION['cart']->in_cart_mixed($products[$i]['id']) < 0) {
            $flagStockCheck = '<span class="markProductOutOfStock">' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '</span>';
          } else {
            $flagStockCheck = '';
          }
        }
    // eof: extra check on stock for mixed YES
        if ($flagStockCheck == true) {
          $flagAnyOutOfStock = true;
        }
      }
      $linkProductsImage = zen_href_link(zen_get_info_page($products[$i]['id']), 'products_id=' . $products[$i]['id']);
      $linkProductsName = zen_href_link(zen_get_info_page($products[$i]['id']), 'products_id=' . $products[$i]['id']);
      $productsImage = (IMAGE_SHOPPING_CART_STATUS == 1 ? zen_image(DIR_WS_IMAGES . $products[$i]['image'], $products[$i]['name'], IMAGE_SHOPPING_CART_WIDTH, IMAGE_SHOPPING_CART_HEIGHT) : '');
      $show_products_quantity_max = zen_get_products_quantity_order_max($products[$i]['id']);
      $showFixedQuantity = (($show_products_quantity_max == 1 or zen_get_products_qty_box_status($products[$i]['id']) == 0) ? true : false);

      $showFixedQuantityAmount = $products[$i]['quantity'] . zen_draw_hidden_field('cart_quantity[]', $products[$i]['quantity']);
      $showMinUnits = zen_get_products_quantity_min_units_display($products[$i]['id']);
      $quantityField = zen_draw_input_field('cart_quantity[]', $products[$i]['quantity'], 'size="4"');
      $ppe = $products[$i]['final_price'];
      $ppe = zen_round(zen_add_tax($ppe, zen_get_tax_rate($products[$i]['tax_class_id'])), $currencies->get_decimal_places($_SESSION['currency']));
      $ppt = $ppe * $products[$i]['quantity'];
      //$productsPriceEach = $currencies->format($ppe) . ($products[$i]['onetime_charges'] != 0 ? '<br />' . $currencies->display_price($products[$i]['onetime_charges'], zen_get_tax_rate($products[$i]['tax_class_id']), 1) : '');
	  $productsPriceEach = zen_get_products_display_price((int)$products[$i]['id']);
      $productsPriceTotal = $currencies->format($ppt) . ($products[$i]['onetime_charges'] != 0 ? '<br />' . $currencies->display_price($products[$i]['onetime_charges'], zen_get_tax_rate($products[$i]['tax_class_id']), 1) : '');
      $buttonUpdate = ((SHOW_SHOPPING_CART_UPDATE == 1 or SHOW_SHOPPING_CART_UPDATE == 3) ? zen_image_submit(ICON_IMAGE_UPDATE, ICON_UPDATE_ALT) : '') . zen_draw_hidden_field('products_id[]', $products[$i]['id']);
      $productArray[$i] = array('attributeHiddenField'=>$attributeHiddenField,
                                'flagStockCheck'=>$flagStockCheck,
                                'flagShowFixedQuantity'=>$showFixedQuantity,
                                'linkProductsImage'=>$linkProductsImage,
                                'linkProductsName'=>$linkProductsName,
                                'productsImage'=>$productsImage,
                                'productsName'=>$productsName,
                                'showFixedQuantity'=>$showFixedQuantity,
                                'showFixedQuantityAmount'=>$showFixedQuantityAmount,
                                'showMinUnits'=>$showMinUnits,
                                'productsPrice'=>$productsPriceTotal,
                                'productsPriceEach'=>$productsPriceEach,
                                'id'=>$products[$i]['id'],
                                'attributes'=>$attrArray,
                                'quantity'=>$products[$i]['quantity']);
    } // end FOR loop
    
    
 $cartContentsDisplay ='<tr class="productHeadings">
    	<td width="40%">Product</td>
        <td width="10%">Price</td>
        <td width="10%">Qty</td>
        <td width="12%">Action</td>
        <td width="20%">Subtotal</td>
        <td width="8%">Remove</td>
    </tr>';   

  foreach ($productArray as $product) {

   $cartContentsDisplay .='<tr class="bc_rows">
     
     <td class="bc_cartProductDisplay" width="40%">
<a href="'. $product['linkProductsName'].'"><span id="cartImage" class="back">'. $product['productsImage'].'</span><span id="cartProdTitle">'. $product['productsName'] . '<span class="alert bold">' . $product['flagStockCheck'] . '</span></span></a>
<br class="clearBoth" />'.
  $product['attributeHiddenField'];
  
  if (isset($product['attributes']) && is_array($product['attributes'])) {
      $cartContentsDisplay .= '<div class="cartAttribsList">';
      $cartContentsDisplay .= '<ul>';
        reset($product['attributes']);
        foreach ($product['attributes'] as $option => $value) {
            $cartContentsDisplay .= '<li>'. $value['products_options_name'] . TEXT_OPTION_DIVIDER . nl2br($value['products_options_values_name']).'</li>';
        }
      $cartContentsDisplay .= '</ul>';
      $cartContentsDisplay .= '</div>';
  }

$cartContentsDisplay .='</td>
<td class="bc_cartUnitDisplay" width="10%">'. $product['productsPriceEach'].'</td>
<td class="bc_cartQuantity" width="10%">'.
   zen_draw_input_field('cart_quantity_'.$product['id'], $product['quantity'], 'id="cart_quantity_'.$product['id'].'" size="4" onkeypress=" if (event.keyCode==13) {bc_quantity_keypress(\''.$product['id'].'\');  event.preventDefault();} "').

'</td>
<td class="bc_cartQuantityUpdate" width="12%">'.

 '<i class="fa fa-refresh" id="update_'.$product['id'].'" onclick="bc_update_product_cart(\''.$product['id'].'\')" ></i>' ;

$cartContentsDisplay .='</td>

<td class="bc_cartTotalDisplay" width="20%">'. $product['productsPrice'].'</td>
<td class="bc_cartRemoveItemDisplay"  width="8%">';

$cartContentsDisplay .=  '<i class="fa fa-remove" id="del_'.$product['id'].'"  onclick="bc_del_product_cart(\''.$product['id'].'\')" ></i>' ; 

  
$cartContentsDisplay .='</td>
     </tr>';
} // end foreach ($productArray as $product)

if(isset($_SESSION['discount_in_cart']) && $_SESSION['discount_in_cart'] != '$0.00') {
	$cartContentsDisplay .= '<tr>
		<td class="bc_cartProductDisplay" width="40%">
			<span id="cartImage" class="back"><img width="50" height="40" title=" Admin Discount " alt="Admin Discount" src="images/admin_discount.png">
</span>
			<span id="cartProdTitle">Admin Discount<span class="alert bold"></span></span>
		</td>
		<td class="bc_cartUnitDisplay" width="10%">
			-'.$_SESSION['discount_in_cart'].'
		</td>
		<td class="bc_cartQuantity" width="10%">
			&nbsp;
		</td>
		<td class="bc_cartQuantityUpdate" width="12%">
			&nbsp;
		</td>
		<td class="bc_cartTotalDisplay" width="20%">-'.$_SESSION['discount_in_cart'].'</td>
		<td class="bc_cartRemoveItemDisplay" width="8%">
			<i id="del_26" class="fa fa-remove" onclick="setDiscount(\'remove\')"></i>
		</td>';
}
    
    $cartTotalsDisplay = '<div class="cartTotalsDisplay important">'.  $totalsDisplay.'</div>';
    $cartTotalsDisplay .= '<br class="clearBoth" />';
    
    
    $result['result_content'] = array('cartContentsDisplay'=> $cartContentsDisplay,
                                      'cartTotalsDisplay' => $cartTotalsDisplay);
    $result['result_success']= true;                                  

}
   


echo  json_encode($result);
exit;
// EOF Barcode Cart  
?>
