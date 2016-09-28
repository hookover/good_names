<?php

namespace App\Console\Commands;

use App\Model\task;
use Illuminate\Console\Command;
use DB;

class Kangxi extends Command
{
    private $cookie_jar;
    private $ch;
    private $time_range = [3,10];   //等待时间范围,防被封
    private $kangxi_url = 'http://tool.httpcn.com/KangXi/So.asp';
    private $xinhuan_url = 'http://tool.httpcn.com/Zi/So.asp';  //?Tid=1&wd=%E7%8E%8B
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'names:getkangxi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '抓取康熙字典入库';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->cookie_jar = storage_path('pic.cookie');
        $this->ch = curl_init();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->doit();
    }

    private function doit()
    {
        $tasks = Task::where('status', '!=', '1')->get();
        $bar = $this->output->createProgressBar(count($tasks));

        foreach ($tasks as $task) {
            if($this->KangxiTask($task->char)){
                $task->status = 1;  //成功
                $task->save();
                $this->info("\t" . '康熙字 ' . $task->char . ' 入库成功!');
            } else if($this->XinhuaTask($task->char)) {    //康熙字体没有的时候,用新华字典尝试
                $task->status = 1;  //成功
                $task->save();
                $this->info("\t" . '康熙字 ' . $task->char . ' 入库失败!');
            }
            $bar->advance();
            sleep(random_int($this->time_range[0],$this->time_range[1]));
        }
        $bar->finish();
    }

    private function KangxiTask($char)
    {
        $flag = false;
        if($res = $this->curl_post_302($this->kangxi_url, 'Tid=1&wd=' . $char)) {
            if($data = $this->curl_get($res)) {
                if( $arr = $this->analyze($data)) {
                    if(isset($arr['char']) && $arr['char'] != '') {
                        DB::table('kangxis')->insert($arr);
                        $flag = true;
                    }
                }
            }
        }
        return $flag;
    }
    private function XinhuaTask($char)
    {
        $flag = false;
        if($res = $this->curl_post_302($this->xinhuan_url, 'Tid=1&wd=' . $char)) {
            if($data = $this->curl_get($res)) {
                if( $arr = $this->analyze($data)) {
                    if(isset($arr['char']) && $arr['char'] != '') {
                        DB::table('kangxis')->insert($arr);
                        $flag = true;
                    }
                }
            }
        }
        return $flag;
    }

    private function analyze($data)
    {
        $res = [];
        //搜索字
        preg_match('#『(.*?)』#',$data,$matches);
        if(isset($matches[1])) $res['char'] = $matches[1];

        //异体字
        preg_match('#<span class="b">异体字：</span>(<a[^>]*>([^<]+)</a>)+#',$data,$matches);
        if(isset($matches[0])) {
            preg_match_all('#<a[^>]*>([^<]+)</a>#',$matches[0],$matches);
            if(isset($matches[1])) $res['yiti'] = implode(',',$matches[1]);
        }

        //繁体字
        preg_match('#<span class="b">繁体字：</span><a[^>]*?>([^<]*?)</a>#', $data, $matches);
        if(isset($matches[1])) $res['fanti'] = $matches[1];

        //简体字
        preg_match('#<span class="b">简体字：</span><a[^>]*?>([^<]*?)</a>#', $data, $matches);
        if(isset($matches[1])) $res['jianti'] = $matches[1];

        //拼音
        preg_match('#<span class="b">拼音：</span><span class="pinyin">(.*?)</span>#',$data,$matches);
        if(isset($matches[1])) {
            preg_match_all('#[^>]+(?=<script>)#', $matches[1], $matches);
            if(isset($matches[0])) $res['pinying'] = implode(',', $matches[0]);
        }
        //注音
        preg_match('#<span class="b">注音：</span><span class="pinyin">(.*?)</span>#',$data,$matches);
        if(isset($matches[1])) {
            preg_match_all('#[^>]+(?=<script>)#', $matches[1], $matches);
            if(isset($matches[0])) $res['zhuying'] = implode(',', $matches[0]);
        }

        //搜索字
        preg_match('#『(.*?)』#',$data,$matches);
        if(isset($matches[1])) $res['jianti'] = $matches[1];

        //简体部首,部首笔画,总笔画
        preg_match('#<span class="b">简体部首：</span>([^<]*?)<span class="b">部首笔画：</span>([^<]*?)<span class="b">总笔画：</span>([^<]*?)<br>#', $data, $matches);
        if(isset($matches[1])) $res['jianti_bushou'] = trim($matches[1], '　');
        if(isset($matches[2])) $res['jianti_bushou_bihua'] = trim($matches[2], '　');
        if(isset($matches[3])) $res['jianti_bihua'] = trim($matches[3], '　');

        //繁体部首,部首笔画,总笔画
        preg_match('#<span class="b">繁体部首：</span>([^<]*?)<span class="b">部首笔画：</span>([^<]*?)<span class="b">总笔画：</span>([^<]*?)<br />#', $data, $matches);
        if(isset($matches[1])) $res['fanti_bushou'] = trim($matches[1], '　');
        if(isset($matches[2])) $res['fanti_bushou_bihua'] = trim($matches[2], '　');
        if(isset($matches[3])) $res['fanti_bihua'] = trim($matches[3], '　');

        //没写繁体简体
        preg_match('#<span class="b">部首：</span>([^<]*?)<span class="b">部首笔画：</span>([^<]*?)<span class="b">总笔画：</span>([^<]*?)<br />#', $data, $matches);
        if(isset($matches[1])) $res['bushou'] = trim($matches[1], '　');
        if(isset($matches[2])) $res['bushou_bihua'] = trim($matches[2], '　');
        if(isset($matches[3])) $res['bihua'] = trim($matches[3], '　');
        
        if((!isset($res['jianti']) && isset($res['fanti'])) || !isset($res['fanti']) && isset($res['char'])) {
            $res['jianti'] = $res['char'];
        }

        //康熙笔画
        preg_match('#<span class="b">康熙字典笔画</span>\((.*)\)#u',$data,$matches);
        if(isset($matches[1])) {
            preg_match_all('#([\x{4e00}-\x{9fa5}]+):(\d*)；#u', trim($matches[1], ' '), $matches);

            if(isset($matches[1])) $res['kangxizi'] = implode(',', $matches[1]);
            if(isset($matches[1])) $res['kangxi_bihua'] = implode(',', $matches[2]);
        }

        //输入法编码-五笔
        preg_match('#<span class="b">五笔86：</span>([^<]*?)<span class="b">五笔98：</span>([^<]*?)<span class="b">仓颉：</span>([^<]*?)<br />#',$data,$matches);
        if(isset($matches[1])) $res['wubi86'] = trim($matches[1], '　 ');
        if(isset($matches[1])) $res['wubi98'] = trim($matches[2], '　 ');
        if(isset($matches[1])) $res['cangjie'] = trim($matches[3], '　 ');

        //计算机编码
        preg_match('#<span class="b">四角号码：</span>([^<]*?)<span class="b">UniCode：</span>([^<]*?)<span class="b">规范汉字编号：</span>([^<]*?)<br />#',$data,$matches);
        if(isset($matches[1])) $res['sijiao'] = trim($matches[1], '　 ');
        if(isset($matches[1])) $res['unicode'] = trim($matches[2], '　');
        if(isset($matches[1])) $res['hanbian'] = trim($matches[3], '　');

        //汉字五行
        preg_match('#汉字五行：([\x{4e00}-\x{9fa5}]+)#u',$data,$matches);
        if(isset($matches[1])) $res['wuxing'] = $matches[1];

        //吉凶寓意
        preg_match('#吉凶寓意：([\x{4e00}-\x{9fa5}]+)#u',$data,$matches);
        if(isset($matches[1])) $res['jixiong'] = $matches[1];

        //是否为常用字
        preg_match('#是否为常用字：([\x{4e00}-\x{9fa5}]+)#u',$data,$matches);
        if(isset($matches[1])) $res['changyong'] = $matches[1];

        //姓名学
        preg_match('#姓名学：([^<]+)#',$data,$matches);
        if(isset($matches[1])) $res['xingmingxue'] = $matches[1];

        return $res;
    }
    private function curl_post_302($url, $vars) {
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $vars);  //Tid=1&wd=%E5%BC%A0
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, $this->cookie_jar);

        $data = curl_exec($this->ch);
        $headers = curl_getinfo($this->ch);

        if($data != $headers) {
            return trim($headers['url'], '%');
        } else {
            return false;
        }
    }
    private function curl_get($url) {
        //设置抓取的url
        curl_setopt($this->ch, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($this->ch, CURLOPT_HEADER, 1);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        //执行命令
        $data = curl_exec($this->ch);
        //显示获得的数据
        return $data;
    }
}
