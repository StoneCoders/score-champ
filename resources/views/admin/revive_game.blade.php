@extends('layout')

@section('content')
    <div class="container">
        @if(isset($error))
            <div class="alert alert-danger" role="alert">{{ $error }}</div>
        @endif
        @if(isset($message))
            <div class="alert alert-success" role="alert">{{ $message }}</div>
        @endif
        <div class="jumbotron">
            <form method="post" action="{{ route('cancelGameFinished') }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                @if ($games->count())
                    <div class="form-group text-right">
                        <label for="prevent_bet_minutes_before_game">בחר משחק</label>
                        <select name="game_id" class="form-control">
                            @foreach($games as $game)
                                <option value="{{ $game->id }}">{{ $game->team_a->name }} נגד {{ $game->team_b->name }} בתאריך {{ $game->game_date }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">בטל סיום משחק</button>
                @else
                    <p>לא קיימים משחקים שהסתיימו</p>
                @endif
            </form>
        </div>
    </div>
@endsection
