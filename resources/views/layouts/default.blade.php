<!DOCTYPE html>
<html>
  <head>
    <title>@yield('title', 'Weibo App') - Laravel 新手入门教程</title>
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
  </head>
  <body>
    @include('layouts.header')
    <div class="container">
      <div class="offset-md-1 col-md-10">
        @include('shared._messages')
        @yield('content')
        @include('layouts.footer')
      </div>
    </div>

    {{-- 引入js文件 --}}
    <script src="{{ mix('js/app.js') }}"></script>
  </body>
</html>
