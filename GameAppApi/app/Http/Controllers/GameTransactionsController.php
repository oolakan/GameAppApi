<?php

namespace App\Http\Controllers;

use App\GameTransaction;
use Illuminate\Http\Request;

class GameTransactionsController extends Controller
{

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */

    private $status;
    private $message;
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
            $Transactions       = GameTransaction::with(['game_name', 'game_type', 'game_type_option'])
                ->where('users_id', '=', $id)
                ->whereBetween('date_played', array($from, $to))->get();

            $TotalAmount       = GameTransaction::with(['game_name', 'game_type', 'game_type_option'])
                ->where('users_id', '=', $id)
                ->whereBetween('date_played', array($from, $to))->sum('total_amount');

            return response()->json(['Transactions' => $Transactions, 'id'=>$id, 'from'=>$from, 'to'=> $to, 'total_amount' => number_format($TotalAmount, '2', '.', ',')]);
        }catch (\ErrorException $ex){
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
    public function destroy($id)
    {
        //
    }
}
