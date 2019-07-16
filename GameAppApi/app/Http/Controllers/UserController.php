<?php

namespace App\Http\Controllers;

use App\Agent;
use App\Credit;
use App\Game;
use App\GameTransaction;
use App\Merchant;
use App\Otp;
use App\User;
use Illuminate\Hashing\BcryptHasher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    private $status_code = 0;

    private $message = '';
    private $recipient_email;
    private $recipient_name;
    private $customer;
    private $new_password;
    private $sms_url;
    private $recipient_phone;

    private $sms_api_key = 'e066554019bbd1ea31e4ae6d1fef362a5bc81dbf';


    private $username = 'oolakan@yahoo.com';
    private $password = 'Oluwatobi43';
    private $sms_base_url = 'http://login.betasms.com';
    private $sms_sender = 'RASHOLINV';
    private $PENDING = 'PENDING';
    private $BLOCKED = 'BLOCKED';
    private $APPROVED = 'APPROVED';

    private $user;
    public function __construct()
    {}
    //create otp
    public function createOtpForSignUp(Request $request){
        $message = 'Use this OTP to confirm your phone number ';
        return $this->sendOtp($request, $message);
    }

    public function createOtpForPasswordReset(Request $request){
        $message = 'Use this OTP to confirm your phone number  ';
        return $this->sendOtp($request, $message);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * create customers detail
     */
    public function completeUserRegistration(Request $request, $id)
    {
        $rules = [
            'email'     => 'required|email|max:255|unique:users',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status'=> 400, 'message' => 'Email already exist']);
        }
        $hashedPassword = md5($request->password);

        $User = User::find($id);
        if ($User) {
            $this->user = User::find($User->id);
            $this->user->phone = $request->phone;
            $this->user->name = $request->name;
            $this->user->email = $request->email;
            $this->user->location = $request->location;
            $this->user->approval_status = $this->PENDING;
            $this->user->password = $hashedPassword;
            $this->api_token = $User->api_token;
            $this->user->save();
            // get user
            if ($this->user) {
                $Agent = new Agent();
                $ticket_id              =   $this->generateTicketId();
                $Agent->users_id = $User->id;
                $Agent->agent_name = $request->name;
                $Agent->credit_balance = 0.0;
                $Agent->ticket_id              =   $ticket_id;
                $Agent->save();
                $this->status_code = 200;//
                $this->message = 'Request successfully served';
                //Send sms
                $message = "Thanks for joining LottoStars. Download the app from playstore and login with \nEmail:" . $request->email . " and your password";
                $message = preg_replace('/\s/', '%20', $message);
                $this->recipient_phone = $request->phone;
                $this->sms_url = 'http://api.ebulksms.com:8080/sendsms?username=' . $this->username . '&apikey=' . $this->sms_api_key . '&messagetext=' . $message . '&sender=LottoStars&flash=0&recipients=' . $this->recipient_phone;
                $this->sms_url = trim($this->sms_url);
                $this->getContent($this->sms_url);
                //Send user login details as email
//        Mail::send('emails.password', ['username' => $this->recipient_email, 'password' => $this->recipient_password], function ($m) {
//            $m->from($this->sender, $this->msg_title);
//            $m->to($this->recipient_email, $this->recipient_name)->subject($this->subject);
//        });
                return response()->json(array('status' => $this->status_code, 'message' => $this->message, 'UserData' => $this->user));
            } else {
                return response()->json(array('status' => $this->status_code, 'message' => 'failed'));
            }
        }

    }


    /**
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * Get all customers data
     */
    public function index()
    {
        $User = User::where('delete_status', '=', 0)->get();
        return response()->json(array('Users' => $User));
    }

    public function agents(Request $request, $mid) {
        try {
            $User   =   User::find($mid);
            $version    = $request->has('version') ? $request->query('version') : 'old';
            if ($version != '3') {
                return response()->json(array('status' => 400, 'message' => 'failed'), 400);
            }
            if ($User) {
                if ($User->approval_status != 'APPROVED') {
                    return response()->json(array('status' => 400, 'data' => 'Your account has been blocked'), 400);

                } else {
                    $Agents = Agent::with(['user'])->where('agents.merchants_id', '=', $mid)
                        ->where('delete_status', '=', 0)
                        ->leftJoin('credits', 'agents.users_id', '=', 'credits.users_id')
                        ->get();
                    // $Agents = Agent::with(['user'])->where('merchants_id', '=', $mid)->where('delete_status', '=', 0)->get();
                    if (count($Agents) > 0) {
                        $this->status_code = 200;
                        $this->message = 'success';
                        return response()->json(array('message' => $this->message, 'status' => $this->status_code, 'Agents' => $Agents));
                    } else {
                        $this->status_code = 201;
                        $this->message = 'no user available';
                        return response()->json(array('message' => $this->message, 'status' => $this->status_code));
                    }
                }
            }
            else {
                return response()->json(array('status' => 400, 'message' => 'failed'), 400);
            }
        }
        catch (\ErrorException $exception) {
            $this->status_code = 400;
            $this->message = 'failed';
            return response()->json(array('message' => $this->message, 'status' => $this->status_code));
        }
    }

    public function changeStatus($uid, $status) {
        try {
            $User = User::find($uid);
            $User->approval_status = $status;
            $User->save();
            if ($User) {
                $this->status_code = 200;
                $this->message = $User->name . ' has been ' . strtolower($status);
                //GET merchat id
                $mid = Agent::where('users_id', '=', $uid)->first()->merchants_id;

                $User = User::find($mid);
                if ($User) {
                    if ($User->approval_status != 'APPROVED') {
                        return response()->json(array('status' => 400, 'data' => 'Your account has been blocked'), 400);
                    } else {
                        $Agents = Agent::with(['user'])->where('merchants_id', '=', $mid)->where('delete_status', '=', 0)->get();
                        if (count($Agents) > 0) {
                            $this->status_code = 200;
                            $this->message = 'success';
                            return response()->json(array('message' => $this->message, 'status' => $this->status_code, 'Agents' => $Agents));
                        }
                    }
                } else {
                    return response()->json(array('status' => 400, 'message' => 'Your account has been blocked'), 400);
                }
            } else {
                $this->status_code = 200;
                $this->message = 401;
                return response()->json(array('status' => $this->status_code, 'message' => $this->message));
            }
        }
        catch (\ErrorException $exception) {
            $this->status_code = 200;
            $this->message = 401;
            return response()->json(array('status' => $this->status_code, 'message' => $this->message));
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * Get customers password
     */

    public function resetPassword(Request $request){
        $this->user = User::find($request->users_id);
        if($this->user){
            $hashedPassword                 =   md5($request->new_password);
            $this->user->password = $hashedPassword;
            $this->user->save();
            $this->status_code = 200;
            $this->message = 'You have successfully changed your password. Proceed to home page';
            $this->recipient_email  =   $this->user->email;
            $this->recipient_name   =   $this->user->name;
            return response()->json(array('UserData' => $this->user, 'status' => $this->status_code, 'message' => $this->message));
        }
        else{
            $this->status_code = 201;//
            $this->message = 'User Id does not exist';
            return response()->json(array('status' => $this->status_code, 'message' => $this->message));
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * Login
     */

    public function loginUser(Request $request)
    {
        $User = DB::table('users')
            ->where('email', '=', $request->email)
            ->where('password', '=', md5($request->password))
            ->where('delete_status', '=', 0)
            ->first();
        if ($User) {
            //if user is an agent
            if ($User->roles_id == 3) {
                $Merch = Agent::where('users_id', '=', $User->id)->first();
                $merchatid = $Merch->merchants_id;
                $Merchant = User::find($merchatid);
                if ($Merchant) {
                    $MName = $Merchant->name;
                    if ($User->approval_status == $this->APPROVED) {
                        $this->status_code = 200;
                        $this->message = 'Login Successful';
                        return response()->json(array('status' => $this->status_code, 'message' => $this->message, 'UserData' => $User, 'Merchant' => $MName));
                    } else if ($User->approval_status == $this->BLOCKED) {
                        $this->status_code = 203;
                        $this->message = 'Your account has been blocked. Contact the administrator';
                        return response()->json(array('status' => $this->status_code, 'message' => $this->message, 'UserData' => $User, 'Merchant' => $MName));
                    } else if ($User->approval_status == $this->PENDING) {
                        $this->status_code = 202;
                        $this->message = 'Your account is pending. Contact the administrator';
                        return response()->json(array('status' => $this->status_code, 'message' => $this->message, 'UserData' => $User, 'Merchant' => $MName));
                    }
                } else {
                    $this->status_code = 201;//
                    $this->message = 'Contact admin to be assigned to a merchant';
                    return response()->json(array('status' => $this->status_code, 'message' => $this->message));
                }
            } else {
                if ($User->approval_status == $this->APPROVED) {
                    $this->status_code = 200;
                    $this->message = 'Login Successful';
                    return response()->json(array('status' => $this->status_code, 'message' => $this->message, 'UserData' => $User, 'Merchant' => $User->name));
                } else if ($User->approval_status == $this->BLOCKED) {
                    $this->status_code = 203;
                    $this->message = 'Your account has been blocked. Contact the administrator';
                    return response()->json(array('status' => $this->status_code, 'message' => $this->message, 'UserData' => $User, 'Merchant' => $User->name));
                } else if ($User->approval_status == $this->PENDING) {
                    $this->status_code = 202;
                    $this->message = 'Your account is pending. Contact the administrator';
                    return response()->json(array('status' => $this->status_code, 'message' => $this->message, 'UserData' => $User, 'Merchant' => $User->name));
                }
            }
        } else {
            $this->status_code = 201;//
            $this->message = 'Login credentials does not exist';
            return response()->json(array('status' => $this->status_code, 'message' => $this->message));
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function getUser($id){
        /**
         * Get a particular User
         */
        $User  = User::find($id);
        return response()->json(array('Customer' => $User));
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * Delete Customer
     */
    public function deleteUser($id){
        $User = User::find($id);
        try {
            if ($User) {
                $User->delete();
                $this->status_code = 200;
                $this->message = 'User detials deleted successfully';
                return response()->json(array('status' => $this->status_code, 'message' => $this->message));
            } else {
                $this->status_code = 400;
                $this->message = 'Customer does not exist';
                return response()->json(array('status' => $this->status_code, 'message' => $this->message));
            }
        }
        catch(\Exception $ex){
            $this->status_code = 400;
            $this->message = 'Bad Request';
            return response()->json(array('status' => $this->status_code, 'message' => $this->message));
        }


    }

    /**
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * Update Customer
     */
    public function updateUser(Request $request,$id){

        /**
         * Define rules
         */
        $rules = [
            'customer_name'         => 'required',
            'customer_email'        => 'required',
            'customer_phone_no'     => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
        {
            $status_code = 400;//
            $message = 'Empty request sent to server';
            return response()->json(array('status' => $status_code, 'message' => $message ));

        }
        else
        {
            $User   =   User::find($id);

            $User->name        =   $request->input('name');
            $User->email       =   $request->input('email');
            $User->phone       =   $request->input('phone');

            $User->save();
            $this->status_code = 201;//
            $this->message = 'Request successfully served';

            return response()->json(array('status' => $this->status_code, 'message' => $this->message, 'CustomerData' => $User));
        }
    }
    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * Get Activity histories
     */

    public function getTransactions($id){
        try {
            $Transactions = GameTransaction::with(['user'])->where('users_id','=', $id)->orderBy('id', 'desc')->get();
            if ($Transactions) {
                return response()->json(['status' => 200, 'message' => 'Successful', 'History' => $Transactions]);
            }
        }
        catch(\ErrorException $ex){
            return response()->json(['status'   =>  $ex->getCode(), 'message' => $ex->getMessage()]);
        }
    }

    /**
     * @param $length
     * @return string
     * Generate api token of 60 character length
     */
    function randomKey($length) {
        $key = '';
        $pool = array_merge(range(0,9), range('a', 'z'),range('A', 'Z'));

        for($i=0; $i < $length; $i++) {
            $key .= $pool[mt_rand(0, count($pool) - 1)];
        }
        return $key;
    }

    /**
     * @param $url
     * @return mixed|string
     * Get contents from url
     */
    public function getContent($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $contents = curl_exec($ch);
        if (curl_errno($ch)) {
            echo curl_error($ch);
            echo "\n<br />";
            $contents = '';
            return $contents;
        } else {
            curl_close($ch);
        }
        if (!is_string($contents) || !strlen($contents)) {
            echo "Failed to get contents.";
            $contents = '';
            return $contents;
        }
        return $contents;
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */

    public function sendOtp(Request $request, $message)
    {
        $rules = [
            'phone' => 'required|unique:users',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status'=> 400, 'message' => 'Phone no already exist']);
        }
        $User = User::where('phone', '=', $request->phone)->first();
        if ($User) {
            $this->user = User::find($User->id);
        } else {
            $this->user = new User();
        }
        $api_token = $this->randomKey(50);
        $this->user->phone = $request->phone;
        $this->user->api_token =  $api_token;
        $this->user->roles_id   =   3;//3: agent 2:Merchant 1: Admin
        $this->user->save();
        $this->recipient_phone = $request->phone;
        //delete previous otp code generated for user
        $prevOtp =
            //create customer otp code
        $otp = rand(10000, 99999);
        //delete previous otp code generated for user
        //check if otp code was already generated for user
        //create customer otp code
        $OtpCode = new Otp();
        $OtpCode->code = $otp;
        $OtpCode->users_id = $this->user->id;
        $OtpCode->save();
        //send otp to customer phone
        $_message = $message . $otp;
        $_message = preg_replace('/\s/', '%20', $_message);
       // $this->sms_url = $this->sms_base_url . '/api/?username=' . $this->username . '&password=' . $this->password . '&message=' . $_message . '&sender=' . $this->sms_sender . '&mobiles=' . $this->recipient_phone;
        $this->sms_url = $this->sms_base_url . '/api/?username=' . $this->username . '&password=' . $this->password . '&message=' . $_message . '&sender=' . $this->sms_sender . '&mobiles=' . $this->recipient_phone;
        $this->sms_url = trim($this->sms_url);
        $this->getContent($this->sms_url);
        return response()->json(array('status'=> 200, 'message' => 'successful', 'otp' => $OtpCode));
    }
}