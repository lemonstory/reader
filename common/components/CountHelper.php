<?php
namespace common\components;

use yii\base\Component;

class CountHelper extends Component {

    public static function humanize($count) {

        if($count >= 100000000){
            //大于1亿时
            $countStr = sprintf("%.2f", $count/100000000);
            $countStr .= "亿";
        }else if($count >= 100000){
            //大于10万时
            $countStr = sprintf("%.0f", $count/10000);
            $countStr .= "万";
        }else {
            $countStr = $count;
        }
        return $countStr;
    }

}