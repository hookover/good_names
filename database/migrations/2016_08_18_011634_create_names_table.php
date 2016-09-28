<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('names', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 5)->unique();    //姓名
            $table->char('name_wuxing', 3);         //姓名五行

            $table->tinyInteger('tiange');          //天格
            $table->char('tiange_wuxing', 1);       //天格五行
            $table->char('tiange_yinyang', 1);      //天格阴阳
            $table->string('tiange_jixiong',3);     //天格吉凶

            $table->tinyInteger('renge');           //人格
            $table->char('renge_wuxing', 1);        //人格五行
            $table->char('renge_yinyang', 1);       //人格阴阳
            $table->string('renge_jixiong',3);      //人格吉凶
            $table->char('zhongzi',1);              //中间的字,数据库五行与文本五行是否匹配

            $table->tinyInteger('dige');            //地格
            $table->char('dige_wuxing', 1);         //地格五行
            $table->char('dige_yinyang', 1);        //地格阴阳
            $table->string('dige_jixiong',3);       //地格吉凶
            $table->char('sanzi',1);                //中间的字,数据库五行与文本五行是否匹配

            $table->tinyInteger('waige');           //外格
            $table->char('waige_wuxing', 1);        //外格五行
            $table->string('waige_jixiong',3);      //外格吉凶

            $table->tinyInteger('zongge');          //总格
            $table->char('zongge_wuxing', 1);       //总格五行
            $table->string('zongge_jixiong',3);     //总格吉凶

            $table->string('shengxiao',2);          //生肖禁忌部首
            $table->string('sancai',3);             //三才
            $table->string('sancai_jixiong',2);     //三才吉凶

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('names');
    }
}
