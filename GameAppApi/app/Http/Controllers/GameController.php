<?php

namespace App\Http\Controllers;

use App\Day;
use App\Game;
use App\GameName;
use App\GameQuater;
use App\GameTransaction;
use App\GameType;
use App\GameTypeOption;
use App\Winning;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class GameController extends Controller
{

    private $message;
    private $status;
    private $WON = 'WON';
    private $LOOSE = 'LOOSE';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        try {
            $today = date('l');
            $dayOfWeek      =   Day::where('name', '=', $today)->first();
            $dayOfWeekId    =   $dayOfWeek->id;
           // $GameNames    =   GameName::where('days_id', '=', $dayOfWeekId)->get();
            $GameNames      =   GameName::with(['day', 'quater'])->get();
            $GameTypes = GameType::all();
            $GameTypeOptions = GameTypeOption::all();
            $GameQuaters = GameQuater::all();
            return response()->json(['GameNames' => $GameNames, 'GameTypes' => $GameTypes,
                'GameTypeOptions' => $GameTypeOptions,
                'GameQuaters' => $GameQuaters]);
        } catch (\ErrorException $ex){
            response()->json(['mesage' => $ex->getMessage()]);
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

    public function validateGame($serial_no) {
        try{

            $Transaction    =   GameTransaction::where('serial_no', '=', $serial_no)->first();

            $gameNameId     =   $Transaction->game_names_id;
            $datePlayed     =   $Transaction->date_played;
            $gameNoPlayed   =   $Transaction->game_no_played;
            $gameNoArr = explode(',', $gameNoPlayed);

            // get winning number
            $Winning = Winning::where('game_names_id', '=', $gameNameId)
                ->where('winning_date', '=', $datePlayed)
                ->first();
            // explode winning number
            $winningNumber = $Winning->winning_no;
            $winningNoArr = explode(',', $winningNumber);

            // get match numbers
            $result         =   array_intersect($winningNoArr, $gameNoArr);
            $match_no_count = count($result);
            $Transaction = GameTransaction::with(['game_name', 'game_type', 'game_type_option'])->find($Transaction->id);
            $unitStake   = $Transaction->unit_stake;
            $Transaction->no_of_matched_figures = $match_no_count;
            $Transaction->winning_amount = $this->winningAmount($Transaction->game_types_id, $Transaction->game_type_options_id, $match_no_count, $unitStake);
            if ($match_no_count < 1) {
                $this->message = 'Game status: '.$this->LOOSE;
                $Transaction->status = $this->LOOSE;
            }
            else {
                $this->message = 'Game status: '.$this->WON;
                $Transaction->status = $this->WON;
            }
            $Transaction->save();
            if ($Transaction) {
                $this->status = 200;
                $this->message = 'success';
                return response(array('status' => $this->status, 'message' => $this->message, 'Transactions' => $Transaction));
            }
            else{
                $this->status = 401;
                $this->message = 'faile';
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
                $amount = (240 * $unitStake) * ($noOfMatchedFigures) * ($noOfMatchedFigures - 1) * ($noOfMatchedFigures -2)/6;
                return $amount;
            }// PERM 3
            else if ($oid == 3) {
                $amount = (240 * $unitStake) * ($noOfMatchedFigures) * ($noOfMatchedFigures - 1) * ($noOfMatchedFigures - 2) * ($noOfMatchedFigures - 3)/24;
                return $amount;
            }// PERM 4
            else if ($oid == 4){
                $amount = (240 * $unitStake) * ($noOfMatchedFigures) * ($noOfMatchedFigures - 1) * ($noOfMatchedFigures - 2) * ($noOfMatchedFigures - 3) * ($noOfMatchedFigures - 4)/120;
                return $amount;
            }// PERM 5
        }// PERM
        else if ($tid == 2) {
            if ($oid == 5){
                $amount = 1 * $unitStake * $noOfMatchedFigures * 240;
                return $amount;
            }//AGAINST 1
            else if ($oid == 6){
                $amount = 2 * $unitStake * $noOfMatchedFigures * 240;
                return $amount;
            }// AGAINST 2
            else if ($oid == 7) {
                $amount = 3 * $unitStake * $noOfMatchedFigures * 240;
                return $amount;
            }// AGAINST 3
            else if ($oid == 8){
                $amount = 4 * $unitStake * $noOfMatchedFigures * 240;
                return $amount;
            }// AGAISNT 4
            else if ($oid == 9){
                $amount = 5 * $unitStake * $noOfMatchedFigures * 240;
                return $amount;
            }// AGAINST 5
        }// AGAIANST
        else if ($tid == 3) {
            if ($oid == 10){
                $amount = (240 * $unitStake * $noOfMatchedFigures) / $noOfMatchedFigures;
                return $amount;
            }//DIRECT 2
            else if ($oid == 11){
                $amount = (2100 * $unitStake * $noOfMatchedFigures) / $noOfMatchedFigures;
                return $amount;
            }// DIRECT 3
            else if ($oid == 12) {
                $amount = (2100 * $unitStake * $noOfMatchedFigures) / $noOfMatchedFigures;
                return $amount;
            }// DIRECT 4
            else if ($oid == 13){
                $amount = (2100 * $unitStake * $noOfMatchedFigures) / $noOfMatchedFigures;
                return $amount;
            }// DIRECT 5
        }//DIRECT
        return $amount;
    }

}
