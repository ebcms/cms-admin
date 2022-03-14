<?php

declare(strict_types=1);

namespace App\Ebcms\CmsAdmin\Http\Content;

use App\Ebcms\Admin\Http\Common;
use DigPHP\Database\Db;
use DigPHP\Request\Request;

class State extends Common
{
    public function post(
        Request $request,
        Db $db
    ) {
        $db->update('ebcms_cms_content', [
            'state' => $request->post('state'),
        ], [
            'id' => $request->post('ids'),
        ]);
        return $this->success('操作成功！');
    }
}
