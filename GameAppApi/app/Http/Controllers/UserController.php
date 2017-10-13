<?php

namespace App\Http\Controllers;

use App\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $APPROVED   =   'APPROVED';
    public function index()
    {
        try {
            $Users = User::with('role')->get();
            return response()->json(['Users' => $Users]);
        }catch (\ErrorException $ex){
            return response()->json(['ErrorMessage'=>$ex->getMessage()]);
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        try{
            $Users = User::with('role')->get();
            $Admins = User::with('role')->where('roles_id', '=', 1)->get();
            $Merchants = User::with('role')->where('roles_id', '=', 2)->get();
            $Agents = User::with('role')->where('roles_id', '=', 3)->get();
            $Roles      =   Role::all();
            $Games = Game::all();
            $Winnings = Winning::all();
            return view('user.create', compact(['Admins', 'Merchants', 'Agents', 'Games', 'Winnings', 'Users', 'Roles']));
        }
        catch(\ErrorException $ex){
            $ex->getMessage();
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{
            /**
             * Define rules
             */
            $rules = [
                'name' => 'required',
                'email'     => 'required|email|max:255|unique:users',
                'phone' => 'required',
                'password' => 'required|min:6|confirmed',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return back()
                    ->withInput()
                    ->withErrors($validator);
            }
            $User                   =   new User();
            $api_token              =   $this->randomKey(60);
            $User->name             =   $request->name;
            $User->email            =   $request->email;
            $User->phone            =   $request->phone;
            $User->api_token        =   $api_token;
            $User->roles_id         =   $request->roles_id;
            $User->approval_status  =   $this->APPROVED;
            $User->location         =   $request->location;
            $User->password         =   bcrypt($request->password);
            $User->save();
            if($User){
                flash()->success($request->name.' Added successfully');
                return redirect()->action('UserController@index');
            }
        }
        catch(\ErrorException$ex){
            $ex->getMessage();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try{
            $User       =   User::find($id);
            return $User;
        }
        catch(\ErrorException $ex){
            $ex->getMessage();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
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
        try{
            $User       =   User::find(base64_decode($id));
            if($User){
                $User->update($request->all());
                flash()->success('User info updated successfully');
                return redirect()->action('UserController@index');
            }
        }
        catch(\ErrorException $ex){
            $ex->getMessage();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            $User       =   User::find(base64_decode($id));
            $User->delete();
            flash()->success('User info deleted successfully');
            return redirect()->action('UserController@index');
        }
        catch(\ErrorException $ex){
            $ex->getMessage();
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

}
