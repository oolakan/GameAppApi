<?php

namespace App\Http\Controllers;

use App\Game;
use App\GameTransaction;
use App\Otp;
use App\User;
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
//    private $username = 'info@parcel-it.com';
//    private $password = 'parceladmin';
//    private $sms_base_url = 'http://login.betasms.com';

    private $username = 'asenimegregory@gmail.com';
    private $password = 'parceladmin';
    private $sms_api_key = 'e066554019bbd1ea31e4ae6d1fef362a5bc81dbf';


    private $sms_sender = 'LottoStars';
    private $NOT_APPROVED = 'NOT APPROVED';
    private $APPROVED = 'APPROVED';

    private $user;
    public function __construct()
    {}
    //create otp
    public function createOtpForSignUp(Request $request){
        $message = 'Your account verification code is: ';
        return $this->sendOtp($request, $message);
    }

    public function createOtpForPasswordReset(Request $request){
        $message = 'Your password reset code is: ';
        return $this->sendOtp($request, $message);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * create customers detail
     */
    public function completeUserRegistration(Request $request, $id)
    {

        $User                           =   new User();
        $api_token                      =   $this->randomKey(50);
        $hashedPassword                 =   app('hash')->make($request->password);
        $User->updateOrCreate(['phone'  => $request->phone],
            [   'name'                  => $request->name,
                'email'                 => $request->email,
                'location'              => $request->location,
                'phone'                 =>  $request->phone,
                'approval_status'       => $this->NOT_APPROVED,
                'password'              => $hashedPassword,
                'api_token'             => $api_token,
            ]);
        // get user
        $this->user                     =  User::find($id);
        $this->status_code = 201;//
        $this->message = 'Request successfully served';
        //Send sms
        $message = "Thanks for joining LottoStars. Download the app from playstore and login with \nEmail:" . $this->recipient_email . " and password";
        $message    =   preg_replace('/\s/','%20', $message);
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
    }


    /**
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * Get all customers data
     */
    public function index()
    {
        $User = User::all();
        return response()->json(array('Users' => $User));
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * Get customers password
     */

    public function resetPassword(Request $request){
        $this->user = User::find($request->users_id);
        if($this->user){
            $hashedPassword                 =   app('hash')->make($request->password);
            $this->user->password = $hashedPassword;
            $this->user->save();
            $this->status_code = 200;
            $this->message = 'You have successfully changed your password. You can now login with your new password.';
            $this->recipient_email  =   $this->user->email;
            $this->recipient_name   =   $this->user->name;
//            //Send email notification
//            Mail::send('emails.new_password', [ 'uname' => $this->recipient_name], function ($m) {
//                $m->from($this->sender, $this->msg_title);
//                $m->to($this->recipient_email, $this->recipient_name)->subject($this->subject);
//            });
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
        $hashedPassword      =   app('hash')->make($request->password);
        $User = DB::table('users')
            ->where('email', '=', $request->email)
            ->where('password', '=', $hashedPassword)
            ->where('approval_status', '=', $this->APPROVED)
            ->first();
        if(count($User)   >   0){
            $this->status_code = 200;
            $this->message = 'Login Successful';
            return response()->json(array('status' => $this->status_code, 'message' => $this->message, 'UserData' => $User));
        }
        else{
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
        $User = User::where('phone', '=', $request->phone)->first();
        if ($User) {
            $this->user = User::find($User->id);
        } else {
            $this->user = new User();
        }
        $this->user->phone = $request->phone;
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

        $this->sms_url = 'http://api.ebulksms.com:8080/sendsms?username=' . $this->username . '&apikey=' . $this->sms_api_key . '&messagetext=' . $_message . '&sender=LottoStars&flash=0&recipients=' . $this->recipient_phone;
        $this->sms_url = trim($this->sms_url);
        $this->getContent($this->sms_url);
        return response()->json(array('otp' => $OtpCode));
    }
}
