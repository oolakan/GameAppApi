<?php

namespace App\Http\Controllers;

use App\Agent;
use App\Day;
use App\Game;
use App\GameName;
use App\GameQuater;
use App\GameTransaction;
use App\GameType;
use App\GameTypeOption;
use App\User;
use App\Winning;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class GameController extends Controller
{

    private $message;
    private $status;
    private $WON = 'WON';
    private $LOOSE = 'LOOSE';
    private $result;
    private $match_no_count;
    private $winning_amount;
    private $Transaction;
    private $WINNING_GAME = 'WINNING GAME';
    private $MACHINE_GAME = 'MACHINE GAME';

    private $start_time = ' 00:00:00';
    private $stop_time = ' 23:59:59';

    /**
     * GameController constructor.
     * @param $message
     */
    public function __construct()
    {
        date_default_timezone_set('Africa/Lagos'); //set choice timezone
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function index()
    {
        try {
            $today = date('l');
            $dayOfWeek = Day::where('name', '=', $today)->first();
            $dayOfWeekId = $dayOfWeek->id;
            // $GameNames    =   GameName::where('days_id', '=', $dayOfWeekId)->get();
            $GameNames = GameName::with(['day', 'quater'])->where('game_status', '=', '0')->get();
            $GameTypes = GameType::all();
            $GameTypeOptions = GameTypeOption::all();
            $GameQuaters = GameQuater::all();
            return response()->json(['GameNames' => $GameNames, 'GameTypes' => $GameTypes,
                'GameTypeOptions' => $GameTypeOptions,
                'GameQuaters' => $GameQuaters]);
        } catch (\ErrorException $ex) {
            response()->json(['mesage' => $ex->getMessage()]);
        }
    }
    /**
     * @param $day
     * @param $date
     * @param $userid
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * Get game sales transactions
     */
    public function getGamesStatistics($day, $date, $userid)
    {
        $User   =   Agent::where('users_id', '=', $userid)->first();
        $merchantId = $User->merchants_id;
        $User   =   User::find($merchantId);
        if ($User) {
            if ($User->approval_status != 'APPROVED') {
                return response()->json(array('status' => 400, 'data' => 'Your account has been blocked'), 400);

            } else {
                $dayOfWeek = Day::where('name', '=', $day)->first();
                $dayOfWeekId = $dayOfWeek->id;
                $GamesOfDay = GameName::where('days_id', '=', $dayOfWeekId)->get();
                $main_array = array(); //Your array that you want to push the value into
                $total = 0;
                $winning = 0;
                array_push($main_array, array('name' => 'Name', 'TotalAmount' => 'Sales',
                    'WinningAmount' => 'Winnings'));
                for ($i = 0; $i < count($GamesOfDay); $i++) {
                    $TotalAmount = GameTransaction::with(['game_name'])
                        ->where('users_id', '=', $userid)
                        ->where('game_names_id', '=', $GamesOfDay[$i]->id)
                        ->where('date_played', '=', $date)->sum('total_amount');

                    $WinningAmount = GameTransaction::with(['game_name'])
                        ->where('users_id', '=', $userid)
                        ->where('game_names_id', '=', $GamesOfDay[$i]->id)
                        ->where('date_played', '=', $date)->sum('winning_amount');

                    // array_push($main_array, array($GamesOfDay[$i]->name => number_format($TotalAmount, 2, '.', ',')));

                    array_push($main_array, array('name' => $GamesOfDay[$i]->name, 'TotalAmount' => number_format($TotalAmount, 2, '.', ','),
                        'WinningAmount' => $WinningAmount));

                    $total += $TotalAmount;
                    $winning += $WinningAmount;
                }
                //return response()->json($total);
                array_push($main_array, array('name' => 'Total', 'TotalAmount' => number_format($total, 2, '.', ','),
                    'WinningAmount' => number_format($winning, 2, '.', ',')));
                return response()->json(array('status' => 200, 'data' => $main_array));
            }
        }
        else {
            return response()->json(array('status' => 400, 'message' => 'Your account has been blocked'), 400);
        }
    }


    /**
     * @param $day
     * @param $date
     * @param $userid
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * Get game sales transactions
     */
    public function getAgentsGamesStatistics($from, $to, $mid)
    {
        $User   =   User::find($mid);
        if ($User) {
            if ($User->approval_status != 'APPROVED') {
                return response()->json(array('status' => 400, 'data' => 'Your account has been blocked'), 400);

            } else {
                $main_array = array(); //Your array that you want to push the value into
                $total = 0;
                $winning = 0;
                $Agents = Agent::with(['user'])->where('agents.merchants_id', '=', $mid)
                    ->where('delete_status', '=', 0)
                    ->get();

                array_push($main_array, array('name' => 'Name', 'TotalAmount' => 'Sales',
                    'WinningAmount' => 'Winnings'));
                for ($i = 0; $i < count($Agents); $i++) {
                    $TotalAmount = GameTransaction::with(['game_name'])
                        ->where('users_id', '=', $Agents[$i]->user->id)
                        ->whereBetween('date_played', array($from, $to))
                        ->sum('total_amount');

                    $WinningAmount = GameTransaction::with(['game_name'])
                        ->where('users_id', '=', $Agents[$i]->user->id)
                        ->whereBetween('date_played', array($from, $to))
                        ->sum('winning_amount');

                    array_push($main_array, array('name' => $Agents[$i]->agent_name, 'TotalAmount' => number_format($TotalAmount, 2, '.', ','),
                        'WinningAmount' => number_format($WinningAmount, 2, '.', ',')));

                    $total += $TotalAmount;
                    $winning += $WinningAmount;
                }
                array_push($main_array, array('name' => 'Total', 'TotalAmount' => number_format($total, 2, '.', ','),
                    'WinningAmount' => number_format($winning, 2, '.', ',')));

                return response()->json(array('status' => 200, 'message' => 'successful', 'data' => $main_array));
            }
        }
        else {
            return response()->json(array('status' => 400, 'message' => 'Your account has been blocked'), 400);
        }
    }


    /**
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     *
     */
    public function allGames($uid)
    {
        try {
            $User   =   User::find($uid);
            if ($User) {
                if ($User->approval_status != 'APPROVED') {
                    return response()->json(array('status' => 400, 'data' => 'Your account has been blocked'), 400);

                } else {
                    $today = date('l');
                    $dayOfWeek = Day::where('name', '=', $today)->first();
                    $dayOfWeekId = $dayOfWeek->id;
                    // $GameNames    =   GameName::where('days_id', '=', $dayOfWeekId)->get();
                    $GameNames = GameName::with(['day', 'quater'])->get();
                    $GameTypes = GameType::all();
                    $GameTypeOptions = GameTypeOption::all();
                    $GameQuaters = GameQuater::all();
                    return response()->json(['GameNames' => $GameNames, 'GameTypes' => $GameTypes,
                        'GameTypeOptions' => $GameTypeOptions,
                        'GameQuaters' => $GameQuaters]);
                }
            }
            else {
                return response()->json(array('status' => 400, 'data' => 'Account not found'), 400);

            }
        } catch (\ErrorException $ex){
            response()->json(['mesage' => $ex->getMessage()]);
        }
    }

    /**
     * block game
     * @param $id
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function block($status, $id, $uid)
    {
        try {
            $User   =   User::find($uid);
            if ($User) {
                if ($User->approval_status != 'APPROVED') {
                    return response()->json(array('status' => 400, 'data' => 'Your account has been blocked'), 400);

                } else {
                    $GameNames = GameName::find($id);
                    $GameNames->game_status = $status;
                    $GameNames->save();
                    if ($GameNames)
                        return response()->json(['status' => 200, 'message' => 'successful']);
                    else
                        return response()->json(['status' => 400, 'message' => 'failed']);
                }
            }
            else{
                return response()->json(array('status' => 400, 'data' => 'Record not found'), 400);
            }
        } catch (\ErrorException $ex){
            response()->json(['message' => $ex->getMessage()]);
        }
    }

    public function gameInfo(Request $request) {
        try{
            $rules = [
                'game_names_id' => 'required',
                'game_types_id' => 'required',
                'game_type_options_id' => 'required',
                'game_quaters_id' => 'required',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
               return response()->json(['message' => 'Input Error']);
            }
            $GameInfo   =   Game::where('game_names_id', '=', $request->game_names_id)
                ->where('game_types_id', '=', $request->game_types_id)
                ->where('game_type_options_id', '=', $request->game_type_options_id)
                ->where('game_quaters_id', '=', $request->game_quaters_id)
                ->get();
            if($GameInfo) {
                $this->message = 'Successful';
                $this->status = 200;
                return response()->json(['GameInfo' => $GameInfo, 'status' => $this->status, 'message' => $this->message]);
            }
            else{
                $this->message = 'No data';
                $this->status = 201;
                return response()->json(['GameInfo' => $GameInfo, 'status' => $this->status, 'message' => $this->message]);
            }
        }
        catch(\ErrorException $ex){
            $this->message = $ex->getMessage();
            $this->status = 201;
            return response()->json(['GameInfo' => [], 'status' => $this->status, 'message' => $this->message]);
        }

    }
    public function checkGameAvailability(Request $request ) {
        $Game   =   Game::where('game_names_id', '=', $request->game_names_id)
                            ->where('game_quaters_id', '=', $request->game_quaters_id)
                            ->first();
        if ($Game) {
            if ($Game->game_status == 0) {
                $this->message = 'Game is opened';
                $this->status  = 200;
                return response()->json(array('message' => $this->message, 'status' => $this->status));
            }
            else{
                $this->message = 'Game is closed';
                $this->status  = 202;
                return response()->json(array('message' => $this->message, 'status' => $this->status));
            }
        }
        else{
            $this->message = 'This game is not available now, contact the administrator';
            $this->status  = 203;
            return response()->json(array('message' => $this->message, 'status' => $this->status));
        }
    }


    public function searchGame($serial_no) {
        $Transactions    =   GameTransaction::with(['game_name', 'game_type', 'game_type_option'])->where('serial_no', '=', $serial_no)->get();
        if ($Transactions) {
            $this->status = 200;
            $this->message = 'success';
            return response(array('status' => $this->status, 'message' => $this->message, 'Transactions' => $Transactions));
        } else {
            $this->status = 401;
            $this->message = 'failed';
            return response(array('status' => $this->status, 'message' => $this->message));
        }
    }

    public function validateGame($serial_no) {
        try{
            $Transactions    =   GameTransaction::where('serial_no', '=', $serial_no)->paginate(500);

            foreach ($Transactions as $transaction) {
                $gameNameId = $transaction->game_names_id;
                $datePlayed = $transaction->date_played;
                $gameNoPlayed = $transaction->game_no_played;
                $gameNoArr = explode(',', $gameNoPlayed);

                $bankerNo = $transaction->banker_no;
                $bankerNoArr = [];
                if (strpos( $bankerNo, ",") !== false) {
                    $bankerNoArr = explode(',', $bankerNo);
                } else {
                    array_push($bankerNoArr, $bankerNo);
                }
                // get winning number
                $Winning = Winning::where('game_names_id', '=', $gameNameId)
                    ->where('winning_date', '=', $datePlayed)
                    ->first();

                if (!$Winning) {
                    $this->status = 402;
                    $this->message = 'No winning game has been registered for this game';
                    return response(array('status' => $this->status, 'message' => $this->message));
                }
                //check game draw type,
                //if it is machine game or winning game
                //then validate draw based on game type
                $winningNumber  = $transaction->draw_type == $this->WINNING_GAME ? $Winning->winning_no : $Winning->machine_no;
                // explode winning number

                $winningNoArr = explode(',', $winningNumber);
                // get match numbers
                $this->Transaction = GameTransaction::with(['game_name', 'game_type', 'game_type_option'])->find($transaction->id);
                $gameOptionId = $this->Transaction->game_type_options_id;
                $gameTypeId = $this->Transaction->game_types_id;
                $unitStake = $this->Transaction->unit_stake;
                //CHECK IF GAME TYPE IS AGAINST
                // check if banker number is in winning number
             //   var_dump($bankerNoArr);
                if ($gameTypeId == '2') {
                    if ($this->isBankerInWinningNo($winningNoArr, $bankerNoArr, $gameNoArr, $gameOptionId)) {
                        $this->result = array_intersect($winningNoArr, $gameNoArr);
                        //check if there is at least one number in winning number
                        $this->match_no_count = count($gameNoArr); /**count($this->result) * count($bankerNoArr);**/
                        $this->Transaction->no_of_matched_figures = $this->match_no_count;
                        if ($this->match_no_count >=1) {
                            $this->winning_amount = $this->winningAmount($this->Transaction->game_types_id, $this->Transaction->game_type_options_id, $this->match_no_count, $unitStake);
                        }
                        else{
                            $this->winning_amount = 0;
                        }
                    } else {
                        $this->winning_amount = 0;
                    }
                }

                else {
                    $this->result = array_intersect($winningNoArr, $gameNoArr);
                    $this->match_no_count = count($this->result);
                    $this->Transaction->no_of_matched_figures = $this->match_no_count;
                    $this->winning_amount = $this->winningAmount($this->Transaction->game_types_id, $this->Transaction->game_type_options_id, $this->match_no_count, $unitStake);
                }
                $this->Transaction->winning_amount = $this->winning_amount;
                if ($this->winning_amount < 1) {
                    $this->message = 'Game status: ' . $this->LOOSE;
                    $this->Transaction->status = $this->LOOSE;
                } else {
                    $this->message = 'Game status: ' . $this->WON;
                    $this->Transaction->status = $this->WON;
                }
                $this->Transaction->save();
            }
            if ($this->Transaction) {
                $Transactions    =   GameTransaction::with(['game_name', 'game_type', 'game_type_option'])->where('serial_no', '=', $serial_no)->get();
                $this->status = 200;
                $this->message = 'success';
                return response(array('status' => $this->status, 'message' => $this->message, 'Transactions' => $Transactions));
            } else {
                $this->status = 401;
                $this->message = 'failed';
                return response(array('status' => $this->status, 'message' => $this->message));
            }
        }
        catch (\ErrorException $e) {
            $this->status = 400;
            $this->message = 'failed';
            return response(array('status' => $this->status, 'message' => $this->message));
        }
    }
    public function winningAmount($tid, $oid, $noOfMatchedFigures, $unitStake) {
        $amount = 0.0;

        if ($tid == 1) {
            if ($oid == 1){
                $amount = (($noOfMatchedFigures * ($noOfMatchedFigures - 1)) / 2) * (240 * $unitStake);
                return $amount;
            }//PERM 2
            else if ($oid == 2){
                $amount = (2100 * $unitStake) * ($noOfMatchedFigures) * ($noOfMatchedFigures - 1) * ($noOfMatchedFigures -2)/6;
                return $amount;
            }// PERM 3
            else if ($oid == 3) {
                $amount = (5000 * $unitStake) * ($noOfMatchedFigures) * ($noOfMatchedFigures - 1) * ($noOfMatchedFigures - 2) * ($noOfMatchedFigures - 3)/24;
                return $amount;
            }// PERM 4
            else if ($oid == 4){
                $amount = (40000 * $unitStake) * ($noOfMatchedFigures) * ($noOfMatchedFigures - 1) * ($noOfMatchedFigures - 2) * ($noOfMatchedFigures - 3) * ($noOfMatchedFigures - 4)/120;
                return $amount;
            }// PERM 5
        } // PERM

        else if ($tid == 2) {

            if ($oid == 5) {
                $amount =  $noOfMatchedFigures * $unitStake  * 240;
                return $amount;
            } //AGAINST 1

            else if ($oid == 6){
                $amount =  $noOfMatchedFigures * $unitStake  * 2100;
                return $amount;
            } // AGAINST 2

            else if ($oid == 7) {
                $amount =  $noOfMatchedFigures * $unitStake  * 5000;
                return $amount;
            } // AGAINST 3
            /**
             * One agsint all
             */
            else if ($oid == 8){
                $amount = 4 * 1 * $unitStake * 240;
                return $amount;
            } // 1 AGAISNT ALL

            else if ($oid == 9){
                $amount = 0;
                return $amount;
            } // AGAINST 5

        }// AGAIANST
        else if ($tid == 3) {
            if ($oid == 10) {
                if ($noOfMatchedFigures == 2 )
                    $amount = (240 * $unitStake * $noOfMatchedFigures) / $noOfMatchedFigures;
                else
                    $amount = 0;
                return $amount;
            }//DIRECT 2

            else if ($oid == 11) {
                if ($noOfMatchedFigures == 3 )
                    $amount = (2100 * $unitStake * $noOfMatchedFigures) / $noOfMatchedFigures;
                else
                    $amount = 0;
                return $amount;
            }// DIRECT 3

            else if ($oid == 12) {
                if ($noOfMatchedFigures == 4 )
                    $amount = (5000 * $unitStake * $noOfMatchedFigures) / $noOfMatchedFigures;
                else
                    $amount = 0;
                return $amount;
            }// DIRECT 4

            else if ($oid == 13){
                if ($noOfMatchedFigures == 5 )
                    $amount = (40000 * $unitStake * $noOfMatchedFigures) / $noOfMatchedFigures;
                else
                    $amount = 0;
                return $amount;
            }// DIRECT 5
        }//DIRECT

        return $amount;
    }
    public function isBankerInWinningNo($winningNo, $bankerNo, $gameNoArr, $oid) {
        $result         =   array_intersect($winningNo, $bankerNo); //check if banker no exists
        $match_no_count =   count($result); //count banker numbers
        $gameNoresult   =   array_intersect($winningNo, $gameNoArr); //check if game no selected exists
        $gameNoresultCount = count($gameNoresult); //count game numbers
        //var_dump($match_no_count);
        if ($oid == 8) {
            if ($match_no_count >= 1)
                return true;
            else return false;
        }
        else {
            if ($gameNoresultCount > 0) {
                if ($oid == 5) {
                    if ($match_no_count == 1)
                        return true;
                }//AGAINST 1
                else if ($oid == 6) {
                    if ($match_no_count == 2)
                        return true;
                }// AGAINST 2
                else if ($oid == 7) {
                    if ($match_no_count == 3)
                        return true;
                }// AGAINST 3
            }
            else
                return false;
        }
        return false;
    }
}