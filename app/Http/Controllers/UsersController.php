<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Mail;

class UsersController extends Controller
{
    public function create(){
        return view('users.create');
    }

    // 获取用户发布过的所有微博
    public function show(User $user){

        $statuses = $user->statuses()->orderBy('created_at', 'desc')->paginate(10);
        return view('users.show', compact('user', 'statuses'));

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

        $this->sendEmailConfirmationTo($user);  // 调用sendEmailConfirmationTo方法激活发送邮件给指定的用户
        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
        return redirect('/');
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

        // 开启未登录用户访问权限
        $this->middleware('auth', [
            'except' => ['show', 'create', 'store', 'index', 'confirmEmail']
        ]);

        // 注册限流 一个小时内只能提交 10 次请求；
        $this->middleware('throttle:10,60', [ 'only' => ['store'] ]);
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
        return back();
    }

    // 实现邮件发送功能
    protected function sendEmailConfirmationTo($user) {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'summer2@example.com';
        $name = 'Summer2';
        $to = $user->email;
        $subject = "感谢注册 Weibo 应用！请确认你的邮箱。";
        Mail::send($view, $data, function ($message) use ($from, $name, $to, $subject) {
            $message->from($from, $name)->to($to)->subject($subject);
        });
    }

    // 完成用户激活操作
    public function confirmEmail($token) {
        $user = User::where('activation_token', $token)->firstOrFail();
        $user->activated = true;
        $user->activation_token = null;
        $user->save();
        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('users.show', [$user]);
    }
}
