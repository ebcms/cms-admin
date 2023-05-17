<?php

declare(strict_types=1);

namespace App\Ebcms\CmsAdmin\Http\Category;

use App\Ebcms\Admin\Http\Common;
use PsrPHP\Database\Db;
use PsrPHP\Request\Request;

class Delete extends Common
{
    public function get(
        Request $request,
        Db $db
    ) {
        if ($db->get('ebcms_cms_category', '*', [
            'pid' => $request->get('id', 0, ['intval'])
        ])) {
            return $this->error('请先删除子栏目~');
        }

        $db->delete('ebcms_cms_content', [
            'category_id' => $request->get('id', 0, ['intval']),
        ]);
        $db->delete('ebcms_cms_category', [
            'id' => $request->get('id', 0, ['intval']),
        ]);
        return $this->success('操作成功！');
    }
}
