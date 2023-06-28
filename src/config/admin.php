<?php

use App\Ebcms\CmsAdmin\Http\Category\Index;
use App\Ebcms\CmsAdmin\Http\Content\Index as ContentIndex;

return [
    'menus' => [[
        'title' => '栏目管理',
        'node' => Index::class,
    ], [
        'title' => '内容管理',
        'node' => ContentIndex::class,
    ]]
];
