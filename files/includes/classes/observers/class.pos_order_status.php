<?php
class pos_order_status extends base {

  function __construct() {
    $this->attach ($this, array('NOTIFY_ORDER_CART_ORDERSTATUS'));
    
  }
  
  // -----
  // Monitoring the notifier just after the order-status record for the order has been created.
  // The 'updated_by' field is set to the logged-in EMP admin's name, so long as the field has
  // been previously added to the table.
  //
  function update(&$class, $paramArray) {
	  if(isset($_SESSION['POStoken']) && isset($_SESSION['pos_order_status'])) {
          $GLOBALS['order']->info['order_status'] = $_SESSION['pos_order_status'];
	  }
 }
}