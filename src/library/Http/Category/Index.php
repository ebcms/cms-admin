<?php

declare(strict_types=1);

namespace App\Ebcms\CmsAdmin\Http\Category;

use App\Ebcms\Admin\Http\Common;
use App\Ebcms\CmsAdmin\Model\Category;
use PsrPHP\Template\Template;

class Index extends Common
{

    public function get(
        Category $categoryModel,
        Template $template
    ) {
        return $this->html($template->renderFromFile('category/index@ebcms/cms-admin', [
            'categorys' => $categoryModel->getAll(),
        ]));
    }
}
