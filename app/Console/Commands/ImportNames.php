<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Illuminate\Database\QueryException;

class ImportNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'names:importnames';

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
        $mu = fopen(public_path('木.txt'), 'r');
        $arr_mu = [];
        while (! feof($mu)) {
            $line = fgets($mu);
            $line = trim($line, '　　');
            $line = trim($line, '  ');
            $line = trim($line);
            if($line != '') {
                $arr = explode('　', $line);
                $arr_mu = array_merge($arr_mu,$arr);
            }
        }

        $shui = fopen(public_path('水.txt'), 'r');
        $arr_shui = [];
        while (! feof($shui)) {
            $line = fgets($shui);
            $line = trim($line, '　　');
            $line = trim($line, '  ');
            $line = trim($line);
            if($line != '') {
                $arr = explode('　', $line);
                $arr_shui = array_merge($arr_shui, $arr);
            }
        }

        $shui_2 = fopen(public_path('水2.txt'), 'r');
        while(! feof($shui_2)) {
            $line = fgets($shui_2);
            if($line != '') {
                $arr = explode(' ', $line);
                foreach ($arr as $item) {
                    if(!in_array($item, $arr_shui)) {
                        $arr_shui[] = $item;
                    }
                }
            }
        }

        $mu_2 = fopen(public_path('木2.txt'), 'r');
        while(! feof($mu_2)) {
            $line = fgets($mu_2);
            if($line != '') {
                $arr = explode(' ', $line);
                foreach ($arr as $item) {
                    if(!in_array($item, $arr_mu)) {
                        $arr_mu[] = $item;
                    }
                }
            }
        }

        $arr = array_merge(['陈'], $arr_mu, $arr_shui);





        $bar = $this->output->createProgressBar(count($arr));
        foreach ($arr as $item) {
            try{
                $res = DB::table('tasks')->insert(['char'=>$item]);
                if($res) {
                    $this->info("\t" . '插入' . $item . '成功!');
                } else {
                    $this->info("\t" . '插入' . $item . '失败!');
                }
            } catch (QueryException $e){
                $this->info("\t" . '插入' . $item . '失败!');
            }

            $bar->advance();
        }
        $bar->finish();


//        $res =  DB::table('tasks')->insert($dbArr);
//
//        if($res) {
//            echo 'OK';
//        } else {
//            echo 'Failed';
//        }
    }
}
