<?php
namespace App\Http\Controllers;
use App\Agent;
use App\Credit;
use App\GameName;
use App\GameTransaction;
use App\User;
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
    private $APPROVED = 'APPROVED';
    private $Game;

    /**
     * GameTransactionsController constructor.
     * @param $status
     */

    public function __construct()
    {
        date_default_timezone_set('Africa/Lagos'); //set choice timezone
    }

    /**
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * get transactions
     */
    public function index()
    {
        try {
            $Transactions       = GameTransaction::with(['game_name', 'game_type', 'game_type_option'])->get();
            return response()->json(['Transactions' => $Transactions]);
        }catch (\ErrorException $ex){
            response()->json(['message' => $ex->getMessage()]);
        }
    }

    /**
     * @param $id
     * @param $from
     * @param $to
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * get game transactions
     */
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
        $usersid = $transactions[0]['users_id'];
        $Userinfo = User::find($usersid);
        $status = $Userinfo->approval_status;
        $timeNow = strtotime(date("H:i"));
        //get draw time
        $gameName = GameName::find($transactions[0]['game_names_id']);
        $drawTime = strtotime($gameName->draw_time);
        //get current date
        $version    =   $request->has('version') ? $request->query('version') : 'old';
        if ($version != '3') {
            return response()->json(array('status' => 400, 'message' => 'failed'), 400);
        }
        if ($status == 'APPROVED') {
            try {
                foreach ($transactions as $transaction) {
                    //get game date played
                    $transaction = (object)$transaction;
                    $timeHrPlayed = $transaction->time_played;
                    $transaction = (array)$transaction;
                    $timePlayed = strtotime($timeHrPlayed);
                    $drawPlayTime = round($timePlayed - $drawTime / 60,2);
                    $serverTimePlayed   =   round(abs($timeNow - $timePlayed) / 60, 2);
                    if ($serverTimePlayed <= 5 && $drawPlayTime <= 5) {
                        $Transaction = GameTransaction::create($transaction);
                        if ($Transaction) {
                            $this->status = 200;
                            $this->message = 'success';
                            $this->deductCredit($usersid, $transaction->total_amount);
                        } else {
                            $this->status = 400;
                            $this->message = 'failed';
                        }
                    } else {
                        $this->status = 400;
                        $this->message = 'failed';
                    }
                }
                return response()->json(array('status' => $this->status, 'message' => $this->message));
            } catch
            (\Exception $exception) {
                $this->status = 401;
                $this->message = $exception->getMessage();
                return response()->json(array('status' => $this->status, 'message' => $this->message));
            }
        }
        else {
            return response()->json(array('status' => 401, 'message' => 'failed'));
        }
    }
    /**
     * @param $uid
     * @param $credit
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * DEDUCT CREDIT FOR GAME PLAYED
     */
    public function deductCredit($id, $credit)
    {
        $Credit                 = Credit::where('users_id', '=', $id)->first();
        $creditBalance          = $Credit->amount - $credit;
        $NewCredit              = Credit::find($Credit->id);
        $NewCredit->amount      = $creditBalance;
        $NewCredit->save();

        $_Agent                 = Agent::where('users_id', '=', $id)->first();
        $Agent                  = Agent::find($_Agent->id);
        $Agent->credit_balance  = $_Agent->credit_balance - $credit;
        $Agent->save();
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
    public function destroy( $serial_no )
    {
        date_default_timezone_set('Africa/Lagos'); //set choice timezone
        $date = new DateTime;
        $date->modify('-10 minutes');
        $formatted_date = $date->format('Y-m-d H:i:s');
        try {
            $Transaction                    = GameTransaction::where('serial_no', '=', $serial_no)->where('created_at', '>=', $formatted_date)->get();
            if ($Transaction) {
                foreach ($Transaction as $transaction) {
                    $amount                 = $transaction->total_amount;
                    $user_id                = $transaction->users_id;
                    //refund the agent wallet
                    $_Credit                = Credit::where('users_id', '=', $user_id)->first();
                    $Agent                  = Agent::where('users_id', '=', $user_id)->first();
                    $merchantId             = $Agent->merchants_id;
                    $user_credit_id         = $_Credit->id;
                    $Credit                 = Credit::find($user_credit_id);
                    $Credit->amount         = $amount + $_Credit->amount;
                    $Credit->funded_by      = $merchantId;
                    $Credit->users_id       = $user_id;
                    $Credit->merchants_id   = $merchantId;
                    $Credit->save();
                    $this->status           = 200;
                    $this->Game             = GameTransaction::find($transaction->id)->delete();
                }
                if ($this->Game) {
                    return response()->json(array('status' => $this->status, 'message' => 'successful'));
                } else {
                    $this->status = 201;
                    return response()->json(array('status' => $this->status, 'message' => 'Game does not exist'));
                }
            }
            else {
                $this->status = 201;
                return response()->json(array('status' => $this->status, 'message' => 'Game does not exist or time elapsed'));
            }
        }
        catch (\ErrorException $ex) {
            $this->status = 400;
            return response()->json(array('status' => $this->status, 'message' => $ex->getMessage()));
        }
    }
}