@extends('layout')

@section('content')
<div class="container">
    @if(isset($error))
        <div class="alert alert-danger" role="alert">{{ $error }}</div>
    @endif
    @if(isset($message))
        <div class="alert alert-success" role="alert">{{ $message }}</div>
    @endif
    <div class="row">
        <div class="col-md-12">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th class="text-center">כותרת ליגה</th>
                    <th class="text-center">מחזור נוכחי</th>
                    <th class="text-center">סגירת הימור שחקן</th>
                    <th class="text-center">סגירת הימור קבוצה</th>
                    <th style="width: 600px;"></th>
                </tr>
                </thead>
                <tbody>
                @foreach($leagues as $league)
                    <tr class="text-center">
                        <td class="text-right">{{ $league->name_he }} {{ $league->is_default ? ' (ברירת מחדל)' : '' }}</td>
                        <td>{{ \App\Models\MatchWeek::find($league->current_match_week_id) ? \App\Models\MatchWeek::find($league->current_match_week_id)->title_he : '' }}</td>
                        <td>{{ $league->end_bet_top_score_player }}</td>
                        <td>{{ $league->end_bet_winning_team }}</td>
                        <td>
                            <a href="{{ route('leagueEdit', ['league_id' => $league->id]) }}" class="btn btn-sm btn-primary"><i class="fa fa-pencil"></i> עריכת ליגה</a>
                            <a href="{{ route('games', ['league_id' => $league->id]) }}" class="btn btn-sm btn-primary"><i class="fa fa-cogs"></i> משחקים ומחזורים</a>
                            <a href="{{ route('topPlayer', ['league_id' => $league->id]) }}" class="btn btn-sm btn-primary"><i class="fa fa-address-book"></i> מלך השערים</a>
                            <a href="{{ route('winningTeam', ['league_id' => $league->id]) }}" class="btn btn-sm btn-primary"><i class="fa fa-users"></i> ניהול קבוצות </a>
                            <a href="{{ route('toggleActiveLeague', ['league_id' => $league->id]) }}" class="btn btn-sm {{ $league->is_active ? 'btn-danger' : 'btn-success' }}" onclick="return confirm('האם ברצונך לבצע שינוי?')"><i class="fa {{ $league->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i> {{ $league->is_active ? 'הסתר ליגה' : 'הצגת ליגה' }}</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <br /><br />
            <a href="{{ route('add_league') }}" class="btn btn-default"><i class="fa fa-plus"></i> הוספת ליגה
                חדשה</a>

        </div>
    </div>
</div>
    <script type="text/javascript">
        $('#end_bet_winning_team-DatetimePicker').datetimepicker({
            format: 'YYYY-MM-DD HH:mm'
        });
    </script>
@endsection
