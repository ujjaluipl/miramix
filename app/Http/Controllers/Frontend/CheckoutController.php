<?php namespace App\Http\Controllers\Frontend; /* path of this controller*/

use App\Model\Brandmember;  /* Model name*/
use App\Model\Order; 		/* Model name*/
use App\Model\OrderItems;	/* Model name*/
use Illuminate\Support\Collection;
use App\Http\Requests;
use App\Http\Controllers\Controller;    
use Illuminate\Support\Facades\Request;

use Input; /* For input */
use Validator;
use Session;
use Imagine\Image\Box;
use Image\Image\ImageInterface;
use Illuminate\Pagination\Paginator;
use DB;
use Hash;
use Auth;
use Cookie;
use Redirect;
use App\Helper\helpers;
//use Anam\Phpcart\Cart;
use Cart;

class CheckoutController extends BaseController {

    public function __construct() 
    {
    	parent::__construct();
        
    }
   /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index()
    {
        
    }
	
	public function checkoutStep1()
    {
		$obj = new helpers();
        if((!$obj->checkMemberLogin()) && (!$obj->checkBrandLogin()))  //for not logged in member
        {
			return view('frontend.checkout.checkout_setp1',compact('body_class'),array('title'=>'MIRAMIX | Checkout-Step1'));
		}
		else   //If logged in member
		{
			return redirect('/checkout-step2');
		}
    }
	
	public function checkoutStep2()
    {
		$obj = new helpers();
        if(($obj->checkMemberLogin()) && (!$obj->checkBrandLogin()))  //for loggedin member
        {
			if(Request::isMethod('post'))
			{
				Session::put('payment_method',Request::input('payment_type'));
				return redirect('/checkout-step3');
			}
			return view('frontend.checkout.checkout_setp2',compact('body_class'),array('title'=>'MIRAMIX | Checkout-Step2'));
		}
		else
		{
			redirect('/checkout-step1');
		}
    }
	
	public function checkoutStep3()
    {
		$obj = new helpers();
        if(($obj->checkMemberLogin()) && (!$obj->checkBrandLogin()))  //for not logged in member
        {
        	$shipAddress = DB::table('addresses')
                                ->leftJoin('brandmembers', 'brandmembers.id', '=', 'addresses.mem_brand_id')
                                ->leftJoin('countries', 'countries.country_id', '=', 'addresses.country_id')
                                ->leftJoin('zones', 'zones.zone_id', '=', 'addresses.zone_id')
                                ->select('addresses.*', 'brandmembers.fname', 'brandmembers.lname', 'brandmembers.username','brandmembers.address as default_address','countries.name as country_name', 'zones.name as zone_name', 'brandmembers.status', 'brandmembers.admin_status')
                                ->where('addresses.mem_brand_id','=',Session::get('member_userid'))
                                ->get();
                                //echo "<pre>";print_r($shipAddress); exit;
            $allcountry = DB::table('countries')->orderBy('name','ASC')->get();

            if(Request::isMethod('post'))
			{
				if(Request::input('select_address') == 'existing')
				{
					Session::put('select_address','existing');
					Session::put('selected_address_id',Request::input('existing_address'));
				}
				else if(Request::input('select_address') == 'newaddress')
				{
					Session::put('select_address','newaddress');

					$insert_address = DB::table('addresses')
											->insert([
												'mem_brand_id' => Session::get('member_userid'), 
												'first_name' => Request::input('fname'), 
												'last_name' => Request::input('lname'), 
												'email' => Request::input('email'),
												'phone' => Request::input('phone'), 
												'address' => Request::input('address'),
												'address2' => Request::input('address2'),
												'city' => Request::input('city'),
												'country_id' => Request::input('country_id'),
												'zone_id' => Request::input('state'),
												'postcode' => Request::input('zip_code')
												]);
					$lastInsertedId =DB::getPdo()->lastInsertId();   			// Getting last inserted id

					Session::put('selected_address_id',$lastInsertedId);		// shipping address option value store in session.
				}
				
				return redirect('/checkout-step4');
			}

			return view('frontend.checkout.checkout_setp3',compact('body_class','shipAddress','allcountry'),array('title'=>'MIRAMIX | Checkout-Step3'));
		}
		else   //If logged in member
		{
			return redirect('/checkout-step1');
		}
    }

    public function checkoutStep4()
    {
		$obj = new helpers();
        if(($obj->checkMemberLogin()) && (!$obj->checkBrandLogin()))  //for loggedin member
        {
        	$sitesettings = DB::table('sitesettings')->get();
            if(!empty($sitesettings))
            {
	            foreach($sitesettings as $each_sitesetting)
	            {
	              if($each_sitesetting->name == 'shipping_rate')
	              {
	                $shipping_rate = ((int)$each_sitesetting->value);
	              }
	            }
            }

			if(Request::isMethod('post'))
			{
				$shp_address = DB::table('addresses')
								->where('mem_brand_id',Session::get('member_userid'))
								->where('id',Session::get('selected_address_id'))
								->first();	

				// Serialize the Shipping Address because If user delete there address from "addresses" table,After that the address also store in the "order" table for  getting order history//
				$shiping_address = array('address_title' 			=> $shp_address->address_title,
										'mem_brand_id'				=> $shp_address->mem_brand_id,
										'first_name' 				=> $shp_address->first_name,
										'last_name' 				=> $shp_address->last_name,
										'email' 					=> $shp_address->email,
										'phone' 					=> $shp_address->phone,
										'address' 					=> $shp_address->address,
										'address2' 					=> $shp_address->address2,
										'city' 						=> $shp_address->city,
										'zone_id' 					=> $shp_address->zone_id,
										'country_id' 				=> $shp_address->country_id,
										'postcode' 					=> $shp_address->postcode
										);

				$shiping_address_serial = serialize($shiping_address);

				$order= Order::create([
										'order_total'            	=> Request::input('grand_total'),
										'sub_total'					=> Request::input('sub_total'),
										'order_status'           	=> 'pending',
										'shipping_address_id'    	=> Session::get('selected_address_id'),
										'shipping_cost'    			=> $shipping_rate,
										'shipping_type'    			=> 'flat',
										'user_id'          			=> Session::get('member_userid'),
										'ip_address'  				=> $_SERVER['REMOTE_ADDR'],
										'payment_method'          	=> Session::get('payment_method'),
										'transaction_id'    		=> '',
										'transaction_status'      	=> '',
										'shiping_address_serialize' => $shiping_address_serial,
										'created_at' => date('Y-m-d H:s:i'),
										'updated_at' => date('Y-m-d H:s:i')
									]);
				$last_order_id = $order->id;

				$allCart= DB::table('carts')->where('user_id',Session::get('member_userid'))->get();
				foreach($allCart as $eachCart)
				{
					$product_details = DB::table('products')->where('id',$eachCart->product_id)->first();
		           // echo $each_content->brandmember_id; exit;
		            $brandmember_deatils = DB::table('products')
		                                ->leftJoin('brandmembers', 'brandmembers.id', '=', 'products.brandmember_id')
		                                ->select('products.*', 'brandmembers.fname', 'brandmembers.lname', 'brandmembers.username', 'brandmembers.slug', 'brandmembers.pro_image', 'brandmembers.brand_details', 'brandmembers.brand_sitelink', 'brandmembers.status', 'brandmembers.admin_status')
		                                ->where('products.id','=',$eachCart->product_id)
		                                ->first();
		                                //echo "<pre>";print_r($brandmember_deatils); exit;
		                                //echo $brandmember->slug ; exit;
		            $brand_member_name = ($brandmember_deatils->fname)?($brandmember_deatils->fname.' '.$brandmember_deatils->lname):$brandmember_deatils->username;

		            $formfactor = DB::table('form_factors')->where('id','=',$eachCart->form_factor)->first();
				
					$order_item = OrderItems::create([
												'order_id'       	=> $last_order_id,
												'brand_id'          => $brandmember_deatils->brandmember_id,
												'brand_name'    	=> $brand_member_name,
												'product_id'        => $eachCart->product_id,
												'product_name'  	=> $eachCart->product_name,
												'product_image'     => $product_details->image1,
												'quantity'     		=> $eachCart->quantity,
												'price'    			=> $eachCart->amount,
												'form_factor_id'    => $formfactor->id,
												'form_factor_name'  => $formfactor->name
											]);

					// All Cart deleted from cart table after inserting all data to order and order_item table.
					//$deleteCart =  Cart::where('user_id', '=', Session::get('member_userid'))->delete(); 
				}

				if(Session::get('payment_method') =='creditcard') 	  // if Payment With Credit Card 
				{
					return redirect('/checkout-authorize/'.$last_order_id);
				}
				elseif(Session::get('payment_method') =='paypal')	 // if Payment With Paypal Account 
				{
					return redirect('/checkout-paypal/'.$last_order_id);	
				}
				
			}
			// All Cart Contain  In Session Will Display Here //
			$content = DB::table('carts')->where('user_id',Session::get('member_userid'))->get();
			//echo "<pre>";print_r($content); exit;
			foreach($content as $each_content)
	        {
	            
	            $product_res = DB::table('products')->where('id',$each_content->product_id)->first();
	           // echo $each_content->brandmember_id; exit;
	            $brandmember = DB::table('products')
	                                ->leftJoin('brandmembers', 'brandmembers.id', '=', 'products.brandmember_id')
	                                ->select('products.*', 'brandmembers.fname', 'brandmembers.lname', 'brandmembers.username', 'brandmembers.slug', 'brandmembers.pro_image', 'brandmembers.brand_details', 'brandmembers.brand_sitelink', 'brandmembers.status', 'brandmembers.admin_status')
	                                ->where('products.id','=',$each_content->product_id)
	                                ->first();
	                                //echo "<pre>";print_r($brandmember); 
	                                //echo $brandmember->slug ; exit;
	            $brand_name = ($brandmember->fname)?($brandmember->fname.' '.$brandmember->lname):$brandmember->username;

	            $formfactor = DB::table('form_factors')->where('id','=',$each_content->form_factor)->first();
	            $formfactor_name = $formfactor->name;
	            $formfactor_id = $formfactor->id;

	            $cart_result[] = array('rowid'=>$each_content->row_id,
	                'product_name'=>$each_content->product_name,
	                'product_slug'=>$brandmember->product_slug,
	                'product_image'=>$product_res->image1,
	                'qty'=>$each_content->quantity,
	                'price'=>$each_content->amount,
	                'duration'=>$each_content->duration,
	                'formfactor_name'=>$formfactor_name,
	                'formfactor_id'=>$formfactor_id,
	                'brand_name'=>$brand_name,
	                'brand_slug'=>$brandmember->slug,
	                'subtotal'=>$each_content->sub_total);

	        }

	        
            //echo "sph= ".$shipping_rate; exit;
			return view('frontend.checkout.checkout_setp4',compact('body_class','cart_result','shipping_rate'),array('title'=>'MIRAMIX | Checkout-Step4'));
		}
		else
		{
			redirect('/checkout-step1');
		}
    }


    public function checkoutPaypal($id)
    {
    	$order_id = $id;
    	
		$order_list = DB::table('orders')
                    ->leftJoin('order_items', 'order_items.order_id', '=', 'orders.id')
                    ->select('orders.*', 'order_items.brand_id', 'order_items.brand_name', 'order_items.product_id', 'order_items.product_name', 'order_items.product_image', 'order_items.quantity', 'order_items.price', 'order_items.form_factor_id', 'order_items.form_factor_name')
                    ->where('orders.id','=',$order_id)
                    ->get();
        // echo "<pre>";
        // print_r($order_list);
        // exit;
        return view('frontend.checkout.checkout_paypalpage',compact('body_class','order_list'),array('title'=>'MIRAMIX | Checkout-Paypal'));
    	//echo "boom";
    }
	
	public function paypalNotify()
    {
    	@mail("sumitra.unified@gmail.com", "Me PAYPAL DEBUGGING1".$message, "Invalid Response<br />data = <pre>".print_r($post, true)."</pre>");
    	// Call ipn
    	$req = 'cmd=_notify-validate';
		foreach ($_POST as $key => $value) 
		{
			$value = urlencode(stripslashes($value));
			$value = preg_replace('/(.*[^%^0^D])(%0A)(.*)/i','${1}%0D%0A${3}',$value);// IPN fix
			$req .= "&$key=$value";
		}
			// assign posted variables to local variables
			//$data['item_name']		= $_POST['item_name'];
			//$data['item_number'] 		= $_POST['item_number'];
			$data['payment_status'] 	= $_POST['payment_status'];
			$data['payment_amount'] 	= $_POST['mc_gross'];
			$data['discount'] 			= $_POST['discount'];
			$data['mc_shipping'] 		= $_POST['mc_shipping'];
			$data['mc_handling'] 		= $_POST['mc_handling'];
			$data['payment_currency']	= $_POST['mc_currency'];
			$data['txn_id']				= $_POST['txn_id'];
			$data['receiver_email'] 	= $_POST['receiver_email'];
			$data['payer_email'] 		= $_POST['payer_email'];
			$cnt		 				= unserialize($_POST['custom']);

			//list($data['user_id'],$data['plan_id'])= explode("-",$_POST['custom']);

			$pay_date=date('l jS \of F Y h:i:s A');

			// post back to PayPal system to validate
			$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";

			$header .= "Host: www.sandbox.paypal.com\r\n";
			//$header .= "Host: www.paypal.com:443\r\n";

			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

			$fp = fsockopen ('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);	
			//$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);
			if (!$fp) 
			{
				// HTTP ERROR
			} 
			else 
			{
				fputs ($fp, $header . $req);
				while (!feof($fp)) {
					$res = fgets ($fp, 1024);
					if (strcmp($res, "VERIFIED") == 0) 
					{
						
						$order_id= $cnt['order_id'];
						$user_id= $cnt['user_id'];

						$update_order = DB::table('orders')
			                                    ->where('id', $order_id)
			                                    ->update(['order_status' => 'complete']);
									
					}
					if (strcmp ($res, "INVALID") == 0) {
					// PAYMENT INVALID & INVESTIGATE MANUALY! 
					// E-mail admin or alert user
					//$message = '
					//	Dear Administrator,
					//	A payment has been made but is flagged as INVALID.
					//	Please verify the payment manualy and contact the buyer.
					//	Buyer Email: '.$data['payer_email'].'
					//	';
					// Used for debugging

					@mail("sumitra.unified@gmail.com", "Me PAYPAL DEBUGGING2".$message, "Invalid Response<br />data = <pre>".print_r($post, true)."</pre>");
			    	
				}
			}	
			fclose ($fp);
			}

			@mail("sumitra.unified@gmail.com", "Me PAYPAL DEBUGGING3".$message, "Invalid Response<br />data = <pre>".print_r($post, true)."</pre>");

    }
    public function success()
    {
    	//SuccessFull payment View

    	$xsrfToken = app('Illuminate\Encryption\Encrypter')->encrypt(csrf_token());

    	return view('frontend.checkout.pyament_success',array('title'=>'MIRAMIX | Checkout-Success'))->with('xsrf_token', $xsrfToken);
    }
    public function cancel()
    {
    	//Cancel payment View
    	return view('frontend.checkout.payment_cancel',array('title'=>'MIRAMIX | Checkout-Cancel'));
    }

    public function checkoutAuthorize($id)
    {
    	$card_type 		= Input::get('card_type');
        $card_number 	= "4042760173301988";//Input::get('card_number');
        $card_exp_month = "03"; //Input::get('card_exp_month');
        $card_exp_year 	= "19"; //Input::get('card_exp_year');
		//$cvv 			=Input::get('cvv');
		
    	$order_id = $id;
		$order_list = DB::table('orders')
	                    ->leftJoin('order_items', 'order_items.order_id', '=', 'orders.id')
	                    ->select('orders.*', 'order_items.brand_id', 'order_items.brand_name', 'order_items.product_id', 'order_items.product_name', 'order_items.product_image', 'order_items.quantity', 'order_items.price', 'order_items.form_factor_id', 'order_items.form_factor_name')
	                    ->where('orders.id','=',$order_id)
	                    ->get();
    	// By default, this sample code is designed to post to our test server for
		// developer accounts: https://test.authorize.net/gateway/transact.dll
		// for real accounts (even in test mode), please make sure that you are
		// posting to: https://secure.authorize.net/gateway/transact.dll
		$post_url = "https://test.authorize.net/gateway/transact.dll";

		$post_values = array(
			
			// the API Login ID and Transaction Key must be replaced with valid values
			"x_login"			=> "2BPuf2X4wmn",
			"x_tran_key"		=> "7kR5A9k8xa8F9ztz",

			"x_version"			=> "3.1",
			"x_delim_data"		=> "TRUE",
			"x_delim_char"		=> "|",
			"x_relay_response"	=> "FALSE",

			"x_type"			=> "AUTH_CAPTURE",
			"x_method"			=> "CC",
			"x_card_num"		=> $card_number, //"4042760173301988", $card_number
			"x_exp_date"		=> $card_exp_month.$card_exp_year ,				//$card_exp_month.$card_exp_year

			"x_amount"			=> $order_list[0]->order_total,
			"x_description"		=> "Miramix Transaction",

			/*"x_first_name"		=> "John",
			"x_last_name"		=> "Doe",
			"x_address"			=> "1234 Street",
			"x_state"			=> "WA",
			"x_zip"				=> "98004"*/
			
			// Additional fields can be added here as outlined in the AIM integration
			// guide at: http://developer.authorize.net
		);

		// This section takes the input fields and converts them to the proper format
		// for an http post.  For example: "x_login=username&x_tran_key=a1B2c3D4"
		$post_string = "";
		foreach( $post_values as $key => $value )
			{ $post_string .= "$key=" . urlencode( $value ) . "&"; }
		$post_string = rtrim( $post_string, "& " );

		// The following section provides an example of how to add line item details to
		// the post string.  Because line items may consist of multiple values with the
		// same key/name, they cannot be simply added into the above array.
		//
		// This section is commented out by default.
		/*
		$line_items = array(
			"item1<|>golf balls<|><|>2<|>18.95<|>Y",
			"item2<|>golf bag<|>Wilson golf carry bag, red<|>1<|>39.99<|>Y",
			"item3<|>book<|>Golf for Dummies<|>1<|>21.99<|>Y");
			
		foreach( $line_items as $value )
			{ $post_string .= "&x_line_item=" . urlencode( $value ); }
		*/

		// This sample code uses the CURL library for php to establish a connection,
		// submit the post, and record the response.
		// If you receive an error, you may want to ensure that you have the curl
		// library enabled in your php configuration
		$request = curl_init($post_url); // initiate curl object
			curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
			curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
			curl_setopt($request, CURLOPT_POSTFIELDS, $post_string); // use HTTP POST to send form data
			curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response.
			$post_response = curl_exec($request); // execute curl post and store results in $post_response
			// additional options may be required depending upon your server configuration
			// you can find documentation on curl options at http://www.php.net/curl_setopt
		curl_close ($request); // close curl object

		// This line takes the response and breaks it into an array using the specified delimiting character
		$response_array = explode($post_values["x_delim_char"],$post_response);
		echo "<pre>"; print_r($response_array); exit;
		if($response_array[0] == 1)
		{
			$transaction_status ="success";
			$update_order = DB::table('orders')
							->where('id', $order_id)
							->update(['transaction_id' => $response_array[6],'transaction_status'=>$transaction_status]);
		}
		else
		{
			$msg = $response_array[3];
			
		}
		// The results are output to the screen in the form of an html numbered list.
		echo "<OL>\n";
		foreach ($response_array as $value)
		{
			echo "<LI>" . $value . "&nbsp;</LI>\n";
		}
		echo "</OL>\n";
		// individual elements of the array could be accessed to read certain response
		// fields.  For example, response_array[0] would return the Response Code,
		// response_array[2] would return the Response Reason Code.
		// for a list of response fields, please review the AIM Implementation Guide
    }
}