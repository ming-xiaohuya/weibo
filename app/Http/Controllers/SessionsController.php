<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class SessionsController extends Controller
{
    // 添加登录控制器
    public function create(){
        return view('sessions.create');
    }
    public function store(Request $request) {
        $credentials = $this->validate($request, [
            'email' => 'required|email|max:255',
            'password' => 'required'
        ]);
        if (Auth::attempt($credentials, $request->has('remember'))) {

            // 判断用户是否已激活
            if(Auth::user()->activated) {
                // 登录成功
                session()->flash('success', '欢迎回来！');
                $fallback = route('users.show', Auth::user());

                // intended 方法，该方法可将页面重定向到上一次请求尝试访问的页面上，
                // 并接收一个默认跳转地址参数，当上一次请求记录为空时，跳转到默认地址上。
                return redirect()->intended($fallback);
            } else {
                Auth::logout();
                session()->flash('warning', '你的账号未激活，请检查邮箱中的注册邮件进行激活。');
                return redirect('/');
            }

        } else {
            // 登录失败
            session()->flash('danger', '很抱歉，您的邮箱和密码不匹配');
            return redirect()->back()->withInput();
        }
    }

    // 添加退出控制器
    public function destroy(){
        Auth::logout();
        session()->flash('success', '您已成功退出！');
        return redirect('login');
    }

    // 实现只让未登录的用户访问登录页面和注册页面
    public function __construct() {
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }
}
