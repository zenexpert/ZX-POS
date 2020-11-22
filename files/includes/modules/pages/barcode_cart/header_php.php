<?php
/**
* // BOF Barcode Cart
 * barcode_cart header_php.php
 * @package page
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_SHOPPING_CART');

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
$breadcrumb->add(NAVBAR_TITLE);

// Validate Cart for checkout
$_SESSION['valid_to_checkout'] = true;
$_SESSION['cart_errors'] = '';
$_SESSION['cart']->get_products(true);

// used to display invalid cart issues when checkout is selected that validated cart and returned to cart due to errors
if (isset($_SESSION['valid_to_checkout']) && $_SESSION['valid_to_checkout'] == false) {
  $messageStack->add('shopping_cart', ERROR_CART_UPDATE . $_SESSION['cart_errors'] , 'caution');
}

// check admin profiles, see if the token sent from admin matches any of the profiles and start a POS session
$pos_sql = 'SELECT admin_name, admin_email from ' . TABLE_ADMIN . ' WHERE admin_profile IN (1,2)';
$pos_result = $db->Execute($pos_sql);
$login_valid = false;
while(!$pos_result->EOF) {
	$pos_token = md5($pos_result->fields['admin_name'].$pos_result->fields['admin_email']);
	if($pos_token == $_POST['POStoken']) {
		$_SESSION['POStoken'] = $_POST['POStoken'];
		$_SESSION['POSstaff'] = $pos_result->fields['admin_name'];
	}
	if(isset($_COOKIE['zxpos'])) {
		if($pos_token == $_COOKIE['zxpos']){
			$_SESSION['POStoken'] = $_COOKIE['zxpos'];
			$_SESSION['POSstaff'] = $pos_result->fields['admin_name'];
		}
	}
	$pos_result->MoveNext();
}

// allow POS page for authorized admin
if(isset($_SESSION['POStoken'])) {
	
	// build shipping with Tare included
	$shipping_weight = $_SESSION['cart']->show_weight();
	/*
	  $shipping_weight = 0;
	  require(DIR_WS_CLASSES . 'order.php');
	  $order = new order;
	  require_once('includes/classes/http_client.php'); // shipping in basket
	  $total_weight = $_SESSION['cart']->show_weight();
	  $total_count = $_SESSION['cart']->count_contents();
	  require(DIR_WS_CLASSES . 'shipping.php');
	  $shipping_modules = new shipping;
	  $quotes = $shipping_modules->quote();
	*/
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

	// testing/debugging
	//  require(DIR_WS_MODULES . 'debug_blocks/shopping_cart_contents.php');

	$flagHasCartContents = ($_SESSION['cart']->count_contents() > 0);
	$cartShowTotal = $currencies->format($_SESSION['cart']->show_total());

	$flagAnyOutOfStock = false;
	$products = $_SESSION['cart']->get_products();

	$productArray = array();
	for ($i=0, $n=sizeof($products); $i<$n; $i++) {
	  if (($i/2) == floor($i/2)) {
	    $rowClass="rowEven";
	  } else {
	    $rowClass="rowOdd";
	  }
	  switch (true) {
	    case (SHOW_SHOPPING_CART_DELETE == 1):
	    $buttonDelete = true;
	    $checkBoxDelete = false;
	    break;
	    case (SHOW_SHOPPING_CART_DELETE == 2):
	    $buttonDelete = false;
	    $checkBoxDelete = true;
	    break;
	    default:
	    $buttonDelete = true;
	    $checkBoxDelete = true;
	    break;
	    $cur_row++;
	  } // end switch
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
	//  $showFixedQuantityAmount = $products[$i]['quantity'] . zen_draw_hidden_field('products_id[]', $products[$i]['id']) . zen_draw_hidden_field('cart_quantity[]', 1);
	//  $showFixedQuantityAmount = $products[$i]['quantity'] . zen_draw_hidden_field('cart_quantity[]', 1);
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
	//  $productsPriceEach = $currencies->display_price($products[$i]['final_price'], zen_get_tax_rate($products[$i]['tax_class_id']), 1) . ($products[$i]['onetime_charges'] != 0 ? '<br />' . $currencies->display_price($products[$i]['onetime_charges'], zen_get_tax_rate($products[$i]['tax_class_id']), 1) : '');
	//  $productsPriceTotal = $currencies->display_price($products[$i]['final_price'], zen_get_tax_rate($products[$i]['tax_class_id']), $products[$i]['quantity']) . ($products[$i]['onetime_charges'] != 0 ? '<br />' . $currencies->display_price($products[$i]['onetime_charges'], zen_get_tax_rate($products[$i]['tax_class_id']), 1) : '');
	//  $productsPriceTotal = $currencies->display_price($products[$i]['final_price'], zen_get_tax_rate($products[$i]['tax_class_id']), $products[$i]['quantity']) . ($products[$i]['onetime_charges'] != 0 ? '<br />' . $currencies->display_price($products[$i]['onetime_charges'], zen_get_tax_rate($products[$i]['tax_class_id']), 1) : '');
	//  echo  $currencies->rateAdjusted($tmp);
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
	                            'quantityField'=>$quantityField,
	                            'buttonUpdate'=>$buttonUpdate,
	                            'productsPrice'=>$productsPriceTotal,
	                            'productsPriceEach'=>$productsPriceEach,
	                            'rowClass'=>$rowClass,
	                            'buttonDelete'=>$buttonDelete,
	                            'checkBoxDelete'=>$checkBoxDelete,
	                            'id'=>$products[$i]['id'],
	                            'attributes'=>$attrArray,
	                            'quantity'=>$products[$i]['quantity']);
	} // end FOR loop

} else {  // if not authorized POS access
	$messageStack->add_session('header', 'Sorry, but you are not authorized to view this page. Please log in from the admin section.' , 'error');
	zen_redirect(zen_href_link(FILENAME_DEFAULT, '', $request_type));
}

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_SHOPPING_CART');
// EOF Barcode Cart
?>