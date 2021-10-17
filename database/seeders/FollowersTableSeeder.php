<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class FollowersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();
        $user = $users->first();
        $user_id = $user->id;
        // 获取去除掉 ID 为 1 的所有用户 ID 数组
        $followers = $users->slice(1);
        $follower_ids = $followers->pluck('id')->toArray();
        // 关注除了 1 号用户以外的所有用户
        $user->follow($follower_ids);
        // 除了 1 号用户以外的所有用户都来关注 1 号用户
        foreach ($followers as $follower) {
            $follower->follow($user_id);
        }
    }


    public function __construct() {
        $this->middleware('auth');
    }
    public function store(User $user) {
        $this->authorize('follow', $user);
        if ( ! Auth::user()->isFollowing($user->id)) {
            Auth::user()->follow($user->id);
        }
        return redirect()->route('users.show', $user->id);
    }
    public function destroy(User $user) {
        $this->authorize('follow', $user);
        if (Auth::user()->isFollowing($user->id)) {
            Auth::user()->unfollow($user->id);
        }
        return redirect()->route('users.show', $user->id);
    }
}
