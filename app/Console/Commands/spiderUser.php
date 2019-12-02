<?php

namespace App\Console\Commands;

use GuzzleHttp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class spiderUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spiderUser {minId} {maxId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'spider MBD user';
    protected $guzzleClient = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->guzzleClient = new GuzzleHttp\Client([
            'Connection' => 'close',
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
        ]);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $minId = $this->argument('minId');
        $maxId = $this->argument('maxId');
        if (!is_numeric($minId)) {
            $this->error('bad minId ' . $minId);
            return false;
        }
        if (!is_numeric($maxId)) {
            $this->error('bad maxId ' . $maxId);
            return false;
        }
        if ($minId > $maxId) {
            $this->error('minID is bigger than maxId');
            return false;
        }
        $startTime = time();
        $count = 0;

        for ($id = $minId; $id <= $maxId; $id++) {
            $count++;
            $nowTime = time();
            $avgCost = number_format(($nowTime - $startTime) / $count, 2, '.', '');
            $this->info($count . ' processing, avg time cost ' . $avgCost);
            try {
                $this->getUserInfo($id);

            } catch (\Exception $e) {
                $this->info($e->getMessage());
                $this->info($e->getTraceAsString());
                //$this->dingNotify('shuai 面包多机器人报Exception了,去看看吧');
            }
        }

    }

    public function getUserInfo($userId)
    {
        $row = DB::table('mbd_user')->where('mbd_user_id', $userId)->first();
        if (!empty($row) && $row->has_article == 1) {
            $this->info('user ' . $userId . ' has article ,skipping');
            return true;
        }
        $url = sprintf('https://x.mianbaoduo.com/api/products/?producttype=1,2&page=1&limit=20&productstates=1&ordering=-publishtime&userid=%s', $userId);
        $json = $this->fetchJson($url);
        $arr = GuzzleHttp\json_decode($json, true);
        //insert
        if (empty($row)) {
            $dataInsert = [
                'mbd_user_id' => $userId,
                'has_article' => '',
                'date' => '',
                'status' => 0,
            ];
            if (isset($arr['count']) && $arr['count'] > 0) {
                $dataInsert['has_article'] = 1;
                $dataInsert['date'] = date('Ymd');
            } else {
                $dataInsert['has_article'] = 0;
            }
            $this->info('inserting row ,has article value ' . $dataInsert['has_article']);
            DB::table('mbd_user')->insert($dataInsert);
        } else {
            $dataUpdate = [];
            if (isset($arr['count']) && $arr['count'] > 0) {
                $dataUpdate['has_article'] = 1;
                $dataUpdate['date'] = date('Ymd');
            } else {
                $dataUpdate['has_article'] = 0;
            }
            //update
            $this->info('updating row ,has article ' . $dataUpdate['has_article']);
            DB::table('mbd_user')->where('mbd_user_id', $userId)->update($dataUpdate);
        }
    }

    public function fetchJson($url)
    {
        $clinet = $this->guzzleClient;
        $header = [
            'User-Agent' => 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.70 Safari/537.36',
        ];

        return $clinet->request('GET', $url, ['headers' => $header])->getBody()->getContents();
    }

    public function dingNotify($msg)
    {
        $webhook = "https://oapi.dingtalk.com/robot/send?access_token=1614c3faa6c1bf292978867cdf4764e7124d281800dcb267854742a0744118ec";
        $data = array('msgtype' => 'text', 'text' => array('content' => $msg));
        $data_string = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $webhook);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 线下环境不用开启curl证书验证, 未调通情况可尝试添加该代码
        // curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}
