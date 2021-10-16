<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    // 关联数据表，多对一
    public function user() {
        return $this->belongsTo(User::class);
    }
}
