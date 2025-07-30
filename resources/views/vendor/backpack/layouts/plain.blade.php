<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ config('backpack.base.html_direction') }}">
<meta http-equiv="refresh" content="{{ config('session.lifetime') * 60 }}">
@if(env('APP_ENV') == 'staging')
<div class="bg-danger text-center font-weight-bold font-xl d-print-none" style="position: fixed;top: 0;width: 100%">Warning! This is a Testing/Staging Environment. All changes can and will be deleted!</div>
@endif
<head>
    @include(backpack_view('inc.head'))
</head>

<body class="app flex-row align-items-center">

  @yield('header')

  <div class="container">
  @yield('content')
  </div>

  <footer class="app-footer sticky-footer">
    @include('backpack::inc.footer')
  </footer>

  @yield('before_scripts')
  @stack('before_scripts')

  @include(backpack_view('inc.scripts'))

  @yield('after_scripts')
  @stack('after_scripts')

</body>
</html>
