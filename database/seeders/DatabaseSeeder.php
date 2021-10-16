<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        // 调用 call 方法来指定我们要运行假数据填充的文件
        $this->call(UsersTableSeeder::class);
        $this->call(StatusesTableSeeder::class);

        Model::reguard();
    }
}
