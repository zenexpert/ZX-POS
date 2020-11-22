<?php
class pos_shipping_observer extends base {

  function __construct() {
    $this->attach ($this, array('NOTIFY_SHIPPING_MODULE_GET_ALL_QUOTES'));
    
  }
  
  // -----
  // Monitoring the notifier just after the order-status record for the order has been created.
  // The 'updated_by' field is set to the logged-in EMP admin's name, so long as the field has
  // been previously added to the table.
  //
  function update(&$class, $paramArray) {
	  if(isset($_SESSION['POStoken'])) {
		  if($_POST['shipping'] != 'posinstore_posinstore') {
			//  unset($_SESSION['shipping']);
		  } else {
			  $_SESSION['shipping'] = array('id'=>'posinstore_posinstore');
		  }
		  $_SESSION['payment'] = 'posinstorepayment';
	  }
 }
}