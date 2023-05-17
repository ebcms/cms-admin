<?php

declare(strict_types=1);

namespace App\Ebcms\CmsAdmin\Http\Category;

use App\Ebcms\Admin\Http\Common;
use PsrPHP\Database\Db;
use PsrPHP\Request\Request;

class Priority extends Common
{
    public function post(
        Request $request,
        Db $db
    ) {
        $type = $request->post('type');
        $category = $db->get('ebcms_cms_category', '*', [
            'id' => $request->post('id'),
        ]);

        $categorys = $db->select('ebcms_cms_category', '*', [
            'pid' => $category['pid'],
            'ORDER' => [
                'priority' => 'DESC',
                'id' => 'ASC',
            ],
        ]);

        $count = $db->count('ebcms_cms_category', [
            'id[!]' => $category['id'],
            'pid' => $category['pid'],
            'priority[<=]' => $category['priority'],
            'ORDER' => [
                'priority' => 'DESC',
                'id' => 'ASC',
            ],
        ]);
        $change_key = $type == 'up' ? $count + 1 : $count - 1;

        if ($change_key < 0) {
            return $this->error('已经是最有一位了！');
        }
        if ($change_key > count($categorys) - 1) {
            return $this->error('已经是第一位了！');
        }
        $categorys = array_reverse($categorys);
        foreach ($categorys as $key => $value) {
            if ($key == $change_key) {
                $db->update('ebcms_cms_category', [
                    'priority' => $count,
                ], [
                    'id' => $value['id'],
                ]);
            } elseif ($key == $count) {
                $db->update('ebcms_cms_category', [
                    'priority' => $change_key,
                ], [
                    'id' => $value['id'],
                ]);
            } else {
                $db->update('ebcms_cms_category', [
                    'priority' => $key,
                ], [
                    'id' => $value['id'],
                ]);
            }
        }
        return $this->success('操作成功！');
    }
}
