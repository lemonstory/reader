<?php
namespace api\models;

use common\models\User;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $mobilePhone;
    public $password;

    private $_user;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['mobilePhone', 'password'], 'required'],

            //手机号验证规则
            ['mobilePhone', 'number'],
            ['mobilePhone', 'string', 'min' => 11],
            ['mobilePhone', 'filter', 'filter' => 'trim'],

            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, '手机号或密码错误.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {

            //修改用户登录信息
            $userModel = $this->getUser();
            $userModel->last_login_ip = Yii::$app->request->getUserIP();
            $userModel->last_login_time = time();
            $userModel->save();

            return Yii::$app->user->login($userModel, 0);
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByMobilePhone($this->mobilePhone);
        }

        return $this->_user;
    }
}
