<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class updateColumns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateColumns';

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
        //
        $rows = DB::table('mbd')->where('status', 0)->pluck('data', 'id')->toArray();
        foreach ($rows as $id => $data) {
            $arr = json_decode($data, true);
            $dataUpdate = [];
            $dataUpdate['userid'] = (string)$arr['userid']['id'];
            $dataUpdate['category'] = (string)$arr['category'];
            $dataUpdate['productname'] = (string)$arr['productname'];
            $dataUpdate['productsize'] = (string)$arr['productsize'];
            $dataUpdate['productprice'] = (string)$arr['productprice'];
            $dataUpdate['productversion'] = (string)$arr['productversion'];
            $dataUpdate['viewcount'] = (string)$arr['viewcount'];
            $dataUpdate['soldcount'] = (string)$arr['soldcount'];
            $dataUpdate['allincome'] = (string)$arr['allincome'];
            $dataUpdate['agreevalue'] = (string)$arr['agreevalue'];
            $dataUpdate['rank'] = (string)$arr['rank'];
            $dataUpdate['publishtime'] = (string)date('Y-m-d H:i:s', $arr['publishtime']);

            $dataUpdate['status'] = 1;

            DB::table('mbd')->where('id', $id)->update($dataUpdate);
            $this->info('id ' . $id . ' processed');
        }
        $this->info('all done');
    }
}
