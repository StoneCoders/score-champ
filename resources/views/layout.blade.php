<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <link rel="icon" href="/favicon.ico">
    <title>ממשק ניהול</title>
    <meta name="theme-color" content="#4866E0">

    <link rel="stylesheet" href="{{ URL::asset('css/app.css') }}"/>
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/r/bs-3.3.5/jq-2.1.4,dt-1.10.8/datatables.min.css"/>
    <link rel="stylesheet" href="{{ URL::asset('css/bootstrap.min.css') }}"/>
    <link rel="stylesheet" href="//bootswatch.com/cerulean/bootstrap.min.css" />
    <link rel="stylesheet" href="{{ URL::asset('css/bootstrap-rtl.min.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('css/toastr.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('css/bootstrap-datetimepicker.css') }}"/>
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">

    <script src="{{ URL::asset('js/jquery.js') }}"></script>
    <script src="{{ URL::asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ URL::asset('js/jquery.dataTables.js') }}"></script>
    <script src="{{ URL::asset('js/dataTables.bootstrap.js') }}"></script>
    <script src="{{ URL::asset('js/toastr.js') }}"></script>
    <script src="{{ URL::asset('js/moment.js') }}"></script>
    <script src="{{ URL::asset('js/moment_he.js') }}"></script>
    <script src="{{ URL::asset('js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ URL::asset('js/bootbox.js') }}"></script>
    <script src="{{ URL::asset('js/global.js') }}"></script>
</head>
<body>
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="javascript:void(0);" style="cursor: default; color: #fff;">ממשק ניהול</a>
    </div>
    <div id="navbar" class="collapse navbar-collapse">
        <ul class="nav navbar-nav">
            {{--<li class="{{ Request::is('games') ? 'active' : '' }}"><a href="{{ route('games') }}">משחקים</a></li>--}}
            {{--<li class="{{ Request::is('teams') ? 'active' : '' }}"><a href="{{ route('winningTeam') }}">קבוצה מנצחת</a></li>--}}
            {{--<li class="{{ Request::is('players') ? 'active' : '' }}"><a href="{{ route('topPlayer') }}">מלך השערים</a></li>--}}
            <li class="{{ Request::is('leagues') ? 'active' : '' }}"><a href="{{ route('showLeagues') }}">ניהול הליגות</a></li>

            <li class="{{ Request::is('settings') ? 'active' : '' }}"><a href="{{ route('settings') }}">הגדרות ותכנים</a></li>
            <li class="{{ Request::is('push') ? 'active' : '' }}"><a href="{{ route('push') }}">הודעת דחיפה</a></li>
            <li><a href="{{ route('logout') }}">התנתקות</a></li>
        </ul>
    </div>
</nav>
<div class="starter-template">
    @yield('content')
</div>
</body>
</html>
