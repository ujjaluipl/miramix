<?php namespace App\Http\Controllers\Frontend; /* path of this controller*/

use App\Model\Brandmember;  /* Model name*/
use App\Model\Address;      /* Model name*/
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Input;              /* For input */
use Validator;
use Session;
use Imagine\Image\Box;
use Image\Image\ImageInterface;
use Illuminate\Pagination\Paginator;
use DB;
use Hash;
use Mail;
use App\Helper\helpers;


class RegisterController extends Controller {

    public function __construct() 
    {
       // view()->share('brand_class','active');
    }
   /**
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index()
    {
        $obj = new helpers();
        if($obj->checkUserLogin()){
            return redirect('home');
        }
        $country = DB::table('countries')->orderBy('name','ASC')->get();
        // echo "<pre>";print_r($country); exit;
        $alldata = array();
        foreach($country as $key=>$value)
        {
            $alldata[$value->country_id] = $value->name;
        }
        //echo "<pre>";print_r($alldata); exit;
        return view('frontend.register.create',compact('alldata'));
    }

    public function brandRegister()
    {
        $obj = new helpers();
        if($obj->checkUserLogin()){
            return redirect('home');
        }

        $country = DB::table('countries')->orderBy('name','ASC')->get();
        
        $alldata = array();
        foreach($country as $key=>$value)
        {
            $alldata[$value->country_id] = $value->name;
        }
		//echo "<pre>";print_r($alldata); exit;
		$reg_brand_id =''; // No register brand id for first time.
		
		if(Request::isMethod('post'))
        {
			//echo $_FILES['image']['name']."<pre>";print_r($_FILES);exit;

			//if(Input::hasFile('government_issue'))
			if($_FILES['government_issue']['name']!="")
			{
				$destinationPath = 'uploads/brand_government_issue_id/'; // upload path
				$extension = Input::file('government_issue')->getClientOriginalExtension(); // getting image extension
				$government_issue = rand(111111111,999999999).'.'.$extension; // renameing image
				Input::file('government_issue')->move($destinationPath, $government_issue); // uploading file to given path
				
			}
			else
			{
				$government_issue = '';
			}
			//if(Input::hasFile('image'))
			if($_FILES['image']['name']!="")
			{
				$destinationPath = 'uploads/brandmember/'; // upload path
                $thumb_path = 'uploads/brandmember/thumb/';
                $medium = 'uploads/brandmember/thumb/';
				$extension = Input::file('image')->getClientOriginalExtension(); // getting image extension
				$fileName = rand(111111111,999999999).'.'.$extension; // renameing image
				Input::file('image')->move($destinationPath, $fileName); // uploading file to given path
				
                $obj->createThumbnail($fileName,771,517,$destinationPath,$thumb_path);
                $obj->createThumbnail($fileName,109,89,$destinationPath,$medium);
			}
			else
			{
				$fileName = '';
			}

			$hashpassword = Hash::make(Request::input('password'));
			$address = New Address;

			$brandmember= Brandmember::create([
				'fname'             => Request::input('fname'),
				'lname'             => Request::input('lname'),
				'email'             => Request::input('email'),
				'password'          => $hashpassword,
				'government_issue'  => $government_issue,
				'phone_no'          => Request::input('phone_no'),
				'routing_number'    => Request::input('routing_number'),
				'paypal_email'      => Request::input('paypal_email'),
				'mailing_address'   => Request::input('mailing_address'),
				'default_band_preference'   => Request::input('banking_address'),
				'pro_image'         => $fileName,
				'role'              => 1,                   // for member role is "0"
				'admin_status'      => 0                   // Admin status
			]);
			
			
			$shipping_card_addr = array('card_holder_name' => Request::input('card_holder_name'),'card_name' => Request::input('card_name'),'expiry_month' => Request::input('expiry-month'),'expiry_year' => Request::input('expiry-year'),'cvv' => Request::input('cvv'),'card_shiping_name' => Request::input('card_shiping_name'),'card_shiping_address' => Request::input('card_shiping_address'),'card_country_id' => Request::input('card_country_id'),'card_shiping_city' => Request::input('card_shiping_city'),'card_shipping_phone_no' => Request::input('card_shipping_phone_no'),'card_shipping_address2' => Request::input('card_shipping_address2'),'card_state' => Request::input('card_state'),'card_shipping_postcode' => Request::input('card_shipping_postcode'));
			$shipping_card_addr_serial = serialize($shipping_card_addr);
	
			$lastInsertedId = $brandmember->id;
			
			$reg_brand_id = $lastInsertedId; //base64_encode ($lastInsertedId); // encrypted last register brand member id
			
			$address->mem_brand_id = $lastInsertedId;
			$address->address = Request::input('shiping_address');
			$address->address2 = Request::input('shipping_address2');
			$address->country_id = Request::input('country');
			$address->zone_id =  Request::input('state'); // State id
			$address->city =  Request::input('city');
			$address->postcode =  Request::input('shipping_postcode');
			$address->serialize_val =  $shipping_card_addr_serial;
			
			if($address->save()) 
			{
				$addressId = $address->id;
				$dataUpdateAddress = DB::table('brandmembers')
					->where('id', $lastInsertedId)
					->update(['address' => $addressId]);
	
				$sitesettings = DB::table('sitesettings')->get();
				//exit;
				if(!empty($sitesettings))
				{
					foreach($sitesettings as $each_sitesetting)
					{
					  if($each_sitesetting->name == 'email')
					  {
						$admin_users_email = $each_sitesetting->value;
					  }
					}
				}
				
				//Session::flash('success', 'Registration completed successfully.Please check your email to activate your account.'); 
				//return redirect('brandregister');
	
				$user_name = Request::input('fname').' '.Request::input('lname');
				$user_email = Request::input('email');
				$activateLink = url().'/activateLink/'.base64_encode(Request::input('email')).'/brand';
				$sent = Mail::send('frontend.register.activateLink', array('name'=>$user_name,'email'=>$user_email,'activate_link'=>$activateLink), 
				function($message) use ($admin_users_email, $user_email,$user_name)
				{
					$message->from($admin_users_email);
					$message->to($user_email, $user_name)->subject('Activate Profile Mail');
				});
	
				if( ! $sent) 
				{
					Session::flash('error', 'something went wrong!! Mail not sent.'); 
					return redirect('brandregister');
				}
				else
				{
                    Session::flash('success', 'Registration completed successfully.Please check your email to activate your account.'); 
					Session::flash('flush_reg_brand_id','open_modal'); 
                    Session::put('reg_brand_id',$reg_brand_id);
                    return redirect('brandregister');
				}
			}
		}
				
        return view('frontend.register.registerbrand',compact('alldata'),array('reg_brand_id'=>$reg_brand_id));
    }
   
	public function updateDate()
	{
		$selectdate = date('Y-m-d',strtotime(Input::get('selectdate')));
		$brand_member_id = Input::get('brand_member_id');
         
        $dataUpdateDate = DB::table('brandmembers')
                ->where('id', $brand_member_id)
				->where('role', 1)
                ->update(['calender_date' => $selectdate]);
		Session::flash('success', 'Registration completed successfully.Please check your email to activate your account.'); 

		echo  1;
	}
	
    public function create()
    {

        return view('frontend.register.create');
    }

    public function store(Request $request)
    {
        $register=Request::all();
        //print_r($register);
        $obj = new helpers();
        if(Input::hasFile('image'))
        {
            $destinationPath = 'uploads/img/'; // upload path
            $thumb_path = 'uploads/img/thumb/';
            $extension = Input::file('image')->getClientOriginalExtension(); // getting image extension
            $fileName = rand(111111111,999999999).'.'.$extension; // renameing image
            Input::file('image')->move($destinationPath, $fileName); // uploading file to given path
            
            $obj->createThumbnail($fileName,300,200,$destinationPath,$thumb_path);
        }
        else
        {
            $fileName = '';
        }
        $hashpassword = Hash::make($register['password']);
        $address = New Address;

        $brandmember= Brandmember::create([
            'fname'             => $register['fname'],
            'lname'             => $register['lname'],
            'email'             => $register['email'],
            'password'          => $hashpassword,
            'phone_no'          => $register['phone_no'],
            'pro_image'         => $fileName,
            'role'              => 0,                   // for member role is "0"
            'admin_status'      => 1,                   // Admin status
            'updated_at'        => date('Y-m-d H:i:s'),
            'created_at'        => date('Y-m-d H:i:s')
        ]);

        $lastInsertedId = $brandmember->id;

        $address->mem_brand_id = $lastInsertedId;
        $address->address = $register['address'];
        $address->address2 = $register['address2'];
        $address->country_id = $register['country'];
        $address->zone_id = $register['state']; // State id
        $address->city = $register['city'];
        $address->postcode = $register['postcode'];
        
        if($address->save()) 
        {
            $addressId = $address->id;
            $dataUpdateAddress = DB::table('brandmembers')
                ->where('id', $lastInsertedId)
                ->update(['address' => $addressId]);

            $sitesettings = DB::table('sitesettings')->get();
            //exit;
            if(!empty($sitesettings))
            {
                foreach($sitesettings as $each_sitesetting)
                {
                  if($each_sitesetting->name == 'email')
                  {
                    $admin_users_email = $each_sitesetting->value;
                  }
                }
            }

            $user_name = $register['fname'].' '.$register['lname'];
            $user_email = $register['email'];
            $activateLink = url().'/activateLink/'.base64_encode($register['email']).'/member';
            $sent = Mail::send('frontend.register.activateLink', array('name'=>$user_name,'email'=>$user_email,'activate_link'=>$activateLink), 
            function($message) use ($admin_users_email, $user_email,$user_name)
            {
                $message->from($admin_users_email);
                $message->to($user_email, $user_name)->subject('Activate Profile Mail');
            });

            if( ! $sent) 
            {
                Session::flash('error', 'something went wrong!! Mail not sent.'); 
                return redirect('register');
            }
            else
            {
                Session::flash('success', 'Registration completed successfully.Please check your email to activate your account.'); 
                return redirect('register');
            }
        }
        
    }

    public function getState()
    {
        $country_id = Input::get('countryId');
        
        $state = DB::table('zones')
                    ->where('country_id', '=', $country_id)
                    ->get();
        //echo "<pre>";print_r($state); exit;
        $alldata = array();
        $str='';
        foreach($state as $key=>$value)
        {
            //$alldata[$value->zone_id] = $value->name;
            $str .= '<option value='.$value->zone_id.'>'.$value->name.'</option>';
        }

        echo $str;
        exit;
    }

    public function emailChecking()
    {
        $email_id = Input::get('email');
        
        $email_count = DB::table('brandmembers')
                    ->where('email', '=', $email_id)
                    ->count();
        if($email_count >0)
        {
            echo  1; // Email already exist.
        }
        else
        {
            echo 0 ; // New email for registration.
        }
    }
    

    /* for activate Register user */
    public function activateLink($email=false,$role=false)
    {
        $useremail = base64_decode($email);
        //$useremail = 'sumitra1.unified@gmail.com';

        $active_count = DB::table('brandmembers')
                            ->where('email', '=', $useremail)
                            ->where('status', '=', 1)->first();

        if(!empty($active_count))
        {
            Session::flash('error', 'Your account has been already activated.Please login with your valid credentials.'); 
            if($active_count->role == 1)
            {
                return redirect('brandLogin');
            }
            else
            {
                return redirect('memberLogin');
            }
        }
        else
        {
            $success = DB::table('brandmembers')
                ->where('email', $useremail)
                ->update(['status' => 1]);

            if($success)
            {
                Session::flash('success', 'Your account has been activated.Please login with your valid credentials.'); 
                
                if($role == 'brand')
                {
                    return redirect('brandLogin');
                }
                else if($role == 'member')
                {
                    return redirect('memberLogin');
                }
            }
            else
            {
                Session::flash('error', 'Your account has not been activated.Please try again.'); 
                return redirect('register');
            }
        }
    }
    
}