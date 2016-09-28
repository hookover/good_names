<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class IndexController extends Controller
{
    private $data;

    public function Index()
    {
        //搜索字
        preg_match('#『(.*?)』#',$this->data,$matches);
        if(isset($matches[1])) $res['char'] = $matches[1];

        //异体字
        preg_match('#<span class="b">异体字：</span>(<a[^>]*>([^<]+)</a>)+#',$this->data,$matches);
        if(isset($matches[0])) {
            preg_match_all('#<a[^>]*>([^<]+)</a>#',$matches[0],$matches);
            if(isset($matches[1])) $res['yiti'] = implode(',',$matches[1]);
        }

        //繁体字
        preg_match('#<span class="b">繁体字：</span><a[^>]*?>([^<]*?)</a>#', $this->data, $matches);
        if(isset($matches[1])) $res['fanti'] = $matches[1];

        //简体字
        preg_match('#<span class="b">简体字：</span><a[^>]*?>([^<]*?)</a>#', $this->data, $matches);
        if(isset($matches[1])) $res['jianti'] = $matches[1];

        //拼音
        preg_match('#<span class="b">拼音：</span><span class="pinyin">(.*?)</span>#',$this->data,$matches);
        if(isset($matches[1])) {
            preg_match_all('#[^>]+(?=<script>)#', $matches[1], $matches);
            if(isset($matches[0])) $res['pinying'] = implode(',', $matches[0]);
        }
        //注音
        preg_match('#<span class="b">注音：</span><span class="pinyin">(.*?)</span>#',$this->data,$matches);
        if(isset($matches[1])) {
            preg_match_all('#[^>]+(?=<script>)#', $matches[1], $matches);
            if(isset($matches[0])) $res['zhuying'] = implode(',', $matches[0]);
        }

        //简体部首,部首笔画,总笔画
        preg_match('#<span class="b">简体部首：</span>([^<]*?)<span class="b">部首笔画：</span>([^<]*?)<span class="b">总笔画：</span>([^<]*?)<br>#', $this->data, $matches);
        if(isset($matches[1])) $res['jianti_bushou'] = trim($matches[1], '　');
        if(isset($matches[2])) $res['jianti_bushou_bihua'] = trim($matches[2], '　');
        if(isset($matches[3])) $res['jianti_bihua'] = trim($matches[3], '　');

        //繁体部首,部首笔画,总笔画
        preg_match('#<span class="b">繁体部首：</span>([^<]*?)<span class="b">部首笔画：</span>([^<]*?)<span class="b">总笔画：</span>([^<]*?)<br />#', $this->data, $matches);
        if(isset($matches[1])) $res['fanti_bushou'] = trim($matches[1], '　');
        if(isset($matches[2])) $res['fanti_bushou_bihua'] = trim($matches[2], '　');
        if(isset($matches[3])) $res['fanti_bihua'] = trim($matches[3], '　');


        //没写繁体简体
        preg_match('#<span class="b">部首：</span>([^<]*?)<span class="b">部首笔画：</span>([^<]*?)<span class="b">总笔画：</span>([^<]*?)<br />#', $this->data, $matches);
        if((!isset($res['jianti_bushou'])) && (!isset($res['fanti_bushou']))) {
            if (isset($res['fanti']) || !isset($res['jianti'])) {
                if(isset($matches[1])) $res['jianti_bushou'] = trim($matches[1], '　');
                if(isset($matches[2])) $res['jianti_bushou_bihua'] = trim($matches[2], '　');
                if(isset($matches[3])) $res['jianti_bihua'] = trim($matches[3], '　');
            } else {
                if(isset($matches[1])) $res['fanti_bushou'] = trim($matches[1], '　');
                if(isset($matches[2])) $res['fanti_bushou_bihua'] = trim($matches[2], '　');
                if(isset($matches[3])) $res['fanti_bihua'] = trim($matches[3], '　');
            }
        }
        var_dump($res);exit;

        if((!isset($res['jianti']) && isset($res['fanti'])) || !isset($res['fanti']) && isset($res['char'])) {
            $res['jianti'] = $res['char'];
        }

        //康熙笔画
        preg_match('#<span class="b">康熙字典笔画</span>\((.*)\)#u',$this->data,$matches);
        if(isset($matches[1])) {
            preg_match_all('#([\x{4e00}-\x{9fa5}]+):(\d*)；#u', trim($matches[1], ' '), $matches);

            if(isset($matches[1])) $res['kangxizi'] = implode(',', $matches[1]);
            if(isset($matches[1])) $res['kangxi_bihua'] = implode(',', $matches[2]);
        }

        //输入法编码-五笔
        preg_match('#<span class="b">五笔86：</span>([^<]*?)<span class="b">五笔98：</span>([^<]*?)<span class="b">仓颉：</span>([^<]*?)<br />#',$this->data,$matches);
        if(isset($matches[1])) $res['wubi86'] = trim($matches[1], '　 ');
        if(isset($matches[1])) $res['wubi98'] = trim($matches[2], '　 ');
        if(isset($matches[1])) $res['cangjie'] = trim($matches[3], '　 ');

        //计算机编码
        preg_match('#<span class="b">四角号码：</span>([^<]*?)<span class="b">UniCode：</span>([^<]*?)<span class="b">规范汉字编号：</span>([^<]*?)<br />#',$this->data,$matches);
        if(isset($matches[1])) $res['sijiao'] = trim($matches[1], '　 ');
        if(isset($matches[1])) $res['unicode'] = trim($matches[2], '　');
        if(isset($matches[1])) $res['hanbian'] = trim($matches[3], '　');

        //汉字五行
        preg_match('#汉字五行：([\x{4e00}-\x{9fa5}]+)#u',$this->data,$matches);
        if(isset($matches[1])) $res['wuxing'] = $matches[1];

        //吉凶寓意
        preg_match('#吉凶寓意：([\x{4e00}-\x{9fa5}]+)#u',$this->data,$matches);
        if(isset($matches[1])) $res['jixiong'] = $matches[1];

        //是否为常用字
        preg_match('#是否为常用字：([\x{4e00}-\x{9fa5}]+)#u',$this->data,$matches);
        if(isset($matches[1])) $res['changyong'] = $matches[1];

        //姓名学
        preg_match('#姓名学：([^<]+)#',$this->data,$matches);
        if(isset($matches[1])) $res['xingmingxue'] = $matches[1];

        var_dump($res);
    }
    public function __construct()
    {
        $this->data = <<<EF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>康熙字典：霏,“霏”康熙字典笔画,繁体笔画,汉字五行_HttpCN</title>
<meta name="Description" content="网络中国提供“霏”的康熙字典意思解释、康熙字典笔画、康熙字典扫描原图、起名用汉字五行等。" />
<link href="/Css/ZiShow.css" rel="stylesheet" type="text/css" />
<link href="/Css/Include.css" rel="stylesheet" type="text/css" />
<script language="javascript" type="text/javascript" src="/Js/Function.js"></script>
<script language="javascript" type="text/javascript" src="/Js/Include.js"></script>
<script language="javascript" type="text/javascript" src="/js/prototype.js"></script>
<script language="javascript" type="text/javascript" src="/Js/zac.js"></script>
<script language="javascript" type="text/javascript" src="/js/tabpane.js"></script>
<style>.U970F{background:url(/Upload/Zi/KaiShu/39/970F.gif) no-repeat;width:100px;height:94px;}</style>
<script>var word="霏";var code="38671";</script>
</head>

<body>
<!--top-->
<script>Set_kangxi_show_top()</script>
<!--top /-->

<!--content-->
<div class="content">
<!--content_l-->
<div class="content_l">
<script>Setkangxileft();Setzileftad()</script>
</div>
<!--content_l /-->

<!--content_m /-->
<div class="content_m">
<div class="content_nav"><a href="/">首页</a> > <a href="/KangXi/">康熙字典</a> > 霏</div>
<script>Setzicenter_01();</script>
<div class="content_dh">
<table border="0" cellpadding="0" cellspacing="0" id="tab_zi">
<tr>
<td class="bg_86_1">康熙字典</td>
<td class="bg_86_2" onclick="location.href('/Html/KangXi/Pic/1375.shtml#霏')">扫描版</td>
<td class="bg_86_2" onclick="Set_soword('zi')">更多解释</td>
</tr>
</table>
</div>
<script>Setzicenter_02();</script>
<!--div_a1-->
<div id="div_a1" style="display:block ">
<table width="620" border="0" cellpadding="0" cellspacing="0">
<tr bgcolor="#FFFFFF">
<td width="100"><div id="zibg"><p class="U970F"></p></div></td>
<td width="510" style="padding-left:10px">
<p class="text15">
『霏』 <br />
<span class="b">拼音：</span><span class="pinyin">fēi<script>Setduyin('Duyin/fei1')</script></span>　<span class="b">注音：</span><span class="pinyin">ㄈㄟ<script>Setduyin('Duyin/fei1')</script></span><br />
<span class="b">部首：</span>雨　<span class="b">部首笔画：</span>8　<span class="b">总笔画：</span>16<br /><span class="b">康熙字典笔画</span>( 霏:16； )
</p></td>
</tr>
</table>
<p class="text16">
<span class="b">五笔86：</span>FDJD　 <span class="b">五笔98：</span>FHDD　 <span class="b">仓颉：</span>MBLMY　<br />
<span class="b">四角号码：</span>10111　 <span class="b">UniCode：</span>U+970F　<span class="b">规范汉字编号：</span>6077<br />
</p>
<p><br /><script>Setzicenter_03();</script><hr class="hr" /><br /></p>
<div class="text16"><span class="zi18b">◎ 民俗参考</span><br />汉字五行：水　吉凶寓意：吉　是否为常用字：是<br />姓名学：非姓氏<br /><br /><hr class="hr" /></div>
<div class="text16"><span class="zi18b">◎ 字形结构</span><br />[ <span class="b">首尾分解查字</span> ]：雨非(yufei)
　[ <span class="b">汉字部件构造</span> ]：雨非
<br />[ <span class="b">笔顺编号</span> ]：1452444421112111<br />
[ <span class="b">笔顺读写</span> ]：横捺折竖捺捺捺捺竖横横横竖横横横<br />
<br /><hr class="hr" /></div>
<div class="content16">
<span class="zi18b">◎ 康熙字典解释</span><br />
<strong style="background:#F6F0EF">【戌集中】【雨字部】　霏； 康熙笔画：16； 页码：<a href="/Html/KangXi/Pic/1375.shtml#霏" class="a16blue" target="_blank"><span style="font-weight:300">页1375第19(点击查看原图)</span></a></strong><br />〔古文〕䬠【唐韻】芳非切【集韻】【韻會】【正韻】芳微切，𠀤音菲。【說文】雨雪貌。从雨非聲。【集韻】雰也。【詩·邶風】雨雪其霏。【傳】霏，甚貌。又【小雅】雨雪霏霏。
</div>
<div class="text16"><hr class="hr" /><span class="zi18b">◎ 音韵参考</span><br />[ <span class="b">上古音</span> ]：微部帮母,piu?i<br />[ <span class="b">广　韵</span> ]：芳非切,上平8微,fēi,止合三平微滂<br />[ <span class="b">平水韵</span> ]：上平五微<br />[ <span class="b">粤　语</span> ]：fei1<script>Setduyin('Yueyin/fei1')</script><br />[ <span class="b">闽南语</span> ]：hui1<br /><br /></div>
<div class="text16"><hr class="hr" /><span class="zi18b">◎ 索引参考</span><br />[ <span class="b">古文字诂林</span> ]：09册，第14部，雨部，48<br />[ <span class="b">故训彙纂</span> ]：2459|2537.8<br />[ <span class="b">说文解字</span> ]：编号7532,第11卷下,雨部第48字<br />[ <span class="b">康熙字典</span> ]：<a href="/Html/KangXi/Pic/1375.shtml#霏" class="a16blue" target="_blank">页1375第19(点击查看原图)</a><br /><br /></div>
</div>
<!--div_a1 /-->

<div class="body_info">
<script>Setzicenter_04();</script>
<hr class="ShowLine">
<script>SetCopy();</script>
【<a href='javascript:SetShcang();'>收藏本页</a>】
【<a href="javascript:window.print()">打印</a>】
【<a href="javascript:self.close()">关闭</a>】
【<a href="#top">顶部</a>】
</div>
</div>
<!--content_m /-->

<!--content_r-->
<div class="content_r">
<script>Setziright();</script>
</div>
<!--content_r /-->
</div>
<!--content /-->

<!--bottom-->
<script>Setdown()</script>
<!--bottom /-->
</body>
</html>
<noscript><iframe src="/"></iframe></noscript>
<script>Settongji();Setcount('kangxi',code)</script>
EF;
    }
}
