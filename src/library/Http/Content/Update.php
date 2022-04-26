<?php

declare(strict_types=1);

namespace App\Ebcms\CmsAdmin\Http\Content;

use App\Ebcms\Admin\Http\Common;
use DiggPHP\Database\Db;
use DiggPHP\Form\Builder;
use DiggPHP\Form\Component\Col;
use DiggPHP\Form\Component\Html;
use DiggPHP\Form\Component\Row;
use DiggPHP\Form\Component\Summary;
use DiggPHP\Form\Field\Checkbox;
use DiggPHP\Form\Field\Cover;
use DiggPHP\Form\Field\Hidden;
use DiggPHP\Form\Field\Input;
use DiggPHP\Form\Field\Radio;
use DiggPHP\Form\Field\Summernote;
use DiggPHP\Form\Field\Textarea;
use DiggPHP\Request\Request;
use DiggPHP\Router\Router;
use DiggPHP\Framework\Config;
use Exception;

class Update extends Common
{
    public function get(
        Db $db,
        Router $router,
        Config $config,
        Request $request
    ) {
        $content = $db->get('ebcms_cms_content', '*', [
            'id' => $request->get('id', 0, ['intval']),
        ]);

        if (!$category = $db->get('ebcms_cms_category', '*', [
            'id' => $content['category_id'],
        ])) {
            return $this->error('内容错误，请删除！');
        }

        $form = new Builder('编辑内容');
        $form->addItem(
            (new Row())->addCol(
                (new Col('col-md-3'))->addItem(
                    new Radio('状态', 'state', $content['state'], [
                        '1' => '发布',
                        '2' => '待审',
                    ]),
                    new Cover('封面图', 'cover', $content['cover'], $router->build('/ebcms/admin/upload')),
                    new Checkbox('属性', 'attrs', json_decode($content['attrs'] ?: '[]', true), array_combine($config->get('attrs@ebcms.cms-admin', []), $config->get('attrs@ebcms.cms-admin', [])), [
                        'help' => '在/config/ebcms/cms-admin/attrs.php中配置',
                    ]),
                    (new Summary('其他参数'))->addItem(
                        new Input('关键词', 'keywords', $content['keywords']),
                        new Textarea('简介', 'description', $content['description']),
                        new Input('别名', 'alias', $content['alias']),
                        new Input('模板', 'tpl', $content['tpl']),
                        new Input('重定向地址', 'redirect_uri', $content['redirect_uri']),
                        ...(function () use ($category, $content): array {
                            $res = [];
                            if ($category['content_priority'] == 1) {
                                $res[] = new Input('权重', 'priority', $content['priority']);
                            }
                            return $res;
                        })()
                    )
                ),
                (new Col('col-md-9'))->addItem(
                    new Hidden('id', $content['id']),
                    new Input('标题', 'title', $content['title'], [
                        'help' => '一般不超过80个字符',
                        'required' => 'required',
                    ]),
                    new Html((function () use ($category, $content): string {
                        if ($category['filters']) {
                            $str = '<div class="card bg-light my-2">';
                            // $str .= '<div class="card-header">筛选项</div>';
                            $str .= '<div class="card-body">';
                            foreach (array_filter(explode(PHP_EOL, $category['filters'])) as $val) {
                                list($label, $name, $items) = explode(',', trim($val) . ',,,,');
                                $str .= new Checkbox($label, $name, json_decode($content[$name] ?: '[]', true), array_combine(explode('|', $items), explode('|', $items)));
                            }
                            $str .= '</div>';
                            $str .= '</div>';
                            return $str;
                        } else {
                            return '';
                        }
                    })()),
                    new Summernote('内容', 'body', $content['body'], $router->build('/ebcms/admin/upload')),
                    new Input('聚合标签', 'tags', implode(' ', json_decode($content['tags'] ?: '[]', true)), [
                        'help' => '多个标签用空格分割'
                    ]),
                    new Html((function () use ($category, $content, $router): string {
                        if ($category['fields']) {
                            $str = '<div class="card bg-light my-2">';
                            // $str .= '<div class="card-header">自定义字段</div>';
                            $str .= '<div class="card-body">';
                            $extra = unserialize($content['extra']);
                            foreach (array_filter(explode(PHP_EOL, $category['fields'] ?? '')) as $val) {
                                list($field, $type, $help, $items) = explode(',', trim($val) . ',,,,');
                                $type = $type ?: 'Input';
                                $help = $help ?: '';
                                $items = $items ?: '';

                                $field_class = 'DiggPHP\\Form\\Field\\' . $type;
                                if (!class_exists($field_class)) {
                                    throw new Exception('类型' . $type . '不支持');
                                }

                                if (in_array($type, ['Checkbox'])) {
                                    $obj = new $field_class($field, 'extra[' . $field . ']', $extra[$field] ?? []);
                                } else {
                                    $obj = new $field_class($field, 'extra[' . $field . ']', $extra[$field] ?? '');
                                }
                                $obj->set('help', $help);
                                $obj->set('upload_url', $router->build('/ebcms/admin/upload'));
                                $obj->set('items', array_combine(explode('|', $items), explode('|', $items)));
                                $str .= $obj;
                            }
                            $str .= '</div>';
                            $str .= '</div>';
                            return $str;
                        } else {
                            return '';
                        }
                    })())
                )
            )
        );
        return $this->html($form->__toString());
    }
    public function post(
        Request $request,
        Db $db
    ) {
        $update = array_intersect_key($request->post(), [
            'title' => '',
            'cover' => '',
            'keywords' => '',
            'description' => '',
            'body' => '',
            'tpl' => '',
            'alias' => '',
            'state' => '',
            'redirect_uri' => '',
            'priority' => '',
        ]);
        $update['update_time'] = time();
        if ($request->has('post.extra')) {
            $update['extra'] = serialize($request->post('extra'));
        }
        if ($request->has('post.tags')) {
            $update['tags'] = json_encode(array_filter(
                array_unique(
                    explode(',', str_replace([' ', '|', ',', '，'], ',', $request->post('tags')))
                ),
                function ($val) {
                    return strlen($val) > 0 ? true : false;
                }
            ), JSON_UNESCAPED_UNICODE);
        }
        if ($request->has('post.attrs')) {
            $update['attrs'] = json_encode($request->post('attrs', []), JSON_UNESCAPED_UNICODE);
        }
        for ($i = 0; $i < 6; $i++) {
            if ($request->has('post.filter' . $i)) {
                $update['filter' . $i] = json_encode($request->post('filter' . $i), JSON_UNESCAPED_UNICODE);
            }
        }

        $db->update('ebcms_cms_content', $update, [
            'id' => $request->post('id', 0, ['intval']),
        ]);

        return $this->success('操作成功！');
    }
}
