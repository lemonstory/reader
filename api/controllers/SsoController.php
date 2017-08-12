<?php
/**
 * Created by PhpStorm.
 * User: gaoyong
 * Date: 2017/8/4
 * Time: 下午4:30
 */

namespace api\controllers;


use common\models\Auth;
use common\models\Oauth;
use common\models\User;
use QC;
use Yii;
use yii\rest\ActiveController;

class SsoController extends ActiveController
{
    public $modelClass = 'common\models\Story';
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function actions()
    {
        $actions = parent::actions();

        // 禁用"delete" 和 "create" 动作
        unset($actions['delete'], $actions['create'], $actions['view']);

        // 使用"prepareDataProvider()"方法自定义数据provider
//        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        return $actions;
    }

    public function prepareDataProvider()
    {
        // 为"index"动作准备和返回数据provider
    }

    /**
     * QQ登录,账号绑定
     * @param $accessToken
     * @param $openId
     */
    public function actionQqAuth($accessToken, $openId)
    {
        $clientId = 'qq';

        $auth = Auth::find()->where([
            'source' => 'qq',
            'source_id' => $openId,
        ])->one();

        if (Yii::$app->user->isGuest) {
            if ($auth) {

                // 登录
                $user = $auth->user;
                Yii::$app->user->login($user);

            } else {

                // 注册
                $qqUserInfo = $this->getQqUserInfo($accessToken,$openId);
                $userName = $qqUserInfo['nickName'];
                //用户姓名不能重复
                if (isset($user['mobile_phone']) && User::find()->where(['username' => $userName])->exists()) {
                    Yii::$app->getSession()->setFlash('error', [
                        Yii::t('app', "User with the same username as in {client} account already exists but isn't linked to it. Login using email first to link it.", ['client' => $clientId]),
                    ]);
                } else {
                    $password = Yii::$app->security->generateRandomString(6);
                    $user = new User([
                        'username' => $userName,
                        'password' => $password,
                    ]);
                    $user->generateAuthKey();
                    $user->generatePasswordResetToken();
                    $transaction = $user->getDb()->beginTransaction();
                    if ($user->save()) {
                        $auth = new Auth([
                            'user_id' => $user->uid,
                            'source' => $clientId,
                            'source_id' => (string)$openId,
                        ]);
                        if ($auth->save()) {
                            $transaction->commit();
                            Yii::$app->user->login($user);
                        } else {
                            print_r($auth->getErrors());
                        }
                    } else {
                        print_r($user->getErrors());
                    }
                }
            }
        } else {

            // 用户已经登陆
            if (!$auth) {

                // 添加验证提供商（向验证表中添加记录）
                $auth = new Auth([
                    'user_id' => Yii::$app->user->uid,
                    'source' => $clientId,
                    'source_id' => $openId,
                ]);
                $auth->save();
            }
        }
    }


    /**
     * 获取QQ开放平台信息
     * @param $accessToken
     * @param $openId
     * @return array
     */
    public function getQqUserInfo($accessToken,$openId)
    {
        require_once (Yii::$app->vendorPath.'/qqconnect-server-sdk-php/API/comm/config.php');
        require_once(CLASS_PATH."QC.class.php");
        
        $qc = new QC($accessToken, $openId);
        $getInfo = $qc->get_user_info();
        if (empty($getInfo)) {
            $this->setError(ErrorConf::qqUserInfoEmpty());
            return array();
        }

        $qqUserInfo = array();
        $qqUserInfo['nickName'] = $getInfo['nickname'];

        $gender = 0;
        $gendertxt = $getInfo['gender'];
        if ($gendertxt == '男') {
            $gender = 1;
        }
        if ($gendertxt == '女') {
            $gender = 2;
        }
        $qqUserInfo['gender'] = $gender;
        $qqUserInfo['province'] = $getInfo['province'];
        $qqUserInfo['city'] = $getInfo['city'];
        $qqUserInfo['year'] = $getInfo['year'];
        $qqUserInfo['qqAvatar'] = $getInfo['figureurl_qq_2'];

        return $qqUserInfo;
    }
}