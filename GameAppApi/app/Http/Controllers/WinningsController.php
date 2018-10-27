<?php

namespace App\Http\Controllers;

use App\GameTransaction;
use App\Winning;
use Illuminate\Http\Request;

class WinningsController extends Controller
{
    private $WON = 'WON';
    private $Transactions;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id, $from, $to, $status)
    {
        try {
            if ($status == 1) {
                $this->Transactions = GameTransaction::with(['game_name', 'game_type', 'game_type_option'])
                    ->where('users_id', '=', $id)
                    ->whereBetween('date_played', array($from, $to))
                    ->where('status', '=', $this->WON)->get();
                return response()->json(['Transactions' => $this->Transactions]);
            }
            else {
                $this->Transactions = GameTransaction::with(['game_name', 'game_type', 'game_type_option'])
                    ->where('users_id', '=', $id)
                    ->whereBetween('date_played', array($from, $to))->get();
                return response()->json(['Transactions' => $this->Transactions]);
            }
        }catch (\ErrorException $ex){
            response()->json(['message' => $ex->getMessage()]);
        }
    }

    /**
     * @param $from
     * @param $to
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * get winning and machine numbers
     */
    public function winningMachineNos($from, $to)
    {
        try {
            $Winnings = Winning::with(['game_name', 'game_type', 'game_type_option'])
                ->whereBetween('winning_date', array($from, $to))
                ->get();
            return response()->json(['Winnings' => $Winnings]);
        }
        catch (\ErrorException $ex){
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
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
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
