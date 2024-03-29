<?php

declare(strict_types=1);

namespace App\Ebcms\CmsAdmin;

use PsrPHP\Framework\AppInterface;
use PDO;

class App implements AppInterface
{

    public static function onInstall()
    {
        $sql = self::getInstallSql();
        fwrite(STDOUT, "是否安装演示数据？y [y,n]：");
        switch (trim((string) fgets(STDIN))) {
            case '':
            case 'y':
            case 'yes':
                fwrite(STDOUT, "安装演示数据\n");
                $sql .= PHP_EOL . self::getDemoSql();
                break;

            default:
                fwrite(STDOUT, "不安装演示数据\n");
                break;
        }
        self::execSql($sql);
    }

    public static function onUninstall()
    {
        $sql = '';
        fwrite(STDOUT, "是否删除数据库？y [y,n]：");
        switch (trim((string) fgets(STDIN))) {
            case '':
            case 'y':
            case 'yes':
                fwrite(STDOUT, "删除数据库\n");
                $sql .= PHP_EOL . self::getUninstallSql();
                break;
            default:
                break;
        }
        self::execSql($sql);
    }

    private static function execSql(string $sql)
    {
        $sqls = array_filter(explode(";" . PHP_EOL, $sql));

        $prefix = 'prefix_';
        $cfg_file = getcwd() . '/config/database.php';
        $cfg = (array)include $cfg_file;
        if (isset($cfg['master']['prefix'])) {
            $prefix = $cfg['master']['prefix'];
        }

        $dbh = new PDO("{$cfg['master']['database_type']}:host={$cfg['master']['server']};dbname={$cfg['master']['database_name']}", $cfg['master']['username'], $cfg['master']['password'], $cfg['master']['option']);

        $dbh->exec('SET SQL_MODE=ANSI_QUOTES');
        $dbh->exec('SET NAMES utf8mb4 COLLATE utf8mb4_general_ci');

        foreach ($sqls as $sql) {
            $dbh->exec(str_replace('prefix_', $prefix, $sql . ';'));
        }
    }

    private static function getInstallSql(): string
    {
        return <<<'str'
DROP TABLE IF EXISTS `prefix_ebcms_cms_category`;
CREATE TABLE `prefix_ebcms_cms_category` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '节点ID',
    `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父节点',
    `type` varchar(255) NOT NULL DEFAULT '' COMMENT '类型 group channel list page',
    `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
    `name` varchar(255) NOT NULL COMMENT '名称',
    `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字',
    `description` varchar(255) NOT NULL DEFAULT '' COMMENT '简介',
    `cover` varchar(255) NOT NULL DEFAULT '' COMMENT '封面',
    `content` text NOT NULL COMMENT '内容',
    `tpl_category` varchar(255) NOT NULL DEFAULT '' COMMENT '模板',
    `tpl_content` varchar(255) NOT NULL DEFAULT '' COMMENT '内容页模板',
    `priority` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
    `state` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否发布',
    `nav` tinyint(3) unsigned NOT NULL DEFAULT '1',
    `content_priority` tinyint(3) unsigned NOT NULL DEFAULT '2' COMMENT '是否允许内容排序',
    `redirect_uri` varchar(255) NOT NULL DEFAULT '' COMMENT '重定向链接',
    `filters` text NOT NULL,
    `fields` text NOT NULL,
    PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT COMMENT='内容栏目表';
DROP TABLE IF EXISTS `prefix_ebcms_cms_content`;
CREATE TABLE `prefix_ebcms_cms_content` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '节点ID',
    `category_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类id',
    `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
    `cover` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图',
    `keywords` varchar(255) NOT NULL DEFAULT '' COMMENT '关键词',
    `description` varchar(255) NOT NULL DEFAULT '' COMMENT '摘要',
    `body` text NOT NULL,
    `extra` text NOT NULL,
    `alias` varchar(255) NOT NULL DEFAULT '' COMMENT '文件名称',
    `tpl` varchar(255) NOT NULL DEFAULT '' COMMENT '模板',
    `click` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击量',
    `tags` text NOT NULL,
    `attrs` text NOT NULL,
    `priority` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '优先权',
    `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作时间',
    `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
    `state` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
    `redirect_uri` varchar(255) NOT NULL DEFAULT '',
    `filter0` varchar(255) NOT NULL DEFAULT '',
    `filter1` varchar(255) NOT NULL DEFAULT '',
    `filter2` varchar(255) NOT NULL DEFAULT '',
    `filter3` varchar(255) NOT NULL DEFAULT '',
    `filter4` varchar(255) NOT NULL DEFAULT '',
    `filter5` varchar(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `list` (`category_id`,`state`,`id`) USING BTREE,
    KEY `list2` (`state`,`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;
str;
    }

    private static function getDemoSql(): string
    {
        return <<<'str'
INSERT INTO `prefix_ebcms_cms_category` (`id`, `pid`, `type`, `title`, `name`, `keywords`, `description`, `cover`, `content`, `tpl_category`, `tpl_content`, `priority`, `state`, `nav`, `content_priority`, `redirect_uri`, `filters`, `fields`) VALUES
(1, 0, 'group', '产品', '', '', '', '', '', '', '', 2, 1, 1, 2, '', '', ''),
(2, 0, 'group', '新闻', '', '', '', '', '', '', '', 3, 1, 1, 2, '', '', ''),
(3, 0, 'channel', '解决方案', 'channel3', '', '', '', '', '', '', 1, 1, 1, 2, '', '', ''),
(4, 0, 'channel', '民用产品', 'channel1', '', '', '', '', '', '', 2, 1, 1, 2, '', '', ''),
(5, 2, 'list', '公司新闻', 'list1', '', '', '', '<p>这里发布公司相关的新闻公告等等。</p>', '', '', 0, 1, 1, 2, '', '是否原创,filter0,是|不是', ''),
(6, 7, 'page', '公司架构', 'page1', '', '', '', '<p>华为助力全球170多个国家和地区的1,500多张运营商网络稳定运行。全球多家第三方机构进行的全球大城市5G网络体验测试结果显示，华为承建的多个运营商5G网络体验排名第一。</p><p>目前华为已参与全球超过3,000个创新项目实践，和运营商、合作伙伴一起在20多个行业签署了1,000多个5GtoB项目合同。</p><p>业界首创的AirPON解决方案，可以借助无线站点和光纤资源，有效降低站址获取难度，快速实现家庭网络覆盖，已在全球超过30家运营商成功商用部署。</p><p>RuralStar系列解决方案已累计为超过60个国家和地区提供移动互联网服务，覆盖5,000多万偏远区域人口。</p><p>截至2020年底，华为企业市场合作伙伴数量超过30,000家，其中销售伙伴超过22,000家，解决方案伙伴超过1,600家，服务与运营伙伴超过5,400家，人才联盟伙伴超过1,600家。</p><p>华为联合伙伴在超过600个场景落地和探索智能体应用，覆盖政府与公共事业、交通、工业、能源、金融、医疗、科研等行业。</p><p>全球通过华为认证的人数已超过40万，其中HCIE专家级认证13,000多人，为行业数字化转型提供了优质的ICT人才资源。</p><p>华为帮助全球多家运营商在LTE/5G网络评测中全面领先；在GlobalData发布的报告中，华为5G RAN和LTE RAN综合竞争力均排名第一，蝉联“唯一领导者”桂冠。</p><p>华为履行绿色节能，PowerStar解决方案，已在中国商用超过40万个站点，每年带来约2亿度电的节省。</p><p>华为云已上线220多个云服务、210多个解决方案，在全球累计获得了80多个权威安全认证，发展19,000多家合作伙伴，汇聚160万开发者，云市场上架应用4,000多个。</p><p>华为全球终端连接数超过10亿，手机存量用户突破7.3亿。</p><p>全球集成HMS Core能力的应用已超过12万个，全球注册开发者超过230万，其中海外开发者30万，上架华为应用市场的海外应用数较2019年增长超过10倍，HMS生态已经成为全球第三大移动应用生态。</p>', '', '', 1, 1, 1, 2, '', '', ''),
(7, 0, 'group', '关于我们', '', '', '', '', '', '', '', 0, 1, 1, 2, '', '', ''),
(8, 7, 'page', '招聘信息', 'page2', '', '', '', '<p>华为是一家100%由员工持有的民营企业。华为通过工会实行员工持股计划，参与人数为121,269人，参与人仅为公司员工，没有任何政府部门、机构持有华为股权。</p><p>华为助力全球170多个国家和地区的1,500多张运营商网络稳定运行。全球多家第三方机构进行的全球大城市5G网络体验测试结果显示，华为承建的多个运营商5G网络体验排名第一。</p><p>目前华为已参与全球超过3,000个创新项目实践，和运营商、合作伙伴一起在20多个行业签署了1,000多个5GtoB项目合同。</p><p>业界首创的AirPON解决方案，可以借助无线站点和光纤资源，有效降低站址获取难度，快速实现家庭网络覆盖，已在全球超过30家运营商成功商用部署。</p><p>RuralStar系列解决方案已累计为超过60个国家和地区提供移动互联网服务，覆盖5,000多万偏远区域人口。</p><p>截至2020年底，华为企业市场合作伙伴数量超过30,000家，其中销售伙伴超过22,000家，解决方案伙伴超过1,600家，服务与运营伙伴超过5,400家，人才联盟伙伴超过1,600家。</p><p>华为联合伙伴在超过600个场景落地和探索智能体应用，覆盖政府与公共事业、交通、工业、能源、金融、医疗、科研等行业。</p><p>全球通过华为认证的人数已超过40万，其中HCIE专家级认证13,000多人，为行业数字化转型提供了优质的ICT人才资源。</p><p>华为帮助全球多家运营商在LTE/5G网络评测中全面领先；在GlobalData发布的报告中，华为5G RAN和LTE RAN综合竞争力均排名第一，蝉联“唯一领导者”桂冠。</p><p>华为履行绿色节能，PowerStar解决方案，已在中国商用超过40万个站点，每年带来约2亿度电的节省。</p><p>华为云已上线220多个云服务、210多个解决方案，在全球累计获得了80多个权威安全认证，发展19,000多家合作伙伴，汇聚160万开发者，云市场上架应用4,000多个。</p><p>华为全球终端连接数超过10亿，手机存量用户突破7.3亿。</p><p>全球集成HMS Core能力的应用已超过12万个，全球注册开发者超过230万，其中海外开发者30万，上架华为应用市场的海外应用数较2019年增长超过10倍，HMS生态已经成为全球第三大移动应用生态。</p><p>华为助力全球170多个国家和地区的1,500多张运营商网络稳定运行。全球多家第三方机构进行的全球大城市5G网络体验测试结果显示，华为承建的多个运营商5G网络体验排名第一。</p><p>目前华为已参与全球超过3,000个创新项目实践，和运营商、合作伙伴一起在20多个行业签署了1,000多个5GtoB项目合同。</p><p>业界首创的AirPON解决方案，可以借助无线站点和光纤资源，有效降低站址获取难度，快速实现家庭网络覆盖，已在全球超过30家运营商成功商用部署。</p><p>RuralStar系列解决方案已累计为超过60个国家和地区提供移动互联网服务，覆盖5,000多万偏远区域人口。</p><p>截至2020年底，华为企业市场合作伙伴数量超过30,000家，其中销售伙伴超过22,000家，解决方案伙伴超过1,600家，服务与运营伙伴超过5,400家，人才联盟伙伴超过1,600家。</p><p>华为联合伙伴在超过600个场景落地和探索智能体应用，覆盖政府与公共事业、交通、工业、能源、金融、医疗、科研等行业。</p><p>全球通过华为认证的人数已超过40万，其中HCIE专家级认证13,000多人，为行业数字化转型提供了优质的ICT人才资源。</p><p>华为帮助全球多家运营商在LTE/5G网络评测中全面领先；在GlobalData发布的报告中，华为5G RAN和LTE RAN综合竞争力均排名第一，蝉联“唯一领导者”桂冠。</p><p>华为履行绿色节能，PowerStar解决方案，已在中国商用超过40万个站点，每年带来约2亿度电的节省。</p><p>华为云已上线220多个云服务、210多个解决方案，在全球累计获得了80多个权威安全认证，发展19,000多家合作伙伴，汇聚160万开发者，云市场上架应用4,000多个。</p><p>华为全球终端连接数超过10亿，手机存量用户突破7.3亿。</p><p>全球集成HMS Core能力的应用已超过12万个，全球注册开发者超过230万，其中海外开发者30万，上架华为应用市场的海外应用数较2019年增长超过10倍，HMS生态已经成为全球第三大移动应用生态。</p><p>华为助力全球170多个国家和地区的1,500多张运营商网络稳定运行。全球多家第三方机构进行的全球大城市5G网络体验测试结果显示，华为承建的多个运营商5G网络体验排名第一。</p><p>目前华为已参与全球超过3,000个创新项目实践，和运营商、合作伙伴一起在20多个行业签署了1,000多个5GtoB项目合同。</p><p>业界首创的AirPON解决方案，可以借助无线站点和光纤资源，有效降低站址获取难度，快速实现家庭网络覆盖，已在全球超过30家运营商成功商用部署。</p><p>RuralStar系列解决方案已累计为超过60个国家和地区提供移动互联网服务，覆盖5,000多万偏远区域人口。</p><p>截至2020年底，华为企业市场合作伙伴数量超过30,000家，其中销售伙伴超过22,000家，解决方案伙伴超过1,600家，服务与运营伙伴超过5,400家，人才联盟伙伴超过1,600家。</p><p>华为联合伙伴在超过600个场景落地和探索智能体应用，覆盖政府与公共事业、交通、工业、能源、金融、医疗、科研等行业。</p><p>全球通过华为认证的人数已超过40万，其中HCIE专家级认证13,000多人，为行业数字化转型提供了优质的ICT人才资源。</p><p>华为帮助全球多家运营商在LTE/5G网络评测中全面领先；在GlobalData发布的报告中，华为5G RAN和LTE RAN综合竞争力均排名第一，蝉联“唯一领导者”桂冠。</p><p>华为履行绿色节能，PowerStar解决方案，已在中国商用超过40万个站点，每年带来约2亿度电的节省。</p><p>华为云已上线220多个云服务、210多个解决方案，在全球累计获得了80多个权威安全认证，发展19,000多家合作伙伴，汇聚160万开发者，云市场上架应用4,000多个。</p><p>华为全球终端连接数超过10亿，手机存量用户突破7.3亿。</p><p>全球集成HMS Core能力的应用已超过12万个，全球注册开发者超过230万，其中海外开发者30万，上架华为应用市场的海外应用数较2019年增长超过10倍，HMS生态已经成为全球第三大移动应用生态。</p>', '', '', 0, 1, 1, 2, '', '', ''),
(9, 2, 'list', '行业资讯', 'list2', '', '', '', '<p>介绍xxx的相关产品<span style=\"font-size: 1rem;\">还是不错的呢。</span><span style=\"font-size: 1rem;\">介绍xxx的相关产品</span><span style=\"font-size: 1rem;\">还是不错的呢。</span><span style=\"font-size: 1rem;\">介绍xxx的相关产品</span><span style=\"font-size: 1rem;\">还是不错的呢。</span><span style=\"font-size: 1rem;\">介绍xxx的相关产品</span><span style=\"font-size: 1rem;\">还是不错的呢。</span><span style=\"font-size: 1rem;\">介绍xxx的相关产品</span><span style=\"font-size: 1rem;\">还是不错的呢。</span><span style=\"font-size: 1rem;\">介绍xxx的相关产品</span><span style=\"font-size: 1rem;\">还是不错的呢。</span><span style=\"font-size: 1rem;\">介绍xxx的相关产品</span><span style=\"font-size: 1rem;\">还是不错的呢。</span><span style=\"font-size: 1rem;\">介绍xxx的相关产品</span><span style=\"font-size: 1rem;\">还是不错的呢。</span><span style=\"font-size: 1rem;\">介绍xxx的相关产品</span><span style=\"font-size: 1rem;\">还是不错的呢。</span></p>', '', '', 0, 1, 1, 2, '', '是否原创,filter0,是|不是', ''),
(10, 1, 'list', '二类产品', 'sublist1', '', '', '', '<p>消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。<br></p>', '', '', 0, 1, 1, 2, '', '是否包邮,filter0,包邮|不包邮\r\n颜色,filter1,红|绿|蓝\r\n大小,filter2,大号|中号|小号', ''),
(11, 0, 'channel', '工业产品', 'channeltop', '', '', '', '', '', '', 1, 1, 1, 2, '', '', ''),
(12, 3, 'list', '子列表', 'sublist', '', '', '', '', '', '', 0, 1, 1, 2, '', '', ''),
(13, 7, 'page', '公司简介', 'gsjj', '', '', '', '<p>华为创立于1987年，是全球领先的ICT（信息与通信）基础设施和智能终端提供商。目前华为约有19.7万员工，业务遍及170多个国家和地区，服务全球30多亿人口。</p><p>华为致力于把数字世界带入每个人、每个家庭、每个组织，构建万物互联的智能世界：让无处不在的联接，成为人人平等的权利，成为智能世界的前提和基础；为世界提供最强算力，让云无处不在，让智能无所不及；所有的行业和组织，因强大的数字平台而变得敏捷、高效、生机勃勃；通过AI重新定义体验，让消费者在家居、出行、办公、影音娱乐、运动健康等全场景获得极致的个性化智慧体验。</p><p>华为助力全球170多个国家和地区的1,500多张运营商网络稳定运行。全球多家第三方机构进行的全球大城市5G网络体验测试结果显示，华为承建的多个运营商5G网络体验排名第一。</p><p>目前华为已参与全球超过3,000个创新项目实践，和运营商、合作伙伴一起在20多个行业签署了1,000多个5GtoB项目合同。</p><p>业界首创的AirPON解决方案，可以借助无线站点和光纤资源，有效降低站址获取难度，快速实现家庭网络覆盖，已在全球超过30家运营商成功商用部署。</p><p>RuralStar系列解决方案已累计为超过60个国家和地区提供移动互联网服务，覆盖5,000多万偏远区域人口。</p><p>截至2020年底，华为企业市场合作伙伴数量超过30,000家，其中销售伙伴超过22,000家，解决方案伙伴超过1,600家，服务与运营伙伴超过5,400家，人才联盟伙伴超过1,600家。</p><p>华为联合伙伴在超过600个场景落地和探索智能体应用，覆盖政府与公共事业、交通、工业、能源、金融、医疗、科研等行业。</p><p>全球通过华为认证的人数已超过40万，其中HCIE专家级认证13,000多人，为行业数字化转型提供了优质的ICT人才资源。</p><p>华为帮助全球多家运营商在LTE/5G网络评测中全面领先；在GlobalData发布的报告中，华为5G RAN和LTE RAN综合竞争力均排名第一，蝉联“唯一领导者”桂冠。</p><p>华为履行绿色节能，PowerStar解决方案，已在中国商用超过40万个站点，每年带来约2亿度电的节省。</p><p>华为云已上线220多个云服务、210多个解决方案，在全球累计获得了80多个权威安全认证，发展19,000多家合作伙伴，汇聚160万开发者，云市场上架应用4,000多个。</p><p>华为全球终端连接数超过10亿，手机存量用户突破7.3亿。</p><p>全球集成HMS Core能力的应用已超过12万个，全球注册开发者超过230万，其中海外开发者30万，上架华为应用市场的海外应用数较2019年增长超过10倍，HMS生态已经成为全球第三大移动应用生态。</p>', '', '', 2, 1, 1, 2, '', '', ''),
(14, 3, 'list', '上市公司', 'sxs', '', '', '', '', '', '', 0, 1, 1, 2, '', '', ''),
(15, 4, 'list', '手机', 'phone', '', '', '', '<p>华为手机，天下无敌。</p>', '', '', 0, 1, 1, 2, '', '颜色,filter1,红|绿|蓝\r\n大小,filter2,大号|中号|小号\r\n年代,filter3,2021|2020|2019|2018|2017|更早', ''),
(16, 11, 'list', '路由器', 'luyouqi', '', '', '', '', '', '', 0, 1, 1, 2, '', '', ''),
(17, 0, 'page', '后台管理', 'houtai', '', '', '', '', '', '', 0, 2, 1, 2, 'http://dev.swoole.plus/ebcms/admin/index', '', ''),
(18, 4, 'list', '电脑', 'diannao', '', '', '', '<p>笔记本电脑，台式机电脑，显卡，显示器，平板电脑等等</p>', '', '', 0, 1, 1, 2, '', '是否包邮,filter0,包邮|不包邮\r\n颜色,filter1,红|绿|蓝\r\n大小,filter2,大号|中号|小号', '');
INSERT INTO `prefix_ebcms_cms_content` (`id`, `category_id`, `title`, `cover`, `keywords`, `description`, `body`, `extra`, `alias`, `tpl`, `click`, `tags`, `attrs`, `priority`, `create_time`, `update_time`, `state`, `redirect_uri`, `filter0`, `filter1`, `filter2`, `filter3`, `filter4`, `filter5`) VALUES
(1, 16, '路由器', 'http://dev.swoole.plus/uploads/2021/10-07/615e6b0d5ef9f.png', '', '', '<p>路由器小米</p>', 'N;', '大苏打撒旦', '', 99, '[\"价值\"]', '[\"首页焦点\"]', 0, 1633010702, 1633572164, 1, '', '[]', '[]', '[]', '[]', '[]', '[]'),
(2, 14, '测试文章', '', '', '', '<p>公司存在的唯一理由是为客户服务。多产粮食，增加土壤肥力是为了更有能力为客户服务。“以客户为中心，为客户创造价值”是公司的共同价值。权力是为了实现共同价值的推进剂和润滑剂。反之，权力不受约束，会阻碍和破坏共同价值守护。公司拥有完善的内部治理架构，各治理机构权责清晰、责任聚焦，但又分权制衡，使权力在闭合中循环，在循环中科学更替。</p><p>公司在治理层实行集体领导，不把公司的命运系于个人身上，集体领导遵循共同价值、责任聚焦、民主集中、分权制衡、自我批判的原则。</p><p>公司坚持以客户为中心、以奋斗者为本，持续优化公司治理架构、组织、流程和考核机制，使公司长期保持有效增长。</p><p>股东会是公司权力机构，对公司增资、利润分配、选举董事/监事等重大事项作出决策。&nbsp;</p><p>董事会是公司战略、经营管理和客户满意度的最高责任机构，承担带领公司前进的使命，行使公司战略与经营管理决策权，确保客户与股东的利益得到维护。</p><p>公司董事会及董事会常务委员会由轮值董事长主持，轮值董事长在当值期间是公司最高领袖。</p><p>监事会主要职责包括董事/高级管理人员履职监督、公司经营和财务状况监督、合规监督。</p><p>自2000年起，华为聘用毕马威作为独立审计师。审计师负责审计年度财务报表，根据会计准则和审计程序，评估财务报表是否真实和公允，对财务报表发表审计意见。</p>', 'N;', '', '', 53, '[]', '', 0, 1633426640, 1633427800, 1, '', '[]', '[]', '[]', '[]', '[]', '[]'),
(3, 14, '在闭合中循环，在循环中科学更替', '', '', '', '<p>公司存在的唯一理由是为客户服务。多产粮食，增加土壤肥力是为了更有能力为客户服务。“以客户为中心，为客户创造价值”是公司的共同价值。权力是为了实现共同价值的推进剂和润滑剂。反之，权力不受约束，会阻碍和破坏共同价值守护。公司拥有完善的内部治理架构，各治理机构权责清晰、责任聚焦，但又分权制衡，使权力在闭合中循环，在循环中科学更替。</p><p>公司在治理层实行集体领导，不把公司的命运系于个人身上，集体领导遵循共同价值、责任聚焦、民主集中、分权制衡、自我批判的原则。</p><p><img src=\"http://dev.swoole.plus/uploads/2021/10-07/615e6b039344a.jpg\" style=\"width: 300px;\"><br></p><p>公司在治理层实行集体领导，不把公司的命运系于个人身上，集体领导遵循共同价值、责任聚焦、民主集中、分权制衡、自我批判的原则。</p><p><img src=\"http://dev.swoole.plus/uploads/2021/10-07/615e6b0d5ef9f.png\" style=\"width: 280px;\"></p><p>公司坚持以客户为中心、以奋斗者为本，持续优化公司治理架构、组织、流程和考核机制，使公司长期保持有效增长。</p><p>股东会是公司权力机构，对公司增资、利润分配、选举董事/监事等重大事项作出决策。&nbsp;</p><p>董事会是公司战略、经营管理和客户满意度的最高责任机构，承担带领公司前进的使命，行使公司战略与经营管理决策权，确保客户与股东的利益得到维护。</p><p>公司董事会及董事会常务委员会由轮值董事长主持，轮值董事长在当值期间是公司最高领袖。</p><p>监事会主要职责包括董事/高级管理人员履职监督、公司经营和财务状况监督、合规监督。</p><p>自2000年起，华为聘用毕马威作为独立审计师。审计师负责审计年度财务报表，根据会计准则和审计程序，评估财务报表是否真实和公允，对财务报表发表审计意见。</p>', 'N;', '', '', 62, '[\"效率\"]', '', 0, 1633428151, 1633577954, 1, '', '[]', '[]', '[]', '[]', '[]', '[]'),
(4, 12, '公司拥有完善的内部治理架构', '', '', '', '<p>公司存在的唯一理由是为客户服务。多产粮食，增加土壤肥力是为了更有能力为客户服务。“以客户为中心，为客户创造价值”是公司的共同价值。权力是为了实现共同价值的推进剂和润滑剂。反之，权力不受约束，会阻碍和破坏共同价值守护。公司拥有完善的内部治理架构，各治理机构权责清晰、责任聚焦，但又分权制衡，使权力在闭合中循环，在循环中科学更替。</p><p>公司在治理层实行集体领导，不把公司的命运系于个人身上，集体领导遵循共同价值、责任聚焦、民主集中、分权制衡、自我批判的原则。</p><p>公司坚持以客户为中心、以奋斗者为本，持续优化公司治理架构、组织、流程和考核机制，使公司长期保持有效增长。</p><p>股东会是公司权力机构，对公司增资、利润分配、选举董事/监事等重大事项作出决策。&nbsp;</p><p>董事会是公司战略、经营管理和客户满意度的最高责任机构，承担带领公司前进的使命，行使公司战略与经营管理决策权，确保客户与股东的利益得到维护。</p><p>公司董事会及董事会常务委员会由轮值董事长主持，轮值董事长在当值期间是公司最高领袖。</p><p>监事会主要职责包括董事/高级管理人员履职监督、公司经营和财务状况监督、合规监督。</p><p>自2000年起，华为聘用毕马威作为独立审计师。审计师负责审计年度财务报表，根据会计准则和审计程序，评估财务报表是否真实和公允，对财务报表发表审计意见。</p>', 'N;', '', '', 73, '[\"治理\",\"价值\"]', '', 0, 1633428171, 1633494003, 1, '', '[]', '[]', '[]', '[]', '[]', '[]'),
(5, 9, '消费者满意度、生态伙伴体验与', '', '', '', '<p>为加强对消费者业务的战略及风险管理，提升决策效率，公司设消费者业务管理委员会，作为消费者业务战略、经营管理和客户满意度的最高责任机构。</p><p>消费者BG是公司面向终端产品用户和生态伙伴的端到端经营组织，对经营结果、风险、市场竞争力和客户满意度负责。</p><p>消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</p><p>智能汽车解决方案BU是公司面向智能汽车领域的端到端业务责任主体，将华为公司的ICT技术优势延伸到智能汽车产业，提供智能网联汽车的增量部件。智能汽车解决方案BU的业务目标是聚焦ICT技术，帮助车企造好车。</p><p>为逐步打造公司支撑不同业务发展的共享服务平台，并有序形成公司统治实施的抓手，公司设平台协调委员会，以推动平台各部门的执行运作优化、跨领域运作简化、协同强化，使平台组织成为“围绕生产、促进生产”的最佳服务组织。集团职能平台是聚焦业务的支撑、服务和监管的平台，向前方提供及时准确有效的服务，在充分向前方授权的同时，加强监管。</p>', 'N;', '', '', 60, '[\"效率\"]', '[\"首页推荐\"]', 0, 1633428382, 1633525328, 1, '', '[\"包邮\"]', '[\"红\",\"绿\"]', '[\"大号\"]', '[]', '[]', '[]'),
(6, 9, '公司设消费者业务管理委员会', '', '', '消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。', '<p>为加强对消费者业务的战略及风险管理，提升决策效率，公司设消费者业务管理委员会，作为消费者业务战略、经营管理和客户满意度的最高责任机构。</p><p>消费者BG是公司面向终端产品用户和生态伙伴的端到端经营组织，对经营结果、风险、市场竞争力和客户满意度负责。</p><p>消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</p><p>智能汽车解决方案BU是公司面向智能汽车领域的端到端业务责任主体，将华为公司的ICT技术优势延伸到智能汽车产业，提供智能网联汽车的增量部件。智能汽车解决方案BU的业务目标是聚焦ICT技术，帮助车企造好车。</p><p>为逐步打造公司支撑不同业务发展的共享服务平台，并有序形成公司统治实施的抓手，公司设平台协调委员会，以推动平台各部门的执行运作优化、跨领域运作简化、协同强化，使平台组织成为“围绕生产、促进生产”的最佳服务组织。集团职能平台是聚焦业务的支撑、服务和监管的平台，向前方提供及时准确有效的服务，在充分向前方授权的同时，加强监管。</p>', 'N;', '', '', 126, '[\"发展\",\"效率\"]', '[\"首页头条\",\"栏目置顶\"]', 0, 1633428392, 1633525304, 1, '', '[\"包邮\"]', '[\"绿\"]', '[\"中号\"]', '[]', '[]', '[]'),
(7, 10, 'LOGO编辑器', '', '', '', '<p><span style=\"text-indent: 32px;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span></p><p><span style=\"text-indent: 32px;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span></p><p><span style=\"text-indent: 32px;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span><br></p>', 'N;', '', '', 19, '[\"生态\",\"效率\"]', '', 0, 1633440163, 1633494023, 1, '', '[]', '[]', '[]', '[]', '[]', '[]'),
(8, 10, '行业环境变化及竞争动态', '', '', '', '<p><span style=\"text-indent: 32px;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span></p><p><span style=\"text-indent: 32px;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span></p><p><span style=\"text-indent: 32px;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span><br></p>', 'N;', '', '', 43, '[\"价值\"]', '', 0, 1633440181, 1633493983, 1, '', '[]', '[]', '[]', '[]', '[]', '[]'),
(9, 10, '保障终端业务在当地的持续健康发展', '', '', '', '<p><span style=\"text-indent: 32px;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span></p><p><span style=\"text-indent: 32px;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span></p><p><span style=\"text-indent: 32px;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span><span style=\"text-indent: 32px; font-size: 1rem;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span></p><p><span style=\"text-indent: 32px;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span></p><p><span style=\"text-indent: 32px;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span><span style=\"text-indent: 32px; font-size: 1rem;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span></p><p><span style=\"text-indent: 32px;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span></p><p><span style=\"text-indent: 32px;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span></p>', 'N;', '', '', 39, '[\"发展\",\"竞争\"]', '', 0, 1633440191, 1633493915, 1, '', '[]', '[]', '[]', '[]', '[]', '[]'),
(10, 16, 'FLUD-927392路由器', '', '', '', '', 'N;', '', '', 21, '[]', '', 0, 1633501716, 1633501716, 1, '', '[]', '[]', '[]', '[]', '[]', '[]'),
(11, 15, '华为P50pro', '', '', '', '', 'N;', '', '', 14, '[]', '', 0, 1633501736, 1633501736, 1, '', '[]', '[]', '[]', '[]', '[]', '[]'),
(12, 9, '织对终端业务在区域的总体经营目标', '', '', '', '<p>为加强对消费者业务的战略及风险管理，提升决策效率，公司设消费者业务管理委员会，作为消费者业务战略、经营管理和客户满意度的最高责任机构。</p><p>消费者BG是公司面向终端产品用户和生态伙伴的端到端经营组织，对经营结果、风险、市场竞争力和客户满意度负责。</p><p>消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</p><p>智能汽车解决方案BU是公司面向智能汽车领域的端到端业务责任主体，将华为公司的ICT技术优势延伸到智能汽车产业，提供智能网联汽车的增量部件。智能汽车解决方案BU的业务目标是聚焦ICT技术，帮助车企造好车。</p><p>为逐步打造公司支撑不同业务发展的共享服务平台，并有序形成公司统治实施的抓手，公司设平台协调委员会，以推动平台各部门的执行运作优化、跨领域运作简化、协同强化，使平台组织成为“围绕生产、促进生产”的最佳服务组织。集团职能平台是聚焦业务的支撑、服务和监管的平台，向前方提供及时准确有效的服务，在充分向前方授权的同时，加强监管。</p>', 'N;', '', '', 7, '[\"发展\",\"效率\"]', '[\"首页推荐\"]', 0, 1633515216, 1633525316, 1, '', '[\"包邮\"]', '[\"绿\"]', '[\"中号\"]', '[]', '[]', '[]'),
(13, 9, '端经营组织，对经营结果、风', '', '', '', '<p>为加强对消费者业务的战略及风险管理，提升决策效率，公司设消费者业务管理委员会，作为消费者业务战略、经营管理和客户满意度的最高责任机构。</p><p>消费者BG是公司面向终端产品用户和生态伙伴的端到端经营组织，对经营结果、风险、市场竞争力和客户满意度负责。</p><p>消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</p><p>智能汽车解决方案BU是公司面向智能汽车领域的端到端业务责任主体，将华为公司的ICT技术优势延伸到智能汽车产业，提供智能网联汽车的增量部件。智能汽车解决方案BU的业务目标是聚焦ICT技术，帮助车企造好车。</p><p>为逐步打造公司支撑不同业务发展的共享服务平台，并有序形成公司统治实施的抓手，公司设平台协调委员会，以推动平台各部门的执行运作优化、跨领域运作简化、协同强化，使平台组织成为“围绕生产、促进生产”的最佳服务组织。集团职能平台是聚焦业务的支撑、服务和监管的平台，向前方提供及时准确有效的服务，在充分向前方授权的同时，加强监管。</p>', 'N;', '', '', 10, '[\"效率\"]', '', 0, 1633515234, 1633515234, 1, '', '[\"包邮\"]', '[\"红\",\"绿\"]', '[\"大号\"]', '[]', '[]', '[]'),
(14, 9, '制定区域终端的业务规划和资源投', 'http://dev.swoole.plus/uploads/2021/10-07/615e6b0d5ef9f.png', '', '', '<p>为加强对消费者业务的战略及风险管理，提升决策效率，公司设消费者业务管理委员会，作为消费者业务战略、经营管理和客户满意度的最高责任机构。</p><p>消费者BG是公司面向终端产品用户和生态伙伴的端到端经营组织，对经营结果、风险、市场竞争力和客户满意度负责。</p><p>消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</p><p>智能汽车解决方案BU是公司面向智能汽车领域的端到端业务责任主体，将华为公司的ICT技术优势延伸到智能汽车产业，提供智能网联汽车的增量部件。智能汽车解决方案BU的业务目标是聚焦ICT技术，帮助车企造好车。</p><p>为逐步打造公司支撑不同业务发展的共享服务平台，并有序形成公司统治实施的抓手，公司设平台协调委员会，以推动平台各部门的执行运作优化、跨领域运作简化、协同强化，使平台组织成为“围绕生产、促进生产”的最佳服务组织。集团职能平台是聚焦业务的支撑、服务和监管的平台，向前方提供及时准确有效的服务，在充分向前方授权的同时，加强监管。</p>', 'N;', '', '', 9, '[\"效率\"]', '[\"首页焦点\",\"首页推荐\"]', 0, 1633515246, 1633572153, 1, '', '[\"包邮\"]', '[\"红\",\"绿\"]', '[\"大号\"]', '[]', '[]', '[]'),
(15, 5, '保障终端业务在当地的持', 'http://dev.swoole.plus/uploads/2021/10-07/615e6b0d5ef9f.png', '', '', '<p>为加强对消费者业务的战略及风险管理，提升决策效率，公司设消费者业务管理委员会，作为消费者业务战略、经营管理和客户满意度的最高责任机构。</p><p>消费者BG是公司面向终端产品用户和生态伙伴的端到端经营组织，对经营结果、风险、市场竞争力和客户满意度负责。</p><p>消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</p><p>智能汽车解决方案BU是公司面向智能汽车领域的端到端业务责任主体，将华为公司的ICT技术优势延伸到智能汽车产业，提供智能网联汽车的增量部件。智能汽车解决方案BU的业务目标是聚焦ICT技术，帮助车企造好车。</p><p>为逐步打造公司支撑不同业务发展的共享服务平台，并有序形成公司统治实施的抓手，公司设平台协调委员会，以推动平台各部门的执行运作优化、跨领域运作简化、协同强化，使平台组织成为“围绕生产、促进生产”的最佳服务组织。集团职能平台是聚焦业务的支撑、服务和监管的平台，向前方提供及时准确有效的服务，在充分向前方授权的同时，加强监管。</p>', 'N;', '', '', 15, '[\"效率\"]', '[\"首页焦点\"]', 0, 1633515272, 1633525865, 1, '', '[\"包邮\"]', '[\"红\",\"绿\"]', '[\"大号\"]', '[]', '[]', '[]'),
(16, 10, '合规运营，保障终端业务在当地', '', '', '', '<p><span style=\"text-indent: 32px;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span></p><p><span style=\"text-indent: 32px;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span></p><p><span style=\"text-indent: 32px;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span><span style=\"text-indent: 32px; font-size: 1rem;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span></p><p><span style=\"text-indent: 32px;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span></p><p><span style=\"text-indent: 32px;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span><span style=\"text-indent: 32px; font-size: 1rem;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span></p><p><span style=\"text-indent: 32px;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span></p><p><span style=\"text-indent: 32px;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span></p>', 'N;', '', '', 33, '[\"发展\",\"竞争\"]', '', 0, 1633515293, 1633515293, 1, '', '[]', '[]', '[]', '[]', '[]', '[]'),
(17, 10, '活动策划与执行，渠道、零', '', '', '', '<p><span style=\"text-indent: 32px;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span></p><p><span style=\"text-indent: 32px;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span></p><p><span style=\"text-indent: 32px;\">消费者BG区域组织对终端业务在区域的总体经营目标、消费者满意度、生态伙伴体验与品牌形象提升负责。洞察消费电子行业环境变化及竞争动态，制定区域终端的业务规划和资源投入策略并实施，负责区域产品上市操盘及生命周期管理，生态发展，营销活动策划与执行，渠道、零售、服务的建设及管理。建设和维护合作伙伴关系，营造和谐的商业环境，合规运营，保障终端业务在当地的持续健康发展。</span><br></p>', 'N;', '', '', 6, '[\"价值\"]', '', 0, 1633515301, 1633515301, 1, '', '[]', '[]', '[]', '[]', '[]', '[]'),
(18, 15, '华为P40pro', '', '', '', '', 'N;', '', '', 2, '[]', '', 0, 1633515392, 1633515392, 1, '', '[]', '[]', '[]', '[]', '[]', '[]'),
(19, 15, '华为P30pro', '', '', '', '', 'N;', '', '', 3, '[]', '', 0, 1633515400, 1633515400, 1, '', '[]', '[]', '[]', '[]', '[]', '[]'),
(20, 15, '华为P20pro', '', '', '', '', 'N;', '', '', 1, '[]', '', 0, 1633515406, 1633515406, 1, '', '[]', '[]', '[]', '[]', '[]', '[]'),
(21, 15, '华为P10pro', '', '', '', '', 'N;', '', '', 14, '[]', '', 0, 1633515413, 1633515413, 1, '', '[]', '[]', '[]', '[]', '[]', '[]'),
(22, 14, '不把公司的命运系于个人身', '', '', '', '<p>公司存在的唯一理由是为客户服务。多产粮食，增加土壤肥力是为了更有能力为客户服务。“以客户为中心，为客户创造价值”是公司的共同价值。权力是为了实现共同价值的推进剂和润滑剂。反之，权力不受约束，会阻碍和破坏共同价值守护。公司拥有完善的内部治理架构，各治理机构权责清晰、责任聚焦，但又分权制衡，使权力在闭合中循环，在循环中科学更替。</p><p>公司在治理层实行集体领导，不把公司的命运系于个人身上，集体领导遵循共同价值、责任聚焦、民主集中、分权制衡、自我批判的原则。</p><p>公司坚持以客户为中心、以奋斗者为本，持续优化公司治理架构、组织、流程和考核机制，使公司长期保持有效增长。</p><p>股东会是公司权力机构，对公司增资、利润分配、选举董事/监事等重大事项作出决策。&nbsp;</p><p>董事会是公司战略、经营管理和客户满意度的最高责任机构，承担带领公司前进的使命，行使公司战略与经营管理决策权，确保客户与股东的利益得到维护。</p><p>公司董事会及董事会常务委员会由轮值董事长主持，轮值董事长在当值期间是公司最高领袖。</p><p>监事会主要职责包括董事/高级管理人员履职监督、公司经营和财务状况监督、合规监督。</p><p>自2000年起，华为聘用毕马威作为独立审计师。审计师负责审计年度财务报表，根据会计准则和审计程序，评估财务报表是否真实和公允，对财务报表发表审计意见。</p>', 'N;', '', '', 26, '[\"效率\"]', '[\"首页推荐\"]', 0, 1633515434, 1633525337, 1, '', '[]', '[]', '[]', '[]', '[]', '[]'),
(23, 14, '组织、流程和考核机制', '', '', '', '<p>公司存在的唯一理由是为客户服务。多产粮食，增加土壤肥力是为了更有能力为客户服务。“以客户为中心，为客户创造价值”是公司的共同价值。权力是为了实现共同价值的推进剂和润滑剂。反之，权力不受约束，会阻碍和破坏共同价值守护。公司拥有完善的内部治理架构，各治理机构权责清晰、责任聚焦，但又分权制衡，使权力在闭合中循环，在循环中科学更替。</p><p>公司在治理层实行集体领导，不把公司的命运系于个人身上，集体领导遵循共同价值、责任聚焦、民主集中、分权制衡、自我批判的原则。</p><p>公司坚持以客户为中心、以奋斗者为本，持续优化公司治理架构、组织、流程和考核机制，使公司长期保持有效增长。</p><p>股东会是公司权力机构，对公司增资、利润分配、选举董事/监事等重大事项作出决策。&nbsp;</p><p>董事会是公司战略、经营管理和客户满意度的最高责任机构，承担带领公司前进的使命，行使公司战略与经营管理决策权，确保客户与股东的利益得到维护。</p><p>公司董事会及董事会常务委员会由轮值董事长主持，轮值董事长在当值期间是公司最高领袖。</p><p>监事会主要职责包括董事/高级管理人员履职监督、公司经营和财务状况监督、合规监督。</p><p>自2000年起，华为聘用毕马威作为独立审计师。审计师负责审计年度财务报表，根据会计准则和审计程序，评估财务报表是否真实和公允，对财务报表发表审计意见。</p>', 'N;', '', '', 13, '[\"效率\"]', '', 0, 1633515442, 1633515442, 1, '', '[]', '[]', '[]', '[]', '[]', '[]'),
(24, 14, '监事等重大事项作出决策', '', '', '', '<p>公司存在的唯一理由是为客户服务。多产粮食，增加土壤肥力是为了更有能力为客户服务。“以客户为中心，为客户创造价值”是公司的共同价值。权力是为了实现共同价值的推进剂和润滑剂。反之，权力不受约束，会阻碍和破坏共同价值守护。公司拥有完善的内部治理架构，各治理机构权责清晰、责任聚焦，但又分权制衡，使权力在闭合中循环，在循环中科学更替。</p><p><img src=\"http://dev.swoole.plus/uploads/2021/10-07/615e6b039344a.jpg\" style=\"width: 300px;\"><br></p><p>公司在治理层实行集体领导，不把公司的命运系于个人身上，集体领导遵循共同价值、责任聚焦、民主集中、分权制衡、自我批判的原则。</p><p><img src=\"http://dev.swoole.plus/uploads/2021/10-07/615e6b0d5ef9f.png\" style=\"width: 280px;\"><br></p><p>公司坚持以客户为中心、以奋斗者为本，持续优化公司治理架构、组织、流程和考核机制，使公司长期保持有效增长。</p><p>股东会是公司权力机构，对公司增资、利润分配、选举董事/监事等重大事项作出决策。&nbsp;</p><p>董事会是公司战略、经营管理和客户满意度的最高责任机构，承担带领公司前进的使命，行使公司战略与经营管理决策权，确保客户与股东的利益得到维护。</p><p><img src=\"http://dev.swoole.plus/uploads/2021/10-07/615e6b17031d9.jpg\" style=\"width: 470px;\"><br></p><p>公司董事会及董事会常务委员会由轮值董事长主持，轮值董事长在当值期间是公司最高领袖。</p><p>监事会主要职责包括董事/高级管理人员履职监督、公司经营和财务状况监督、合规监督。</p><p><img src=\"http://dev.swoole.plus/uploads/2021/10-07/615e6b2366f65.jpg\" style=\"width: 800px;\"><br></p><p>自2000年起，华为聘用毕马威作为独立审计师。审计师负责审计年度财务报表，根据会计准则和审计程序，评估财务报表是否真实和公允，对财务报表发表审计意见。</p>', 'N;', '', '', 31, '[\"效率\"]', '', 0, 1633515451, 1633675011, 1, '', '[]', '[]', '[]', '[]', '[]', '[]');
str;
    }

    private static function getUninstallSql(): string
    {
        return <<<'str'
DROP TABLE IF EXISTS `prefix_ebcms_cms_category`;
DROP TABLE IF EXISTS `prefix_ebcms_cms_content`;
str;
    }
}
