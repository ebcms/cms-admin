<?php

declare(strict_types=1);

namespace App\Ebcms\CmsAdmin\Http\Content;

use App\Ebcms\Admin\Http\Common;
use PsrPHP\Database\Db;
use PsrPHP\Form\Builder;
use PsrPHP\Form\Component\Col;
use PsrPHP\Form\Component\Html;
use PsrPHP\Form\Component\Row;
use PsrPHP\Form\Component\Summary;
use PsrPHP\Form\Field\Checkbox;
use PsrPHP\Form\Field\Cover;
use PsrPHP\Form\Field\Hidden;
use PsrPHP\Form\Field\Input;
use PsrPHP\Form\Field\Radio;
use PsrPHP\Form\Field\Summernote;
use PsrPHP\Form\Field\Textarea;
use PsrPHP\Request\Request;
use PsrPHP\Router\Router;
use PsrPHP\Framework\Config;
use Exception;

class Create extends Common
{
    public function get(
        Db $db,
        Request $request,
        Config $config,
        Router $router
    ) {
        if (!$category = $db->get('ebcms_cms_category', '*', [
            'id' => $request->get('category_id'),
        ])) {
            return $this->error('操作错误！');
        }

        $content = $db->get('ebcms_cms_content', '*', [
            'id' => $request->get('copyfrom'),
        ]) ?: [];

        return (new Builder('创建内容'))->addItem(
            (new Row())->addCol(
                (new Col('col-md-3'))->addItem(
                    new Radio('状态', 'state', $content['state'] ?? '1', [
                        '1' => '发布',
                        '2' => '待审',
                    ]),
                    new Cover('封面图', 'cover', $content['cover'] ?? '', $router->build('/ebcms/admin/tool/upload')),
                    new Checkbox('属性', 'attrs', (array) json_decode($content['attrs'] ?? '[]', true), array_combine($config->get('attrs@ebcms.cms-admin', []), $config->get('attrs@ebcms.cms-admin', [])), [
                        'help' => '在/config/ebcms/cms-admin/attrs.php中配置',
                    ]),
                    (new Summary('其他参数'))->addItem(
                        new Input('关键词', 'keywords', $content['keywords'] ?? ''),
                        new Textarea('简介', 'description', $content['description'] ?? ''),
                        new Input('别名', 'alias', $content['alias'] ?? ''),
                        new Input('模板', 'tpl', $content['tpl'] ?? ''),
                        new Input('重定向地址', 'redirect_uri', $content['redirect_uri'] ?? ''),
                        ...(function () use ($category, $content): array {
                            $res = [];
                            if ($category['content_priority'] == 1) {
                                $res[] = new Input('权重', 'priority', $content['priority'] ?? 0);
                            }
                            return $res;
                        })()
                    )
                ),
                (new Col('col-md-9'))->addItem(
                    new Hidden('category_id', $category['id']),
                    new Input('标题', 'title', $content['title'] ?? '', [
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
                                $str .= new Checkbox($label, $name, json_decode($content[$name] ?? '[]', true), array_combine(explode('|', $items), explode('|', $items)));
                            }
                            $str .= '</div>';
                            $str .= '</div>';
                            return $str;
                        } else {
                            return '';
                        }
                    })()),
                    new Summernote('内容', 'body', $content['body'] ?? '', $router->build('/ebcms/admin/tool/upload')),
                    new Input('聚合标签', 'tags', implode(' ', json_decode($content['tags'] ?? '[]', true)), [
                        'help' => '多个标签用空格分割',
                    ]),
                    new Html((function () use ($category, $content, $router): string {
                        if ($category['fields']) {
                            $str = '<div class="card bg-light my-2">';
                            // $str .= '<div class="card-header">自定义字段</div>';
                            $str .= '<div class="card-body">';
                            $extra = isset($content['extra']) ? unserialize($content['extra']) : [];
                            foreach (array_filter(explode(PHP_EOL, $category['fields'] ?? '')) as $val) {
                                list($field, $type, $help, $ext) = explode(',', trim($val) . ',,,,');
                                $type = $type ?: 'Input';
                                $help = $help ?: '';
                                $ext = $ext ?: '';

                                $field_class = 'PsrPHP\\Form\\Field\\' . $type;
                                if (!class_exists($field_class)) {
                                    throw new Exception('类型' . $type . '不支持');
                                }

                                if (in_array($type, ['Checkbox'])) {
                                    $obj = new $field_class($field, 'extra[' . $field . ']', $extra[$field] ?? []);
                                } else {
                                    $obj = new $field_class($field, 'extra[' . $field . ']', $extra[$field] ?? '');
                                }
                                $obj->set('help', $help);
                                $obj->set('upload_url', $router->build('/ebcms/admin/tool/upload'));
                                $obj->set('items', array_combine(explode('|', $ext), explode('|', $ext)));
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
    }

    public function post(
        Db $db,
        Request $request
    ) {
        $data = [
            'category_id' => $request->post('category_id', 0),
            'title' => $request->post('title', ''),
            'keywords' => $request->post('keywords', ''),
            'description' => $request->post('description', ''),
            'cover' => $request->post('cover', ''),
            'body' => $request->post('body', ''),
            'extra' => serialize($request->post('extra')),
            'state' => $request->post('state', 0),
            'tpl' => $request->post('tpl', ''),
            'alias' => $request->post('alias', ''),
            'tags' => json_encode(array_filter(
                array_unique(
                    explode(',', str_replace([' ', '|', ',', '，'], ',', $request->post('tags')))
                ),
                function ($val) {
                    return strlen($val) > 0 ? true : false;
                }
            ), JSON_UNESCAPED_UNICODE),
            'attrs' => json_encode($request->post('attrs', []), JSON_UNESCAPED_UNICODE),
            'redirect_uri' => $request->post('redirect_uri', ''),
            'priority' => $request->post('priority', 0),
            'filter0' => json_encode($request->post('filter0', []), JSON_UNESCAPED_UNICODE),
            'filter1' => json_encode($request->post('filter1', []), JSON_UNESCAPED_UNICODE),
            'filter2' => json_encode($request->post('filter2', []), JSON_UNESCAPED_UNICODE),
            'filter3' => json_encode($request->post('filter3', []), JSON_UNESCAPED_UNICODE),
            'filter4' => json_encode($request->post('filter4', []), JSON_UNESCAPED_UNICODE),
            'filter5' => json_encode($request->post('filter5', []), JSON_UNESCAPED_UNICODE),
            'create_time' => time(),
            'update_time' => time(),
        ];
        $db->insert('ebcms_cms_content', $data);
        return $this->success('操作成功！');
    }
}
