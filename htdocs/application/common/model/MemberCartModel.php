<?php


namespace app\common\model;

use think\Db;

/**
 * Class MemberCartModel
 * @package app\common\model
 */
class MemberCartModel extends BaseModel
{
    private function getSort($member_id){
        $sort=Db::name('MemberCart')->where('member_id',$member_id)
            ->max('sort');

        return $sort?($sort+1):1;
    }
    public function mapCart($product,$sku){
        return [
            'product_id' => $product['id'],
            'sku_id' => $sku['sku_id'],
            'product_title' => $product['title'],
            'product_image' => $sku['image']?$sku['image']:$product['image'],
            'product_price' => $sku['price'],
            'product_weight' => $sku['weight']
        ];
    }
    public function mapProduct($product,$sku){
        $data=[];
        $productMaps=['id'=>'product_id','title'=>'product_title','image'=>'product_image','spec_data','levels','is_discount','is_commission','type'];
        $skuMaps=['sku_id','storage','sale','goods_no'=>'sku_goods_no','weight'=>'product_weight','specs','price'=>'product_price','market_price','cost_price','image'=>'sku_image'];
        foreach ($productMaps as $k=>$v){
            if(is_string($k)){
                $data[$v]=$product[$k];
            }else{
                $data[$v]=$product[$v];
            }
        }
        foreach ($skuMaps as $k=>$v){
            if(is_string($k)){
                $data[$v]=$sku[$k];
            }else{
                $data[$v]=$sku[$v];
            }
        }
        return $data;
    }
    public function getCount($member_id)
    {
        return Db::name('MemberCart')
            ->where('member_id',$member_id)
            ->count('id');
    }
    public function addCart($product,$sku,$count,$member_id)
    {
        $sort=$this->getSort($member_id);
        $exists=Db::name('MemberCart')->where([
            'member_id'=>$member_id,
            'sku_id'=>$sku['sku_id'],
        ])->find();
        if(!empty($exists)){
            return Db::name('MemberCart')->where([
                'member_id'=>$member_id,
                'sku_id'=>$sku['sku_id'],
            ])->inc('count',$count)->update(['sort'=>$sort]);
        }else {
            $data=$this->mapCart($product,$sku);
            $data['member_id']=$member_id;
            $data['count']=$count;
            $data['sort']=$sort;
            return Db::name('MemberCart')->insert($data);
        }
    }
    public function updateCartData($product,$sku,$member_id,$id)
    {
        $data=$this->mapCart($product,$sku);
        return Db::name('MemberCart')->where([
            'member_id'=>$member_id,
            'id'=>$id,
        ])->update($data);
    }
    public function updateCart($sku_id,$count,$member_id)
    {
        $sort=$this->getSort($member_id);
        return Db::name('MemberCart')->where([
            'member_id'=>$member_id,
            'sku_id'=>$sku_id,
        ])->update([
            'count'=>$count,
            'sort'=>$sort
        ]);
    }
    public function getCart($member_id, $sku_ids='')
    {
        $model=Db::view('MemberCart',['id','member_id','product_id','sku_id','product_title'=>'cart_product_title','product_image'=>'cart_product_image','product_price'=>'cart_product_price','count','sort'])
            ->view('ProductSku',['storage','sale','goods_no'=>'sku_goods_no','weight','specs','price'=>'product_price','market_price','cost_price','image'=>'sku_image'],'ProductSku.sku_id=MemberCart.sku_id','LEFT')
            ->view('Product',['title'=>'product_title','image'=>'product_image','spec_data','levels','is_discount','is_commission','type'],'MemberCart.product_id=Product.id','LEFT')
            ->where('MemberCart.member_id',$member_id);
        if(!empty($sku_ids)){
            $model->whereIn('MemberCart.sku_id',idArr($sku_ids));
        }
        $lists = $model->order('MemberCart.sort DESC')->select();
        foreach ($lists as $k=>&$item){
            if(!empty($item['spec_data'])){
                $item['spec_data']=json_decode($item['spec_data'],true);
            }else{
                $item['spec_data']=[];
            }
            if(!empty($item['specs'])){
                $item['specs']=json_decode($item['specs'],true);
            }else{
                $item['specs']=[];
            }
            if(!empty($item['levels'])) {
                $item['levels'] = json_decode($item['levels'], true);
            }
        }
        return $lists;
    }
    public function delCart($sku_ids,$member_id)
    {
        return Db::name('MemberCart')
            ->where('member_id',$member_id)
            ->whereIn('sku_id',idArr($sku_ids))
        ->delete();
    }
    public function clearCart($member_id)
    {
        return Db::name('MemberCart')->where('member_id',$member_id)->delete();
    }
}