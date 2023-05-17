<?php

declare(strict_types=1);

namespace App\Ebcms\CmsAdmin\Http\Content;

use App\Ebcms\Admin\Http\Common;
use PsrPHP\Database\Db;
use PsrPHP\Request\Request;

class Delete extends Common
{
    public function post(
        Request $request,
        Db $db
    ) {
        $db->delete('ebcms_cms_content', [
            'id' => $request->post('ids'),
        ]);
        return $this->success('操作成功！');
    }
}
