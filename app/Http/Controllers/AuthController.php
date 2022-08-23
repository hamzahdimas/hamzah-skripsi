<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Hash;
use App\Models\Auth;
use Session;

class AuthController extends Controller
{
    public function index()
    {
        return view('login');
    }

    public function doLogin(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');

        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];

        $isValid = Validator::make($request->all(),$rules);

        if($isValid->fails()){
            return redirect()->back()->withErrors($isValid->errors());
        }else{
            $check = Auth::where('username',$username);
            if($check->count() > 0){
                $data = $check->first();
                if($data){
                    if(Hash::check($password,$data->password)){
                        $session =[
                            'id_user' => $data->id_user,
                            'username' => $data->username,
                            'is_logged' => true
                        ];
                        session($session);
                        return redirect('/dashboard');
                    }else{
                        return redirect()->back()->with('error','Password yang anda masukkan salah!');
                    }
                }else{
                    return redirect()->back()->with('error','User Error!');
                }
            }else{
                return redirect()->back()->with('error','User tidak ditemukan!');
            }
        }
    }

    public function createUser(Request $request)
    {
        $data = [
            'username' => 'admin',
            'password' => Hash::make('hello123#'),
            'no_hp' => '089333888999'
        ];
        $insert = Auth::insert($data);
        if($insert){
            echo 'Sukses';
        }else{
            echo 'Gagal';
        }
    }

    public function doLogout()
    {
        Session::put('is_logged',false);
        Session::save();
        return redirect('/');
    }
}
