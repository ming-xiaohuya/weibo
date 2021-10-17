<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // 创建监听器，激活令牌需用户在注册之前生成
    public static function boot() {
        parent::boot();
        static::creating(function ($user) {
            $user->activation_token = Str::random(10);
        });
    }

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Gravatar 为 “全球通用头像”
    public function gravatar($size = '100') {
        $hash = md5(strtolower(trim($this->attributes['email'])));
        return "http://www.gravatar.com/avatar/$hash?s=$size";
    }

    // 关联数据表，一对多
    public function statuses() {
        return $this->hasMany(Status::class);
    }

    // 获取数据，并根据创建的时间倒序排序
    public function feed() {

        // 通过 followings 方法取出所有关注用户的信息，再借助 pluck 方法将 id 进行分离并赋值给 user_ids
        $user_ids = $this->followings->pluck('id')->toArray();

        // 将当前用户的 id 加入到 user_ids 数组中
        array_push($user_ids, $this->id);
        return Status::whereIn('user_id', $user_ids) ->with('user') ->orderBy('created_at', 'desc');
    }

    // 建立表关联
    public function followers() {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'follower_id');
    }
    public function followings() {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'user_id');
    }

    // 用户关注
    public function follow($user_ids) {
        if ( ! is_array($user_ids)) {   // is_array判断参数是否为数组
            $user_ids = compact('user_ids');
        }
        $this->followings()->sync($user_ids, false);
    }
    // 用户取消关注
    public function unfollow($user_ids) {
        if ( ! is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->detach($user_ids);
    }

    public function isFollowing($user_id) {
        return $this->followings->contains($user_id);
    }
}
