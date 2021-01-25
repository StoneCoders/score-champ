<?php

namespace App\Http\Controllers;
use App\Models\Push;
use App\Models\League;
use App\Models\MatchWeek;
use App\Models\SchedulerPush;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Models\PushToken;
use App\Models\Game;
use App\Models\User;

class PushController extends Controller
{
    public function showPush($sent = FALSE)
    {
        return view('admin/push', [
            'pushType' => request()->get('pushType', 'all'),
            'sent'     => (bool) $sent,
            'leagues' => League::where('is_active', TRUE)->get(),
            'allPushes' => SchedulerPush::orderBy('time_to_send','ASC')->where('sent_to_pushes_table', false)->get()
        ]);
    }

    public function sendPush(Request $request)
    {
        $send_in = $request->get('send_in');
        switch ($send_in)
        {
            case 'now':
                Push::create([
                    'type'     => $request->get('pushType', 'all'),
                    'title_he' => $request->get('title_he'),
                    'title'    => $request->get('title'),
                    'msg_he'   => $request->get('msg_he'),
                    'msg'      => $request->get('msg'),
                    'route'    => $request->get('push_route'),
                ]);
                break;
            case 'time':
                SchedulerPush::create([
                    'type'     => $request->get('pushType', 'all'),
                    'title_he' => $request->get('title_he'),
                    'title'    => $request->get('title'),
                    'msg_he'   => $request->get('msg_he'),
                    'msg'      => $request->get('msg'),
                    'route'    => $request->get('push_route'),
                    'sent_to_pushes_table' => 0,
                    'time_to_send' => $request->get('date_to_send'),
                ]);
                break;
        }

        return redirect()->route('push', [ 'sent' => TRUE ]);
    }

    public function deletePush(Request $request)
    {
        SchedulerPush::where('id', $request['idPush'])->delete();
        return response()->json(['delete' =>'true']);
    }

}
