{include common/header@ebcms/cms-admin}
<?php
function format_date($time)
{
    $today = strtotime(date('Y-m-d 23:59:59', time()));
    if ($today - 86400 < $time) {
        return date('H:i', $time);
    }
    if ($today - 2 * 86400 < $time) {
        return '昨天';
    }
    if ($today - 3 * 86400 < $time) {
        return '前天';
    }
    return date('m/d', $time);
}

function get_category($categorys, $id): array
{
    foreach ($categorys as $item) {
        if ($item['id'] == $id) {
            return $item;
        }
    }
    return [];
}
$categorys = $container->get(\App\Ebcms\CmsAdmin\Model\Category::class)->getAll();
?>
<style>
    a {
        text-decoration: none;
    }
</style>
<div class="container">
    <div class="h1 my-4">内容管理</div>
    <div class="mb-3">
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                新建内容
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                {foreach $categorys as $vo}
                {if $vo['type']=='list'}
                <li><a class="dropdown-item" href="{echo $router->build('/ebcms/cms-admin/content/create', ['category_id'=>$vo['id']])}">{:str_repeat('ㅤ', $vo['_level'])}{$vo.title}</a></li>
                {else}
                <li><a class="dropdown-item disabled">{:str_repeat('ㅤ', $vo['_level'])}{$vo.title}</a></li>
                {/if}
                {/foreach}
            </ul>
        </div>
    </div>
    <div class="mb-3">
        <form id="form_2" class="row gy-2 gx-3 align-items-center" action="{echo $router->build('/ebcms/cms-admin/content/index')}" method="GET">

            <div class="col-auto">
                <label class="visually-hidden">栏目</label>
                <select class="form-select" name="category_id" onchange="document.getElementById('form_2').submit();">
                    <option {if $request->get('state')=='' }selected{/if} value="">不限</option>
                    {foreach $categorys as $vo}
                    <option {if $request->get('category_id')==$vo['id']}selected{/if} {if $vo['type'] != 'list'}disabled{/if} value="{$vo.id}">{:str_repeat('ㅤ', $vo['_level'])}{$vo.title}</option>
                    {/foreach}
                </select>
            </div>

            <div class="col-auto">
                <label class="visually-hidden">属性</label>
                <select class="form-select" name="attr" onchange="document.getElementById('form_2').submit();">
                    <option {if $request->get('attr')=='' }selected{/if} value="">不限</option>
                    {foreach $config->get('attrs@ebcms/cms-admin') as $vo}
                    <option {if $request->get('attr')==$vo}selected{/if} value="{$vo}">{$vo}</option>
                    {/foreach}
                </select>
            </div>

            <div class="col-auto">
                <label class="visually-hidden">状态</label>
                <select class="form-select" name="state" onchange="document.getElementById('form_2').submit();">
                    <option {if $request->get('state')=='' }selected{/if} value="">不限</option>
                    <option {if $request->get('state')=='2' }selected{/if} value="2">待审</option>
                    <option {if $request->get('state')=='1' }selected{/if} value="1">发布</option>
                </select>
            </div>

            <div class="col-auto">
                <label class="visually-hidden">分页</label>
                <select class="form-select" name="page_num" onchange="document.getElementById('form_2').submit();">
                    <option {if $request->get('page_num')=='20' }selected{/if} value="20">20</option>
                    <option {if $request->get('page_num')=='50' }selected{/if} value="50">50</option>
                    <option {if $request->get('page_num')=='100' }selected{/if} value="100">100</option>
                    <option {if $request->get('page_num')=='500' }selected{/if} value="500">500</option>
                </select>
            </div>

            <div class="col-auto">
                <label class="visually-hidden">搜索</label>
                <input type="search" class="form-control" name="q" value="{:$request->get('q')}" placeholder="搜索.." onchange="document.getElementById('form_2').submit();">
            </div>
            <input type="hidden" name="page" value="1">
        </form>
    </div>
    <?php
    $where = [];

    $where['ORDER'] = [
        'id' => 'DESC',
    ];

    if ($request->get('category_id')) {
        $where['category_id'] = $request->get('category_id');

        $category = $db->get('ebcms_cms_category', '*', [
            'id' => $request->get('category_id')
        ]);
        if ($category['content_priority'] == 1) {
            $where['ORDER'] = [
                'priority' => 'DESC',
                'id' => 'DESC',
            ];
        }
    }

    if ($request->get('state')) {
        $where['state'] = $request->get('state');
    }

    if ($attr = $request->get('attr')) {
        $where['attrs[~]'] = '%"' . $attr . '"%';
    }

    if ($q = $request->get('q')) {
        $where['OR'] = [
            'id' => $q,
            'title[~]' => '%' . $q . '%',
            'body[~]' => '%' . $q . '%',
            'extra[~]' => '%' . $q . '%',
        ];
    }

    $total = $db->count('ebcms_cms_content', $where);

    $page = $request->get('page', 1, ['intval']) ?: 1;
    $page_num = min(100, $request->get('page_num', 20, ['intval']) ?: 20);
    $where['LIMIT'] = [($page - 1) * $page_num, $page_num];

    $contents = $db->select('ebcms_cms_content', '*', $where);
    $pagination = $container->get(\PsrPHP\Pagination\Pagination::class)->render($page, $total, $page_num);
    ?>
    <nav class="mb-3">
        <ul class="pagination">
            {foreach $pagination as $v}
            {if $v=='...'}
            <li class="page-item disabled"><a class="page-link" href="javascript:void(0);">{$v}</a></li>
            {elseif isset($v['current'])}
            <li class="page-item active"><a class="page-link" href="javascript:void(0);">{$v.page}</a></li>
            {else}
            <li class="page-item"><a class="page-link" href="{echo $router->build('/ebcms/cms-admin/content/index', array_merge($_GET, ['page'=>$v['page']]))}">{$v.page}</a></li>
            {/if}
            {/foreach}
        </ul>
    </nav>
    <div class="table-responsive mb-3">
        <table class="table table-bordered" id="tablexx">
            <thead>
                <tr>
                    <th class="text-nowrap" style="width:30px;">ID</th>
                    <th class="text-nowrap">栏目</th>
                    <th class="text-nowrap">标题</th>
                    <th class="text-nowrap" style="width:80px;">点击量</th>
                    <th class="text-nowrap" style="width:80px;">时间</th>
                    <th class="text-nowrap" style="width:100px;">管理</th>
                </tr>
            </thead>
            <tbody>
                {foreach $contents as $vo}
                <tr>
                    <td>
                        <div class="form-check">
                            <input type="checkbox" name="ids[]" value="{$vo.id}" class="form-check-input" id="checkbox_{$vo.id}">
                            <!-- <label class="form-check-label" for="checkbox_{$vo.id}">{$vo.id}</label> -->
                        </div>
                    </td>
                    <td>
                        <?php
                        $cate = get_category($categorys, $vo['category_id']);
                        ?>
                        {foreach $cate['_pitems'] as $item}
                        <span class="text-muted">{$item.title}</span>
                        <span class="text-muted">/</span>
                        {/foreach}
                        <a href="{echo $router->build('/ebcms/cms-admin/content/index',['category_id'=>$cate['id']])}">{$cate.title}</a>
                    </td>
                    <td class="text-nowrap text-truncate align-middle" style="max-width: 30em;">
                        {if $vo['state'] != 1}
                        <span class="text-warning">[待审]</span>
                        {/if}
                        {if $vo['cover']}
                        <span class="text-danger">[图]</span>
                        {/if}
                        {if $vo['redirect_uri']}
                        <span class="text-info">[跳转]</span>
                        {/if}
                        <span>{$vo.title}</span>
                        {foreach json_decode($vo['attrs']?:'[]', true) as $_attr}
                        <a href="{echo $router->build('/ebcms/cms-admin/content/index',['attr'=>$_attr])}" class="badge rounded-pill bg-primary text-white">{$_attr}</a>
                        {/foreach}
                    </td>
                    <td>{$vo.click}</td>
                    <td>
                        <span class="text-muted text-nowrap" title="{:date(DATE_ISO8601, $vo['create_time'])}">{:format_date($vo['create_time'])}</span>
                    </td>
                    <td class="text-nowrap">
                        <a href="{echo $router->build('/ebcms/cms-admin/content/update', ['id'=>$vo['id']])}">编辑</a>
                        <a href="{echo $router->build('/ebcms/cms-admin/content/create', ['category_id'=>$vo['category_id'], 'copyfrom'=>$vo['id']])}">复制</a>
                        {if isset(\PsrPHP\Framework\Framework::getAppList()['ebcms/cms-web'])}
                        <a href="{echo $router->build('/ebcms/cms-web/content', ['category_id'=>$vo['category_id'], 'id'=>$vo['id']])}" target="_blank">查看</a>
                        {/if}
                    </td>
                </tr>
                {/foreach}
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6">
                        <form class="row gy-2 gx-3 align-items-center">

                            <div class="col-auto">
                                <button class="btn btn-secondary" type="button" id="fanxuan">全选/反选</button>
                                <script>
                                    $(document).ready(function() {
                                        $("#fanxuan").on("click", function() {
                                            $("#tablexx td :checkbox").each(function() {
                                                $(this).prop("checked", !$(this).prop("checked"));
                                            });
                                        });
                                    });
                                </script>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-danger" id="delete">删除</button>
                                <script>
                                    $(document).ready(function() {
                                        $("#delete").bind('click', function() {
                                            if (confirm('确定删除吗？删除后不可恢复！')) {
                                                var ids = [];
                                                $.each($('#tablexx input:checkbox:checked'), function() {
                                                    ids.push($(this).val());
                                                });
                                                $.ajax({
                                                    type: "POST",
                                                    url: "{echo $router->build('/ebcms/cms-admin/content/delete')}",
                                                    data: {
                                                        ids: ids
                                                    },
                                                    dataType: "JSON",
                                                    success: function(response) {
                                                        if (response.errcode) {
                                                            alert(response.message);
                                                        } else {
                                                            location.reload();
                                                        }
                                                    }
                                                });
                                            }
                                        });
                                    });
                                </script>
                            </div>

                            <div class="col-auto">
                                <label class="visually-hidden" for="inlinestate">状态变更</label>
                                <select class="form-select" id="inlinestate">
                                    <option value="">状态...</option>
                                    <option value="1">通过审核</option>
                                    <option value="2">待审</option>
                                </select>
                                <script>
                                    $(function() {
                                        $("#inlinestate").bind('change', function() {
                                            var state = $(this).val();
                                            if (state >= 0) {
                                                var ids = [];
                                                $.each($('#tablexx input:checkbox:checked'), function() {
                                                    ids.push($(this).val());
                                                });
                                                $.ajax({
                                                    type: "POST",
                                                    url: "{echo $router->build('/ebcms/cms-admin/content/state')}",
                                                    data: {
                                                        ids: ids,
                                                        state: state,
                                                    },
                                                    dataType: "JSON",
                                                    success: function(response) {
                                                        if (response.code == 0) {
                                                            location.reload();
                                                        } else {
                                                            alert(response.message);
                                                        }
                                                    }
                                                });
                                            }
                                        });
                                    });
                                </script>
                            </div>

                            <div class="col-auto">
                                <label class="visually-hidden" for="inlineFormCustomSelect">移动</label>
                                <select class="form-select" id="inlineFormCustomSelect">
                                    <option value="">移动到...</option>
                                    {foreach $categorys as $vo}
                                    {if $vo['type']=='list'}
                                    <option value="{$vo.id}">{:str_repeat('ㅤ', $vo['_level'])}{$vo.title}</option>
                                    {else}
                                    <option value="{$vo.id}" disabled>{:str_repeat('ㅤ', $vo['_level'])}{$vo.title}</option>
                                    {/if}
                                    {/foreach}
                                </select>
                                <script>
                                    $(function() {
                                        $("#inlineFormCustomSelect").bind('change', function() {
                                            var category_id = $(this).val();
                                            if (category_id >= 0) {
                                                if (confirm('确定移动吗？若字段不一样会造成数据错乱！')) {
                                                    var ids = [];
                                                    $.each($('#tablexx input:checkbox:checked'), function() {
                                                        ids.push($(this).val());
                                                    });
                                                    $.ajax({
                                                        type: "POST",
                                                        url: "{echo $router->build('/ebcms/cms-admin/content/move')}",
                                                        data: {
                                                            ids: ids,
                                                            category_id: category_id,
                                                        },
                                                        dataType: "JSON",
                                                        success: function(response) {
                                                            if (response.code == 0) {
                                                                location.reload();
                                                            } else {
                                                                alert(response.message);
                                                            }
                                                        }
                                                    });
                                                }
                                            }
                                        });
                                    });
                                </script>
                            </div>
                        </form>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <nav class="mb-3">
        <ul class="pagination">
            {foreach $pagination as $v}
            {if $v=='...'}
            <li class="page-item disabled"><a class="page-link" href="javascript:void(0);">{$v}</a></li>
            {elseif isset($v['current'])}
            <li class="page-item active"><a class="page-link" href="javascript:void(0);">{$v.page}</a></li>
            {else}
            <li class="page-item"><a class="page-link" href="{echo $router->build('/ebcms/cms-admin/content/index', array_merge($_GET, ['page'=>$v['page']]))}">{$v.page}</a></li>
            {/if}
            {/foreach}
        </ul>
    </nav>
</div>
{include common/footer@ebcms/admin}