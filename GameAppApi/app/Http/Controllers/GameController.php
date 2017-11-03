<?php

namespace App\Http\Controllers;

use App\Game;
use App\GameName;
use App\GameQuater;
use App\GameType;
use App\GameTypeOption;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class GameController extends Controller
{

    private $message;
    private $status;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        try {
            $GameNames = GameName::all();
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

}
