<?php
namespace common\components;

use Yii;
use yii\base\Component;
class DateTimeHelper extends Component {

    public static function validate($dateTime) {

        if(!empty($dateTime)) {
            $date = new DateTime($dateTime);
            $time = $date->getTimestamp();
            $now = time();
            if($time > $now) {
                $ret = false;
            }else {
                $ret = true;
            }
        }else {
            $ret = false;
        }

        return $ret;
    }

    public static function inputCheck($dateTime) {

        $currentDateTime = date('Y-m-d H:i:s',time());
        if(DateTimeHelper::validate($dateTime)) {
            $currentDateTime = $dateTime;
        }
        return $currentDateTime;
    }

}