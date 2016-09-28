<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class OtherController extends Controller
{
    private $mu = [];
    private $shui = [];
    private $names_total = 0;
    public function index()
    {
        $mu = fopen(public_path('木.txt'), 'r');
        while (! feof($mu)) {
            $line = fgets($mu);
            $line = trim($line, '　　');
            $line = trim($line, '  ');
            $line = trim($line);
            if($line != '') {
                $arr = explode('　', $line);
                $this->mu = array_merge($this->mu,$arr);
            }
        }

        $shui = fopen(public_path('水.txt'), 'r');
        while (! feof($shui)) {
            $line = fgets($shui);
            $line = trim($line, '　　');
            $line = trim($line, '  ');
            $line = trim($line);
            if($line != '') {
                $arr = explode('　', $line);
                $this->shui = array_merge($this->shui, $arr);
            }
        }

        $shui_2 = fopen(public_path('水2.txt'), 'r');
        while(! feof($shui_2)) {
            $line = fgets($shui_2);
            if($line != '') {
                $arr = explode(' ', $line);
                foreach ($arr as $item) {
                    if(!in_array($item, $this->shui)) {
                        $this->shui[] = $item;
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
                    if(!in_array($item, $this->mu)) {
                        $this->mu[] = $item;
                    }
                }
            }
        }


        $this->names_total = count($this->mu) * count($this->shui);

        echo '载入备选木属性文字:' . count($this->mu) . '个, 水属性文字:' . count($this->shui) .'个, 即将开始生成' . $this->names_total .'*2 个名字';
//        $this->info(
//            '载入备选木属性文字:' . count($this->mu) . '个, 水属性文字:' . count($this->shui) .'个, 即将开始生成' . $this->names_total .'*2 个名字'
//        );
    }

    public function test()
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

        $dbArr = [];
        foreach ($arr as $item) {
            $dbArr[] = ['char'=>$item];
        }

        var_dump($dbArr);
    }

    public function p()
    {
        $mu = fopen(public_path('水2_.txt'), 'r');
        $arr_mu = [];
        while(! feof($mu)) {
            $line = fgets($mu);
            if($line != '') {
                $line = trim($line);
                $arr = explode(' ', $line);
                foreach ($arr as $item) {
                    if(!in_array($item, $arr_mu)) {
                        $arr_mu[] = $item;
                    }
                }
            }
        }

        $i = 0;
        foreach ($arr_mu as $item) {
           $i ++;
            echo $item . ' ';
            if($i == 30) {
                echo '<br/>';
                $i = 0;
            }
        }

    }
}
