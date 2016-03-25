@if(request()->input('__content_only'))
    @yield('content')
    <?php return ?>
@endif

@extends('dms::template.template')
@section('body-content')
    <body class="hold-transition login-page">

    <div class="login-box-logo">
        <div class="login-logo">
            <a href="{{ route('dms::index') }}"><b>DMS</b> <br/> {{ '{' . request()->server->get('SERVER_NAME') . '}' }}</a>
        </div>
    </div>
    <div class="login-box">
        <div class="login-box-body">
            @include('dms::partials.alerts')
            @yield('content')
        </div>
        <!-- /.login-box-body -->
    </div>
    <!-- /.login-box -->
    </body>
@endsection
