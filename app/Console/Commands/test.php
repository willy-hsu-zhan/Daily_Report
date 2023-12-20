<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class test extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $data = [
            'grant_type' => 'client_credentials',
            'client_id' => '61023021l-f319abb5-b546-43e4',
            'client_secret' => '066bfb65-8b1b-43db-a082-bd7a0d02cd60'
        ];

        $response = Http::asForm()->post('https://tdx.transportdata.tw/auth/realms/TDXConnect/protocol/openid-connect/ten', $data);
        dd($response->failed());
        dd($response->body());
        
        $access_token = json_decode($response->body())->access_token;
        //dd($access_token);

        $base_url = 'https://tdx.transportdata.tw/api/basic';
        $action = '/v2/Bus/RealTimeByFrequency/City/';

        $url = $base_url . $action . 'Hsinchu';
        //Hsinchu
        $response = Http::withToken($access_token)->get($url);
        //$response = Http::get($url);

        dd(json_decode($response->body()));
    }
}
