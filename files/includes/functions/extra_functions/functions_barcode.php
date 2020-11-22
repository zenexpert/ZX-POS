<?php
// BOF Barcode Cart
?>
<?php
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

function zen_bc_lookup_product_by_model($product_model='')
{
    global $db;
    
    $sql = "Select products_id from " . TABLE_PRODUCTS . " WHERE products_model = :products_model:";
    $sql = $db->bindVars($sql, ':products_model:', $product_model, 'string');

    $tmp = $db->execute($sql);
    if ($tmp->RecordCount())
        return $tmp->fields['products_id'];
    else
        return '';        
}

function zen_bc_lookup_product_attr_by_model($product_model='')
{
    global $db;

    $sql = "Select * from " . TABLE_PRODUCTS_WITH_ATTRIBUTES_STOCK . " where `customid` LIKE :customid:";
    $sql = $db->bindVars($sql, ':customid:', $product_model, 'string');


    $tmp = $db->execute($sql);
    if ($tmp->RecordCount() > 0)
        return $tmp->fields;
    else
        return '';
}

function zen_bc_lookup_product_by_barcode($products_barcode='')
{
    global $db;
    
    $sql = "Select products_id from " . TABLE_PRODUCTS . " where `products_barcode` LIKE :products_barcode:";
    $sql = $db->bindVars($sql, ':products_barcode:', $products_barcode, 'string');
    
    
    $tmp = $db->execute($sql);
    if ($tmp->RecordCount())
        return $tmp->fields['products_id'];
    else
        return '';        
}

function zen_bc_lookup_product_attr_by_barcode($products_barcode='')
{
    global $db;

    // ZenExpert - modified to allow lookup by stock_id
    $sql = "Select * from " . TABLE_PRODUCTS_WITH_ATTRIBUTES_STOCK . " where `barcode` LIKE :barcode: OR stock_id = :stockid:";
    $sql = $db->bindVars($sql, ':barcode:', $products_barcode, 'string');
    $sql = $db->bindVars($sql, ':stockid:', $products_barcode, 'integer');


    $tmp = $db->execute($sql);
    if ($tmp->RecordCount() > 0)
        return $tmp->fields;
    else
        return '';
}

function zen_bc_lookup_product_attr_pro_id($products_attributes_id='', $products_id='')
{
    global $db;
    $att = implode(',',$products_attributes_id);
    $sql = "Select * from " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE `products_attributes_id` IN (".$att.") AND `products_id` = :products_id:";
    $sql = $db->bindVars($sql, ':products_id:', $products_id, 'integer');

    $result = array();
    $tmp = $db->execute($sql);
    if ($tmp->RecordCount()>0)
    {
        while (!$tmp->EOF) {
            $result[$tmp->fields['options_id']] = $tmp->fields['options_values_id'];
            $tmp->MoveNext();
        }
    }

    return $result;
}

function zen_bc_customer_lookup() {
	global $db;
	$customer_list = array();
	$customer_list[] = array('id'=>'', 'text'=>'');
	$customers_values = $db->Execute("select customers_email_address, customers_firstname, customers_lastname, customers_telephone " .
                  "from " . TABLE_CUSTOMERS . " WHERE customers_authorization = '0' " .
                  "order by customers_lastname, customers_firstname, customers_email_address");
    while(!$customers_values->EOF) {
      $customer_list[] = array('id' => $customers_values->fields['customers_email_address'],
                 'text' => $customers_values->fields['customers_lastname'] . ', ' . $customers_values->fields['customers_firstname'] . ' [' . $customers_values->fields['customers_email_address'] . '], [' . preg_replace("/[^0-9]/","",$customers_values->fields['customers_telephone']).']');
      $customers_values->MoveNext();
    }
	return $customer_list;
}

function create_coupon_code($salt="secret", $length=SECURITY_CODE_LENGTH) {
    global $db;
    $ccid = md5(uniqid("","salt"));
    $ccid .= md5(uniqid("","salt"));
    $ccid .= md5(uniqid("","salt"));
    $ccid .= md5(uniqid("","salt"));
    srand((double)microtime()*1000000); // seed the random number generator
    $random_start = @rand(0, (128-$length));
    $good_result = 0;
    while ($good_result == 0) {
      $id1=substr($ccid, $random_start,$length);
      $query = $db->Execute("select coupon_code
                             from " . TABLE_COUPONS . "
                             where coupon_code = '" . $id1 . "'");

      if ($query->RecordCount() < 1 ) $good_result = 1;
    }
    return $id1;
  }
  
if (!function_exists('zen_datetime_short')) {
  function zen_datetime_short($raw_datetime) {
    if ( ($raw_datetime == '0001-01-01 00:00:00') || ($raw_datetime == '') ) return false;

    $year = (int)substr($raw_datetime, 0, 4);
    $month = (int)substr($raw_datetime, 5, 2);
    $day = (int)substr($raw_datetime, 8, 2);
    $hour = (int)substr($raw_datetime, 11, 2);
    $minute = (int)substr($raw_datetime, 14, 2);
    $second = (int)substr($raw_datetime, 17, 2);

    return strftime(DATE_TIME_FORMAT, mktime($hour, $minute, $second, $month, $day, $year));
  }
}

if (!function_exists('zen_get_order_status_name')) {
  function zen_get_order_status_name($order_status_id, $language_id = '') {
    global $db;

    if ($order_status_id < 1) return TEXT_DEFAULT;

    if (!is_numeric($language_id)) $language_id = $_SESSION['languages_id'];

    $status = $db->Execute("select orders_status_name
                            from " . TABLE_ORDERS_STATUS . "
                            where orders_status_id = '" . (int)$order_status_id . "'
                            and language_id = '" . (int)$language_id . "'");
    if ($status->EOF) return 'ERROR: INVALID STATUS ID: ' . (int)$order_status_id;
    return $status->fields['orders_status_name'] . ' [' . (int)$order_status_id . ']';
  }
}
?>
<?php
// EOF Barcode Cart
?>
