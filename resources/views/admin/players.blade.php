@extends('layout')

@section('content')
<div class="container">
    <h2>{{ $league->name_he }} - הגדרת שחקנים</h2>
    @if(isset($error))
        <div class="alert alert-danger" role="alert">{{ $error }}</div>
    @endif
    @if(isset($message))
        <div class="alert alert-success" role="alert">{{ $message }}</div>
    @endif
    <div>
        <form method="post" action="{{ route('updateTopPlayer', ['league_id' => $league_id]) }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <div class="row">
                <div class="col-md-8">
                    <a class="btn btn-success" href="{{ route('createTopPlayer', ['league_id' => $league_id]) }}"
                       onclick="return window.isDirty ? confirm('שינויים שלא נשמרו ימחקו') : true;"><i class="fa fa-plus"></i> הוספת שחקן</a><br><br>
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th class="text-center">שם השחקן - אנגלית</th>
                            <th class="text-center">שם השחקן - עברית</th>
                            <th class="text-center">גולים</th>
                            <th class="text-center">קלאס</th>
                            <th class="text-center"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($players as $player)
                            <tr>
                                <td><input class="form-control input-sm" type="text" name="player[{{ $player->id }}][name]" value="{{ $player->name }}" /></td>
                                <td><input class="form-control input-sm" type="text" name="player[{{ $player->id }}][name_he]" value="{{ $player->name_he }}" /></td>
                                <td style="width: 100px"><input class="form-control input-sm" type="number" min="0" name="player[{{ $player->id }}][goals]" value="{{ $player->goals }}" /></td>
                                <td>
                                    <select class="form-control input-sm" name="player[{{ $player->id }}][class]">
                                        <option {{ $player->class == 'a' ? 'selected="seletected"' : '' }} value="a">Class A</option>
                                        <option {{ $player->class == 'b' ? 'selected="seletected"' : '' }} value="b">Class B</option>
                                        <option {{ $player->class == 'other' ? 'selected="seletected"' : '' }} value="other">Class Other</option>
                                    </select>
                                </td>
                                <td>
                                    <a class="btn btn-danger btn-sm"
                                       href="{{ route('deleteTopPlayer', ['player_id' => $player->id]) }}" onclick="return confirm(window.isDirty ? 'האם ברצונך למחוק? שאר השינויים לא ישמרו!' : 'האם ברצונך למחוק?');"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                   <button type="submit" class="btn btn-block btn-lg btn-primary">שמירת שינויים</button>
                </div>
                <div class="col-md-4">
                    <label for="end_bet_top_score_player">מועד סיום הימורים</label>
                    <div class="input-group date" id="end_bet_top_score_player-DatetimePicker">
                        <input type="text" name="settings[end_bet_top_score_player]" class="form-control text-left" value="{{ $league->end_bet_top_score_player }}" dir="ltr" />
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input value="1" type="checkbox" name="settings[allow_bet_top_score_player]" {{ $league->allow_bet_top_score_player ? 'checked="checked"' : '' }} /> הימור מוצג
                        </label>
                    </div>
                    @if ($league->top_player_finished)
                        <a href="{{ route('cancelFinishTopPlayer', ['league_id' => $league->id]) }}" class="btn btn-block btn-danger">פתיחת הימור וביטול נקודות</a>
                    @elseif ($allow_finish)
                        <a href="{{ route('finishTopPlayer', ['league_id' => $league->id]) }}" class="btn btn-block btn-success">סגירת הימור וחישוב נקודות</a>
                    @else
                        <button class="btn btn-block btn-success" type="button" disabled="disabled">סגירת הימור וחישוב נקודות</button>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>
    <script type="text/javascript">
        $('#end_bet_top_score_player-DatetimePicker').datetimepicker({
            format: 'YYYY-MM-DD HH:mm'
        });

        $('body').one('input', ':input', function () {
            window.isDirty = true;
        })
    </script>
@endsection
