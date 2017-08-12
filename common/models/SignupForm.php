<?php
namespace common\models;

use Yii;
use yii\base\Model;
use common\models\User;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $password;
    public $mobile_phone;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => '\common\models\User', 'message' => '用户名已经被使用'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['password', 'required'],
            ['password', 'string', 'min' => 6,'max' => 20, 'message' => '密码请输入长度为6-20位字符'],

            //手机号验证规则
            ['mobile_phone', 'number'],
            ['mobile_phone', 'string', 'min' => 11],
            ['mobile_phone', 'filter', 'filter' => 'trim'],
            ['mobile_phone', 'match', 'pattern' => '/^1(3|4|5|7|8)[0-9]\d{8}$/','message'=>'手机号码格式不正确'],
            ['mobile_phone', 'unique', 'targetClass' => '\common\models\User', 'message' => '手机号已经被使用'],
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }
        $user = new User();
        //TODO:用户名默认为:用户_4位随机数
        $user->username = $this->username;
        $user->mobile_phone = $this->mobile_phone;
        $user->register_ip = Yii::$app->request->userIP;
        $user->register_time = date('Y-m-d H:i:s',time());
        $user->last_login_ip = Yii::$app->request->userIP;
        $user->last_login_time = date('Y-m-d H:i:s',time());
        $user->last_modify_time = date('Y-m-d H:i:s',time());
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->generateAccessToken();
        if($user->save()) {
            return $user;
        }else {
            return null;
        }
    }
}
