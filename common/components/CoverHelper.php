<?php
namespace common\components;

use DateTime;
use InvalidArgumentException;
use Yii;
use yii\base\Component;
use GuzzleHttp\Pool;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use yii\base\Exception;

class CoverHelper extends Component {

    /**
     * 获取Oss(和非Oss)故事封面图片基本信息
     * @param $url
     * @return mixed|string
     */
    public static function imageInfo($url) {

        $imageInfo = array();
        $redis = Yii::$app->redis;
        $key = sprintf(Yii::$app->params['cacheKeyYouweiCoverImageInfo'],md5($url));
        $value = $redis->get($key);
        if(!empty($value)) {
            $imageInfo = \GuzzleHttp\json_decode($value,true);
        }else {
            $client = new Client();
            $response = $client->request('GET', $url, [
                'query' => ['x-oss-process' => 'image/info']
            ]);

            $body = $response->getBody();
            $content = $body->getContents();
            try{
                if(!empty($content)) {
                    $content = \GuzzleHttp\json_decode($content,true);
                    $format = $content['Format']['value'];
                    $width = $content['ImageWidth']['value'];
                    $height = $content['ImageHeight']['value'];
                    //数据格式化
                    $imageInfo['format'] = $format;
                    $imageInfo['width'] = $width;
                    $imageInfo['height'] = $height;
                }
            }catch (InvalidArgumentException $e) {

                //非oss的图片url,获取oss的接口有更改
                //Yii::error($e->getMessage());
                $content = getimagesize($url);
                if($content) {
                    //数据格式化
                    $format = image_type_to_extension($content[2],false);
                    $imageInfo['format'] = $format;
                    $imageInfo['width'] = $content[0];
                    $imageInfo['height'] = $content[1];
                }
            }

            if(!empty($imageInfo)) {
                //写入cache
                CoverHelper::saveImageInfo($url,$imageInfo['format'],$imageInfo['width'],$imageInfo['height']);
            }
        }
        return $imageInfo;
    }


    /**
     * 保存图片基本信息
     * @param $url
     * @param $format
     * @param $width
     * @param $height
     * @return bool
     */
    public static function saveImageInfo($url,$format,$width,$height) {

        $isSaved = false;
        if(!filter_var($url, FILTER_VALIDATE_URL) === false && !empty($format) && intval($width) > 0 && intval($height) > 0) {

            //redis存储
            $redis = Yii::$app->redis;
            $key = sprintf(Yii::$app->params['cacheKeyYouweiCoverImageInfo'],md5($url));
            $seconds = Yii::$app->params['expireCoverImageInfo'];

            $value = array(
                'format' => $format,
                'width'  => $width,
                'height' => $height,
            );
            $value = \GuzzleHttp\json_encode($value);
            $isSaved = $redis->setex($key, $seconds, $value);
        }
        return $isSaved;
    }
}