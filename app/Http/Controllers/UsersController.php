<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;

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

    // 编辑用户操作
    public function edit(User $user){
        // 使用authorize方法来验证用户授权策略
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    // 更新用户信息
    public function update(User $user,Request $request){
        $this->authorize('update', $user);
        $this->validate($request,[
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'        // nullable当用户提交的密码为空是也能通过验证
        ]);

        $data = [];
        $data['name'] = $request->name;
        // 判断当密码不为空时才将其赋值给data
        if($request->password){
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);
        session()->flash('success', '个人资料更新成功！');
        return redirect()->route('users.show',$user);
    }

    // __construct是PHP的构造方法
    //  Auth 中间件在过滤指定动作时，如该用户未通过身份验证（未登录用户），默认将会被重定向到 /login 登录页面。
    public function __construct() {
        $this->middleware('auth', [
            'except' => ['show', 'create', 'store']
        ]);

        // 实现只让未登录的用户访问登录页面和注册页面
        $this->middleware('guest', [
            'only' => ['create']
        ]);

        // 实现删除功能
        $this->middleware('auth', [
            'except' => ['show', 'create', 'store', 'index']
        ]);
    }

    // 实现显示所有用户数据动作
    public function index(){
        // $users = user::all();  获取全部数据
        $users = User::paginate(6); // 分页获取用户数据
        return view('users.index', compact('users'));
    }

    // 实现删除动作
    public function destroy(User $user) {
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back(); }
}
