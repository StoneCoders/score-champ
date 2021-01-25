@extends('layout')

@section('content')
	<div class="container">
		{{--@if($sent)--}}
		{{--<div class="alert alert-success" role="alert">ברגעים אלו מתבצעת שליחת הודעות למשתמשים.<br />פעולה זו נמשכת מספר דקות.</div>--}}
		{{--@else--}}
		<div class="jumbotron">
			<form id="push_form" method="post" action="{{ route('sendPush') }}">
				<input type="hidden" name="_token" value="{{ csrf_token() }}" />
				<input type="hidden" name="push_route" />

				<h4 style="display: none">קהל יעד</h4>
				<div class="radio" style="display: none">
					<label>
						<input type="radio" name="pushType"
						       value="can_bet_today" {{ $pushType == 'can_bet_today' ? 'checked="checked"' : '' }}>
						שלח הודעה לכל המשתמשים
						שיכולים להמר היום
						(שלא הימרו על משחק שיתקיים בהמשך היום)
					</label>
				</div>
				<div class="radio" style="display: none">
					<label>
						<input type="radio" name="pushType"
						       value="all" {{ $pushType == 'all' ? 'checked="checked"' : '' }}>
						שלח הודעה לכל המשתמשים
					</label>
				</div>
				<br/>
				<div class="form-group">
					<label for="exampleInputEmail1">כותרת ההודעה - אנגלית
						<small>(יופיע רק למשתמשי אנדרואיד)</small>
					</label>
					<input type="text" class="form-control" name="title" required/>
				</div>
				<div class="form-group">
					<label for="exampleInputEmail1">כותרת ההודעה - עברית
						<small>(יופיע רק למשתמשי אנדרואיד)</small>
					</label>
					<input type="text" class="form-control" name="title_he" required/>
				</div>
				<div class="form-group">
					<label for="exampleInputEmail1">תוכן ההודעה - אנגלית</label>
					<input type="text" class="form-control" name="msg" required/>
				</div>
				<div class="form-group">
					<label for="exampleInputEmail1">תוכן ההודעה - עברית</label>
					<input type="text" class="form-control" name="msg_he" required/>
				</div>
				<div class="form-group text-right">
					<label for="screen_select">מסך</label>
					<select id="screen_select" class="form-control" name="screen">
						<option value="bets.opened">ניחושים פתוחים</option>
						{{--<option value="bets.closed">ניחושים סגורים</option>--}}
						<option value="highlight">תקציר</option>
					</select>
				</div>
				<div class="form-group text-right">
					<label for="league_select">ליגת</label>
					<select id="league_select" class="form-control" name="league_id">
						@foreach($leagues as $league)
							<option
								value="{{ $league->id }}" {!! $league->is_default ? 'selected="selected"' : '' !!}>{{ $league->name_he }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group text-right" style="display: none">
					<label for="game_select">משחק</label>
					<select id="game_select" class="form-control" name="game_id" disabled="disabled">
						<option>צריך לבחור ליגה</option>
					</select>
				</div>
				<div class="form-group text-right">
					<label for="show_ad">הצג פרסומת</label>
					<input type="checkbox" id="show_ad" name="show_ad" />
				</div>
				<br>
				<div class="form-group" style="width: 250px;">
					<input type="checkbox" id="insert-time-push">
					<label>הכנס תאריך לתיזמון</label>
					<div class='input-group date' id='datetimepicker1'>
						<input type='text' class="form-control date-picker text-right" dir="ltr" disabled="disabled"
						       name="date_to_send"/>
						<span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
					</div>
				</div>
				<div class="row"></div>
				<div class="form-group">
					<button class="btn btn-danger" name="send_in" value="now">שליחת הודעות מיידית</button>
					<button class="btn btn-success hidden" name="send_in" value="time">שליחת הודעות בתיזמון</button>
				</div>
			</form>
		</div>
		{{--@endif--}}
		@if(sizeof($allPushes)>0)
			<h3>הודעות ממתינות לשליחה</h3>
			<table class="table">
				<th>כותרת הדעה אנגלית</th>
				<th>כותרת הודעה עברית</th>
				<th>תוכן הודעה אנגלית</th>
				<th>תוכן הודעה עברית</th>
				<th>תאריך שליחה</th>
				<th>מחיקת הודעה</th>
				@foreach($allPushes as $push)
					<tr>
						<td>{{$push->title}}</td>
						<td>{{$push->title_he}}</td>
						<td>{{$push->msg}}</td>
						<td>{{$push->msg_he}}</td>
						<td><strong>{{date('H:i  d-m-Y', strtotime($push->time_to_send)) }}</strong></td>
						<td>
							<button class="btn btn-danger" type="submit" onclick="deletePush({{$push->id}})">Delete
							</button>
						</td>
					</tr>
				@endforeach
			</table>
		@endif
	</div>
	<script type="text/javascript">
		$(function () {
			$('#datetimepicker1').datetimepicker({
				format: 'YYYY-MM-DD HH:mm:ss',

			}).on('dp.show', function () {
				return $(this).data('DateTimePicker').defaultDate(new Date());
			});

			$('#screen_select').on('change', function () {
				var show_games = ($(this).val() == 'highlight');
				$('#game_select').prop('disabled', ! show_games).closest('.form-group').toggle(show_games);
				if(show_games) {
					$('#league_select').trigger('change');
				}
			});

			$('#league_select').on('change', function () {
				var league_id = $(this).val();
				var $game_select = $('#game_select');
				if (!$game_select.prop('disabled')) {
					$.ajax({
						url: '/json_games/' + league_id,
						success: function (match_weeks) {
							$game_select.find('optgroup, option').remove();
							for (w in match_weeks) {
								var $optgroup = $('<optgroup>');
								$optgroup.attr('label', match_weeks[w]['title_he']);
								var games = match_weeks[w]['game'];
								for (g in games) {
									var game = games[g];
									var $option = $('<option>').val(game['id']);
									$option.text(game.team_a.name_he + ' נגד ' + game.team_b.name_he + ' [' + game.game_date + ']');
									$optgroup.append($option);
								}
								$game_select.append($optgroup)
							}
						}
					});
				}
			});
			$('#league_select').trigger('change');

			$('#push_form').on('submit', function () {
				var route = {
					screen: $('#screen_select').val(),
					league_id: $('#league_select').val(),
					show_ad: $('#show_ad').prop('checked') ? 1 : 0
				}
				if(! $('#game_select').prop('disabled')) {
					route['game_id'] = $('#game_select').val();
				}
				route = JSON.stringify(route);
				$('input[name="push_route"]').val(route);
			})
		});
	</script>

	<script>

		function deletePush(id) {
			var url = '{{ route('deletePush') }}';
			var token = '{{ csrf_token() }}';
			$.ajax({
				method: 'POST',
				url: url,
				data: {idPush: id, _token: token}
			}).done(function (msg) {
				window.location.reload(true);
			})
		}
	</script>

@endsection
