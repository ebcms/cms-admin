<?php

declare(strict_types=1);

namespace App\Ebcms\CmsAdmin\Model;

use PsrPHP\Database\Db;

class Category
{

    private $db;

    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    public function getAll(): array
    {
        static $categorys;
        if ($categorys == null) {
            $categorys = $this->db->select('ebcms_cms_category', '*', [
                'ORDER' => [
                    'priority' => 'DESC',
                    'id' => 'ASC'
                ],
            ]);

            $categorys = $this->make($categorys);
        }
        return $categorys;
    }

    public function getOne($id): ?array
    {
        foreach ($this->getAll() as $value) {
            if ($value['id'] == $id) {
                return $value;
            }
        }
        return null;
    }

    private function make(array $data, $pitems = []): array
    {
        $res = [];
        $pid = $pitems ? $pitems[count($pitems) - 1]['id'] : 0;
        foreach ($data as $item) {
            if ($item['pid'] == $pid) {
                $item['_pitems'] = $pitems;
                $item['_level'] = count($pitems);
                $item['_path'] = (function () use ($item): string {
                    $paths = [];
                    foreach ($item['_pitems'] as $value) {
                        if ($value['type'] != 'group') {
                            $paths[] = $value['name'];
                        }
                    }
                    if ($item['type'] != 'group') {
                        $paths[] = $item['name'];
                    }
                    return implode('/', $paths);
                })();
                $item['_cids'] = [];
                $subitems = $this->make($data, array_merge($pitems, [$item]));
                if (in_array($item['type'], ['list', 'channel'])) {
                    $item['_cids'][] = $item['id'];
                }
                foreach ($subitems as $v) {
                    $item['_cids'] = array_merge($item['_cids'], $v['_cids']);
                }
                $item['_cids'] = array_values(array_unique($item['_cids']));
                $res[] = $item;
                foreach ($subitems as $v) {
                    $res[] = $v;
                }
            }
        }
        return $res;
    }
}
