<?php
namespace common\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use OSS\OssClient;
use OSS\Core\OssException;

/**
 * UploadForm is the model behind the upload form.
 */
class UploadForm extends Model
{
    /**
     * @var UploadedFile|Null file attribute
     */
    public $file;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['file'], 'file'],

        ];
    }

    /**
     * 上传故事封面图到Oss
     * @param $uid
     * @return bool
     */
    public function uploadPicOss($uid)
    {
        $configJson = Yii::$app->vendorPath.'/sts-server/config.json';
        $configObj  = json_decode(file_get_contents($configJson));

        $accessKeyId = $configObj->AccessKeyID;
        $accessKeySecret = $configObj->AccessKeySecret;
        $bucket = Yii::$app->params['ossPicObjectBucket'];
        $endpoint = Yii::$app->params['ossEndPoint'];

        if ($this->validate()) {

            try {
                $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
                $object = $this->picOssObject($uid);
                $filePath = $this->file->tempName;
                $ossRet = $ossClient->uploadFile($bucket, $object, $filePath);
                if(!empty($ossRet['info']['url'])) {
                    return $ossRet['info']['url'];
                }
                return null;

            } catch (OssException $e) {
                printf(__FUNCTION__ . ": FAILED\n");
                printf($e->getMessage() . "\n");
                return null;
            }

        } else {
            return null;
        }
    }

    /**
     * 生成故事封面图的Object(oss使用)
     * @param $uid
     * @return string
     */
    public function picOssObject($uid) {

        //object = cover/2017/06/26/0_1498457928781.jpg
        $object = Yii::$app->params['ossPicObjectCoverPrefix'];
        $object .= date("Y/m/d/",time());
        $object .= $uid._.time();
        $object .= Yii::$app->params['ossPicObjectCoverSuffix'];
        return $object;
    }
}