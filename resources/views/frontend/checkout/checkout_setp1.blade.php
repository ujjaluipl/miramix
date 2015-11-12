@extends('frontend/layout/frontend_template')
@section('content')

<div class="inner_page_container">
    	<div class="header_panel">
        	<div class="container">
        	 <h2>Brands</h2>
             <ul class="breadcrumb">
                <li><a href="#">Home</a></li>
                <li><a href="#">Brands</a></li>
                <li>Health Takes Guts</li>
             </ul>
            </div>
        </div>   
<!-- Start Products panel -->
<div class="products_panel">
	<div class="container">
    
    <!--steps_main-->
    <div class="steps_main text-center">
    <ul>
    <li class="active"><span>1</span><h6>Checkout Option</h6></li>
    <li><span>2</span><h6>Payment Method</h6></li>
    <li><span>3</span><h6>Shipping Details</h6></li>
    <li><span>4</span><h6>Confirm Order</h6></li>
    </ul>
    </div>
    <!--steps_main-->
    
    <div class="col-sm-12">
    <div class="row">
    <div class="checkout_cont">
    <h5>Step 1 :  Checkout option</h5>
    
    <div class="row">
    <div class="col-sm-6 spec_padright">
    <h4>New Customer</h4>
    
    <div class="clearfix"><p class="specil_p pull-left mr20">Checkout option :</p>
    <div class="check_box_tab green_version pull-left">                            
         <input type="radio" class="regular-checkbox" id="radio-1" name="RadioGroup1">
         <label for="radio-1">Register Account</label>
    </div></div>
    
    <p>By creating an account you will be able to shop faster, be up to date on an order's status, and keep track of the orders you have previously made.</p>
    
    <a href="" class="full_green_btn pull-left text-uppercase">Continue</a>
    
    </div>
    <div class="col-sm-6 spec_padleft">
    <h4>Returning Customer</h4>
    <p class="specil_p">I am a returning customer</p>
    
    <form>
    <div class="form-group"><input type="email" class="form-control" placeholder="Email"></div>
    <div class="form-group"><input type="password" class="form-control" placeholder="Password"></div>
    <a href="" class="btn-link">Forgot Password?</a>
    <input type="submit" class="full_green_btn text-uppercase" value="Login">
    </form>
    
    <p class="specil_p clearfix">OR</p>
    <a href="" class="pull-left"><img src="images/shopping-checkout/goog_btn.png" alt=""></a>
    <a href="" class="pull-left"><img src="images/shopping-checkout/face_btn.png" alt=""></a>
    </div>
    </div>
    
    </div>
    </div>
    </div>
    
    </div>
</div>
<!-- End Products panel --> 
 </div>


@stop