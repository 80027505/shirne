<extend name="Public:Base" />

<block name="body">
<include file="Public/bread" menu="manager_index" section="系统" title="管理员" />

<div id="page-wrapper">
    <div class="row">
        <div class="col-md-6">
            <a href="{:U('manager/add')}" class="btn btn-success">添加管理员</a>
        </div>
        <div class="col-md-6">
            <form action="{:U('manager/index')}" method="post">
                <div class="form-group input-group">
                    <input type="text" class="form-control" name="key" placeholder="输入用户名或者邮箱关键词搜索">
                    <span class="input-group-btn">
                      <button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
                    </span>
                </div>
            </form>
        </div>
    </div>
    <table class="table table-hover table-striped">
        <thead>
            <tr>
                <th width="50">编号</th>
                <th>用户名</th>
                <th>邮箱</th>
                <th>注册时间</th>
                <th>上次登陆</th>
                <th>类型</th>
                <th>状态</th>
                <th width="220">操作</th>
            </tr>
        </thead>
        <tbody>
        <foreach name="lists" item="v">
            <tr>
                <td>{$v.id}</td>
                <td>{$v.username}</td>
                <td>{$v.email}</td>
                <td>{$v.create_at|showdate}</td>
                <td>{$v.login_ip}<br />{$v.logintime|showdate}</td>
                <td>
                    <if condition="$v.type eq 1"> <span class="label label-success">超级管理员</span>
                    <elseif condition="$v.type eq 2"/><span class="label label-danger">管理员</span>
                    </if>
                </td> 
                <td><if condition="$v.status eq 1">正常<else/><span style="color:red">禁用</span></if></td>
                <td>
                    <a class="btn btn-default btn-sm" href="{:U('manager/update',array('id'=>$v['id']))}"><i class="fa fa-edit"></i> 编辑</a>
                <if condition="$v.type neq 1">	<a class="btn btn-default btn-sm" href="{:U('manager/permision',array('id'=>$v['id']))}"><i class="fa fa-edit"></i> 权限</a></if>
                <if condition="$v.status eq 1">	
                    <a class="btn btn-default btn-sm" href="{:U('manager/delete',array('id'=>$v['id']))}" style="color:red;" onclick="javascript:return del('禁用后用户将不能登陆后台!\n\n请确认!!!');"><i class="fa fa-close"></i> 禁用</a>
            	<else/>
                    <a class="btn btn-default btn-sm" href="{:U('manager/delete',array('id'=>$v['id']))}" style="color:#50AD1E;"><i class="fa fa-check"></i> 启用</a>
            	</if>
                </td>
            </tr>
        </foreach>
        </tbody>
    </table>
</div>

</block>