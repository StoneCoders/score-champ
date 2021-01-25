@extends('layout')

@section('content')
<div>
    <div class="alert alert-warning" role="alert">
        <i class="fa fa-clock-o" aria-hidden="true" style="font-size: 16px;"></i>
        שים לב! שעות המשחקים לפי אזור זמן UTC
    </div>
    <div class="alert alert-info" role="alert">
        כדי לבטל סימון של משחק כהסתיים יש <a href="{{ route('showReviveGame', ['league_id' => $league->id]) }}">ללחוץ כאן</a>.
        זוהי פעולה הדורשת משאבים רבים מהמערכת לכן יש להמנע ממנה ככל הניתן
    </div>

@if(isset($error))
        <div class="alert alert-danger" role="alert">{{ $error }}</div>
    @endif
    @if(isset($message))
        <div class="alert alert-success" role="alert">{{ $message }}</div>
    @endif
            <div class="col-sm-12">
                <div class="panel-group" role="tablist" aria-multiselectable="true" id="accordion">
                @foreach($match_weeks as $match_week)
                    <div class="panel panel-default" style="margin: 10px">
                        <div class="panel-heading" role="tab" id="headingOne">
                            <h4 class="panel-title">
                                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#matchWeek{{ $match_week->id }}" aria-expanded="true" aria-controls="matchWeek{{ $match_week->id }}" style="display: inline-block; width: 100%">
                                    {{ $match_week->title_he }}
                                    @if ($league->current_match_week_id == $match_week->id)
                                        - מחזור נוכחי
                                    @endif
                                </a>
                            </h4>
                        </div>
                        <div id="matchWeek{{ $match_week->id }}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
                            <div class="panel-body">
                                <form method="post" action="{{ route('updateGames', ['league_id' => $league->id]) }}">

                                    <div class="form-inline">
                                        <div class="form-group">
                                            <label for="exampleInputName2">כותרת מחזור עברית</label>
                                            <input type="hidden" name="match_week_id" value="{{ $match_week->id }}">
                                            <input type="text" class="form-control" name="match_week_he" value="{{ $match_week->title_he }}">
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleInputEmail2">אנגלית</label>
                                            <input type="text" class="form-control" name="match_week_en" value="{{ $match_week->title_en }}">
                                        </div>
                                    </div>

                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <table class="table table-hover">
                                        <thead>
                                        <tr>
                                            <th class="text-center">קבוצה בית</th>
                                            <th class="text-center">קבוצה אורחת</th>
                                            <th class="text-center">תאריך משחק</th>
                                            <th class="text-center">ניקוד קבוצה בית</th>
                                            <th class="text-center">ניקוד קבוצה אורחת</th>
                                            <th class="text-center">סוג משחק</th>
                                            <th class="text-center">משחק הסתיים?</th>
                                            <th class="text-center">הסתר?</th>
                                            <th class="text-center">הסר הסתרה בתאריך</th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($match_week->game()->orderBy('game_date')->get() as $game)
                                            <tr style="border-bottom: none">
                                                <td style="width: 10%">
                                                    <select {{ $game->isFinished ? 'disabled="disabled" ' : '' }} class="form-control flags input-sm" name="game[{{ $game->id }}][team_a_id]" style="direction: rtl">
                                                        @foreach($winning_teams as $team_id => $team)
                                                            <option {{ $game->team_a_id == $team_id ? 'selected="seletected"' : '' }} value="{{ $team_id }}">{{ $team->name_he }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td style="width: 10%">
                                                    <select {{ $game->isFinished ? 'disabled="disabled" ' : '' }} class="form-control flags input-sm" name="game[{{ $game->id }}][team_b_id]" style="direction: rtl">
                                                        @foreach($winning_teams as $team_id => $team)
                                                            <option {{ $game->team_b_id == $team_id ? 'selected="seletected"' : '' }} value="{{ $team_id }}">{{ $team->name_he }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td style="width: 10%">
                                                    <div class="input-group date game_date-DatetimePicker">
                                                        <input {{ $game->isFinished ? 'disabled="disabled" ' : '' }} type="text" name="game[{{ $game->id }}][game_date]" class="form-control input-sm text-left" value="{{ $game->game_date }}" dir="ltr" />
                                    <span class="input-group-addon input-group-sm">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                                    </div>
                                                </td>
                                                <td style="width: 5%"><input {{ $game->isFinished ? 'disabled="disabled"' : '' }} class="form-control input-sm" type="number" min="0" name="game[{{ $game->id }}][team_a_score]" value="{{ $game->team_a_score }}" /></td>
                                                <td style="width: 5%"><input {{ $game->isFinished ? 'disabled="disabled"' : '' }} class="form-control input-sm" type="number" min="0" name="game[{{ $game->id }}][team_b_score]" value="{{ $game->team_b_score }}" /></td>

                                                <td style="width: 10%">
                                                    <select {{ $game->isFinished ? 'disabled="disabled" ' : '' }} class="form-control input-sm" name="game[{{ $game->id }}][stage]">
                                                        <option {{ $game->stage == 'a' ? 'selected="seletected"' : '' }} value="a">משחק רגיל</option>
                                                        <option {{ $game->stage == 'b' ? 'selected="seletected"' : '' }} value="b">משחק מרכזי</option>
                                                    </select>
                                                </td>
                                                <td class="text-center" style="width: 5%">
                                                    <input {{ $game->isFinished || !$game->is_game_date_passed ? 'disabled="disabled" ' : '' }} type="checkbox" name="game[{{ $game->id }}][isFinished]" value="1" {!!  $game->isFinished ? 'checked="checked"' : '' !!} />
                                                </td>
                                                <td class="text-center" style="width: 5%">
                                                    <input {{ $game->isFinished ? 'disabled="disabled" ' : '' }} type="checkbox" name="game[{{ $game->id }}][hide]" value="1" {!!  $game->hide ? 'checked="checked"' : '' !!} data-game-id="{{ $game->id }}" class="clickToHide {{  $game->id }}" />
                                                </td>

                                                <td class="text-center" style="width: 10%">

                                                    <div class="input-group date game_date-DatetimePicker  {{$game->hide ? "" : 'hidden'}}" data-showOrHide="{{ $game->id }}">
                                                        <input {{ $game->isFinished ? 'disabled="disabled" ' : '' }} type="text" name="game[{{ $game->id }}][game_show_in_date]" class="form-control input-sm text-left" value="{{ $game->show_in_date }}" dir="ltr" />
                                                        <span class="input-group-addon input-group-sm">
                                                        <span class="glyphicon glyphicon-calendar"></span></span>
                                                    </div>

                                                </td>
                                                <td style="width: 5%">
                                                    @if( ! $game->isFinished)
                                                        <a class="btn btn-danger btn-sm"
                                                           href="{{ route('delete_game', ['game_id' => $game->id]) }}"
                                                           onclick="return confirm(window.isDirty ? 'האם ברצונך למחוק? שאר השינויים לא ישמרו!' : 'האם ברצונך למחוק?');"><i
                                                                    class="fa fa-trash"></i></a>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr style="border-top: none">
                                                <td colspan="9" style="border:none">
                                                    <label><input type="checkbox" class="toggleSum" {!! $game->link_button_text_he ? 'checked' : '' !!}> הצג תקציר</label>
                                                    <table style="width: 100%">
                                                        <tr>
                                                            <th>כפתור עברית</th>
                                                            <th>כפתור אנגלית</th>
                                                            <th>קישור סרטון</th>
                                                            <th>תקציר עברית</th>
                                                            <th>תקציר אנגלית</th>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding: 0 10px"><input type="text" class="form-control" name="game[{{ $game->id }}][link_button_text_he]" value="{{ $game->link_button_text_he }}"></td>
                                                            <td style="padding: 0 10px"><input type="text" class="form-control" name="game[{{ $game->id }}][link_button_text_en]" value="{{ $game->link_button_text_en }}"></td>
                                                            <td style="padding: 0 10px"><input type="text" class="form-control" name="game[{{ $game->id }}][link_video]" value="{{ $game->link_video }}"></td>
                                                            <td style="padding: 0 10px"><textarea class="form-control" name="game[{{ $game->id }}][link_text_info_he]">{{ $game->link_text_info_he }}</textarea></td>
                                                            <td style="padding: 0 10px"><textarea class="form-control" name="game[{{ $game->id }}][link_text_info_en]" dir="ltr">{{ $game->link_text_info_en }}</textarea></td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                    <button type="submit" class="btn btn-block btn-lg btn-primary">שמור שינויים</button><br />
                                        <a class="btn btn-default btn-sm" href="{{ route('add_game', ['match_week_id' => $match_week->id]) }}"
                                           onclick="return window.isDirty ? confirm('האם ברצונך להוסיף משחק? שינויים שבוצעו בטופס לא ישמרו!') : redirectToPosition();"><i
                                                    class="fa fa-plus"></i> הוסף משחק</a>
                                        <a class="btn btn-danger btn-sm" href="{{ route('delete_match_week', ['match_week_id' => $match_week->id]) }}"
                                           onclick="return confirm(window.isDirty ? 'האם ברצונך למחוק? שאר השינויים לא ישמרו!' : 'האם ברצונך למחוק?');"><i
                                                    class="fa fa-trash"></i> מחק מחזור</a>
                                    <a class="btn btn-success btn-sm" href="{{ route('copy_match_week', ['match_week_id' => $match_week->id]) }}"
                                       onclick="return confirm(window.isDirty ? 'האם ברצונך לשכפל מחזור? שאר השינויים לא ישמרו!' : 'האם ברצונך לשכפל?');"><i
                                                class="fa fa-files-o"></i> שכפל מחזור</a>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
                </div>


                <br /><br />
                <a href="{{ route('add_match_week', ['league_id' => $league->id]) }}" class="btn btn-default"><i class="fa fa-plus"></i> הוספת מחזור
                    חדש</a>
            </div>

        </form>
</div>
    <script type="text/javascript">
        $('.game_date-DatetimePicker').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss'
        });

        $('body').one('input', ':input', function () {
            window.isDirty = true;
        });

        @if ($match_id)
         $('#matchWeek{{$match_id}}').collapse()
        @endif

        $('.toggleSum').on('change', function() {
            if ($(this).is(':checked')) {
                $(this).parent().siblings('table').show();
            } else {
                $(this).parent().siblings('table').hide()
                    .find(':input').val('');
            }
        });

        $('.toggleSum').trigger('change');

        function redirectToPosition(e) {
            localStorage.setItem('topPosition', window.pageYOffset);
        }

        if (localStorage.getItem('topPosition')) {
            console.log('topPosition', localStorage.getItem('topPosition'))
            $('html, body').animate({
                scrollTop: localStorage.getItem('topPosition')
            }, 100);
            localStorage.setItem('topPosition', '');
        }
    </script>
@endsection
