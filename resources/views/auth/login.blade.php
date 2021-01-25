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
    <link rel="stylesheet" href="{{ URL::asset('css/bootstrap.min.css') }}"/>
    <link rel="stylesheet" href="//bootswatch.com/cerulean/bootstrap.min.css" />
    <link rel="stylesheet" href="{{ URL::asset('css/bootstrap-rtl.min.css') }}"/>
</head>
<body>
<nav class="navbar navbar-fixed-top">
    <div class="navbar-header">
        <span class="navbar-brand" style="color: #000">ממשק ניהול</span>
    </div>
</nav>
<div class="starter-template">
    <div class="col-md-6 col-md-offset-3">
        <div class="panel panel-info">
            <div class="panel-heading text-center">התחברות</div>
            <div class="panel-body">
                <form class="form-horizontal" role="form" method="POST" action="{{ route('login') }}">
                    {!! csrf_field() !!}

                    <div class="form-group">
                        <label class="col-md-4 control-label">דואר אלקטרוני</label>

                        <div class="col-md-6">
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}" style="direction: ltr">

                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 control-label">סיסמה</label>

                        <div class="col-md-6">
                            <input type="password" class="form-control" name="password" style="direction: ltr">

                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12 text-center">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="remember"> זכור אותי
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12 text-center">
                            <button type="submit" class="btn btn-info">
                                <i class="glyphicon glyphicon-lock"></i> התחברות
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
