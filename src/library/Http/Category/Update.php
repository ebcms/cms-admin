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

class Update extends Common
{
    public function get(
        Category $categoryModel,
        Db $db,
        Request $request,
        Router $router
    ) {
        $category = $db->get('ebcms_cms_category', '*', [
            'id' => $request->get('id', 0, ['intval']),
        ]);

        switch ($category['type']) {
            case 'group':
                $form = new Builder('更新分组');
                $form->addItem(
                    (new Row())->addCol(
                        (new Col('col-md-3'))->addItem(
                            new Hidden('id', $category['id']),
                            new Input('分组名称', 'title', $category['title']),
                            (new Radio('导航是否显示', 'nav', $category['nav'], [
                                '1' => '是',
                                '2' => '否',
                            ]))->set('inline', true)
                        )
                    )
                );
                return $form;
                break;

            case 'channel':
                $form = new Builder('更新频道');
                $form->addItem(
                    (new Row())->addCol(
                        (new Col('col-md-3'))->addItem(
                            new Hidden('id', $category['id']),
                            new Select('上级', 'pid', $category['pid'], (function () use ($categoryModel): array {
                                $res = [];
                                $res[0] = '顶级';
                                foreach ($categoryModel->getAll() as $value) {
                                    if (in_array($value['type'], ['group', 'channel'])) {
                                        $res[$value['id']] = str_repeat('ㅤ', $value['_level'] + 1) . '' . $value['title'];
                                    }
                                }
                                return $res;
                            })()),
                            new Cover('封面', 'cover', $category['cover'], $router->build('/ebcms/admin/tool/upload')),
                            (new Radio('是否发布', 'state', $category['state'], [
                                '1' => '是',
                                '2' => '否',
                            ]))->set('inline', true),
                            (new Radio('导航显示', 'nav', $category['nav'], [
                                '1' => '是',
                                '0' => '否',
                            ]))->set('inline', true),
                            (new Summary('其他参数'))->addItem(
                                new Input('关键词', 'keywords', $category['keywords']),
                                new Textarea('简介', 'description', $category['description']),
                                new Input('频道页模板', 'tpl_category', $category['tpl_category']),
                                new Input('重定向地址', 'redirect_uri', $category['redirect_uri'], ['type' => 'url'])
                            )
                        ),
                        (new Col('col-md-9'))->addItem(
                            (new Input('标题', 'title', $category['title']))->set('help', '一般不超过20个字符')->set('required', 1),
                            (new Input('名称', 'name', $category['name']))->set('help', '一般用英文')->set('required', 1),
                            new Summernote('频道介绍', 'content', $category['content'], $router->build('/ebcms/admin/tool/upload'))
                        )
                    )
                );
                return $form;
                break;

            case 'list':
                $form = new Builder('更新栏目');
                $form->addItem(
                    (new Row())->addCol(
                        (new Col('col-md-3'))->addItem(
                            (new Hidden('id', $category['id'])),
                            new Select('上级', 'pid', $category['pid'], (function () use ($categoryModel): array {
                                $res = [];
                                $res[0] = '顶级';
                                foreach ($categoryModel->getAll() as $value) {
                                    if (in_array($value['type'], ['group', 'channel'])) {
                                        $res[$value['id']] = str_repeat('ㅤ', $value['_level'] + 1) . '' . $value['title'];
                                    }
                                }
                                return $res;
                            })()),
                            new Cover('封面', 'cover', $category['cover'], $router->build('/ebcms/admin/tool/upload')),
                            (new Radio('是否发布', 'state', $category['state'], [
                                '1' => '是',
                                '2' => '否',
                            ]))->set('inline', true),
                            (new Radio('是否允许内容排序', 'content_priority', $category['content_priority'], [
                                '1' => '是',
                                '2' => '否',
                            ]))->set('inline', true)->set('help', '栏目内容过多请勾选不允许'),
                            (new Radio('导航显示', 'nav', $category['nav'], [
                                '1' => '是',
                                '0' => '否',
                            ]))->set('inline', true),
                            (new Summary('其他参数'))->addItem(
                                new Input('关键词', 'keywords', $category['keywords']),
                                new Textarea('简介', 'description', $category['description']),
                                new Input('栏目页模板', 'tpl_category', $category['tpl_category']),
                                new Input('内容页模板', 'tpl_content', $category['tpl_content']),
                                (new Input('重定向地址', 'redirect_uri', $category['redirect_uri'], ['type' => 'url']))
                            )
                        ),
                        (new Col('col-md-9'))->addItem(
                            (new Input('标题', 'title', $category['title']))->set('help', '一般不超过20个字符')->set('required', 1),
                            (new Input('名称', 'name', $category['name']))->set('help', '一般用英文')->set('required', 1),
                            new Summernote('栏目介绍', 'content', $category['content'], $router->build('/ebcms/admin/tool/upload')),
                            (new Textarea('筛选项设置', 'filters', $category['filters']))->set('help', '一行一个，每行格式是：字段标题,字段,选项<br>支持的字段为：filter0 - filter5<br>例如：<br>是否包邮,filter0,包邮|不包邮<br>颜色,filter1,红|绿|蓝<br>大小,filter2,大号|中号|小号'),
                            (new Textarea('扩展字段设置', 'fields', $category['fields']))->set('help', '一行一个，每行格式是：字段名称,字段类型,字段说明,其他信息<br>支持的字段类型：Input,Textarea,Cover,Radio,Select,Checkbox,Upload,Summernote,SimpleMDE,Code,Files,Pics,Upload<br>例如：<br>下载地址,Upload,支持三方链接<br>来源<br>作者<br>封面图,Cover,推荐200x200<br>颜色,Radio,,红|绿|蓝')
                        )
                    )
                );
                return $form;
                break;

            case 'page':
                $form = new Builder('更新页面');
                $form->addItem(
                    (new Row())->addCol(
                        (new Col('col-md-3'))->addItem(
                            (new Hidden('id', $category['id'])),
                            new Select('上级', 'pid', $category['pid'], (function () use ($categoryModel): array {
                                $res = [];
                                $res[0] = '顶级';
                                foreach ($categoryModel->getAll() as $value) {
                                    if (in_array($value['type'], ['group'])) {
                                        $res[$value['id']] = str_repeat('ㅤ', $value['_level'] + 1) . '' . $value['title'];
                                    }
                                }
                                return $res;
                            })()),
                            new Cover('封面', 'cover', $category['cover'], $router->build('/ebcms/admin/tool/upload')),
                            (new Radio('是否发布', 'state', $category['state'], [
                                '1' => '是',
                                '2' => '否',
                            ]))->set('inline', true),
                            (new Radio('导航显示', 'nav', $category['nav'], [
                                '1' => '是',
                                '0' => '否',
                            ]))->set('inline', true),
                            (new Summary('其他参数'))->addItem(
                                new Input('关键词', 'keywords', $category['keywords']),
                                new Textarea('简介', 'description', $category['description']),
                                new Input('页面模板', 'tpl_content', $category['tpl_content']),
                                new Input('重定向地址', 'redirect_uri', $category['redirect_uri'], ['type' => 'url'])
                            )
                        ),
                        (new Col('col-md-9'))->addItem(
                            (new Input('标题', 'title', $category['title']))->set('help', '一般不超过20个字符')->set('required', 1),
                            (new Input('名称', 'name', $category['name']))->set('help', '一般用英文')->set('required', 1),
                            new Summernote('页面内容', 'content', $category['content'], $router->build('/ebcms/admin/tool/upload'))
                        )
                    )
                );
                return $form;
                break;

            default:
                return $this->error('错误，请删除该板块~');
                break;
        }
    }

    public function post(
        Request $request,
        Db $db
    ) {
        $update = array_intersect_key($request->post(), [
            'pid' => '',
            'title' => '',
            'name' => '',
            'keywords' => '',
            'description' => '',
            'cover' => '',
            'content' => '',
            'filters' => '',
            'fields' => '',
            'state' => '',
            'nav' => '',
            'content_priority' => '',
            'priority' => '',
            'tpl_category' => '',
            'tpl_content' => '',
            'redirect_uri' => '',
        ]);

        $db->update('ebcms_cms_category', $update, [
            'id' => $request->post('id', 0, ['intval']),
        ]);

        return $this->success('操作成功！');
    }
}
