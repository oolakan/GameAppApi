<?php

namespace App\Http\Controllers;

use App\Agent;
use App\Credit;
use App\GameTransaction;
use DateTime;
use Illuminate\Http\Request;

class GameTransactionsController extends Controller {

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */

    private $status;
    private $message;
    private $Transactions;
    private $from = '0000-00-00';

    /**
     * GameTransactionsController constructor.
     * @param $status
     */

    public function __construct()
    {
        date_default_timezone_set('Africa/Lagos'); //set choice timezone
    }


    public function index()
    {
        try {
            $Transactions       = GameTransaction::with(['game_name', 'game_type', 'game_type_option'])->get();
            return response()->json(['Transactions' => $Transactions]);
        }catch (\ErrorException $ex){
            response()->json(['message' => $ex->getMessage()]);
        }
    }

    public function transactions($id, $from, $to)
    {
        try {
            $Transactions = GameTransaction::with(['game_name', 'game_type', 'game_type_option'])
                ->where('users_id', '=', $id)
                ->whereBetween('date_played', array($from, $to))->get();
            $TotalAmount = GameTransaction::with(['game_name', 'game_type', 'game_type_option'])
                ->where('users_id', '=', $id)
                ->whereBetween('date_played', array($from, $to))->sum('total_amount');
            return response()->json(['Transactions' => $Transactions, 'id' => $id, 'from' => $from, 'to' => $to, 'total_amount' => number_format($TotalAmount, '2', '.', ',')]);
        } catch (\ErrorException $ex) {
            response()->json(['message' => $ex->getMessage()]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        date_default_timezone_set('Africa/Lagos'); //set choice timezone
        $transactions = $request->json()->all();
        try {
            foreach ($transactions as $transaction) {
                $Transaction = new GameTransaction();
                $Transaction->create($transaction);
                if ($Transaction) {
                    $this->status = 200;
                    $this->message = 'success';
                } else {
                    $this->status = 400;
                    $this->message = 'failed';
                }
            }
            return response()->json(array('status' => $this->status, 'message' => $this->message));
        }catch (\Exception $exception) {
            $this->status = 401;
            $this->message = $exception->getMessage();
            return response()->json(array('status' => $this->status, 'message' => $this->message));
        }
    }

    /**
     * Display the specified resource.
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($serial_no)
    {
        date_default_timezone_set('Africa/Lagos'); //set choice timezone
        $date = new DateTime;
        $date->modify('-5 minutes');
        $formatted_date = $date->format('Y-m-d H:i:s');
        try {
            $Transaction = GameTransaction::where('serial_no', '=', $serial_no)->where('created_at', '>=', $formatted_date)->first();
            if ($Transaction) {
                $amount                 =   $Transaction->total_amount;
                $user_id                =   $Transaction->users_id;
                //refund the agent wallet
                $_Credit                =   Credit::where('users_id', '=', $user_id)->first();
                $Agent                  =   Agent::where('users_id', '=', $user_id)->first();
                $merchantId             =   $Agent->merchants_id;
                $user_credit_id         =   $_Credit->id;
                $Credit                 =   Credit::find($user_credit_id);
                $Credit->amount         =   $amount + $_Credit->amount;
                $Credit->funded_by      =   $merchantId;
                $Credit->users_id       =   $user_id;
                $Credit->merchants_id   =   $merchantId;
                $Credit->save();
                $this->status           =   200;
                $Game = GameTransaction::find($Transaction->id)->delete();
                if ($Game) {
                    return response()->json(array('status' => $this->status, 'message' => 'successful'));
                }
                else {
                    $this->status           = 201;
                    return response()->json(array('status' => $this->status, 'message' => 'Game does not exist'));
                }
            } else {
                $this->status           = 201;
                return response()->json(array('status' => $this->status, 'message' => 'Game does not exist or time elapsed'));
            }
        }
        catch (\ErrorException $ex) {
            $this->status = 400;
            return response()->json(array('status' => $this->status, 'message' => $ex->getMessage()));
        }
    }
}