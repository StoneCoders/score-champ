<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\SchedulerPush;

class SchedulerPushes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedulerPushesToPushes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $dataNow =  date('Y-m-d H:i:s');
        $scheduler_pushes = SchedulerPush::where('time_to_send', '<', $dataNow)->where('sent_to_pushes_table', false)->get();
        foreach ($scheduler_pushes as $row){
            \App\Models\Push::create([
                'type'     => $row->type,
                'title_he' => $row->title_he,
                'title'    => $row->title,
                'msg_he'   => $row->msg_he,
                'msg'      => $row->msg,
	              'route'    => $row->route
            ]);

            $row->sent_to_pushes_table =true;
            $row->save();
        }
    }
}
