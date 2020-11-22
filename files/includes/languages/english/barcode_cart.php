<?php
/**
* // BOF Barcode Cart
 * @package languageDefines
 * @version $Id: barcode_cart.php 1 $
 */

define('NAVBAR_TITLE', 'Shopping Cart - POS');
define('HEADING_TITLE', 'Your Shopping Cart Contents');
define('HEADING_TITLE_EMPTY', 'Your Shopping Cart');
define('TEXT_INFORMATION', 'You may want to add some instructions for using the shopping cart here. (defined in includes/languages/english/shopping_cart.php)');
define('TABLE_HEADING_REMOVE', 'Remove');
define('TABLE_HEADING_QUANTITY', 'Qty.');
define('TABLE_HEADING_MODEL', 'Model');
define('TABLE_HEADING_PRICE','Unit');
define('TEXT_CART_EMPTY', 'Your Shopping Cart is empty.');
define('SUB_TITLE_SUB_TOTAL', 'Sub-Total:');
define('SUB_TITLE_TOTAL', 'Total:');

define('OUT_OF_STOCK_CANT_CHECKOUT', 'Products marked with ' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . ' are out of stock or there are not enough in stock to fill your order.<br />Please change the quantity of products marked with (' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '). Thank you');
define('OUT_OF_STOCK_CAN_CHECKOUT', 'Products marked with ' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . ' are out of stock.<br />Items not in stock will be placed on backorder.');

define('TEXT_TOTAL_ITEMS', '<i>Total Items:</i>&nbsp;&nbsp;&nbsp; ');
define('TEXT_TOTAL_WEIGHT', '<i>Total weight:</i>&nbsp;&nbsp; ');
define('TEXT_TOTAL_AMOUNT', '<i>TOTAL COST:</i>&nbsp; ');

define('TEXT_VISITORS_CART', '<a href="javascript:session_win();">[help (?)]</a>');
define('TEXT_OPTION_DIVIDER', '&nbsp;-&nbsp;');
define('TEXT_CART_CONTENTS', 'CART CONTENTS');
define('TEXT_NEW_BUTTON', 'NEW');
define('TEXT_EXISTING_BUTTON', 'EXISTING');
define('TEXT_DUMMY_BUTTON', 'GUEST');
define('TEXT_CUSTOMER_EMAIL', 'Customer\'s email:');
define('TEXT_CUSTOMER_ID', 'Customer\'s ID:');
define('TEXT_CUSTOMER_LOGIN_BTN', 'Login:');



// EOF Barcode Cart
?>