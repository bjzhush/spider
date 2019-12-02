<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp;
use Illuminate\Support\Facades\DB;


class spiderUserArticle extends Command
{
    const STATUS_NEW = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_EXCEP = 2;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spiderUserArticle';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
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
        while (true) {
            try {
                $row = DB::table('mbd_user')->where('status', self::STATUS_NEW)->first();
                if (is_null($row)) {
                    $this->info('no task to process,sleeping');
                    sleep(10);
                    continue;
                }
                if ($row->has_article == 0){
                    $this->info('skipping ,for no article for user ' . $row->mbd_user_id);
                    DB::table('mbd_user')->where('mbd_user_id', $row->mbd_user_id)->update(['status' => self::STATUS_SUCCESS]);
                    continue;
                }
                $this->info('start to spider user ' . $row->mbd_user_id);
                $this->spiderUser($row->mbd_user_id);
                DB::table('mbd_user')->where('mbd_user_id', $row->mbd_user_id)->update(['status' => self::STATUS_SUCCESS]);

            } catch (\Exception $e) {
                $this->info($e->getMessage());
                $this->info($e->getTraceAsString());
                if (isset($row) && !is_null($row->mbd_user_id)) {
                    DB::table('mbd_user')->where('mbd_user_id', $row->mbd_user_id)->update(['status' => self::STATUS_EXCEP]);
                }

            }
        }
    }

    public function spiderUser($userId)
    {
        $rowCount = 0;
        $hasNext = true;
        $url = sprintf('https://x.mianbaoduo.com/api/products/?producttype=1,2&page=1&limit=20&productstates=1&ordering=-publishtime&userid=%s', $userId);
        while ($hasNext) {
            try {
                $data = $this->fetchJson($url);

                $arr = GuzzleHttp\json_decode($data, true);
                $rows = $arr['results'];
                $rowCount += count($rows);
                $this->info($rowCount . ' rows processing');
                $rows = $arr['results'];
                $this->insertData($rows);

                if (strlen($arr['next'])) {
                    $hasNext = true;
                    $url = $arr['next'];
                } else {
                    $hasNext = false;
                    $url = '';
                }

            } catch (\Exception $e) {
                $this->info($e->getMessage());
                $this->info($e->getTraceAsString());
                $this->dingNotify('shuai 面包多SpiderUserArticle 报Exception了,去看看吧');
            }

        }

    }

    public function insertData($rows)
    {
        foreach ($rows as $row) {
            $rowCheck = DB::table('mbd_user_data')->where('date', date('Ymd'))->where('mbd_id', $row['id'])->first();
            $data = [];
            $data['mbd_id'] = $row['id'];
            $data['date'] = date('Ymd');
            $data['data'] = json_encode($row);

            $data['userid'] = $row['userid']['id'];
            $data['producttype'] = $row['producttype'];
            $data['productname'] = $row['productname'];
            $data['productsize'] = isset($row['productsize']) ? $row['productsize'] : '';
            $data['productprice'] = $row['productprice'];
            $data['productversion'] = $row['productversion'];
            $data['viewcount'] = $row['viewcount'];
            $data['soldcount'] = $row['soldcount'];
            $data['allincome'] = $row['allincome'];
            $data['agreevalue'] = $row['agreevalue'];
            $data['rank'] = $row['rank'];
            $data['publishtime'] = $row['publishtime'];

            if (empty($rowCheck)) {
                $this->info('updating id ' . $row['id']);
                DB::table('mbd_user_data')->insert($data);

            } else {
                $this->info('updating id ' . $row['id']);
                DB::table('mbd')
                    ->where('mbd_id', $row['id'])
                    ->where('date', date('Ymd'))
                    ->update([
                        'data' => $data,
                    ]);
            }
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
