<?php

namespace App\Http\Controllers;
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

    public function index()
    {
        try {
            $Users = User::with(['role', 'credit'])->get();
            $Admins = User::with('role')->where('roles_id', '=', 1)->get();
            $Merchants = User::with('role')->where('roles_id', '=', 2)->get();
            $Agents = User::with('role')->where('roles_id', '=', 3)->get();
            $Games = Game::all();
            $Winnings = Winning::all();
            return view('credit.index', compact(['Admins', 'Merchants', 'Agents', 'Games', 'Winnings', 'Users']));
        }catch (\ErrorException $ex){
            $ex->getMessage();
        }
    }
    /**
     * Store a newly created resource in storage.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeOrUpdate(Request $request)
    {
        try{
            $rules = [
                'amount' => 'required',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return back()
                    ->withInput()
                    ->withErrors($validator);
            }
            $Credit                 =   new Credit();
            $Credit->updateOrCreate(['users_id' => $request->users_id],
                [   'amount' => $request->amount,
                    'funded_by' => Auth::user()->id,
                    'users_id' => $request->users_id
                ]);
            if($Credit){
                flash()->success('Credit balance updated successfully');
                return redirect()->action('CreditController@index');
            }
        }
        catch(\ErrorException$ex){
            $ex->getMessage();
        }
    }
}
