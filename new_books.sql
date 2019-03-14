/*
Navicat MySQL Data Transfer

Source Server         : 127.0.0.1
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : new_books

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2019-03-14 18:41:55
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for books_admin
-- ----------------------------
DROP TABLE IF EXISTS `books_admin`;
CREATE TABLE `books_admin` (
  `admin_id` int(8) NOT NULL AUTO_INCREMENT,
  `admin_name` varchar(255) NOT NULL DEFAULT '' COMMENT '管理员名称',
  `admin_password` varchar(255) NOT NULL COMMENT '管理员密码',
  `admin_power` text COMMENT '权限组',
  `admin_describe` varchar(255) DEFAULT '' COMMENT '描述',
  `add_time` datetime DEFAULT NULL COMMENT '创建时间',
  `is_disable` tinyint(4) DEFAULT '0' COMMENT '是否禁用 0否 1是',
  PRIMARY KEY (`admin_id`,`admin_name`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of books_admin
-- ----------------------------
INSERT INTO `books_admin` VALUES ('1', 'admin', '21232f297a57a5a743894a0e4a801fc3', '[\"\\u89d2\\u8272\\u7ba1\\u7406\",\"\\u7f51\\u7ad9\\u7528\\u6237\",\"\\u540e\\u53f0\\u7ba1\\u7406\\u5458\",\"\\u5c0f\\u8bf4\\u7ba1\\u7406\",\"\\u6dfb\\u52a0\\u5c0f\\u8bf4\",\"\\u5c0f\\u8bf4\\u5217\\u8868\",\"\\u5206\\u7c7b\\u7ba1\\u7406\",\"\\u5c0f\\u8bf4\\u5206\\u7c7b\",\"\\u91c7\\u96c6\\u7ba1\\u7406\",\"\\u89c4\\u5219\\u7ba1\\u7406\",\"\\u5c0f\\u8bf4\\u91c7\\u96c6\",\"\\u8bbe\\u7f6e\",\"\\u9996\\u9875\\u8bbe\\u7f6e\",\"\\u5e7b\\u706f\\u7247\\u8bbe\\u7f6e\",\"\\u7cfb\\u7edf\\u8bbe\\u7f6e\",\"\\u7f51\\u7ad9\\u8bbe\\u7f6e\",\"\\u90ae\\u4ef6\\u670d\\u52a1\",\"\\u7545\\u8a00\\u63a5\\u5165\"]', '拥有至高无上的权利', '2018-08-03 12:13:27', '0');

-- ----------------------------
-- Table structure for books_article
-- ----------------------------
DROP TABLE IF EXISTS `books_article`;
CREATE TABLE `books_article` (
  `article_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '文章id',
  `article_name` varchar(255) DEFAULT '' COMMENT '文章标题',
  `article_author` varchar(255) DEFAULT '' COMMENT '文章作者',
  `article_content` text COMMENT '文章内容',
  `article_status` tinyint(4) DEFAULT '1' COMMENT '是否发布 0否，1是',
  `add_time` datetime DEFAULT NULL COMMENT '发布时间',
  PRIMARY KEY (`article_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='文章';

-- ----------------------------
-- Records of books_article
-- ----------------------------

-- ----------------------------
-- Table structure for books_chapter
-- ----------------------------
DROP TABLE IF EXISTS `books_chapter`;
CREATE TABLE `books_chapter` (
  `books_id` int(11) NOT NULL COMMENT '对应的小说id',
  `chapter_name` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '章节名称',
  `chapter_url` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT '章节源链接',
  PRIMARY KEY (`books_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of books_chapter
-- ----------------------------

-- ----------------------------
-- Table structure for books_cou
-- ----------------------------
DROP TABLE IF EXISTS `books_cou`;
CREATE TABLE `books_cou` (
  `books_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `books_name` varchar(255) COLLATE utf8_bin NOT NULL COMMENT '书籍名称',
  `books_author` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT '书籍作者',
  `books_time` date DEFAULT NULL COMMENT '更新时间',
  `books_type` int(11) DEFAULT NULL COMMENT '书籍类型',
  `books_status` int(11) DEFAULT '0' COMMENT '书箱状态 0 连载 1完结',
  `books_synopsis` text COLLATE utf8_bin COMMENT '书籍简介',
  `books_img` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT '书籍封面',
  `books_url` varchar(255) COLLATE utf8_bin DEFAULT NULL COMMENT '书箱来源地址',
  PRIMARY KEY (`books_id`,`books_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of books_cou
-- ----------------------------

-- ----------------------------
-- Table structure for books_history
-- ----------------------------
DROP TABLE IF EXISTS `books_history`;
CREATE TABLE `books_history` (
  `books_id` int(8) NOT NULL,
  `user_id` int(8) NOT NULL,
  `history_name` varchar(255) DEFAULT '' COMMENT '记录章节',
  `history_url` varchar(255) DEFAULT '' COMMENT '章节地址',
  `history_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '阅读时间',
  PRIMARY KEY (`books_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='阅读记录';

-- ----------------------------
-- Records of books_history
-- ----------------------------

-- ----------------------------
-- Table structure for books_module
-- ----------------------------
DROP TABLE IF EXISTS `books_module`;
CREATE TABLE `books_module` (
  `module_id` int(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '模块id',
  `module_name` varchar(255) DEFAULT '' COMMENT '模块名称',
  `module_key` varchar(255) DEFAULT NULL COMMENT '标识字段',
  `module_data` text COMMENT '数据',
  PRIMARY KEY (`module_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of books_module
-- ----------------------------
INSERT INTO `books_module` VALUES ('3', '热门精选', 'hot', '{\"4\":\"545,911,1392,1507,2069\",\"5\":\"13,92,145,190,230\",\"6\":\"1322,1641,3012,3879,5547\",\"7\":\"2604,2873,4045,4072,4164\"}');
INSERT INTO `books_module` VALUES ('2', '首页幻灯片', 'slide', '{\"books_ids\":[\"33829\",\"33974\",\"9195\",\"10722\",\"12652\",\"33207\"]}');
INSERT INTO `books_module` VALUES ('5', '网站设置', 'settings', '{\"name\":\"\\u963f\\u91cc\\u5c0f\\u8bf4\\u7f51\",\"domain\":\"http:\\/\\/dev.books.com\\/\",\"title\":\"\\u963f\\u91cc\\u5c0f\\u8bf4\\u7f51\",\"keywords\":\"\\u963f\\u91cc\\u5c0f\\u8bf4\\u7f51\\uff0c\\u5c0f\\u8bf4\\uff0c\\u5c0f\\u8bf4\\u7f51,\\u8a00\\u60c5\\u5c0f\\u8bf4,\\u9752\\u6625\\u5c0f\\u8bf4,\\u7384\\u5e7b\\u5c0f\\u8bf4,\\u6b66\\u4fa0\\u5c0f\\u8bf4,\\u90fd\\u5e02\\u5c0f\\u8bf4,\\u5386\\u53f2\\u5c0f\\u8bf4,\\u7f51\\u7edc\\u5c0f\\u8bf4,\\u5c0f\\u8bf4\\u4e0b\\u8f7d\\uff0c\\u539f\\u521b\\u7f51\\u7edc\\u6587\\u5b66\",\"descript\":\"\\u963f\\u91cc\\u5c0f\\u8bf4\\u7f51\\u63d0\\u4f9b\\u514d\\u8d39\\u5c0f\\u8bf4,\\u70ed\\u95e8\\u5c0f\\u8bf4,\\u7cbe\\u54c1\\u5c0f\\u8bf4,\\u597d\\u770b\\u5c0f\\u8bf4,\\u5c0f\\u8bf4\\u8fde\\u8f7d,\\u5c0f\\u8bf4\\u6392\\u884c\\u699c,\\u5c0f\\u8bf4\\u5728\\u7ebf\\u9605\\u8bfb,\\u5c0f\\u8bf4\\u4e0b\\u8f7d,\\u5c3d\\u8bf7\\u6d4f\\u89c8\\u7a37\\u4e0b\\u5b66\\u5bab\\u5404\\u79cd\\u7384\\u5e7b\\u5c0f\\u8bf4,\\u90fd\\u5e02\\u5c0f\\u8bf4,\\u8a00\\u60c5\\u5c0f\\u8bf4,\\u7a7f\\u8d8a\\u5c0f\\u8bf4,\\u9752\\u6625\\u5c0f\\u8bf4,\\u6b66\\u4fa0\\u5c0f\\u8bf4,\\u5386\\u53f2\\u5c0f\\u8bf4,\\u519b\\u4e8b\\u5c0f\\u8bf4,\\u79d1\\u5e7b\\u5c0f\\u8bf4,\\u7075\\u5f02\\u5c0f\\u8bf4,\\u6e38\\u620f\\u5c0f\\u8bf4,\\u7ade\\u6280\\u5c0f\\u8bf4,\\u540c\\u4eba\\u5c0f\\u8bf4\",\"record\":\" \\u7ca4ICP\\u590700000000\\u53f7-0\",\"copyright\":\"\\u963f\\u91cc\\u5c0f\\u8bf4\\u7f51\\u6709\\u9650\\u516c\\u53f8\\u00a0\\u00a0\\u00a0\\u00a0\\u7248\\u6743\\u6240\\u6709\"}');
INSERT INTO `books_module` VALUES ('6', 'wap幻灯片', 'slide_wap', '{\"1\":{\"image\":\"\\/static\\/images\\/mobile_slide\\/5bc19c73acf0a.jpg\",\"url\":\"http:\\/\\/dev.books.com\\/cover\\/index\\/books_id\\/33821.html\"},\"2\":{\"image\":\"\\/static\\/images\\/mobile_slide\\/5bc19c76b6482.jpg\",\"url\":\"http:\\/\\/dev.books.com\\/cover\\/index\\/books_id\\/33859.html\"},\"3\":{\"image\":\"\\/static\\/images\\/mobile_slide\\/5bc19c791fa62.jpg\",\"url\":\"http:\\/\\/dev.books.com\\/cover\\/index\\/books_id\\/9195.html\"},\"4\":{\"image\":\"\\/static\\/images\\/mobile_slide\\/5bc19c7b8a1f6.jpg\",\"url\":\"http:\\/\\/dev.books.com\\/cover\\/index\\/books_id\\/4881.html\"}}');
INSERT INTO `books_module` VALUES ('7', 'stmp邮箱', 'stmp', '{\"smtp_server\":\"smtp.163.com\",\"smtp_number\":\"465\",\"send_email\":\"QQ0000000000@163.com\",\"send_nickname\":\"\\u963f\\u91cc\\u5c0f\\u8bf4-\\u672c\\u5730\",\"send_username\":\"QQ0000000000@163.com\",\"send_password\":\"000000\"}');
INSERT INTO `books_module` VALUES ('8', '畅言', 'changyan', '{\"appid\":\"cytGk2A0a\",\"conf\":\"prod_588f079f2cbaab4bd1ddcaa19dd4aaaa\"}');

-- ----------------------------
-- Table structure for books_rule
-- ----------------------------
DROP TABLE IF EXISTS `books_rule`;
CREATE TABLE `books_rule` (
  `rule_id` int(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '规则id',
  `rule_name` varchar(255) NOT NULL COMMENT '规则名称',
  `rule_url` varchar(255) DEFAULT NULL COMMENT '规则搜索地址',
  `search_name` varchar(255) DEFAULT NULL COMMENT '结果页书名匹配规则',
  `search_url` varchar(255) DEFAULT NULL COMMENT '结果页地址匹配规则',
  `is_search` tinyint(4) DEFAULT '0' COMMENT '是否可用于小说搜索 0否 1是',
  `is_urlencode` tinyint(4) DEFAULT '0' COMMENT '书名是否开启urlencode化 0否 1是',
  PRIMARY KEY (`rule_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='小说添加规则';

-- ----------------------------
-- Records of books_rule
-- ----------------------------
INSERT INTO `books_rule` VALUES ('1', '笔趣阁', 'https://www.biquge5200.cc/modules/article/search.php?searchkey=', '.odd>a', '.odd>a', '1', '0');
INSERT INTO `books_rule` VALUES ('2', '天籁小说', 'https://www.23txt.com/search.php?keyword=', '.result-game-item-detail>h3>a>span', '.result-game-item-detail>h3>a', '1', '1');
INSERT INTO `books_rule` VALUES ('5', '新笔趣阁', 'http://www.xbequge.com/', '', '', '0', '0');
INSERT INTO `books_rule` VALUES ('4', '爱好中文网', 'https://www.ahzww.net', 'dd', 'dd', '0', '0');
INSERT INTO `books_rule` VALUES ('6', '笔趣阁3', 'https://www.bqg5.cc/', '', '', '0', '0');
INSERT INTO `books_rule` VALUES ('8', '顶点小说', 'https://www.23wxw.cc/', '', '', '0', '0');
INSERT INTO `books_rule` VALUES ('11', '无图小说网', 'http://www.wutuxs.com/', '', '', '0', '0');
INSERT INTO `books_rule` VALUES ('10', '笔趣阁小说网', 'https://www.biqugexsw.com/', '', '', '0', '0');

-- ----------------------------
-- Table structure for books_rule_info
-- ----------------------------
DROP TABLE IF EXISTS `books_rule_info`;
CREATE TABLE `books_rule_info` (
  `rule_id` int(8) NOT NULL COMMENT '规则id',
  `books_name` varchar(255) DEFAULT NULL COMMENT '书名匹配规则',
  `books_author` varchar(255) DEFAULT NULL COMMENT '作者匹配规则',
  `books_time` varchar(255) DEFAULT NULL COMMENT '更新时间匹配规则',
  `books_type` varchar(255) DEFAULT NULL COMMENT '小说类型匹配规则',
  `books_synopsis` varchar(255) DEFAULT NULL COMMENT '小说简介匹配规则',
  `books_img` varchar(255) DEFAULT NULL COMMENT '小说封面匹配规则',
  `chapter_name` varchar(255) DEFAULT NULL COMMENT '章节名匹配规则',
  `chapter_url` varchar(255) DEFAULT NULL COMMENT '章节地址匹配规则',
  `info_title` varchar(255) DEFAULT NULL COMMENT '章节标题',
  `info_content` varchar(255) DEFAULT NULL COMMENT '章节内容',
  PRIMARY KEY (`rule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='具体添加规则';

-- ----------------------------
-- Records of books_rule_info
-- ----------------------------
INSERT INTO `books_rule_info` VALUES ('1', 'h1', '#info>p:eq(0)', '#info>p:eq(2)', '.con_top>a:eq(1)', '#intro>p', '#fmimg>img', 'dd>a', 'dd>a', 'h1', '#content');
INSERT INTO `books_rule_info` VALUES ('2', 'h1', '#info>p:eq(0)', '#info>p:eq(2)', '.con_top>a:eq(1)', '#intro>p', '#fmimg>img', 'dd>a', 'dd>a', 'h1', '#content');
INSERT INTO `books_rule_info` VALUES ('4', 'h1', '.author>span:eq(0)', '.author>span:eq(1)', '.p2>p', '.p3', '.articleinfo>.l>p>img', '.chapterlist>ul>li>a', '.chapterlist>ul>li>a', 'h1', '#content>p');
INSERT INTO `books_rule_info` VALUES ('5', '', '', '', '', '', '', '#chapterlist>li>a', '#chapterlist>li>a', 'h1', '#book_text');
INSERT INTO `books_rule_info` VALUES ('6', '', '', '', '', '', '', '#list>dl>dd>a', '#list>dl>dd>a', 'h1', '#content');
INSERT INTO `books_rule_info` VALUES ('8', '', '', '', '', '', '', '#list>.dl>.dd>a', '#list>.dl>.dd>a', 'h1', '#content');
INSERT INTO `books_rule_info` VALUES ('11', '', '', '', '', '', '', '.L>a', '.L>a', 'h1', '#amain>dl>#contents');
INSERT INTO `books_rule_info` VALUES ('10', '', '', '', '', '', '', '.listmain>dl>dd>a', '.listmain>dl>dd>a', 'h1', '#content');

-- ----------------------------
-- Table structure for books_seo
-- ----------------------------
DROP TABLE IF EXISTS `books_seo`;
CREATE TABLE `books_seo` (
  `seo_id` int(11) NOT NULL AUTO_INCREMENT,
  `seo_module` varchar(255) NOT NULL DEFAULT '' COMMENT '模块标识',
  `seo_remark` varchar(255) DEFAULT '' COMMENT '模块备注',
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_keywords` text,
  `seo_description` text,
  `is_disable` tinyint(4) DEFAULT '0' COMMENT '是否禁用 0否 1是',
  PRIMARY KEY (`seo_id`,`seo_module`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of books_seo
-- ----------------------------

-- ----------------------------
-- Table structure for books_shelf
-- ----------------------------
DROP TABLE IF EXISTS `books_shelf`;
CREATE TABLE `books_shelf` (
  `user_id` int(8) NOT NULL COMMENT '用户id',
  `books_id` int(8) NOT NULL COMMENT '书籍id',
  `sort` int(4) DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`books_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of books_shelf
-- ----------------------------

-- ----------------------------
-- Table structure for books_type
-- ----------------------------
DROP TABLE IF EXISTS `books_type`;
CREATE TABLE `books_type` (
  `type_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type_name` varchar(255) COLLATE utf8_bin NOT NULL COMMENT '类型名称',
  `type_sort` int(11) DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of books_type
-- ----------------------------
INSERT INTO `books_type` VALUES ('1', '玄幻', '1');
INSERT INTO `books_type` VALUES ('2', '奇幻', '2');
INSERT INTO `books_type` VALUES ('3', '武侠', '3');
INSERT INTO `books_type` VALUES ('4', '仙侠', '4');
INSERT INTO `books_type` VALUES ('5', '都市', '5');
INSERT INTO `books_type` VALUES ('6', '历史', '6');
INSERT INTO `books_type` VALUES ('7', '军事', '7');
INSERT INTO `books_type` VALUES ('8', '游戏', '8');
INSERT INTO `books_type` VALUES ('9', '竞技', '9');
INSERT INTO `books_type` VALUES ('10', '科幻', '10');
INSERT INTO `books_type` VALUES ('11', '灵异', '11');
INSERT INTO `books_type` VALUES ('12', '同人', '12');
INSERT INTO `books_type` VALUES ('13', '女生', '13');
INSERT INTO `books_type` VALUES ('14', '其他', '14');

-- ----------------------------
-- Table structure for books_user
-- ----------------------------
DROP TABLE IF EXISTS `books_user`;
CREATE TABLE `books_user` (
  `user_id` int(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `user_name` varchar(255) DEFAULT '' COMMENT '用户名',
  `user_password` varchar(255) DEFAULT '' COMMENT '用户密码',
  `user_img` varchar(255) DEFAULT '' COMMENT '用户头像',
  `user_email` varchar(255) DEFAULT '' COMMENT '用户邮箱',
  `add_time` datetime DEFAULT NULL COMMENT '创建时间',
  `is_disable` tinyint(4) DEFAULT '0' COMMENT '是否禁用',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of books_user
-- ----------------------------
INSERT INTO `books_user` VALUES ('1', '守望的深渊', 'e10adc3949ba59abbe56e057f20f883e', '/static/images/portrait/5c3323881e9c0.jpg', '1258598558@qq.com', '2018-07-31 15:06:21', '0');
