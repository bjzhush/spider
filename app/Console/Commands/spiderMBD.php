<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use GuzzleHttp;

class spiderMBD extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spiderMBD';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
        $rowCount = 0;
        $page = 1;
        $hasNext = true;
        $url = sprintf('https://x.mianbaoduo.com/api/products/?page=%s&limit=20&productstates=1&ordering=-rank&noshow=0', $page);
        $this->info('next url ' . $url);
        while ($hasNext) {
            $data = $this->fetchJson($url);

            $arr = GuzzleHttp\json_decode($data, true);

            $rows = $arr['results'];
            $rowCount += count($rows);
            $this->info($rowCount . ' rows processing');
            foreach ($rows as $row) {
                $mbdId = $row['id'];
                $rowData = json_encode($row);
                $rowCheck = DB::table('mbd')->where('date', date('Ymd'))->where('mbd_id', $mbdId)->first();
                if (empty($rowCheck)) {
                    $this->info('inserting id ' . $mbdId);
                    DB::table('mbd')->insert(
                        [
                            'mbd_id' => $mbdId,
                            'data' => $rowData,
                            'date' => date('Ymd'),
                        ]
                    );

                } else {
                    $this->info('updating id ' . $mbdId);
                    DB::table('mbd')
                        ->where('mbd_id', $mbdId)
                        ->where('date', date('Ymd'))
                        ->update([
                            'data' => $rowData,
                        ]);
                }
            }
            if (strlen($arr['next'])) {
                $hasNext = true;
                $url = $arr['next'];
            } else {
                $hasNext = false;
                $url = '';
            }
            $this->info('sleeping 3 to avoid ban');
            sleep(3);
        }

        $this->info('all finished');

        $this->info('start to call updateColumns');
        $this->call('updateColumns');

    }

    public function fetchJson($url)
    {
        $clinet = new GuzzleHttp\Client();
        $header = [
            'User-Agent' => 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.70 Safari/537.36',
        ];

        return $clinet->request('GET', $url, ['headers' => $header])->getBody()->getContents();
    }
}
