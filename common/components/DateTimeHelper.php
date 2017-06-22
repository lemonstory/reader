<?php
namespace common\components;

use DateTime;
use Yii;
use yii\base\Component;
class DateTimeHelper extends Component {

    const DATE_FORMAT = 'php:Y-m-d';
    const DATETIME_FORMAT = 'php:Y-m-d H:i:s';
    const TIME_FORMAT = 'php:H:i:s';

    public static function validate($timestamp) {

        if(!empty($timestamp)) {
            $now = time();
            if($timestamp > $now) {
                $ret = false;
            }else {
                $ret = true;
            }
        }else {
            $ret = false;
        }

        return $ret;
    }

    public static function inputCheck($timestamp) {

        $currentDateTime = date('Y-m-d H:i:s',time());
        if(DateTimeHelper::validate($timestamp)) {
            $currentDateTime = date('Y-m-d H:i:s',$timestamp);
        }
        return $currentDateTime;
    }

    public static function convert($dateStr, $type='date', $format = null) {

        $convertDateStr = "";
        if(!empty($dateStr) && is_string($dateStr)) {
            if ($type === 'datetime') {
                $fmt = ($format == null) ? self::DATETIME_FORMAT : $format;
            }
            elseif ($type === 'time') {
                $fmt = ($format == null) ? self::TIME_FORMAT : $format;
            }
            else {
                $fmt = ($format == null) ? self::DATE_FORMAT : $format;
            }
            $convertDateStr = Yii::$app->formatter->asDate($dateStr, $fmt);
        }
        return $convertDateStr;
    }

}