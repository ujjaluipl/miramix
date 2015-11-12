<?php namespace App\Http\Controllers\Frontend; /* path of this controller*/

use App\Model\Brandmember; /* Model name*/
use App\Model\Product; /* Model name*/
use App\Http\Requests;
use App\Http\Controllers\Controller;    
use Illuminate\Support\Facades\Request;
use App\Model\Address;  
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
use App\Helper\helpers;
use Authorizenet;
use App\libraries\auth\AuthorizeNetCIM;
use App\libraries\auth\shared\AuthorizeNetCustomer;
use App\libraries\auth\shared\AuthorizeNetPaymentProfile;
use App\libraries\auth\shared\AuthorizeNetAddress;


class BrandController extends BaseController {

    public function __construct() 
    {
        parent::__construct();
        $obj = new helpers();
        if(!$obj->checkBrandLogin())
        {
            $brandlogin = 0; // Logged as a member
        }
        else
        {
            $brandlogin = 1; // Logged as a brand
        }
        view()->share('brandlogin',$brandlogin);
        view()->share('obj',$obj);
    }
   

    public function index()
    {
        
        
        $whereClause = array('role'=>1,'status'=>1,'admin_status'=>1);
        $all_brand_member = DB::table('brandmembers')->where($whereClause)->get();

       // echo "<pre>";print_r($all_brand_member);exit;

        return view('frontend.brand.brand',compact('all_brand_member'),array('title'=>'MIRAMIX | Brand Listing','brand_active'=>'active'));
    }

    public function brandDetails($brand_slug)
    {
        $all_brand_member = DB::table('brandmembers')->where('slug', $brand_slug)->first();

        //$total_brand_pro = 0;
        $limit = 1;
        $product = DB::table('products')
                 ->select(DB::raw('products.id,products.brandmember_id,products.product_name,products.product_slug,products.image1, MIN(`actual_price`) as `min_price`,MAX(`actual_price`) as `max_price`'))
                 ->leftJoin('product_formfactors', 'products.id', '=', 'product_formfactors.product_id')
                 ->where('products.brandmember_id', '=', $all_brand_member->id)
                 ->where('products.active', 1)
                 ->groupBy('product_formfactors.product_id')
                 ->paginate($limit);

        $total_brand_pro = DB::table('products')
                 ->select(DB::raw('products.id,products.brandmember_id,products.product_name,products.product_slug,products.image1, MIN(`actual_price`) as `min_price`,MAX(`actual_price`) as `max_price`'))
                 ->leftJoin('product_formfactors', 'products.id', '=', 'product_formfactors.product_id')
                 ->where('products.brandmember_id', '=', $all_brand_member->id)
                 ->where('products.active', 1)
                 ->groupBy('product_formfactors.product_id')
                 ->count();
        // print_r(DB::enableQueryLog()); exit;
        //echo "<pre/>";print_r($product); exit;
        //$total_brand_pro = count($product);
        $product->setPath($brand_slug);
        return view('frontend.brand.brand_details',compact('all_brand_member','total_brand_pro','product','limit'),array('title'=>'MIRAMIX | Brand Listing','brand_active'=>'active'));
    }


    public function brandDashboard()
    {
          $obj = new helpers();
         if(!$obj->checkBrandLogin()){
            return redirect('brandLogin');
        }
        
        $body_class = 'home';
        $brand_details = Brandmember::find(Session::get('brand_userid'));
        return view('frontend.brand.brand_dashboard',compact('brand_details','body_class'),array('title'=>'MIRAMIX | Brand Dashboard'));
    } 

   
    
    public function validateCalltime(){
        $time=Request::input('time');
        $date=Request::input('date');
        if($time=='' || $date==''){
            echo "invalid";
            exit;
        }
        $given_date=strtotime($date." ".$time);
        $given_date= date("Y-m-d H:s:i",$given_date);
        
        $all_brand_member = DB::table('brandmembers')->where('call_datetime', $given_date)->first();
        
        if(!isset($all_brand_member->id)){
            
            echo 'valid';
        }
        else{
            echo "alreadybooked";
        }
        
        exit;
    }

 public function brandAccount()
    {
        $obj = new helpers();
        if(!$obj->checkBrandLogin())
        {
            return redirect('brandLogin');
        }
       if(Request::isMethod('post'))
        {
            
            
            if($_FILES['image']['name']!="")
			{
				$destinationPath = 'uploads/brandmember/'; // upload path
                $thumb_path = 'uploads/brandmember/thumb/';
                $medium = 'uploads/brandmember/thumb/';
				$extension = Input::file('image')->getClientOriginalExtension(); // getting image extension
				$fileName = rand(111111111,999999999).'.'.$extension; // renameing image
				Input::file('image')->move($destinationPath, $fileName); // uploading file to given path
				
                $obj->createThumbnail($fileName,661,440,$destinationPath,$thumb_path);
                $obj->createThumbnail($fileName,116,116,$destinationPath,$medium);
			}
			else
			{
				$fileName = '';
			}
            
            $brand=array('fname'=> Request::input('fname'),
			 'lname'=> Request::input('lname'),
                         'business_name'=> Request::input('business_name'),
                         'phone_no'=> Request::input('phone_no'),
                         'youtube_link'=> Request::input('youtube_link'),
                         'brand_details'=> Request::input('brand_details'),
                         'brand_sitelink'=> Request::input('brand_sitelink'),
                                );
            if(!empty($fileName)){
                $brand['pro_image']=$fileName;
            }
           
            $brandresult=Brandmember::find(Session::get('brand_userid') );
            $brandresult->update($brand);
            Session::flash('success', 'Your profile is successfully updated.');
            return redirect('brand-account');
            
        }
      $brand_details = Brandmember::find(Session::get('brand_userid'));
        
        
        return view('frontend.brand.brand_account',compact('brand_details'),array('title'=>'Brand Information'));
    }
    
public function brand_creditcard_details(){
    $obj = new helpers();
        if(!$obj->checkBrandLogin())
        {
            return redirect('brandLogin');
        }
        
         $country = DB::table('countries')->orderBy('name','ASC')->get();
        
        $alldata = array();
        foreach($country as $key=>$value)
        {
            $alldata[$value->country_id] = $value->name;
        }
        $brand_details = Brandmember::find(Session::get('brand_userid'));
      
        $brand_details->auth_payment_profile_id;
        $brand_details->auth_address_id;
        
        $allstates=array();
       if(Request::isMethod('post'))
        {
            
            
            $request = new AuthorizeNetCIM;
            $paymentProfile = new AuthorizeNetPaymentProfile;
            
            
            if($brand_details->auth_profile_id==''){
                $country = DB::table('countries') ->where('country_id', '=',Request::input('card_country_id'))->first();
                
                $shipping_card_addr = array('card_holder_fname' => Request::input('card_holder_fname'),
					'card_holder_lname' => Request::input('card_holder_lname'),
					
					'expiry_month' => Request::input('expiry_month'),
					'expiry_year' => Request::input('expiry_year'),
					'cvv' => Request::input('cvv'),
					'card_shiping_name' => Request::input('card_shiping_name'),
					'card_shiping_address' => Request::input('card_shiping_address'),
					'card_country_id' => Request::input('card_country_id'),
					'card_shiping_city' => Request::input('card_shiping_city'),
					'card_shipping_phone_no' => Request::input('card_shipping_phone_no'),
					
					'card_state' => Request::input('card_state'),
					'card_shipping_postcode' => Request::input('card_shipping_postcode'),
					'email'=> Request::input('email'),
					'card_number'=>Request::input('card_number'),
					'country'=>$country->name
					);
            
            $shipping_card_addr['company_name'] = Request::input('company_name');
            
            $shipping_card_addr['card_shipping_fax']  =Request::input('card_shipping_fax');
	    
	    $res=Authorizenet::createprofile($shipping_card_addr);
            
                    if($res['status']=='success'){
                        
                        $brand=array('auth_profile_id'  =>$res['customer']['profile_id'],
				'auth_payment_profile_id'  =>$res['customer']['payment_profile_id'],
				'auth_address_id'  =>$res['customer']['address_id']);
                         $shipping_card_addr_serial = serialize($shipping_card_addr);
                        $brand['card_details']=$shipping_card_addr_serial;
                         $brandresult=Brandmember::find(Session::get('brand_userid') );
                        $brandresult->update($brand);
                        
                         Session::flash('success', 'Your credit card information is created successfully.');
                        return redirect('brand-creditcards');
                        
                    }else{
                        Session::flash('error', 'Something went wrong to update credit card.');
                     return redirect('brand-creditcards');
                    
                    }
                
            }else{
            
            
            if(strstr(Request::input('card_number'),'XXXX')!=false)
           $paymentProfile->payment->creditCard->cardNumber = Request::input('card_number');
            
            $paymentProfile->payment->creditCard->expirationDate = Request::input('expiry_year')."-".Request::input('expiry_month');
            $response = $request->updateCustomerPaymentProfile($brand_details->auth_profile_id,$brand_details->auth_payment_profile_id, $paymentProfile);
           
            
            
            $address = new AuthorizeNetAddress;
            $address->firstName = Request::input('card_holder_fname');
            $address->lastName = Request::input('card_holder_lname');
            if(Request::input('company_name'))
            $address->company = Request::input('company_name');
            
           //  if(strstr(Request::input('card_number'),'XXXX')!=false)
            //$address->card_number = Request::input('card_number');
            
            $address->address = Request::input('card_shiping_address');
            $address->city = Request::input('card_shiping_city');
            $address->state = $obj->get_state(Request::input('card_state'));
            $address->zip = Request::input('card_shipping_postcode');
            $country = DB::table('countries') ->where('country_id', '=',Request::input('card_country_id'))->first();
            $address->country =$country->name;
            $address->phoneNumber =   Request::input('card_shipping_phone_no');
            if(Request::input('card_shipping_fax'))
            $address->faxNumber =Request::input('card_shipping_fax');
           // print_r($address);
           // exit;
            $response2 = $request->updateCustomerShippingAddress($brand_details->auth_profile_id, $brand_details->auth_address_id, $address);
            
            
            
            
                    if($response->isOk() && $response2->isOk()){
        
            $shipping_card_addr = array('card_holder_fname' => Request::input('card_holder_fname'),
                                        'card_holder_lname' => Request::input('card_holder_lname'),
                                        'expiry_month' => Request::input('expiry_month'),
                                        'expiry_year' => Request::input('expiry_year'),
                                        'card_shiping_address' => Request::input('card_shiping_address'),
                                        'card_country_id' => Request::input('card_country_id'),
                                        'card_shiping_city' => Request::input('card_shiping_city'),
                                        'card_shipping_phone_no' => Request::input('card_shipping_phone_no'),
                                       
                                        'card_state' => Request::input('card_state'),
                                        'card_shipping_postcode' => Request::input('card_shipping_postcode'));
	    $shipping_card_addr_serial = serialize($shipping_card_addr);
            $brand['card_details']=$shipping_card_addr_serial;
            $brandresult=Brandmember::find(Session::get('brand_userid') );
            $brandresult->update($brand);
            
             Session::flash('success', 'Your credit card information is updated successfully.');
            return redirect('brand-creditcards');
            
           }else{
            Session::flash('error', 'Something went wrong to update credit card.');
            return redirect('brand-creditcards');
           
           }
            
            }
            
           
          
        }
        
        
        
        
        $request = new AuthorizeNetCIM;
        $creditcard=$request->getCustomerProfile($brand_details->auth_profile_id);
        if(isset($creditcard->xml->profile)){
        $carddetails['profile_id']=$creditcard->xml->profile->customerProfileId;
        $carddetails['first_name']=$creditcard->xml->profile->shipToList->firstName;
        $carddetails['last_name']=$creditcard->xml->profile->shipToList->lastName;
        
        $carddetails['card_number']=$creditcard->xml->profile->paymentProfiles->payment->creditCard->cardNumber;
        $cc=unserialize($brand_details->card_details);
        $carddetails['expiry_month']=$cc['expiry_month'];
        $carddetails['expiry_year']=$cc['expiry_year'];
        $carddetails['company_name']=$creditcard->xml->profile->shipToList->company;
        $carddetails['card_shiping_address']=$creditcard->xml->profile->shipToList->address;
        $carddetails['card_country_id']=$cc['card_country_id'];
        $carddetails['card_state']=$cc['card_state'];
        $carddetails['card_shiping_city']=$creditcard->xml->profile->shipToList->city;
        $carddetails['card_shipping_postcode']=$creditcard->xml->profile->shipToList->zip;
        $carddetails['card_shipping_phone_no']=$creditcard->xml->profile->shipToList->phoneNumber;
        $carddetails['card_shipping_fax']=$creditcard->xml->profile->shipToList->faxNumber;
        
        $states = DB::table('zones')->where('country_id',  $carddetails['card_country_id'])->orderBy('name','ASC')->get();
        
        $allstates = array();
        foreach($states as $key=>$value)
        {
            $allstates[$value->zone_id] = $value->name;
        }
        
        }else{
        $carddetails['profile_id']='';
        $carddetails['first_name']='';
        $carddetails['last_name']='';
        
        $carddetails['card_number']='';
        
        $carddetails['expiry_month']='';
        $carddetails['expiry_year']='';
        $carddetails['company_name']='';
        $carddetails['card_shiping_address']='';
        $carddetails['card_country_id']='';
        $carddetails['card_state']='';
        $carddetails['card_shiping_city']='';
        $carddetails['card_shipping_postcode']='';
        $carddetails['card_shipping_phone_no']='';
        $carddetails['card_shipping_fax']='';
        }
        
        
        
       
         return view('frontend.brand.brand_creditcard',compact('brand_details','alldata','allstates','carddetails'),array('title'=>'Creditcard Information'));
    
    }
    
public function brand_paydetails(){
    $obj = new helpers();
        if(!$obj->checkBrandLogin())
        {
            return redirect('brandLogin');
        }
        $country = DB::table('countries')->orderBy('name','ASC')->get();
        
        $alldata = array();
        foreach($country as $key=>$value)
        {
            $alldata[$value->country_id] = $value->name;
        }
        
        
        
       if(Request::isMethod('post'))
        {
            
            $brand['bank_name']=Request::input('bank_name');
           
            $brand['account_number']=Request::input('account_number');
            $brand['routing_number']=Request::input('routing_number');
            $brand['paypal_email']=Request::input('paypal_email'); 
            $brand['mailing_name']=Request::input('mailing_name');
            $brand['mailing_lastname']=Request::input('mailing_lastname');
            $brand['mailing_address']=Request::input('mailing_address');
            $brand['mailing_country_id']=Request::input('mailing_country_id');
            $brand['mailing_state']=Request::input('mailing_state');
            $brand['mailing_city']=Request::input('mailing_city');
            $brand['mailing_postcode']=Request::input('mailing_postcode');
            $brand['mailing_address2']=Request::input('mailing_address2');
            //$brand['mailing_phone_no']=Request::input('mailing_phone_no');
            $brand['default_band_preference']=Request::input('default_band_preference');
            
            $brandresult=Brandmember::find(Session::get('brand_userid') );
            $brandresult->update($brand);
            
             Session::flash('success', 'Your payment information is updated successfully.');
            return redirect('brand-paydetails');
        }
        $brand_details = Brandmember::find(Session::get('brand_userid'));
        
        $states = DB::table('zones')->where('country_id', $brand_details->mailing_country_id)->orderBy('name','ASC')->get();
        
        $allstates = array();
        foreach($states as $key=>$value)
        {
            $allstates[$value->zone_id] = $value->name;
        }
        
        
         return view('frontend.brand.brand_payment',compact('brand_details','alldata','allstates'),array('title'=>'Payment Information'));
    
    }
    
    
    
      public function brand_change_pass()
    { 
        $obj = new helpers();
        if(!$obj->checkBrandLogin()){
            return redirect('brandLogin');
        }
        if(Request::isMethod('post'))
        {

            if(!Session::has('brand_userid')){
                return redirect('brandLogin');
            }

           // print_r($_POST);exit;
          $old_password = Request::input('old_password');
          

          $password = Request::input('password');
          $conf_pass = Request::input('conf_pass');

          // Get Admin's password

          $user=Brandmember::find(Session::get('brand_userid'));
          

          if(Hash::check($old_password, $user['password']))
          {
            if($password!=$conf_pass){
              Session::flash('error', 'Password and confirm password is not matched.'); 
              return redirect('change-password');

            }
            else{
              DB::table('brandmembers')->where('id', Session::get('brand_userid'))->update(array('password' => Hash::make($password)));
              
              Session::flash('success', 'Password successfully changed.'); 
              return redirect('change-password');
            }
          }
          else{
            Session::flash('error', 'Old Password does not match.'); 
            return redirect('change-password');
          }
        }

        return view('frontend.brand.brandchangepassword',array('title' => 'Brand Change Password'));
    }

    /* --------------------  Multiple Shipping Address For Brand --------------------*/

     public function brandShippingAddress()
     {
        $obj = new helpers();
        if(!$obj->checkBrandLogin())
        {
            return redirect('brandLogin');
        }
        
        $address = DB::table('addresses')->where("mem_brand_id",Session::get('brand_userid'))->orderBy('id','DESC')->get();
      $brand_details = Brandmember::find(Session::get('brand_userid'));
        
        return view('frontend.brand.brand_shipping_address',compact('address','brand_details'),array('title' => 'My Addresses'));
     } 
     public function createBrandShippingAddress()
     {
        $obj = new helpers();
        if(!$obj->checkBrandLogin())
        {
            return redirect('brandLogin');
        }
        
         $country = DB::table('countries')->orderBy('name','ASC')->get();
        
        $alldata = array();
        foreach($country as $key=>$value)
        {
            $alldata[$value->country_id] = $value->name;
        }
        
         if(Request::isMethod('post'))
        {
            $address = New Address;
            $address->address_title = Request::input('address_title');
            $address->mem_brand_id = Session::get('brand_userid');
	    $address->first_name = Request::input('first_name');
	    $address->last_name = Request::input('last_name');
            $address->address = Request::input('address');
	    $address->address2 = Request::input('address2');
            $address->country_id = Request::input('country');
            $address->zone_id =  Request::input('zone_id'); // State id
            $address->city =  Request::input('city');
	    $address->postcode =  Request::input('postcode');
            $address->phone =  Request::input('phone');
           // $address->email =  Request::input('email');
            
            if($address->save()) 
			{
				
                                if(Request::input('default_address')=='1'){
                                $addressId = $address->id;
				$dataUpdateAddress = DB::table('brandmembers')
					->where('id',Session::get('brand_userid'))
					->update(['address' => $addressId]);
                                        
                                }
                                
              Session::flash('success', 'Shipping Address successfully added.'); 
              return redirect('brand-shipping-address');
                                        
                        }
            
            
            
        }
        
        return view('frontend.brand.create_brand_shipping',compact('alldata'),array('title' => 'Create Shipping Address'));
     } 
     public function editBrandShippingAddress()
     {
        $obj = new helpers();
        if(!$obj->checkBrandLogin())
        {
           return redirect('brandLogin');
        }
        
        $id=Request::input('id');
        if(empty($id)){
            
            return redirect('brand-shipping-address');
        }
        
        $country = DB::table('countries')->orderBy('name','ASC')->get();
        
        $alldata = array();
        foreach($country as $key=>$value)
        {
            $alldata[$value->country_id] = $value->name;
        }
        
        
         if(Request::isMethod('post'))
        {
            $address = array();
            
            $address['mem_brand_id'] = Session::get('brand_userid');
	    $address['first_name'] = Request::input('first_name');
	    $address['last_name'] = Request::input('last_name');
            $address['address'] = Request::input('address');
	    $address['address2'] = Request::input('address2');
            $address['country_id'] = Request::input('country');
            $address['zone_id'] =  Request::input('zone_id'); // State id
            $address['city'] =  Request::input('city');
	    $address['postcode'] =  Request::input('postcode');
            $address['phone'] =  Request::input('phone');
            //$address['email'] =  Request::input('email');
            $address['address_title'] =  Request::input('address_title');
             
            DB::table('addresses')
					->where('id',Request::input('id'))
					->update($address);
           
				
         if(Request::input('default_address')=='1'){
                                $addressId = Request::input('id');
				$dataUpdateAddress = DB::table('brandmembers')
					->where('id',Session::get('brand_userid'))
					->update(['address' => $addressId]);
                
                                
           
                                        
                        }
            Session::flash('success', 'Shipping Address successfully updated.'); 
              return redirect('brand-shipping-address');   
            
            
        }
        
        
        
        
        
        $address = DB::table('addresses')->find($id);
        
        
        
        $states = DB::table('zones')->where('country_id', $address->country_id)->orderBy('name','ASC')->get();
        
        $allstates = array();
        foreach($states as $key=>$value)
        {
            $allstates[$value->zone_id] = $value->name;
        }
        $brand_details = Brandmember::find(Session::get('brand_userid'));
        
        return view('frontend.brand.edit_brand_shipping',compact('alldata','address','allstates','brand_details'),array('title' => 'Edit Shipping Address'));
     }
     
     public function delAddress(){
        
        $obj = new helpers();
        if(!$obj->checkBrandLogin())
        {
           return redirect('brandLogin');
        }
        
        $id=Request::input('id');
        if(empty($id)){
            
            return redirect('brand-shipping-address');
        }
        
        $address = Address::find($id);

        $address->delete();
        
        
        Session::flash('success', 'Shipping Address successfully deleted.'); 
              return redirect('brand-shipping-address'); 
     }
     
     public function soldProducts(){
         $obj = new helpers();
        if(!$obj->checkBrandLogin())
        {
           return redirect('brandLogin');
        }
        
        $brand_details = Brandmember::find(Session::get('brand_userid'));
        $limit = 2;
		
		$products = DB::table('products')
                ->select(DB::raw('products.id,products.brandmember_id,products.product_name,products.product_slug,products.image1, SUM(order_items.`quantity`) as `sale_qty`,SUM(order_items.`price`) as `total_sale`'))
                 ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
                 ->where('products.brandmember_id', '=', Session::get('brand_userid'))
                 ->whereRaw('products.active="1"')
                
                 ->where('products.is_deleted', 0)
                 ->where('products.discountinue', 0)
                ->whereRaw('(order_items.`quantity`)>0')
                ->groupBy('products.id')
                 ->paginate($limit);
                
        $products->setPath('sold-products');
        return view('frontend.brand.sold_product_history',compact('products','brand_details'),array('title' => 'Sold Product History'));
     }
}