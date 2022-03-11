<?php

declare(strict_types=1);

namespace App\Ebcms\CmsAdmin\Http\Category;

use App\Ebcms\Admin\Http\Common;
use App\Ebcms\CmsAdmin\Model\Content;
use App\Ebcms\CmsAdmin\Model\Category;
use DigPHP\Request\Request;

class Delete extends Common
{
    public function get(
        Request $request,
        Content $contentModel,
        Category $categoryModel
    ) {
        if ($categoryModel->get('ebcms_cms_category', '*', [
            'pid' => $request->get('id', 0, ['intval'])
        ])) {
            return $this->error('请先删除子栏目~');
        }

        $contentModel->delete('ebcms_cms_content', [
            'category_id' => $request->get('id', 0, ['intval']),
        ]);
        $categoryModel->delete('ebcms_cms_category', [
            'id' => $request->get('id', 0, ['intval']),
        ]);
        return $this->success('操作成功！');
    }
}
