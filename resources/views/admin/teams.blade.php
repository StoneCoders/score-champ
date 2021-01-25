@extends('layout')

@section('content')
<div class="container">
    <h2>{{ $league->name_he }} - הגדרת קבוצות</h2>
    @if(isset($error))
        <div class="alert alert-danger" role="alert">{{ $error }}</div>
    @endif
    @if(isset($message))
        <div class="alert alert-success" role="alert">{{ $message }}</div>
    @endif
    <div class="jumbotron">
        <form method="post" action="{{ route('updateWinningTeam', ['league_id' => $league_id]) }}" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <div class="row">
                <div class="col-md-8">
                    <a class="btn btn-success" href="{{ route('createTeam', ['league_id' => $league_id]) }}"
                       onclick="return window.isDirty ? confirm('שינויים שלא נשמרו ימחקו') : true;"><i class="fa fa-plus"></i> הוספת קבוצה</a><br><br>
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th class="text-center">שם הקבוצה - אנגלית</th>
                            <th class="text-center">שם הקבוצה - עברית</th>
                            <th class="text-center">חולצה</th>
                            <th class="text-center">עדיין במשחק?</th>
                            <th class="text-center">קלאס קבוצה מנצחת</th>
                            <th class="text-center"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($teams as $team)
                            <tr>
                                <td><input class="form-control input-sm" type="text" name="team[{{ $team->id }}][name]" value="{{ $team->name }}" /></td>
                                <td><input class="form-control input-sm" type="text" name="team[{{ $team->id }}][name_he]" value="{{ $team->name_he }}" /></td>
                                <td class="text-center shirtWrapper">
                                    <div class="color" {!! $team->team_color1 ? '' : 'style="display:none;"' !!}>
                                        <div class="btn-group">
                                            <button class="btn btn-xs btn-info" style="margin-top: 4px"
                                                    onclick="$(this).parents('.shirtWrapper').find('.image').hide().find(':input').prop('disabled', true); $(this).parents('.shirtWrapper').find('.color').show().find(':input').prop('disabled', false); return false;">
                                                בחר צבע
                                            </button>
                                            <button class="btn btn-xs btn-default" style="margin-top: 4px"
                                                    onclick="$(this).parents('.shirtWrapper').find('.color').hide().find(':input').prop('disabled', true); $(this).parents('.shirtWrapper').find('.image').show().find(':input').prop('disabled', false); return false;">
                                                העלה תמונה
                                            </button>
                                        </div>
                                        <br /><br />
                                        <input type="color" value="{{ $team->team_color1 }}" name="team[{{ $team->id }}][team_color1]" {!! $team->team_color1 ? '' : 'disabled="disabled"' !!}><br />
                                        <input type="color" value="{{ $team->team_color2 }}" name="team[{{ $team->id }}][team_color2]" {!! $team->team_color1 ? '' : 'disabled="disabled"' !!}><br />
                                    </div>
                                    <div class="image" {!! $team->team_color1 ? 'style="display:none;"' : '' !!}>
                                        <div class="btn-group">
                                            <button class="btn btn-xs btn-default" style="margin-top: 4px"
                                                    onclick="$(this).parents('.shirtWrapper').find('.image').hide().find(':input').prop('disabled', true); $(this).parents('.shirtWrapper').find('.color').show().find(':input').prop('disabled', false); return false;">
                                                בחר צבע
                                            </button>
                                            <button class="btn btn-xs btn-info" style="margin-top: 4px"
                                                    onclick="$(this).parents('.shirtWrapper').find('.color').hide().find(':input').prop('disabled', true); $(this).parents('.shirtWrapper').find('.image').show().find(':input').prop('disabled', false); return false;">
                                                העלה תמונה
                                            </button>
                                        </div>
                                        <br />
                                        <br />
                                        <img src="{{ asset($team->team_flag)  }}" style="max-width: 30px; max-height: 30px"><br /><br />
                                        <input type="file" name="team[{{ $team->id }}][upload_shirt]" style="max-width: 150px;" {!! $team->team_color1 ? 'disabled="disabled"' : '' !!} /><br />
                                    </div>
                                </td>
                                <td style="">
                                    <input class="form-control input-sm" type="checkbox" name="team[{{ $team->id }}][isInGame]" value="1" {{ $team->isInGame ? 'checked="checked"' : '' }} />
                                </td>
                                <td>
                                    <select class="form-control input-sm" name="team[{{ $team->id }}][class]">
                                        <option {{ $team->class == 'a' ? 'selected="seletected"' : '' }} value="a">Class A</option>
                                        <option {{ $team->class == 'b' ? 'selected="seletected"' : '' }} value="b">Class B</option>
                                        <option {{ $team->class == 'c' ? 'selected="seletected"' : '' }} value="c">Class C</option>
                                    </select>
                                </td>
                                <td>
                                    <a class="btn btn-danger btn-sm"
                                       href="{{ route('deleteTeam', ['team_id' => $team->id]) }}"
                                       onclick="return confirm(window.isDirty ? 'האם ברצונך למחוק? שאר השינויים לא ישמרו!' : 'האם ברצונך למחוק?');"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="col-md-4">
                    <label for="end_bet_top_score_team">מועד סיום הימורים</label>
                    <div class="input-group date" id="end_bet_winning_team-DatetimePicker">
                        <input type="text" name="settings[end_bet_winning_team]" class="form-control text-left" value="{{ $league->end_bet_winning_team }}" dir="ltr" />
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input value="1" type="checkbox" name="settings[allow_bet_winning_team]" {{ $league->allow_bet_winning_team ? 'checked="checked"' : '' }} /> הימור מוצג
                        </label>
                    </div>
                    @if ($league->winning_team_finished)
                        <a href="{{ route('cancelFinishWinningTeam', ['league_id' => $league_id]) }}" class="btn btn-block btn-danger">פתיחת הימור וביטול נקודות</a>
                    @elseif ($allow_finish)
                        <a href="{{ route('finishWinningTeam', ['league_id' => $league_id]) }}" class="btn btn-block btn-success">סגירת הימור וחישוב נקודות</a>
                    @else
                        <button class="btn btn-block btn-success" type="button" disabled="disabled">סגירת הימור וחישוב נקודות</button>
                    @endif
                </div>
            </div>

            <button type="submit" class="btn btn-block btn-lg btn-primary">עדכון הגדרות</button>
        </form>
    </div>
</div>
    <script type="text/javascript">
        $('#end_bet_winning_team-DatetimePicker').datetimepicker({
            format: 'YYYY-MM-DD HH:mm'
        });

        $('body').one('input', ':input', function() {
            window.isDirty = true;
        })
    </script>
@endsection
