<?php

namespace app\models\project;

use app\models\history\StoreHistory;
use app\models\Project;
use Yii;

class DeleteProject extends Project
{

    public $password;

    /**
     * 验证规则
     * @return array
     */
    public function rules()
    {
        return [
            ['password', 'required', 'message' => '登录密码不可以为空'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * 验证密码是否正确
     * @param $attribute
     */
    public function validatePassword($attribute)
    {

        if (!$this->hasErrors()) {

            $user = Yii::$app->user->identity;

            if (!$user || !$user->validatePassword($this->password)) {

                $this->addError($attribute, '登录密码验证失败');
            }

        }
    }

    /**
     * 删除项目
     * @return bool
     */
    public function delete()
    {

        // 开启事务
        $transaction  = Yii::$app->db->beginTransaction();

        $this->status = self::DISABLE_STATUS;

        if(!$this->validate()){
            return false;
        }

        if(!$this->save(false)){
            $transaction->rollBack();
            return false;
        }

        // 记录日志
        $log = StoreHistory::findModel();

        $log->method    = 'delete';
        $log->res_name  = 'project';
        $log->res_id    = $this->id;
        $log->object    = 'project';
        $log->object_id = $this->id;
        $log->content   = '删除了项目<code>' . $this->title . '</code>';

        if(!$log->store()){
            $transaction->rollBack();
            return false;
        }

        // 事务提交
        $transaction->commit();

        return true;

    }

}
