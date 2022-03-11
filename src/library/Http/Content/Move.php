<?php

declare(strict_types=1);

namespace App\Ebcms\CmsAdmin\Http\Content;

use App\Ebcms\Admin\Http\Common;
use App\Ebcms\CmsAdmin\Model\Content;
use DigPHP\Request;

class Move extends Common
{
    public function post(
        Request $request,
        Content $contentModel
    ) {
        $contentModel->update([
            'category_id' => $request->post('category_id'),
        ], [
            'id' => $request->post('ids'),
        ]);
        return $this->success('操作成功！');
    }
}
