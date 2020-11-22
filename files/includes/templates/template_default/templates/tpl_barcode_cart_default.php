<?php
/**
* // BOF Barcode Cart
 * Page Template
 *
 * Loaded automatically by index.php?main_page=shopping_cart.<br />
 * Displays shopping-cart contents
 *
 * @package templateSystem
 * @version $Id: tpl_barcode_cart_default.php 15881 2010-04-11 16:32:39Z wilt $
 */
?>

<div class="centerColumn1" id="shoppingCartDefault" >

<div id="POSheader" class="row">
    <div class="col-md-4 col-sm-12">
        <div id="POSlogo"><img src="includes/templates/classic/images/logo.gif" /></div>
    </div>
    <div class="col-md-4 col-sm-6">
        <div id="POSheaderDetails">
            <div id="currentTime"><form id="form1" runat="server">
                <asp:Label ID="cTime" runat="server" ClientIDMode="Static" BackColor="#ffff00" Font-Bold="true" />
                </form>
            </div>
            <div>Staff: <?php echo $_SESSION['POSstaff']; ?></div>
        </div>
    </div>

    <div class="col-md-4 col-sm-6">
        <div id="barcodeCartDetails">
            <?php if (!empty($totalsDisplay)) { ?>
                <div class="cartTotalsDisplay important"><?php echo $totalsDisplay; ?></div>
                <br class="clearBoth" />
            <?php } ?>
        </div>
    </div>


</div>
<div class="clearBoth"></div>

<?php if ($messageStack->size('shopping_cart') > 0) echo $messageStack->output('shopping_cart'); ?>

<?php echo zen_draw_form('cart_quantity', zen_href_link(FILENAME_BARCODE_CART, '', $request_type), 'post' ); ?>

<table  border="0" width="100%" cellspacing="0" cellpadding="0" id="cartContentsDisplay">
	<tr class="productHeadings">
    	<td width="40%">Product</td>
        <td width="10%">Price</td>
        <td width="10%">Qty</td>
        <td width="12%">Action</td>
        <td width="20%">Subtotal</td>
        <td width="8%">Remove</td>
    </tr>
         <!-- Loop through all products /-->
<?php

  foreach ($productArray as $product) {
?>
     <tr class="bc_rows">
     
     <td class="bc_cartProductDisplay" width="40%">
<a href="<?php echo $product['linkProductsName']; ?>"><span id="cartImage" class="back"><?php echo $product['productsImage']; ?></span><span id="cartProdTitle"><?php echo $product['productsName'] . '<span class="alert bold">' . $product['flagStockCheck'] . '</span>'; ?></span></a>
<br class="clearBoth" />

<?php
  echo $product['attributeHiddenField'];
  if (isset($product['attributes']) && is_array($product['attributes'])) {
  echo '<div class="cartAttribsList">';
  echo '<ul>';
    reset($product['attributes']);
    foreach ($product['attributes'] as $option => $value) {
?>
<li><?php echo $value['products_options_name'] . TEXT_OPTION_DIVIDER . nl2br($value['products_options_values_name']); ?></li>
<?php
    }
  echo '</ul>';
  echo '</div>';
  }
?>
</td>
<td class="bc_cartUnitDisplay" width="10%"><?php echo $product['productsPriceEach']; ?></td>
<td class="bc_cartQuantity" width="10%">
<?php
  /*  
  if ($product['flagShowFixedQuantity']) {
    echo $product['showFixedQuantityAmount'] . '<br /><span class="alert bold">' . $product['flagStockCheck'] . '</span><br /><br />' . $product['showMinUnits'];
  } else {
    echo $product['quantityField'] . '<br /><span class="alert bold">' . $product['flagStockCheck'] . '</span><br /><br />' . $product['showMinUnits'];
  }
  */
  
  echo zen_draw_input_field('cart_quantity_'.$product['id'], $product['quantity'], 'id="cart_quantity_'.$product['id'].'" size="4" onkeypress=" if (event.keyCode==13) {bc_quantity_keypress(\''.$product['id'].'\');  event.preventDefault();} "');
?>
</td>

<td class="bc_cartQuantityUpdate" width="12%">
<?php
echo '<i class="fa fa-refresh" id="update_'.$product['id'].'" onclick="bc_update_product_cart(\''.$product['id'].'\')" ></i>' ;
?>
</td>
       

<td class="bc_cartTotalDisplay" width="20%"><?php echo $product['productsPrice']; ?></td>
<td class="bc_cartRemoveItemDisplay"  width="8%">
<?php
  if ($product['buttonDelete']) {
      
     echo '<i class="fa fa-remove" id="del_'.$product['id'].'"  onclick="bc_del_product_cart(\''.$product['id'].'\')" ></i>' ; 
?>
    <!--a href="<?php echo zen_href_link(FILENAME_BARCODE_CART, 'action=remove_product&product_id=' . $product['id']); ?>"><?php echo zen_image($template->get_template_dir(ICON_IMAGE_TRASH, DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . ICON_IMAGE_TRASH, ICON_TRASH_ALT); ?></a-->
<?php
  }
  
?>
</td>
     </tr>
<?php
  } // end foreach ($productArray as $product)
?>
<?php 
if(isset($_SESSION['discount_in_cart']) && $_SESSION['discount_in_cart'] != '$0.00') {
	echo '<tr>
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
?>
       <!-- Finished loop through all products /-->
      
      </table>
      
       
       


</form>
<table  border="0" width="100%" cellspacing="0" cellpadding="0" id="scanbarcodecart">
       <!-- BOF Input field for scaning product /-->
          <tr>
             <td class="bc_cartQuantity" width="10%" ><input size="4" type="text" name="product_qty" value="1" id="product_qty" placeholder="QTY" /></td>
             <td class="bc_cartProductDisplay" width="40%"><input type="text" size="36" name="barcode_scaner" value="" autocomplete="off" id="product_barcode_scaner"  onkeypress=" if (event.keyCode==13) {lookupProductByBarcodeManually();  event.preventDefault();} " placeholder="Barcode" /></td>
             <td  class="bc_cartUnitDisplay" width="10%"><input type="text" name="product_model" value="" id="product_model" placeholder="Model" /></td>
             <td class="bc_cartQuantityUpdate" width="12%"></td>
             <td class="bc_cartTotalDisplay" width="20%"></td>
             <td class="bc_cartRemoveItemDisplay" width="8%"></td>
             
          </tr>
        </table>   
        
 <div id="barcodeCartOptions">
 <a id="viewOptions">View Options</a>
 <table border="0" width="100%" cellspacing="0" cellpadding="0" id="barcodecartDiscount">
 	<tr>
    	<td width="100%">
        	<div class="managerDiscount">
            	<div class="discountForm">
                Admin Discount: 
                	<?php echo zen_draw_form('custom_discount_form', zen_href_link(FILENAME_LOGIN, 'action=process', 'SSL'), 'post', 'id="custom_discount_form"'); ?>
                    	<input type="text" name="discount" value="" id="discount" placeholder="discount" onkeypress=" if (event.keyCode==13) {setDiscount();  event.preventDefault();} "/>
                    </form>
                </div>
            </div>
        </td>
    </tr>
 </table>
 </div>
 
 <table border="0" width="100%" cellspacing="0" cellpadding="0" id="barcodecartlogin">
          <tr>
             <td width="100%">
             
                  <div class="existing_customer">
                  
                  <?php if(isset($_SESSION['customer_id'])) {
					echo 'Logged in as ' . $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name'];
					echo '<div class="bcLogoff"><a class="myButton back" href="' . zen_href_link(FILENAME_LOGOFF, '', 'SSL') .'">Log Off</a> <a class="myButton loginButton forward" href="' . zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL') .'">Checkout</a></div>';
					} else { ?>
    	              <a href="<?php echo zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL');?>" class="myButton"><i class="fa fa-cogs"></i> <?php echo TEXT_NEW_BUTTON;?></a>          
        	          <a href="javascript:void(0);" class="myButton" id="show_existing_form"><i class="fa fa-user"></i> <?php echo TEXT_EXISTING_BUTTON;?></a>         
            	      <a href="#" class="myButton" id="dummyLogin"><i class="fa fa-unlink"></i> <?php echo TEXT_DUMMY_BUTTON;?></a> 
                	  <?php echo zen_draw_form('dummy_customer_form', zen_href_link(FILENAME_LOGIN, 'action=process', 'SSL'), 'post', 'id="dummy_customer_form"'); ?>
					  <?php echo zen_draw_hidden_field('email_address', POS_DUMMY_LOGIN_EMAIL);
							echo zen_draw_hidden_field("password", POS_CUSTOMER_LOGIN_PASSWORD);
						?>
	                  </form>
    	              </div>         
        	          <div id="existing_customer_div">
                        <?php echo zen_draw_form('existing_customer_form', zen_href_link(FILENAME_LOGIN, 'action=process', 'SSL'), 'post', 'id="existing_customer_form"'); ?>
                            <div>
                                <span>Customer Lookup</span><br/>
                                <span>
								<?php
								echo zen_draw_pull_down_menu('email_address', zen_bc_customer_lookup(), '', 'id="customer_list" data-placeholder="Choose a customer..." ');
								?>
								</span>
                            </div>
                            <?php echo zen_draw_hidden_field("password", POS_CUSTOMER_LOGIN_PASSWORD);?>
                            <div style="text-align:center;">
                                  <a href="#" class="myButton loginButton" id="customerLogin"><i class="fa fa-bolt"></i> Log In</a>
                            </div>
                        </form>
                   <?php } ?>
          	      </div>
                  
             </td>
          </tr>
 </table>   



</div>

<?php // EOF Barcode Cart ?>