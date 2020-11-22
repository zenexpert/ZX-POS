<?php
// BOF Barcode Cart
?>
<script language="javascript" src="includes/general.js" type="text/javascript"></script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="includes/modules/pages/barcode_cart/jquery.chosen.min.js" type="text/javascript"></script>
<script src="includes/modules/pages/barcode_cart/jquery.scannerdetection.compatibility.js" type="text/javascript"></script>
<script src="includes/modules/pages/barcode_cart/jquery.scannerdetection.js" type="text/javascript"></script>

<script language="javascript" type="text/javascript"><!--
$(document).ready(function(){
	
	// Chosen setup
	var config = {
      '#customer_list'           : {width:"95%",search_contains:true}
    }
    for (var selector in config) {
      $(selector).chosen(config[selector]);
    }
	
	// display time
	DisplayCurrentTime();
	
	// dummy login
	$('#dummyLogin').click(function(){ $('#dummy_customer_form').submit(); return false; });
	
	// customer login
	$('#customerLogin').click(function(){ $('#existing_customer_form').submit(); return false; });
	
    //$("#product_barcode_scaner").scannerDetection(); // Initialize with default options
    $("#product_barcode_scaner").scannerDetection({ 
            onComplete:false, // Callback after detection of a successfull scanning (scanned string in parameter)
            onError:false, // Callback after detection of a unsuccessfull scanning (scanned string in parameter)
            onReceive:false // Callback after receive a char (scanned char in parameter)
     }); // Initialize with an object that contains options
    //$("#product_barcode_scaner").scannerDetection(function(){
        
    //}); // Initialize with a function that is onComplete callback    
   
    resetBarcodeInputs();
    
    $("#product_barcode_scaner")
        .bind('scannerDetectionComplete',function(e,data){
             //if (data.toString!='')
                 //lookupProductByBarcode(data.string);
        })
        .bind('scannerDetectionError',function(e,data){
            if (data.toString!=''){
                //alert("The code \""+data.string+"\" not found "); 
                //resetBarcodeInputs();
            }
        })
        .bind('scannerDetectionReceive',function(e,data){
            //console.log(e);
            //console.log(data);
            //lookupProductByModel(data); 
        })
        
     $('#product_model').change(function(){
        if ( $.trim(($(this).val()))==''){
            alert('Please enter product model field');
            return false;
        }
        else{
            lookupProductByModel($(this).val());    
            return false;          
        }
     });   
     /*     
     $("img").click(function(){
         var pattern = /^([a-z]+)(\_)(\d+)/;
         var tmps = pattern.exec( $(this).attr('id') ) ;
         
         var qty = 1;
         
         if (tmps[1] && tmps[3]){
             if (tmps[1]=='update'){
                 // updateProductToCart
                 qty = $("#cart_quantity_"+tmps[3]).val();
                 updateProductCart(tmps[3], qty);
             }
             else if(tmps[1]=='del'){
                 // delProductCart
                 delProductCart(tmps[3]);
             }
          
         }
     });
     */
     
     $("a#show_existing_form").click(function(){
         if ($("#existing_customer_div").is(':hidden')== true)
            $("#existing_customer_div").show();
         else
            $("#existing_customer_div").hide();   
     });
	 
	 $("#viewOptions").click(function(){
		 $("#barcodecartDiscount").toggle();
	 });
 
});

function DisplayCurrentTime() {
            var dt = new Date();
            var refresh = 1000; //Refresh rate 1000 milli sec means 1 sec
            var cDate = (dt.getMonth() + 1) + "/" + dt.getDate() + "/" + dt.getFullYear();
            document.getElementById('cTime').innerHTML = cDate + " – " + dt.toLocaleTimeString();
            window.setTimeout('DisplayCurrentTime()', refresh);
        }
		
function bc_quantity_keypress(product_id){
    //cart_quantity_2   
        var ele = document.getElementById('cart_quantity_'+product_id);
         
        //if (e.keyCode == "13") {
            
            if ( parseInt($(ele).val()) <=0 || isNaN(parseInt($(ele).val())) ){
                alert("The product quantity must be greater than 0");
                return false;
            }
            else
            {
                bc_update_product_cart(product_id);       
                return false;
            }
       // }
}

function bc_del_product_cart(product_id){
    delProductCart(product_id);
}

function bc_update_product_cart(product_id){
     var el = document.getElementById('cart_quantity_'+product_id);
     var qty = $(el).val();
     updateProductCart(product_id, qty);
}

function resetBarcodeInputs(){
    $("#product_barcode_scaner").val('');
    $("#product_model").val('');
    $("#product_qty").val('1');
    $( "#product_barcode_scaner" ).focus();
}

function lookupProductByBarcode(product_barcode){
    if ($.trim(product_barcode)!=''){
        var proQty = $("#product_qty").val();
        $.ajax({
            //url: '<?php echo zen_href_link('ajax_process.php', '', 'SSL', true, false, true); ?>',
            url: 'ajax_process.php',
            type: "POST",
            dataType: 'json',
            data:'product_barcode='+product_barcode+"&act=lookupProductBarcode",
            success: function(res){
                if (res.result_success==true)
                {
                    if (typeof res.result_content=='object')
                        addProductAttrToCart(res.result_content, proQty);
                    else
                        addProductToCart(res.result_content, proQty);
                }
                else
                {
                    alert(res.result_content);
                    $("#product_model").val('');
                }
                    
            },
            complete: function(res){
                // ajax loading
                resetBarcodeInputs();
            }
        });
    }
}

function lookupProductByModel(product_model){
    if ($.trim(product_model)!=''){
        var proQty = $("#product_qty").val();
        $.ajax({
            //url: '<?php echo zen_href_link('ajax_process.php', '', 'SSL', true, false, true); ?>',
            url: 'ajax_process.php',
            type: "POST",
            dataType: 'json',
            data:'product_model='+product_model+"&act=lookupProductModel",
            success: function(res){
                if (res.result_success==true)
                {
                    if (typeof res.result_content=='object')
                        addProductAttrToCart(res.result_content, proQty);
                    else
                        addProductToCart(res.result_content, proQty);
                }
                else
                {
                    alert(res.result_content);
                    $("#product_model").val('');
                }
                    
            },
            complete: function(res){
                // ajax loading
                resetBarcodeInputs();
            }
        });
    }
}

function addProductToCart(proId, qty){
     $.ajax({
            url: 'ajax_process.php',
            type: "POST",
            dataType: 'json',
            data:'proId='+proId+"&act=addProductToCart&qty="+qty+'&securityToken=<?php echo $_SESSION['securityToken'];?>',
            success: function(res){
                if (res.result_success==true)
                {
                    $("#cartContentsDisplay").html(res.result_content.cartContentsDisplay);      
                    $("#barcodeCartDetails").html(res.result_content.cartTotalsDisplay);      
                }    
            },
            complete: function(res){
                
            }
        });   
}

function updateProductCart(proId, qty){
     $.ajax({
            url: 'ajax_process.php',
            type: "POST",
            dataType: 'json',
            data:'proId='+proId+"&act=updateProductCart&qty="+qty+'&securityToken=<?php echo $_SESSION['securityToken'];?>',
            success: function(res){
                if (res.result_success==true)
                {
                    $("#cartContentsDisplay").html(res.result_content.cartContentsDisplay);      
                    $("#barcodeCartDetails").html(res.result_content.cartTotalsDisplay);
                }    
            },
            complete: function(res){
                
            }
        });   
}

function delProductCart(proId){
     $.ajax({
            url: 'ajax_process.php',
            type: "POST",
            dataType: 'json',
            data:'proId='+proId+"&act=delProductCart&securityToken=<?php echo $_SESSION['securityToken'];?>",
            success: function(res){
                if (res.result_success==true)
                {
                    $("#cartContentsDisplay").html(res.result_content.cartContentsDisplay);      
                    $("#barcodeCartDetails").html(res.result_content.cartTotalsDisplay);
                }    
            },
            complete: function(res){
              
            }
        });   
}

function lookupProductByBarcodeManually()
{
    var product_barcode = $("#product_barcode_scaner").val();
    lookupProductByBarcode(product_barcode);
}

function addProductAttrToCart(proAttr, qty){
    $.ajax({
        url: 'ajax_process.php?act=addProductAttrToCart&qty='+qty+'&securityToken=<?php echo $_SESSION['securityToken'];?>',
        type: "POST",
        dataType: 'json',
        data: proAttr,
        success: function(res){
            if (res.result_success==true)
            {
                $("#cartContentsDisplay").html(res.result_content.cartContentsDisplay);
                $("#barcodeCartDetails").html(res.result_content.cartTotalsDisplay);
            }
        },
        complete: function(res){

        }
    });
}

function setDiscount(value){
	if(!value) var value = $("#discount").val();
     $.ajax({
            url: 'ajax_process.php',
            type: "POST",
            dataType: 'json',
            data:'discount='+value+'&act=discount&securityToken=<?php echo $_SESSION['securityToken'];?>',
            success: function(res){
                if (res.result_success==true)
                {
                    $("#cartContentsDisplay").html(res.result_content.cartContentsDisplay);      
                    $("#barcodeCartDetails").html(res.result_content.cartTotalsDisplay);
					
                }    
            },
            complete: function(res){
				$("#discount").val('');
            }
        });   
}



//--></script>


<script language="javascript" type="text/javascript"><!--
function popupWindow(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=550,height=550,screenX=150,screenY=100,top=100,left=150')
}
//--></script>
<script language="javascript" type="text/javascript"><!--
function session_win() {
  window.open("<?php echo zen_href_link(FILENAME_INFO_SHOPPING_CART); ?>","info_shopping_cart","height=460,width=430,toolbar=no,statusbar=no,scrollbars=yes").focus();
}
//--></script>
<?php
// EOF Barcode Cart
?>
