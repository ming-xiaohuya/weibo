@extends('layouts.default')
@section('title', '所有用户')
@section('content')
<div class="offset-md-2 col-md-8">
  <h2 class="mb-4 text-center">所有用户</h2>
  <div class="list-group list-group-flush">
    {{-- 使用 @foreach 的方法将所有用户的数据逐个输出 --}}
    @foreach ($users as $user)
    @include('users._user')
    @endforeach
  </div>
  <div class="mt-3">
    {{-- 渲染分页视图的代码必须使用 {!! !!} 语法，而不是 {{ }} ，这样生成 HTML 链接才不会被转义 --}}
    {!! $users->render() !!}
  </div>
</div>
@stop
