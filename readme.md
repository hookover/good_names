# 三才五格取名分析入库
为了给自己宝宝取名字, 做得比较简单, 图个开心
先记录一下使用步骤, 方便后期再次完善开发, 以后再完善

原理: 先自己算出宝宝的五行,再根据五行字组合出几十万个备选名字, 再将计算这些名字的三才五格评分,最后用mysql语句统计自己想要的

## 使用需要具备
mysql 使用知识、 php基础知识、 laravel基础知识


#使用
1、找大师计算宝宝的生辰八字,找出缺少的五行属性

2、确定宝宝名字数量(目前这个代码仅支持2位名字)

3、比如宝宝缺水、需要补木, 那么可以到百度汉语将对应属性的文字复制到public目录文本中,如 水.txt

http://hanyu.baidu.com/s?wd=五行属土的字

4、下载laravel组件
    composer update
5、创建数据库
    php artisan migrate
    
6、修改app/Console/Commands脚本, 改文件路径,改姓

7、分析康熙字典
    php artisan names:getkangxi
    
8、生成待测试名字
    php artisan names:importnames
    
9、开始分析计算
    php artisan names:process
    
10、到数据库统计查询合适的名字
    
    select * from names where 
    (`sancai_jixiong`='吉' OR `sancai_jixiong`='半吉')
    AND 
    (`tiange_jixiong`='吉'  OR `tiange_jixiong`='半吉')
    AND
    (`renge_jixiong`=' 吉' OR `renge_jixiong`='半吉')
    AND 
    (`dige_jixiong`='吉'  OR `dige_jixiong`='半吉')
    AND
    (`waige_jixiong`='吉'  OR `waige_jixiong`='半吉')
    AND
    (`zongge_jixiong`='吉'  OR `zongge_jixiong`='半吉')
    AND
    (`shengxiao`!='忌' AND `shengxiao`!='忌忌')
    AND
    (`name_wuxing`='火水木'  OR `name_wuxing`='火木水')
    
    ORDER BY sancai_jixiong,tiange_jixiong,renge_jixiong,dige_jixiong,waige_jixiong,zongge_jixiong ASC
    ;
