@extends('layout')

@section('content')
<div class="container">
    @if(isset($error))
        <div class="alert alert-danger" role="alert">{{ $error }}</div>
    @endif
    @if(isset($message))
        <div class="alert alert-success" role="alert">{{ $message }}</div>
    @endif
    <form method="post" action="{{ route('updateLeagueSettings', [ 'league_id' => $league->id ]) }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="jumbotron flex">
            <h2>הגדרות ותכנים</h2>
            <div class="form-group text-right">
                <label for="is_turnir">האם טורניר?</label>
                <input type="checkbox" name="is_turnir" id="is_turnir" value="1" {!! $league->is_turnir ? 'checked="checked"': '' !!} />
            </div>
            <div class="form-group text-right">
                <label for="show_league_board">הצג טבלה?</label>
                <input type="checkbox" name="show_league_board" id="show_league_board" value="1" {!! $league->show_league_board ? 'checked="checked"': '' !!} />
            </div>
            <div class="form-group text-right">
                <label for="name_he">כותרת ליגה בעברית</label>
                <input type="text" class="form-control" name="name_he" id="name_he" value="{{ $league->name_he }}" />
            </div>
            <div class="form-group text-right">
                <label for="name_en">כותרת ליגה באנגלית</label>
                <input type="text" class="form-control" name="name_en" id="name_en" value="{{ $league->name_en }}" />
            </div>
            <div class="form-group text-right">
                <label for="html_empty_bets_open_he">הודעה לליגה ללא הימורים פתוחים - עברית</label>
                <textarea class="form-control" name="html_empty_bets_open_he" id="html_empty_bets_open_he" rows="7">{{ $league->html_empty_bets_open_he }}</textarea>
            </div>
            <div class="form-group text-right">
                <label for="html_empty_bets_open_en">הודעה לליגה ללא הימורים פתוחים - אנגלית</label>
                <textarea class="form-control" name="html_empty_bets_open_en" id="html_empty_bets_open_en" rows="7">{{ $league->html_empty_bets_open_en }}</textarea>
            </div>
            <div class="form-group text-right">
                <label for="html_empty_bets_closed_he">הודעה לליגה ללא הימורים סגורים - עברית</label>
                <textarea class="form-control" name="html_empty_bets_closed_he" id="html_empty_bets_closed_he" rows="7">{{ $league->html_empty_bets_closed_he }}</textarea>
            </div>
            <div class="form-group text-right">
                <label for="html_empty_bets_closed_en">הודעה לליגה ללא הימורים סגורים - אנגלית</label>
                <textarea class="form-control" name="html_empty_bets_closed_en" id="html_empty_bets_closed_en" rows="7">{{ $league->html_empty_bets_closed_en }}</textarea>
            </div>
            <div class="form-group text-right">
                <label for="html_rules_he">חוקי המשחק - עברית</label>
                <textarea class="form-control" name="html_rules_he" id="html_rules_he" rows="7">{{ $league->html_rules_he }}</textarea>
            </div>
            <div class="form-group text-right">
                <label for="html_rules_en">חוקי המשחק - אנגלית</label>
                <textarea class="form-control" name="html_rules_en" id="html_rules_en" rows="7">{{ $league->html_rules_en }}</textarea>
            </div>
            <div class="form-group text-right">
                <label for="global_rank_title_he">"דירוג עונתי" בעברית</label>
                <input type="text" class="form-control" name="global_rank_title_he" id="global_rank_title_he" value="{{ $league->global_rank_title_he }}" />
            </div>
            <div class="form-group text-right">
                <label for="global_rank_title_en">"דירוג עונתי" באנגלית</label>
                <input type="text" class="form-control" name="global_rank_title_en" id="global_rank_title_en" value="{{ $league->global_rank_title_en }}" />
            </div>
            <div class="form-group text-right full-row">
                <label for="show_global_rank">הצג דירוג עונתי?</label>
                <input type="checkbox" name="show_global_rank" id="show_global_rank" value="1" {!! $league->show_global_rank ? 'checked="checked"': '' !!} />
            </div>
            <div class="form-group text-right">
                <label for="week_rank_title_he">"דירוג שבועי" בעברית</label>
                <input type="text" class="form-control" name="week_rank_title_he" id="week_rank_title_he" value="{{ $league->week_rank_title_he }}" />
            </div>
            <div class="form-group text-right">
                <label for="week_rank_title_en">"דירוג שבועי" באנגלית</label>
                <input type="text" class="form-control" name="week_rank_title_en" id="week_rank_title_en" value="{{ $league->week_rank_title_en }}" />
            </div>
            <div class="form-group text-right full-row">
                <label for="show_week_rank">הצג דירוג שבועי?</label>
                <input type="checkbox" name="show_week_rank" id="show_week_rank" value="1" {!! $league->show_week_rank ? 'checked="checked"': '' !!} />
            </div>

            <div class="form-group text-right">
                <label for="winning_team_title_he">"קבוצה מנצחת" בעברית</label>
                <input type="text" class="form-control" name="winning_team_title_he" id="winning_team_title_he" value="{{ $league->winning_team_title_he }}" />
            </div>
            <div class="form-group text-right">
                <label for="winning_team_title_en">"קבוצה מנצחת" באנגלית</label>
                <input type="text" class="form-control" name="winning_team_title_en" id="winning_team_title_en" value="{{ $league->winning_team_title_en }}" />
            </div>
            <div class="form-group text-right">
                <label for="top_player_title_he">"מלך השערים" בעברית</label>
                <input type="text" class="form-control" name="top_player_title_he" id="top_player_title_he" value="{{ $league->top_player_title_he }}" />
            </div>
            <div class="form-group text-right">
                <label for="top_player_title_en">"מלך השערים" באנגלית</label>
                <input type="text" class="form-control" name="top_player_title_en" id="top_player_title_en" value="{{ $league->top_player_title_en }}" />
            </div>


        </div>
        <div class="jumbotron flex">
            <h2>ניקוד ליגה</h2>
            <div class="form-group text-right">
                <label for="COW_PTS_LEVEL_A">משחק רגיל - פגעתי בקבוצה לא בתוצאה</label>
                <input type="text" class="form-control" name="COW_PTS_LEVEL_A" id="COW_PTS_LEVEL_A" value="{{ $league->COW_PTS_LEVEL_A }}" />
            </div>
            <div class="form-group text-right">
                <label for="COW_PTS_LEVEL_B">משחק מרכזי - פגעתי בקבוצה לא בתוצאה</label>
                <input type="text" class="form-control" name="COW_PTS_LEVEL_B" id="COW_PTS_LEVEL_B" value="{{ $league->COW_PTS_LEVEL_B }}" />
            </div>
            <div class="form-group text-right">
                <label for="BULL_PTS_LEVEL_A">משחק רגיל - פגעתי בדיוק בתוצאה</label>
                <input type="text" class="form-control" name="BULL_PTS_LEVEL_A" id="BULL_PTS_LEVEL_A" value="{{ $league->BULL_PTS_LEVEL_A }}" />
            </div>
            <div class="form-group text-right">
                <label for="BULL_PTS_LEVEL_B">משחק מרכזי - פגעתי בדיוק  בתוצאה</label>
                <input type="text" class="form-control" name="BULL_PTS_LEVEL_B" id="BULL_PTS_LEVEL_B" value="{{ $league->BULL_PTS_LEVEL_B }}" />
            </div>
            <div class="form-group text-right thirty">
                <label for="WINNING_TEAM_PTS_CALSS_A">סוג קבוצה מנצחת לפי קלאס A</label>
                <input type="text" class="form-control" name="WINNING_TEAM_PTS_CALSS_A" id="WINNING_TEAM_PTS_CALSS_A" value="{{ $league->WINNING_TEAM_PTS_CALSS_A }}" />
            </div>
            <div class="form-group text-right thirty">
                <label for="WINNING_TEAM_PTS_CALSS_B">סוג קבוצה מנצחת לפי קלאס B</label>
                <input type="text" class="form-control" name="WINNING_TEAM_PTS_CALSS_B" id="WINNING_TEAM_PTS_CALSS_B" value="{{ $league->WINNING_TEAM_PTS_CALSS_B }}" />
            </div>
            <div class="form-group text-right thirty">
                <label for="WINNING_TEAM_PTS_CALSS_C">סוג קבוצה מנצחת לפי קלאס C</label>
                <input type="text" class="form-control" name="WINNING_TEAM_PTS_CALSS_C" id="WINNING_TEAM_PTS_CALSS_C" value="{{ $league->WINNING_TEAM_PTS_CALSS_C }}" />
            </div>
            <div class="form-group text-right thirty">
                <label for="TOP_SCORER_PTS_CALSS_A">סוג שחקן מנצחת לפי קלאס A</label>
                <input type="text" class="form-control" name="TOP_SCORER_PTS_CALSS_A" id="TOP_SCORER_PTS_CALSS_A" value="{{ $league->TOP_SCORER_PTS_CALSS_A }}" />
            </div>
            <div class="form-group text-right thirty">
                <label for="TOP_SCORER_PTS_CALSS_B">סוג שחקן מנצחת לפי קלאס B</label>
                <input type="text" class="form-control" name="TOP_SCORER_PTS_CALSS_B" id="TOP_SCORER_PTS_CALSS_B" value="{{ $league->TOP_SCORER_PTS_CALSS_B }}" />
            </div>
            <div class="form-group text-right thirty">
                <label for="TOP_SCORER_PTS_OTHER">סוג שחקן מנצחת לפי קלאס OTHER</label>
                <input type="text" class="form-control" name="TOP_SCORER_PTS_OTHER" id="TOP_SCORER_PTS_OTHER" value="{{ $league->TOP_SCORER_PTS_OTHER }}" />
            </div>
            <div class="form-group text-right">
                <label for="score_range">טווח ניחוש תוצאה</label>
                <p style="font-size: 0.8em">השתמש במספרים ובמקב ("-") כדי להגדיר רצף, בעזרת פסיק (",") הפרד בין רצפים ומספרים ספציפיים</p>
                <input type="text" class="form-control" name="score_range" id="score_range" value="{{ $league->score_range ? $league->score_range : '0-100' }}" dir="ltr" />
            </div>
        </div>
        <button type="submit" class="btn btn-lg btn-block btn-primary">שמירת שינויים</button>
    </form>
</div>
<br />
<br />
<br />
@endsection
