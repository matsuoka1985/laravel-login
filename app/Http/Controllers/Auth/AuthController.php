<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginFormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    //
    /**
     * @return View
     */
    public function showLogin(){
        return view('login.login_form');
    }
    /**
     * @param App\Http\Requests\LoginFormRequest
     */
    public function login(LoginFormRequest $request){
        // dd($request->all());
        $credentials=$request->only("email","password");
        //ログインが成功していたら
        if(Auth::attempt($credentials)){
            //sessionを再生成。
            $request->session()->regenerate();

            return redirect()->route('home')->with("success","ログイン成功しました");
        }
        //ログインが失敗した場合は元の画面に戻ってもらう。
        //エラー内容と共に元の画面に返すことができる。
        //withErrorsの中身はsessionで返すことができる。
        return back()->withErrors([
            "danger"=>"メールアドレスかパスワードが間違っています。"
        ])
        ;
    }

    /**
     * ユーザーをアプリケーションからログアウトさせる
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route("login.show")->with("danger","ログアウトしました");
    }
}