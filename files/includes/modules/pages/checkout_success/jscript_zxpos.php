<?php
// BOF Barcode Cart
$orders_query = $db->Execute("SELECT order_total, currency, currency_value FROM " . TABLE_ORDERS . "
                 WHERE orders_id = '".$zv_orders_id."'");
$order_total = $orders_query->fields['order_total'];
$currency_value = $orders_query->fields['currency_value'];
$order_currency = $orders_query->fields['currency'];

$currencies_query = $db->Execute("SELECT decimal_places FROM " . TABLE_CURRENCIES . " WHERE code = '".$order_currency."'");
$decimal_places = $currencies_query->fields['decimal_places'];
?>

<script language="javascript" type="text/javascript"><!--
$(document).ready(function(){
	$('#cashGiven').focus();
	$('#cashGiven').on("keyup", function() {
		var ordertotal = <?php echo $order_total*$currency_value; ?>;
	    var total1 = $('#cashGiven').val() - ordertotal;
		var total = (total1).toFixed(<?php echo $decimal_places; ?>);
		$('#cashReturn').html(total);
  });
  $('#cashCalc').on("keyup keypress", function(e) {
  var code = e.keyCode || e.which; 
  if (code  == 13) {               
    e.preventDefault();
    return false;
  }
});
 
});
</script>