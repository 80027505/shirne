<?php
/**
 * Created by IntelliJ IDEA.
 * User: shirne
 * Date: 2018/4/11
 * Time: 9:03
 */

namespace app\admin\controller;
/**
 * 会员组管理
 */
class MemberLevelController extends BaseController
{
    /**
     * 会员级别列表
     */
    public function index()
    {
        $model = Db::name('memberLevel');

        $this->pagelist($model,array(),'level_id ASC');
        $this->display();
    }

    /**
     * 添加等级
     */
    public function add()
    {
        //默认显示添加表单
        if (!$this->request->isPost()) {
            $this->assign('model',array(
                'commission_layer'=>3
            ));
            $this->display();
        }
        if ($this->request->isPost()) {
            //如果用户提交数据
            $model = D("memberLevel");
            if (!$model->create()) {
                // 如果创建失败 表示验证没有通过 输出错误提示信息
                $this->error($model->getError());
                exit();
            } else {
                $insertId=$model->add();
                if ($insertId!==false) {
                    cache('levels', null);
                    $this->success("添加成功", url('memberLevel/index'));
                } else {
                    $this->error("添加失败");
                }
            }
        }
    }
    /**
     * 更新会员组
     */
    public function update($id)
    {
        $id = intval($id);
        //默认显示添加表单
        if (!$this->request->isPost()) {
            $model = D('memberLevel')->where("level_id= %d",$id)->find();
            $this->assign('model',$model);
            $this->display('add');
        }
        if ($this->request->isPost()) {
            $model = D("memberLevel");
            if (!$model->create()) {
                $this->error($model->getError());
            }else{
                if ($model->save()) {
                    cache('levels', null);
                    $this->success("更新成功", url('memberLevel/index'));
                } else {
                    $this->error("更新失败");
                }
            }
        }
    }
    /**
     * 删除会员组
     */
    public function delete($id)
    {
        $id = intval($id);
        $count=Db::name('member')->where(array('level_id'=>$id))->count();
        if($count>0){
            $this->error("该分组尚有会员,不能删除");
        }
        $model = Db::name('memberLevel');
        $result = $model->delete($id);
        if($result){
            cache('levels', null);
            $this->success("删除成功", url('memberLevel/index'));
        }else{
            $this->error("删除失败");
        }
    }
}