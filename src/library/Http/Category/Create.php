<?php

declare(strict_types=1);

namespace App\Ebcms\CmsAdmin\Http\Category;

use App\Ebcms\Admin\Http\Common;
use App\Ebcms\CmsAdmin\Model\Category;
use PsrPHP\Database\Db;
use PsrPHP\Form\Builder;
use PsrPHP\Form\Component\Col;
use PsrPHP\Form\Component\Row;
use PsrPHP\Form\Component\Summary;
use PsrPHP\Form\Field\Cover;
use PsrPHP\Form\Field\Hidden;
use PsrPHP\Form\Field\Input;
use PsrPHP\Form\Field\Radio;
use PsrPHP\Form\Field\Select;
use PsrPHP\Form\Field\Summernote;
use PsrPHP\Form\Field\Textarea;
use PsrPHP\Request\Request;
use PsrPHP\Router\Router;

class Create extends Common
{
    public function get(
        Router $router,
        Request $request,
        Category $categoryModel
    ) {
        switch ($request->get('type')) {

            case 'group':
                $form = new Builder('创建分组');
                $form->addItem(
                    (new Row())->addCol(
                        (new Col('col-md-3'))->addItem(
                            new Hidden('type', $request->get('type')),
                            new Input('分组名称', 'title'),
                            (new Radio('导航是否显示', 'nav', 1, [
                                '1' => '是',
                                '2' => '否',
                            ]))->set('inline', true)
                        )
                    )
                );
                return $form;
                break;

            case 'channel':
                $form = new Builder('创建频道');
                $form->addItem(
                    (new Row())->addCol(
                        (new Col('col-md-3'))->addItem(
                            new Hidden('type', $request->get('type')),
                            new Select('上级', 'pid', 0, (function () use ($categoryModel): array {
                                $res = [];
                                $res[0] = '顶级';
                                foreach ($categoryModel->getAll() as $value) {
                                    if (in_array($value['type'], ['group', 'channel'])) {
                                        $res[$value['id']] = str_repeat('ㅤ', $value['_level'] + 1) . '' . $value['title'];
                                    }
                                }
                                return $res;
                            })()),
                            new Cover('封面', 'cover', '', $router->build('/ebcms/admin/tool/upload')),
                            (new Radio('是否发布', 'state', 1, [
                                '1' => '是',
                                '2' => '否',
                            ]))->set('inline', true),
                            (new Radio('导航是否显示', 'nav', 1, [
                                '1' => '是',
                                '2' => '否',
                            ]))->set('inline', true),
                            (new Summary('其他参数'))->addItem(
                                new Input('关键词', 'keywords'),
                                new Textarea('简介', 'description'),
                                new Input('频道页模板', 'tpl_category'),
                                new Input('重定向地址', 'redirect_uri', '', ['type' => 'url'])
                            )
                        ),
                        (new Col('col-md-9'))->addItem(
                            (new Input('标题', 'title'))->set('help', '一般不超过20个字符')->set('attr.required', 1),
                            (new Input('名称', 'name'))->set('help', '一般用英文')->set('attr.required', 1),
                            new Summernote('频道介绍', 'content', '', $router->build('/ebcms/admin/tool/upload'))
                        )
                    )
                );
                return $form;
                break;

            case 'list':
                $form = new Builder('创建列表');
                $form->addItem(
                    (new Row())->addCol(
                        (new Col('col-md-3'))->addItem(
                            new Hidden('type', $request->get('type')),
                            new Select('上级', 'pid', 0, (function () use ($categoryModel): array {
                                $res = [];
                                $res[0] = '顶级';
                                foreach ($categoryModel->getAll() as $value) {
                                    if (in_array($value['type'], ['group', 'channel'])) {
                                        $res[$value['id']] = str_repeat('ㅤ', $value['_level'] + 1) . '' . $value['title'];
                                    }
                                }
                                return $res;
                            })()),
                            new Cover('封面', 'cover', '', $router->build('/ebcms/admin/tool/upload')),
                            (new Radio('是否发布', 'state', 1, [
                                '1' => '是',
                                '2' => '否',
                            ]))->set('inline', true),
                            (new Radio('是否允许内容排序', 'content_priority', 2, [
                                '1' => '是',
                                '2' => '否',
                            ]))->set('inline', true)->set('help', '栏目内容过多请勾选不允许'),
                            (new Radio('导航是否显示', 'nav', 1, [
                                '1' => '是',
                                '2' => '否',
                            ]))->set('inline', true),
                            (new Summary('其他参数'))->addItem(
                                new Input('关键词', 'keywords'),
                                new Textarea('简介', 'description'),
                                new Input('列表页模板', 'tpl_category'),
                                new Input('内容页模板', 'tpl_content'),
                                new Input('重定向地址', 'redirect_uri', '', ['type' => 'url'])
                            )
                        ),
                        (new Col('col-md-9'))->addItem(
                            (new Input('标题', 'title'))->set('help', '一般不超过20个字符')->set('attr.required', 1),
                            (new Input('名称', 'name'))->set('help', '一般用英文')->set('attr.required', 1),
                            new Summernote('栏目介绍', 'content', '', $router->build('/ebcms/admin/tool/upload')),
                            (new Textarea('筛选项设置', 'filters'))->set('help', '一行一个，每行格式是：字段标题,字段,选项<br>支持的字段为：filter0 - filter5<br>例如：<br>是否包邮,filter0,包邮|不包邮<br>颜色,filter1,红|绿|蓝<br>大小,filter2,大号|中号|小号'),
                            (new Textarea('扩展字段设置', 'fields'))->set('help', '一行一个，每行格式是：字段名称,字段类型,字段说明,其他信息<br>支持的字段类型：Input,Textarea,Cover,Radio,Select,Checkbox,Upload,Summernote,SimpleMDE,Code,Files,Pics,Upload<br>例如：<br>下载地址,Upload,支持三方链接<br>来源<br>作者<br>封面图,Cover,推荐200x200<br>颜色,Radio,,红|绿|蓝')
                        )
                    )
                );
                return $form;
                break;

            case 'page':
                $form = new Builder('创建页面');
                $form->addItem(
                    (new Row())->addCol(
                        (new Col('col-md-3'))->addItem(
                            new Hidden('type', $request->get('type')),
                            new Select('上级', 'pid', 0, (function () use ($categoryModel): array {
                                $res = [];
                                $res[0] = '顶级';
                                foreach ($categoryModel->getAll() as $value) {
                                    if (in_array($value['type'], ['group'])) {
                                        $res[$value['id']] = str_repeat('ㅤ', $value['_level'] + 1) . '' . $value['title'];
                                    }
                                }
                                return $res;
                            })()),
                            new Cover('封面', 'cover', '', $router->build('/ebcms/admin/tool/upload')),
                            (new Radio('是否发布', 'state', 1, [
                                '1' => '是',
                                '2' => '否',
                            ]))->set('inline', true),
                            (new Radio('导航是否显示', 'nav', 1, [
                                '1' => '是',
                                '2' => '否',
                            ]))->set('inline', true),
                            (new Summary('其他参数'))->addItem(
                                new Input('关键词', 'keywords'),
                                new Textarea('简介', 'description'),
                                new Input('页面模板', 'tpl_content'),
                                new Input('重定向地址', 'redirect_uri', '', ['type' => 'url'])
                            )
                        ),
                        (new Col('col-md-9'))->addItem(
                            (new Input('标题', 'title'))->set('help', '一般不超过20个字符')->set('attr.required', 1),
                            (new Input('名称', 'name'))->set('help', '一般用英文')->set('attr.required', 1),
                            new Summernote('页面内容', 'content', '', $router->build('/ebcms/admin/tool/upload'))
                        )
                    )
                );
                return $form;
                break;

            default:
                return $this->error('参数错误~');
                break;
        }
    }

    public function post(
        Request $request,
        Db $db
    ) {
        $db->insert('ebcms_cms_category', [
            'pid' => $request->post('pid', 0),
            'type' => $request->post('type', ''),
            'title' => $request->post('title', ''),
            'name' => $request->post('name', ''),
            'keywords' => $request->post('keywords', ''),
            'description' => $request->post('description', ''),
            'cover' => $request->post('cover', ''),
            'content' => $request->post('content', ''),
            'filters' => $request->post('filters', ''),
            'fields' => $request->post('fields', ''),
            'state' => $request->post('state', 1),
            'nav' => $request->post('nav', 1),
            'content_priority' => $request->post('content_priority', 2),
            'tpl_category' => $request->post('tpl_category', ''),
            'tpl_content' => $request->post('tpl_content', ''),
            'redirect_uri' => $request->post('redirect_uri', ''),
            'priority' => 0,
        ]);
        return $this->success('操作成功！');
    }
}
