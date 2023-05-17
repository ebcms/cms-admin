<?php

use App\Ebcms\Admin\Model\Account;
use App\Ebcms\CmsAdmin\Http\Category\Index;
use App\Ebcms\CmsAdmin\Http\Content\Index as ContentIndex;
use PsrPHP\Router\Router;
use PsrPHP\Framework\Framework;

return [
    'menus' => Framework::execute(function (
        Account $account,
        Router $router
    ): array {
        $menus = [];
        if ($account->checkAuth(Index::class)) {
            $menus[] = [
                'title' => '栏目管理',
                'url' => $router->build('/ebcms/cms-admin/category/index'),
            ];
        }
        if ($account->checkAuth(ContentIndex::class)) {
            $menus[] = [
                'title' => '内容管理',
                'url' => $router->build('/ebcms/cms-admin/content/index'),
            ];
        }
        return $menus;
    })
];
