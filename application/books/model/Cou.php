<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/5/28
 * Time: 16:57
 */

namespace app\books\model;


use think\Model;

class Cou extends Model
{
    /**
     * @param $list 数据数组
     * 批量更新书籍表
     */
    public function updateAll($list){
        $Cou = new Cou;
        $Cou->isUpdate()->saveAll($list);
    }
}