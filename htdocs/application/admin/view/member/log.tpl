<extend name="Public:Base" />

<block name="body">
<include file="Public/bread" menu="member_index" section="会员" title="操作日志" />

<div id="page-wrapper">
    <div class="row">
        <div class="col-md-6">
            <a href="{:U('member/logclear')}" class="btn btn-success">清理日志</a>
        </div>
        <div class="col-md-6">
            <form action="{:U('member/index')}" method="post">
                <div class="form-group input-group">
                    <input type="text" class="form-control" name="key" placeholder="输入用户名或者关键词搜索">
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
                <th>操作</th>
                <th>状态</th>
                <th>时间</th>
                <th>IP</th>
                <th>备注</th>
                <th width="70">操作</th>
            </tr>
        </thead>
        <tbody>
        <foreach name="lists" item="v">
            <tr>
                <td>{$v.id}</td>
                <td>{$v.username}</td>
                <td>{$v.action}</td>
                <td>{$v.result}</td>
                <td>{$v.create_at|showdate}</td>
                <td>{$v.ip}</td>
                <td>{$v.remark}</td>
                <td>
                    <a class="btn btn-default btn-sm" rel="ajax" href="{:U('member/logview',array('id'=>$v['id']))}"><i class="fa fa-file-text"></i> 查看</a>
                </td>
            </tr>
        </foreach>
        </tbody>
    </table>
    {$page}
</div>

</block>