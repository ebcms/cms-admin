<?php

declare(strict_types=1);

namespace App\Ebcms\CmsAdmin\Model;

use DigPHP\Database\Db;

class Content extends Db
{

    public function getRelationContents(string $tags): array
    {
        if ($tags = json_decode($tags, true)) {
            return $this->select('ebcms_cms_content', '*', [
                'state' => 1,
                'LIMIT' => 10,
                'tags[~]' => $tags,
                'ORDER' => [
                    'id' => 'DESC',
                ],
            ]);
        }
        return [];
    }
}
