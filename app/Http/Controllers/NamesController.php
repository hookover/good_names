<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use DB;
use App\Model\Kangxi as KangxiModel;
use App\Model\Name;
use App\Model\Task;

class NamesController extends Controller
{
    private $mu = [];
    private $shui = [];
    private $names_total = 0;
    public function index()
    {
        $this->loadNames();
//      $this->runing();
        $chen = KangxiModel::where('char', '=', '陈')->first();
        $middlename = KangxiModel::where('char', '=', '华')->first();
        $lastname = KangxiModel::where('char', '=', '评')->first();
        $this->analyze($chen, $middlename, $lastname);
        $this->analyze($chen, $lastname, $middlename);
    }

    public function runing()
    {
        $bar = $this->output->createProgressBar($this->names_total);
        $chen = KangxiModel::where('char', '=', '陈')->first();

        foreach ($this->mu as $mu) {
            foreach ($this->shui as $shui) {
                $middlename = KangxiModel::where('char', '=', $mu)->first();
                $lastname = KangxiModel::where('char', '=', $shui)->first();
                $this->analyze($chen, $middlename, $lastname);
                $this->analyze($chen, $lastname, $middlename);
                print_r("\t" . '陈' . $middlename->char . $lastname->char . " && 陈" . $lastname->char . $middlename->char);

                usleep(200000);
                $bar->advance();
            }
        }

        $bar->finish();
    }
    private function analyze($xing, $middlename, $lastname)
    {
        $name = new Name();
        $name->name = $xing->char . $middlename->char . $lastname->char;

        //汉字笔画
        $bihua_xing = $this->getBiHua($xing);
        $bihua_middlename = $this->getBiHua($middlename);
        $bihua_lastname = $this->getBiHua($lastname);

        if(!$bihua_xing || !$bihua_middlename || !$bihua_lastname) {
            //计入失败名字
            echo $bihua_xing, '--', $bihua_middlename, '--', $bihua_lastname;
            return false;
        }
        echo $bihua_xing, '--', $bihua_middlename, '--', $bihua_lastname;

        //各格
        $name->tiange = $bihua_xing + 1;                        //天格
        $name->renge = $bihua_xing + $bihua_middlename;         //人格
        $name->dige = $bihua_middlename + $bihua_lastname;      //地格
        $name->zongge = $bihua_xing + $bihua_middlename + $bihua_lastname;  //总格
        $name->waige = $name->zongge - $name->renge + 1;            //外格

        //吉凶
        $name->tiange_jixiong = $this->shuli($name->tiange);
        $name->renge_jixiong = $this->shuli($name->renge);
        $name->dige_jixiong = $this->shuli($name->dige);
        $name->waige_jixiong = $this->shuli($name->waige);
        $name->zongge_jixiong = $this->shuli($name->zongge);

        //五行
        $name->tiange_wuxing = $this->wuxing($name->tiange)['wuxing'];
        $name->renge_wuxing = $this->wuxing($name->renge)['wuxing'];
        $name->dige_wuxing = $this->wuxing($name->dige)['wuxing'];
        $name->waige_wuxing = $this->wuxing($name->waige)['wuxing'];
        $name->zongge_wuxing = $this->wuxing($name->zongge)['wuxing'];

        //阴阳
        $name->tiange_yinyang = $this->wuxing($name->tiange)['yinyang'];
        $name->renge_yinyang = $this->wuxing($name->renge)['yinyang'];
        $name->dige_yinyang = $this->wuxing($name->dige)['yinyang'];

        //三才
        $name->sancai = $name->tiange_wuxing . $name->renge_wuxing . $name->dige_wuxing;
        $name->sancai_jixiong = $this->sancai($name->sancai);

        //生肖禁忌
        $name->shengxiao = $this->jingji($this->getBushou($middlename)) .  $this->jingji($this->getBushou($lastname));

        //数据库五行与文本五行是否匹配
        if(($middlename->wuxing != '水') || ($middlename->wuxing != '木')) {
            $name->zhongzi = '否';
        } else {
            $name->zhongzi = '是';
        }
        if(($lastname->wuxing != '水') || ($lastname->wuxing != '木')) {
            $name->sanzi = '否';
        } else {
            $name->sanzi = '是';
        }

        if($name->save()) {
            if(isset($this->info) && function_exists($this->info)) {
                $this->info(
                    "\t" . $name->name . 'OK'
                );
            } else {
                echo '成功';
            }
        } else {
            if(isset($this->info) && function_exists($this->info)) {
                $this->info(
                    "\t" . $name->name . 'FAILED'
                );
            } else {
                echo '失败';
            }
        }
    }

    private function getBushou($charModel)
    {
        if($charModel->jianti_bushou) {
            return $charModel->jianti_bushou;
        }
        if($charModel->fanti_bushou) {
            return $charModel->fanti_bushou;
        }
        if($charModel->bushou) {
            return $charModel->bushou;
        }
        return '';
    }
    /**
     * @param $charModel
     * 获取笔画
     */
    private function getBiHua($charModel)
    {
        //处理康熙笔画
        if($charModel->kangxi_bihua) {
            $kangxi_bihua_arr = explode(',', $charModel->kangxi_bihua);

            if(count($kangxi_bihua_arr) == 1) {
                return trim($charModel->kangxi_bihua);
            }

            if(count($kangxi_bihua_arr) > 1) {
                $kangxizi_arr = explode(',', $charModel->kangxizi);
                for($i = 0; $i<count($kangxizi_arr); ++$i) {
                    if($charModel->char == $kangxizi_arr[$i]) {
                        return trim($kangxi_bihua_arr[$i]);
                    }
                }
                return $kangxi_bihua_arr[0];
            }
        }

        //如果没有康熙笔画
        //使用简体笔画
        if($charModel->jianti_bushou_bihua) {
            return $charModel->jianti_bushou_bihua;
        }

        //使用繁体笔画
        if($charModel->fanti_bihua) {
            return $charModel->fanti_bihua;
        }

        //使用笔画
        if($charModel->bihua) {
            return $charModel->bihua;
        }

        return false;   //这个情况不会出现,除非数据错误
    }

    private function loadNames()
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
        $this->shui = [];
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
        $this->names_total = count($this->mu) * count($this->shui);
//        $this->info(
//            '载入备选木属性文字:' . count($this->mu) . '个, 水属性文字:' . count($this->shui) .'个, 即将开始生成' . $this->names_total .'*2 个名字'
//        );
    }
    private function jingji($bushou)
    {
        $goods = ['禾','玉','豆','米','田','山','月','人'];
        $lows = ['石','口','冖','纟','刀','力','皮','犭'];

        $flag = '';
        if(in_array($bushou, $goods)) {
            $flag = '宜';
        }
        if(in_array($bushou, $lows)) {
            $flag = '忌';
        }
        return $flag;
    }
    private function wuxing($num)
    {
        $num = substr($num, -1);
        if($num == 0) {
            $num = 10;
        }
        $wuxing = null;
        if($num == 1 || $num == 2) {
            $wuxing = '木';
        }
        if($num == 3 || $num == 4) {
            $wuxing = '火';
        }
        if($num == 5 || $num == 6) {
            $wuxing = '土';
        }
        if($num == 7 || $num == 8) {
            $wuxing = '金';
        }
        if($num == 9 || $num == 10) {
            $wuxing = '水';
        }

        return [
            'wuxing'=>$wuxing,
            'yinyang'=>($num%2 == 0) ? '阴' : '阳'
        ];
    }
    private function shuli($num)
    {
        $tops = [1,3,5,6,7,11,13,15,16,21,23,24,29,31,32,33,35,37,41,45,47,48,52,57,61,63,65,67,68,81];
        $middle = [8,17,18,25,30,36,38,39,49,50,51,55,58,71,72,73,77];

        if(in_array($num, $tops)) {
            return '吉';
        }
        if(in_array($num, $middle)) {
            return '半吉';
        }
        return '凶';
    }
    private function sancai($name_wuxing)
    {
        $tops = [
            '木木木','木木火','木木土','木火木','木火土','木水木',
            '火木木','火木火','火木土','火火木','火土火','火土土',
            '土火木','土火土','土土火','土土土','土土金','土金土','土金金',
            '金土土','金土金','金金土','金水金',
            '水木木','水木土','水金土',
        ];
        $middle = [
            '木火火','木土土',
            '火火火','火土木',
            '土木木','土木火','土火火','土火金',
            '金木土','金土火','金金水',
            '水木火','水金金','水水金',
        ];

        if(in_array($name_wuxing, $tops)) {
            return '吉';
        }
        if(in_array($name_wuxing, $middle)) {
            return '半吉';
        }
        return '凶';
    }
}
