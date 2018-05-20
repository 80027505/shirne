<?php

namespace app\admin\controller;

use app\common\model\OrderModel;
use think\Db;

class OrderController extends BaseController
{
    public function index($key='',$status='',$audit=''){
        $model=Db::view('order','*')
            ->view('member',['username','realname','avatar','level_id'],'member.id=order.member_id','LEFT');

        $where=array();
        if(!empty($key)){
            $where[]=['order.order_no|member.username|member.realname|order.recive_name|order.mobile','LIKE',"%$key%"];
        }
        if($status!==''){
            $where[]=['order.status',$status];
        }
        if($audit!==''){
            $where[]=['order.isaudit',$audit];
        }

        $lists=$model->where($where)->paginate(15);
        if(!$lists->isEmpty()) {
            $orderids = array_column($lists->items(), 'order_id');
            $prodata = Db::name('OrderProduct')->where('order_id', 'in', $orderids)->select();
            $products=array_index($prodata,'order_id',true);
            $lists->each(function($item) use ($products){
                if(isset($products[$item['order_id']])){
                    $item['products']=$products[$item['order_id']];
                }else {
                    $item['products'] = [];
                }
                return $item;
            });
        }

        $this->assign('key',$key);
        $this->assign('status',$status);
        $this->assign('audit',$audit);
        $this->assign('lists',$lists);
        $this->assign('page',$lists->render());
        return $this->fetch();
    }

    /**
     * 订单详情
     * @param $id
     * @return \think\Response
     */
    public function detail($id){
        $model=Db::name('Order')->where('order_id',$id)->find();
        if(empty($model))$this->error('订单不存在');
        $member=Db::name('Member')->find($model['member_id']);
        $products = Db::name('OrderProduct')->where('order_id',  $id)->select();
        $this->assign('model',$model);
        $this->assign('member',$member);
        $this->assign('products',$products);
        return $this->fetch();
    }

    /**
     * 订单进度修改
     * @param $id
     */
    public function status($id){
        $order = OrderModel::get($id);
        if(empty($id) || empty($order)){
            $this->error('订单不存在');
        }
        $audit=$this->request->post('status/d');
        $express_no=$this->request->post('express_no');
        $express_code=$this->request->post('express_code');
        $data=array(
            'status'=>$audit
        );
        if(!empty($express_code)){
            $data['express_no']=$express_no;
            $data['express_code']=$express_code;
        }
        $order->save($data);
        user_log($this->mid,'auditorder',1,'更新订单 '.$id .' '.$audit,'manager');
        $this->success('操作成功');
    }

    /**
     * 审核订单
     * @param $id
     */
    public function audit($id){
        $order = OrderModel::get($id);
        if(empty($id) || empty($order)){
            $this->error('订单不存在');
        }
        $audit=$this->request->post('status/d');
        $order->save(['isaudit'=>$audit]);
        user_log($this->mid,'auditorder',1,'审核订单 '.$id .' '.$audit,'manager');
        $this->success('操作成功');
    }
}