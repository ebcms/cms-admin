<?php

use PsrPHP\Database\Db;
use PsrPHP\Router\Router;
use PsrPHP\Framework\Framework;

$items = Framework::execute(function (
    Router $router,
    Db $db
): array {
    $items = [];

    if (!$db->count('ebcms_cms_content', [
        'create_time[>]' => strtotime(date('Y-m-d 00:00:01'))
    ])) {
        $items[] = '今日尚未<a href="' . $router->build('/ebcms/cms-admin/content/index') . '" class="mx-1 fw-bold">发布</a>内容哦~';
    }
    if ($count = $db->count('ebcms_cms_content', [
        'state' => 2
    ])) {
        $items[] = '有<a href="' . $router->build('/ebcms/cms-admin/content/index', ['state' => 2]) . '" class="mx-1 fw-bold">' . $count . '</a>篇内容待审核';
    }
    $items[] = '共有<a href="' . $router->build('/ebcms/cms-admin/content/index') . '" class="mx-1 fw-bold">' . $db->count('ebcms_cms_content') . '</a>篇内容，其中待审核<a href="' . $router->build('/ebcms/cms-admin/content/index', ['state' => 2]) . '" class="mx-1 fw-bold">' . $count . '</a>篇';
    return $items;
});
?>
<div class="bg-light p-3">
    <div class="fs-3 fw-light mb-2 text-info">内容看板</div>
    <hr class="my-2 text-light">
    {foreach $items as $vo}
    <div>{echo $vo}</div>
    {/foreach}
</div>