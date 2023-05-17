{include common/header@ebcms/cms-admin}
<script>
    function priority(id, type) {
        $.ajax({
            type: "POST",
            url: "{echo $router->build('/ebcms/cms-admin/category/priority')}",
            data: {
                id: id,
                type: type,
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
</script>
<div class="container">
    <div class="h1 my-4">栏目管理</div>
    <div class="my-3">
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                新建
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                <li><a class="dropdown-item" href="{echo $router->build('/ebcms/cms-admin/category/create', ['type'=>'group'])}">分组</a></li>
                <li><a class="dropdown-item" href="{echo $router->build('/ebcms/cms-admin/category/create', ['type'=>'channel'])}">频道</a></li>
                <li><a class="dropdown-item" href="{echo $router->build('/ebcms/cms-admin/category/create', ['type'=>'list'])}">列表</a></li>
                <li><a class="dropdown-item" href="{echo $router->build('/ebcms/cms-admin/category/create', ['type'=>'page'])}">页面</a></li>
            </ul>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>标题</th>
                    <th>排序</th>
                    <th>管理</th>
                </tr>
            </thead>
            <tbody>
                {foreach $categorys as $vo}
                <tr>
                    <td>
                        {:str_repeat('ㅤ', $vo['_level'])}
                        {if $vo['type']=='group'}
                        <svg t="1632989934694" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="6520" width="20" height="20">
                            <path d="M81.16 412.073333L0 709.653333V138.666667a53.393333 53.393333 0 0 1 53.333333-53.333334h253.413334a52.986667 52.986667 0 0 1 37.713333 15.62l109.253333 109.253334a10.573333 10.573333 0 0 0 7.54 3.126666H842.666667a53.393333 53.393333 0 0 1 53.333333 53.333334v74.666666H173.773333a96.2 96.2 0 0 0-92.613333 70.74z m922-7.113333a52.933333 52.933333 0 0 0-42.386667-20.96H173.773333a53.453333 53.453333 0 0 0-51.453333 39.333333L11.773333 828.666667a53.333333 53.333333 0 0 0 51.453334 67.333333h787a53.453333 53.453333 0 0 0 51.453333-39.333333l110.546667-405.333334a52.953333 52.953333 0 0 0-9.073334-46.373333z" fill="#5C5C66" p-id="6521"></path>
                        </svg>
                        {elseif $vo['type']=='channel'}
                        <svg t="1632990353866" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="17940" width="20" height="20">
                            <path d="M804.1 64H219.9c-47.8 0-86.6 38.1-86.6 85.2v725.6c0 47 38.7 85.2 86.6 85.2h584.3c47.8 0 86.6-38.2 86.6-85.2V149.2c-0.1-47.1-38.9-85.2-86.7-85.2zM219.9 832.2V385.3h165.9v489.5H263.1c-23.9 0-43.2-19.1-43.2-42.6z m584.2 0c0 23.5-19.4 42.6-43.3 42.6h-283V385.3h326.3v446.9z m0-538.9H219.9V191.7c0-23.5 19.4-42.5 43.3-42.5h497.7c23.9 0 43.3 19 43.3 42.5v101.6z" fill="#040000" p-id="17941"></path>
                        </svg>
                        {elseif $vo['type']=='list'}
                        <svg t="1632990396475" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="18111" width="20" height="20">
                            <path d="M375.1 234.3h-45.6c-18.9 0-34.2 15.3-34.2 34.3 0 18.9 15.3 34.2 34.2 34.2h45.6c18.9 0 34.2-15.3 34.2-34.2 0-18.9-15.3-34.3-34.2-34.3z m319.5 410.8H512c-18.9 0-34.2 15.3-34.2 34.3 0 18.9 15.3 34.3 34.2 34.3h182.6c18.9 0 34.2-15.4 34.2-34.3s-15.3-34.3-34.2-34.3zM375.1 508.2h-45.6c-18.9 0-34.2 15.3-34.2 34.3 0 18.9 15.3 34.2 34.2 34.2h45.6c18.9 0 34.2-15.3 34.2-34.2 0-19-15.3-34.3-34.2-34.3z m0-137h-45.6c-18.9 0-34.2 15.4-34.2 34.3s15.3 34.3 34.2 34.3h45.6c18.9 0 34.2-15.4 34.2-34.3s-15.3-34.3-34.2-34.3z m319.5 137H512c-18.9 0-34.2 15.3-34.2 34.3 0 18.9 15.3 34.2 34.2 34.2h182.6c18.9 0 34.2-15.3 34.2-34.2 0-19-15.3-34.3-34.2-34.3zM799.5 64h-575c-47.1 0-85.2 38.1-85.2 85.2v725.7c0 47 38.1 85.2 85.2 85.2h575c47 0 85.2-38.1 85.2-85.2V149.2c0-47.1-38.1-85.2-85.2-85.2z m0 768.2c0 23.5-19.1 42.6-42.6 42.6H267.1c-23.5 0-42.6-19.1-42.6-42.6V191.8c0-23.5 19.1-42.6 42.6-42.6h489.8c23.5 0 42.6 19.1 42.6 42.6v640.4z m-104.9-461H512c-18.9 0-34.2 15.4-34.2 34.3s15.3 34.3 34.2 34.3h182.6c18.9 0 34.2-15.4 34.2-34.3s-15.3-34.3-34.2-34.3z m0-136.9H512c-18.9 0-34.2 15.3-34.2 34.3 0 18.9 15.3 34.2 34.2 34.2h182.6c18.9 0 34.2-15.3 34.2-34.2 0-18.9-15.3-34.3-34.2-34.3zM375.1 645.1h-45.6c-18.9 0-34.2 15.3-34.2 34.3 0 18.9 15.3 34.3 34.2 34.3h45.6c18.9 0 34.2-15.4 34.2-34.3s-15.3-34.3-34.2-34.3z" fill="#040000" p-id="18112"></path>
                        </svg>
                        {else}
                        <svg t="1632990330185" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="17761" width="20" height="20">
                            <path d="M694.6 645.1H329.4c-18.9 0-34.2 15.3-34.2 34.3 0 18.9 15.3 34.3 34.2 34.3h365.2c18.9 0 34.2-15.4 34.2-34.3s-15.3-34.3-34.2-34.3zM329.4 439.8H512c18.9 0 34.2-15.4 34.2-34.3s-15.3-34.3-34.2-34.3H329.4c-18.9 0-34.2 15.4-34.2 34.3s15.3 34.3 34.2 34.3z m0-137H512c18.9 0 34.2-15.3 34.2-34.2 0-18.9-15.3-34.3-34.2-34.3H329.4c-18.9 0-34.2 15.3-34.2 34.3 0 18.9 15.3 34.2 34.2 34.2zM799.5 64h-575c-47.1 0-85.2 38.1-85.2 85.2v725.7c0 47 38.1 85.2 85.2 85.2h575c47 0 85.2-38.1 85.2-85.2V149.2c0-47.1-38.1-85.2-85.2-85.2z m0 768.2c0 23.5-19.1 42.6-42.6 42.6H267.1c-23.5 0-42.6-19.1-42.6-42.6V191.8c0-23.5 19.1-42.6 42.6-42.6h489.8c23.5 0 42.6 19.1 42.6 42.6v640.4z m-104.9-324H329.4c-18.9 0-34.2 15.3-34.2 34.3 0 18.9 15.3 34.2 34.2 34.2h365.2c18.9 0 34.2-15.3 34.2-34.2 0-19-15.3-34.3-34.2-34.3zM706 234.3h-68.5c-25.2 0-45.6 20.5-45.6 45.6V394c0 25.3 20.4 45.7 45.6 45.7H706c25.2 0 45.6-20.4 45.6-45.7V279.9c0.1-25.1-20.4-45.6-45.6-45.6z" fill="#040000" p-id="17762"></path>
                        </svg>
                        {/if}
                        {$vo.title}
                        {if $vo['state'] != 1}
                        <span class="text-warning">[未发布]</span>
                        {/if}
                        {if $vo['nav'] != 1}
                        <span class="text-success">[隐藏]</span>
                        {/if}
                        {if $vo['redirect_uri']}
                        <span class="text-info">[跳转]</span>
                        {/if}
                        <span class="float-end text-muted">[id:{$vo.id}]</span>
                    </td>
                    <td class="text-nowrap">
                        <a href="#" onclick="priority('{$vo.id}', 'up')">上移</a>
                        <a href="#" onclick="priority('{$vo.id}', 'down')">下移</a>
                    </td>
                    <td class="text-nowrap">
                        <a href="{echo $router->build('/ebcms/cms-admin/category/update', ['id'=>$vo['id']])}">编辑</a>
                        <a href="{echo $router->build('/ebcms/cms-admin/category/delete', ['id'=>$vo['id']])}" onclick="return confirm('删除后无法恢复，确定删除？');">删除</a>
                    </td>
                </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</div>
{include common/footer@ebcms/admin}