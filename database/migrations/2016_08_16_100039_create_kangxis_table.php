<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKangxisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kangxis', function (Blueprint $table) {
            $table->increments('id');
            $table->char('char', 1)->unique();                      //『字』

            $table->char('bushou',1)->nullable();                   //部首
            $table->tinyInteger('bushou_bihua')->nullable();        //笔画
            $table->tinyInteger('bihua')->nullable();               //总笔画

            $table->char('jianti',1)->nullable();                   //简体
            $table->char('jianti_bushou',1)->nullable();            //简体部首
            $table->tinyInteger('jianti_bushou_bihua')->nullable(); //简体部首笔画
            $table->tinyInteger('jianti_bihua')->nullable();        //简体总笔画

            $table->char('fanti',1)->nullable();                    //繁体
            $table->char('fanti_bushou',1)->nullable();             //繁体部首
            $table->tinyInteger('fanti_bushou_bihua')->nullable();  //繁体部首笔画
            $table->tinyInteger('fanti_bihua')->nullable();         //繁体总笔画

            $table->string('yiti',20)->nullable();                  //异体字
            $table->string('kangxizi',20)->nullable();              //康熙字
            $table->string('kangxi_bihua', 20)->nullable();         //康熙字典总笔画
            $table->string('pinying',35)->nullable();               //拼音
            $table->string('zhuying',30)->nullable();               //注音
            $table->string('wubi86',5)->nullable();                 //五笔86
            $table->string('wubi98',5)->nullable();                 //五笔98
            $table->string('cangjie',10)->nullable();               //仓颉
            $table->string('sijiao',16)->nullable();                //4角号码
            $table->string('unicode',16)->nullable();               //unicode
            $table->string('hanbian',16)->nullable();               //规范汉字编号

            $table->string('wuxing',2)->nullable();                 //汉字五行
            $table->string('jixiong',4)->nullable();                //吉凶
            $table->char('changyong',1)->nullable();                //是否为常用字
            $table->string('xingmingxue',19)->nullable();           //姓名学

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
        Schema::drop('kangxis');
    }
}
