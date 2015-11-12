@extends('frontend/layout/frontend_template')
@section('content')

<div class="header_section nomar_bottom">
    	   <!--my_acct_sec-->
           <div class="my_acct_sec">           
               <div class="container">
               <h3 class="text-center"><?php
	       if (is_object($brand_details)){
	       echo ($brand_details->fname !='')?ucfirst($brand_details->fname):$brand_details->username?> 's account
	       <?php }?>
	       </h3>
               <h5 class="text-center">WELCOME TO YOUR ACCOUNT</h5>
               
               <div class="col-sm-12">
               
                <div class="acct_box yellow_act">
                	<div class="acct_box_inn">
                    	<div class="acct_box_inn_inn">
                        <img src="<?php echo url();?>/public/frontend/images/account/sold_products.png" alt="">
                        <a href="<?php echo url();?>/sold-products">Sold Products History</a>
                        </div>                    	
                    </div>
                </div>
                
                <div class="acct_box red_acct">
                	<div class="acct_box_inn">
                    	<div class="acct_box_inn_inn">
                        <a href="<?php echo url();?>/product/create"><img src="<?php echo url();?>/public/frontend/images/account/add_products.png" alt=""></a>
                        <a href="<?php echo url();?>/product/create">Add Products</a>
                        </div>                    	
                    </div>
                </div>
                
                <div class="acct_box org_org_acct no_marg">
                	<div class="acct_box_inn">
                    	<div class="acct_box_inn_inn">
                        <a href="<?php echo url();?>/my-products"><img src="<?php echo url();?>/public/frontend/images/account/productlist.png" alt=""></a>
                        <a href="<?php echo url();?>/my-products">Product List</a>
                        </div>                    	
                    </div>
                </div>
                
                <div class="acct_box new_green_acct no_marg pull-right">
                	<div class="acct_box_inn">
                    	<div class="acct_box_inn_inn">
                         <a href=""><img src="<?php echo url();?>/public/frontend/images/account/order_hist.png" alt=""></a>
                        <a href="">Order History</a>
                        </div>                    	
                    </div>
                </div>
                
                <div class="acct_box blue_acct front">
                	<div class="acct_box_inn">
                    	<div class="acct_box_inn_inn">
                        <a href="<?php echo url();?>/brand-account"><img src="<?php echo url();?>/public/frontend/images/account/pers_info.png" alt=""></a>
                        <a href="<?php echo url();?>/brand-account">Brand Information</a>
                        </div>                    	
                    </div>
                </div>
                
                <div class="acct_box green_acct">
                	<div class="acct_box_inn">
                    	<div class="acct_box_inn_inn">
                        <a href="<?php echo url();?>/change-password"><img src="<?php echo url();?>/public/frontend/images/account/changepassword.png" alt=""></a>
                        <a href="<?php echo url();?>/change-password">Change Password</a>
                        </div>                    	
                    </div>
                </div>
                
                <div class="acct_box violet_acct no_marg">
                	<div class="acct_box_inn">
                    	<div class="acct_box_inn_inn">
                        <img src="<?php echo url();?>/public/frontend/images/account/address.png" alt="">
                        <a href="<?php echo url();?>/brand-shipping-address">My Address</a>
                        </div>                    	
                    </div>
                </div>
                
                <div class="acct_box orange_acct no_marg pull-right">
                	<div class="acct_box_inn">
                    	<div class="acct_box_inn_inn">
                        <img src="<?php echo url();?>/public/frontend/images/account/store.png" alt="">
                        <a href="">Store Font</a>
                        </div>                    	
                    </div>
                </div>
		    
		    
		<div class="acct_box blue_acct front">
                	<div class="acct_box_inn">
                    	<div class="acct_box_inn_inn">
                        <a href="<?php echo url();?>/brand-creditcards"><i class="fa fa-credit-card fa-2x"></i></a>
                        <a href="<?php echo url();?>/brand-creditcards">Credit Card Details</a>
                        </div>                    	
                    </div>
                </div>
		    
		    
		<div class="acct_box blue_acct front">
                	<div class="acct_box_inn">
                    	<div class="acct_box_inn_inn">
                        <a href="<?php echo url();?>/brand-paydetails"><i class="fa fa-cc-paypal fa-2x"></i></a>
                        <a href="<?php echo url();?>/brand-paydetails">Payment Details</a>
                        </div>                    	
                    </div>
                </div>
                
                <div class="btn-block text-center"><a href="" class="btn btn-white">Subscription History</a></div>
               </div>
               </div>           
           </div>
           <!--my_acct_sec ends-->
 </div>

 @stop