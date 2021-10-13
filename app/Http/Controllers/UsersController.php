<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UsersController extends Controller
{
    public function create(){
        return view('users.create');
    }
    public function show(User $user){
        return view('users.show',compact('user'));
    }
    public function store(Request $request) {

        // 使用 required 来验证用户输入的值是否为空
        // unique:users 要验证用户使用的用户名是否已被它人使用，这时我们可以使用唯一性验证，这里是针对于数据表 users 做验证。
        // min:3|max:50 使用 min 和 max 来限制用户名所填写的最小长度和最大长度。
        // email 格式
        // confirmed 验证用户输入的两次密码是否一致
        $this->validate($request, [
            'name' => 'required|unique:users|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        @Auth::login($user);    // 实现用户注册完成后自动登录
        session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
        return redirect()->route('users.show', [$user]);
    }
}
