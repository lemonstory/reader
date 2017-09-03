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
            [['file'], 'file', 'skipOnEmpty' => false,],
        ];
    }

    public function uploadAvatarOss($id, $type)
    {
        $bucket = Yii::$app->params['ossAvatarObjectBucket'];
        $object = $this->avatarOssObject($id, $type);
        return $this->imgUploadOss($bucket, $object);
    }

    /**
     * 上传故事封面图到Oss
     * @param $uid
     * @param $type 故事封面图：type="cover/"; 章节背景图：type="background/"
     * @return bool
     */
    public function uploadPicOss($uid, $type)
    {
        $bucket = Yii::$app->params['ossPicObjectBucket'];
        $object = $this->picOssObject($uid, $type);
        return $this->imgUploadOss($bucket, $object);
    }

    /**
     * 生成故事封面图(或)章节背景图的Object名称(oss使用)
     * @param $uid
     * @param $type 故事封面图：type="cover/"; 章节背景图：type="background/"
     * @return string
     */
    public function picOssObject($uid, $type)
    {

        //object = cover/2017/06/26/0_1498457928781.jpg
        //object = background/2017/06/26/0_1498457928781.jpg
        $object = $type;
        $object .= date("Y/m/d/", time());
        $object .= $uid . "_" . time();
        $object .= Yii::$app->params['ossPicObjectCoverSuffix'];
        return $object;
    }

    /**
     * 生成用户头像(或)角色头像的Object名称(oss使用)
     * @param $id 用户uid,或角色actor_id
     * @param $type 用户头像：type="user/"; 角色头像：type="actor/"
     * @return string
     */
    public function avatarOssObject($id, $type)
    {

        $object = $type;
        $object .= $id;
        $object .= Yii::$app->params['ossPicObjectCoverSuffix'];
        return $object;
    }

    /**
     * 上传图片至Oss
     * @param $bucket
     * @param $object
     * @return null
     */
    public function imgUploadOss($bucket, $object)
    {
        if (!empty($bucket) && !empty($object)) {

            $configJson = Yii::$app->vendorPath . '/aliyun-sts-server/config.json';
            $configObj = json_decode(file_get_contents($configJson));
            $accessKeyId = $configObj->AccessKeyID;
            $accessKeySecret = $configObj->AccessKeySecret;
            $endpoint = Yii::$app->params['ossEndPoint'];
            if ($this->validate()) {

                try {
                    $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
                    $filePath = $this->file->tempName;
                    $ossRet = $ossClient->uploadFile($bucket, $object, $filePath);
                    if (!empty($ossRet['info']['url'])) {
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
        } else {
            return null;
        }
    }
}