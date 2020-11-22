<?php
/**
 * @package admin
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 
 * @author: ZenExpert - www.zenexpert.com
*/

  function zen_get_products_barcode($product_id) {
    global $db;

    $product = $db->Execute("select products_barcode
                             from " . TABLE_PRODUCTS . "
                             where products_id = '" . (int)$product_id . "'");

    return $product->fields['products_barcode'];
  }
  function zx_get_staff($order_id) {
      global $db;
      
      $staff = $db->Execute("SELECT staff_name FROM ".TABLE_POS_ORDERS." WHERE orders_id = ".(int)$order_id."");
      return $staff->fields['staff_name'];
  }