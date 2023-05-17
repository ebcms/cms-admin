<?php

declare(strict_types=1);

namespace App\Ebcms\CmsAdmin\Http\Content;

use App\Ebcms\Admin\Http\Common;
use PsrPHP\Template\Template;

class Index extends Common
{

    public function get(
        Template $template
    ) {
        return $this->html($template->renderFromFile('content/index@ebcms/cms-admin'));
    }
}
