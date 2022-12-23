<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginFormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;#


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

        //①アカウントがロックされていたら弾く。
        $user=User::where('email','=',$credentials['email'])->first();
        //送信されたemailを持つuserが存在した場合
        if(!is_null($user)){
            //userがアカウントロックされていた場合
            if($user->locked_flg===1){
                return back()->withErrors([
                    "danger"=>"アカウントがロックされています"
                ]);
            }
            //送信されたemailを持つuserが存在し、かつuserがアカウントロックされてない場合
            //ログインが成功していたら
            if(Auth::attempt($credentials)){
                //sessionを再生成。
                $request->session()->regenerate();
                // ②成功したらエラーカウントを0に戻す。
                if($user->error_count > 0){
                    $user->error_count=0;
                    $user->save();
                }
                return redirect()->route('home')->with("success","ログイン成功しました");
            }
            //③ログイン失敗したらエラーカウントを1増やす。
            $user->error_count=$user->error_count+1;
            //④エラーカウントが6以上の場合はアカウントをロックする。
            if($user->error_count>5){
                $user->locked_flg=1;
                $user->save();

                return back()->withErrors([
                    "danger"=>"アカウントがロックされました。解除したい場合は運営者に連絡してください。"
                ]);
            }

            $user->save();
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