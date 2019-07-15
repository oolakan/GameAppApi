<?php

namespace App\Http\Controllers;
use App\Agent;
use App\Credit;
use App\Game;
use App\Role;
use App\User;
use App\Winning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CreditController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $status;
    private $message;
    private $user_credit_id;
    private $Credit;
    public function index($uid)
    {
        try {
            $this->status = 200;
            $this->message = 'success';
            $Credit = Credit::where('users_id', '=', $uid)->first();
            return response()->json(array('status' => $this->status, 'message' => $this->message, 'Credit' => $Credit));
        } catch (\ErrorException $ex) {
            $this->status = 400;
            $this->message = 'failed';
            return response()->json(array('status' => $this->status, 'message' => $this->message));
        }
    }

    /**
     * @param $uid
     * @param $credit
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * DEDUCT CREDIT FOR GAME PLAYED
     */
    public function deductCredit($uid, $credit)
    {
        try {
            $this->status = 200;
            $this->message = 'success';
            $Userinfo = User::find($credit);
            $status = $Userinfo->approval_status;
            if ($status == 'APPROVED') {
                $_Agent = Agent::where('users_id', '=', $credit)->where('delete_status', '=', 0)->first();
                if ($_Agent) {
                    $Credit = Credit::where('users_id', '=', $credit)->first();
                    $creditBalance = $Credit->amount - $uid;
                    $NewCredit = Credit::find($Credit->id);
                    $NewCredit->amount = $creditBalance;
                    $NewCredit->save();

                    $_Agent = Agent::where('users_id', '=', $credit)->first();
                    $Agent = Agent::find($_Agent->id);
                    $Agent->credit_balance = $_Agent->credit_balance - $uid;
                    $Agent->save();

                    if ($Credit) {
                        return response()->json(array('status' => $this->status, 'uid' => $uid, 'message' => $this->message, 'Credit' => $NewCredit));
                    }
                } else {
                    $this->status = 400;
                    $this->message = 'failed';
                    return response()->json(array('status' => $this->status, 'message' => 'Your account has been deleted'));
                }
            }
            else {
                return response()->json(array('status' => $this->status, 'message' => 'Your account has been blocked'));
            }
        } catch (\ErrorException $ex) {
            $this->status = 400;
            $this->message = 'failed';
            return response()->json(array('status' => $this->status, 'message' => $ex->getMessage()));
        }
    }

    /**
     * @param $aid
     * @param $uid
     * @param $amount
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * deduct credit
     */
    public function removeCredit($aid, $uid, $amount)
    {
        try {
            $_Credit = Credit::where('users_id', '=', $aid)->first();
            $Agent = Agent::where('users_id', '=', $aid)->first();
            $merchantId = $Agent->merchants_id;
            //check if merchant have enough money in wallet
            $MerchantBalance = Credit::where('users_id', '=', $merchantId)->first();
            if ($_Credit->amount >= $amount) {
                if ($_Credit) {
                    $this->user_credit_id = $_Credit->id;
                    $this->Credit = Credit::find($this->user_credit_id);
                    $this->Credit->amount = $_Credit->amount - $amount;
                }
                $this->Credit->funded_by = $uid;
                $this->Credit->users_id = $aid;
                $this->Credit->merchants_id = $uid;
                $this->Credit->save();

                // credit merchant wallet
                $MerchantWallet = Credit::find($MerchantBalance->id);
                $MerchantWallet->amount = $MerchantBalance->amount + $amount;
                $MerchantWallet->save();

                //deduct agent wallet
                $_Agent = Agent::where('users_id', '=', $aid)->first();
                $Agent = Agent::find($_Agent->id);
                $Agent->credit_balance = $_Agent->credit_balance - $amount;
                $Agent->save();
            } else {
                $this->status = 400;
                $this->message = 'Agent has insufficient credit balance';
                return response()->json(array('status' => $this->status, 'message' => $this->message));
            }
            if ($this->Credit) {
                $this->status = 200;
                $this->message = 'Credit balance updated successfully';
                return $this->agents($uid, $this->message);
                // return response()->json(array('status' => $this->status, 'message' => $this->message));
            } else {
                $this->status = 401;
                $this->message = 'failed to update credit status';
                return response()->json(array('status' => $this->status, 'message' => $this->message));
            }
        } catch (\ErrorException $exception) {
            $this->status = 400;
            $this->message = 'failed';
            return response()->json(array('status' => $this->status, 'message' => $exception->getMessage()));
        }
    }

    public function updateCredit($aid, $uid, $amount)
    {
        try {
            $_Credit            =   Credit::where('users_id', '=', $aid)->first();
            $Agent              =   Agent::where('users_id', '=', $aid)->first();
            $merchantId         =   $Agent->merchants_id;
            //check if merchant have enough money in wallet
            $MerchantBalance    =   Credit::where('users_id', '=', $merchantId)->first();
            if ($MerchantBalance) {
                if (doubleval($MerchantBalance->amount) >= doubleval($amount)){
                    if ($_Credit) {
                        $this->user_credit_id = $_Credit->id;
                        $this->Credit = Credit::find($this->user_credit_id);
                        $this->Credit->amount = $amount + $_Credit->amount;
                    } else {
                        $this->Credit = new Credit();
                        $this->Credit->amount = $amount;
                    }
                    $this->Credit->funded_by = $uid;
                    $this->Credit->users_id = $aid;
                    $this->Credit->merchants_id = $uid;
                    $this->Credit->save();

                    // deduct money from merchant wallet
                    $MerchantWallet     =   Credit::find($MerchantBalance->id);
                    $MerchantWallet->amount = $MerchantBalance->amount - $amount;
                    $MerchantWallet->save();

                    $_Agent = Agent::where('users_id', '=', $aid)->first();
                    $Agent = Agent::find($_Agent->id);
                    $Agent->credit_balance = $amount + $_Agent->credit_balance;
                    $Agent->save();
                }
                else {
                    $this->status = 400;
                    $this->message = 'Insufficient credit balance';
                    return response()->json(array('status' => $this->status, 'message' => $this->message));
                }
            }
            else {
                $this->status = 400;
                $this->message = 'Contact the admin to fund your wallet';
                return response()->json(array('status' => $this->status, 'message' => $this->message));
            }
            if ($this->Credit) {
                $this->status = 200;
                $this->message = 'Credit balance updated successfully';
                return $this->agents($uid, $this->message);
                // return response()->json(array('status' => $this->status, 'message' => $this->message));
            } else {
                $this->status = 401;
                $this->message = 'failed to update credit status';
                return response()->json(array('status' => $this->status, 'message' => $this->message));
            }
        }
        catch (\ErrorException $exception) {
            $this->status = 400;
            $this->message = 'failed';
            return response()->json(array('status' => $this->status, 'message' => $exception->getMessage()));
        }
    }

    public function agents($mid, $message) {
        try {
            $Agents = Agent::where('merchants_id', '=', $mid)->get();
            $this->status = 200;
            return response()->json(array('message' => $message, 'status' => $this->status, 'Agents' => $Agents));
        }
        catch (\ErrorException $exception) {
            $this->status_code = 400;
            $this->message = 'failed to fetch';
            return response()->json(array('message' => $this->message, 'status' => $this->status));
        }
    }


}
