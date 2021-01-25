@extends('layout')

@section('content')
<div class="container">
    @if(isset($error))
        <div class="alert alert-danger" role="alert">{{ $error }}</div>
    @endif
    @if(isset($message))
        <div class="alert alert-success" role="alert">{{ $message }}</div>
    @endif
    <form method="post" action="{{ route('updateSettings') }}">
        <div class="jumbotron">
            <h2>הגדרות הימורים</h2>
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="form-group text-right">
                <label for="default_league_id">ליגת ברירת מחדל</label>
                <select class="form-control" name="default_league_id">
                    @foreach($leagues as $league)
                        <option value="{{ $league->id }}" {!! $league->is_default ? 'selected="selected"' : '' !!}>{{ $league->name_he }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group text-right">
                <label for="prevent_bet_minutes_before_game">הפסקת הימורים מספר דקות לפני תחילת משחק</label>
                <input type="text" class="form-control" name="prevent_bet_minutes_before_game" id="prevent_bet_minutes_before_game" value="{{ $settings->prevent_bet_minutes_before_game }}" />
            </div>
        </div>
        <div class="jumbotron">
            <h2>תכנים</h2>
            <div class="form-group text-right">
                <label for="html_rules_he">חוקי המשחק - עברית</label>
                <textarea class="form-control" name="html_rules_he" id="html_rules_he" rows="7">{{ $settings->html_rules_he }}</textarea>
            </div>
            <div class="form-group text-right">
                <label for="html_rules_en">חוקי המשחק - אנגלית</label>
                <textarea class="form-control" name="html_rules_en" id="html_rules_en" rows="7">{{ $settings->html_rules_en }}</textarea>
            </div>
            <div class="form-group text-right">
                <label for="html_empty_group_he">הודעה לקבוצה ללא משתתפים - עברית</label>
                <textarea class="form-control" name="html_empty_group_he" id="html_empty_group_he" rows="7">{{ $settings->html_empty_group_he }}</textarea>
            </div>
            <div class="form-group text-right">
                <label for="html_empty_group_en">הודעה לקבוצה ללא משתתפים - אנגלית</label>
                <textarea class="form-control" name="html_empty_group_en" id="html_empty_group_en" rows="7">{{ $settings->html_empty_group_en }}</textarea>
            </div>
            {{--<div class="form-group text-right">--}}
                {{--<label for="html_empty_bets_open_en">הודעה לליגה ללא הימורים פתוחים - אנגלית</label>--}}
                {{--<textarea class="form-control" name="html_empty_bets_open_en" id="html_empty_bets_open_en" rows="7">{{ $settings->html_empty_bets_open_en }}</textarea>--}}
            {{--</div>--}}
            {{--<div class="form-group text-right">--}}
                {{--<label for="html_empty_bets_open_he">הודעה לליגה ללא הימורים פתוחים - עברית</label>--}}
                {{--<textarea class="form-control" name="html_empty_bets_open_he" id="html_empty_bets_open_he" rows="7">{{ $settings->html_empty_bets_open_he }}</textarea>--}}
            {{--</div>--}}
            {{--<div class="form-group text-right">--}}
                {{--<label for="html_empty_bets_closed_en">הודעה לליגה ללא הימורים סגורים - אנגלית</label>--}}
                {{--<textarea class="form-control" name="html_empty_bets_closed_en" id="html_empty_bets_closed_en" rows="7">{{ $settings->html_empty_bets_closed_en }}</textarea>--}}
            {{--</div>--}}
            {{--<div class="form-group text-right">--}}
                {{--<label for="html_empty_bets_closed_he">הודעה לליגה ללא הימורים סגורים - עברית</label>--}}
                {{--<textarea class="form-control" name="html_empty_bets_closed_he" id="html_empty_bets_closed_he" rows="7">{{ $settings->html_empty_bets_closed_he }}</textarea>--}}
            {{--</div>--}}
        </div>
        <div class="jumbotron">
            <h2>תזכורות</h2>
            <div class="form-group text-right">
                <label for="reminder_title_he">כותרת תזכורת למשחק - עברית</label>
                <input type="text" class="form-control" name="reminder_title_he" id="reminder_title_he" value="{{ $settings->reminder_title_he }}" />
            </div>
            <div class="form-group text-right">
                <label for="reminder_title_en">כותרת תזכורת למשחק - אנגלית</label>
                <input type="text" class="form-control" name="reminder_title_en" id="reminder_title_en" value="{{ $settings->reminder_title_en }}" />
            </div>
            <div class="form-group text-right">
                <label for="reminder_content_he">תוכן תזכורת למשחק - עברית</label>
                <input type="text" class="form-control" name="reminder_content_he" id="reminder_content_he" value="{{ $settings->reminder_content_he }}" />
            </div>
            <div class="form-group text-right">
                <label for="reminder_content_en">תוכן תזכורת למשחק - אנגלית</label>
                <input type="text" class="form-control" name="reminder_content_en" id="reminder_content_en" value="{{ $settings->reminder_content_en }}" />
            </div>
        </div>
        <div class="jumbotron">
            <h2>פרסומות</h2>
            <div class="form-group text-right">
                <label for="show_ads">פרסומות באפליקציה</label>
                <select class="form-control" name="show_ads" style="display: block">
                    <option value="0" {{ !$settings->show_ads ? 'selected="selected"' : '' }}>לא פעיל</option>
                    <option value="1" {{ $settings->show_ads ? 'selected="selected"' : '' }}>פעיל</option>
                </select>
            </div>
            <div class="form-group text-right">
                <label for="adsplash_counter">תדירות פרסומת ספלאש</label>
                <input type="text" class="form-control" name="adsplash_counter" id="adsplash_counter" value="{{ $settings->adsplash_counter }}">
            </div>
        </div>
        <button type="submit" class="btn btn-lg btn-block btn-primary">עדכון הגדרות</button>
    </form>
</div>
<br />
<br />
<br />
@endsection
