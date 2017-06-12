/*
Navicat MySQL Data Transfer

Source Server         : WebServer
Source Server Version : 50549
Source Host           : 115.28.133.134:3306
Source Database       : tp3

Target Server Type    : MYSQL
Target Server Version : 50549
File Encoding         : 65001

Date: 2017-05-28 23:41:53
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for tp_admin_nav
-- ----------------------------
DROP TABLE IF EXISTS `tp_admin_nav`;
CREATE TABLE `tp_admin_nav` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '菜单表',
  `pid` int(11) unsigned DEFAULT '0' COMMENT '所属菜单',
  `name` varchar(15) DEFAULT '' COMMENT '菜单名称',
  `mca` varchar(255) DEFAULT '' COMMENT '模块、控制器、方法',
  `ico` varchar(20) DEFAULT '' COMMENT 'font-awesome图标',
  `order_number` int(11) unsigned DEFAULT NULL COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tp_auth_group
-- ----------------------------
DROP TABLE IF EXISTS `tp_auth_group`;
CREATE TABLE `tp_auth_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(100) NOT NULL DEFAULT '' COMMENT '用户组中文名称',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：为1正常，为0禁用',
  `rules` char(255) NOT NULL DEFAULT '' COMMENT '用户组拥有的规则id， 多个规则","隔开',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='用户组表';

-- ----------------------------
-- Table structure for tp_auth_group_access
-- ----------------------------
DROP TABLE IF EXISTS `tp_auth_group_access`;
CREATE TABLE `tp_auth_group_access` (
  `uid` mediumint(8) unsigned NOT NULL COMMENT '用户id',
  `group_id` mediumint(8) unsigned NOT NULL COMMENT '用户组id',
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户组明细表';

-- ----------------------------
-- Table structure for tp_auth_rule
-- ----------------------------
DROP TABLE IF EXISTS `tp_auth_rule`;
CREATE TABLE `tp_auth_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT NULL COMMENT '上级权限',
  `name` char(80) NOT NULL DEFAULT '' COMMENT '规则唯一标识',
  `title` char(20) NOT NULL DEFAULT '' COMMENT '规则中文名称',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：为1正常，为0禁用',
  `condition` char(100) NOT NULL DEFAULT '' COMMENT '规则表达式，为空表示存在就验证，不为空表示按照条件验证',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=55 DEFAULT CHARSET=utf8 COMMENT='规则表';

-- ----------------------------
-- Table structure for tp_chat_room
-- ----------------------------
DROP TABLE IF EXISTS `tp_chat_room`;
CREATE TABLE `tp_chat_room` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `series` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'uid1_rid1_uid2_rid2',
  `messages` text NOT NULL COMMENT '历史消息 json\r\nuid: rid: content: time: ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='聊天室  双人聊';

-- ----------------------------
-- Table structure for tp_course
-- ----------------------------
DROP TABLE IF EXISTS `tp_course`;
CREATE TABLE `tp_course` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(30) NOT NULL DEFAULT '' COMMENT '课程名称',
  `subtitle` varchar(80) NOT NULL COMMENT '课程副标题',
  `category_id` int(11) NOT NULL DEFAULT '0' COMMENT '分类id',
  `status` enum('draft','published','closed') NOT NULL DEFAULT 'closed' COMMENT '课程状态',
  `teacher_id` int(12) NOT NULL COMMENT '任课教师id',
  `university_id` int(11) DEFAULT NULL COMMENT '学校id',
  `description` text NOT NULL COMMENT '课程介绍',
  `tags` varchar(255) NOT NULL,
  `create_time` int(11) NOT NULL COMMENT '课程创建时间',
  `release_date` int(11) NOT NULL COMMENT '课程发布日期',
  `course_start_date` int(11) NOT NULL COMMENT '课程开始时间',
  `course_end_date` int(11) NOT NULL COMMENT '课程结束时间',
  `exam_start_date` int(11) NOT NULL COMMENT '考试开始时间',
  `exam_end_date` int(11) NOT NULL COMMENT '考试结束时间',
  `query_results_start_date` int(11) NOT NULL COMMENT '查询成绩开始时间',
  `query_results_end_date` int(11) NOT NULL COMMENT '查询成绩结束时间',
  `require_skills` varchar(255) NOT NULL COMMENT '需要知识 ',
  `picture_path` varchar(255) NOT NULL COMMENT '课程图像的地址',
  `picture_name` varchar(80) NOT NULL COMMENT '课程图像的名字\r\n分类slug+课程name+time()',
  `has_picture` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否课程的图像\r\n若无使用默认图片',
  `learn_count` int(11) NOT NULL COMMENT '学习人数',
  `finish_count` int(11) NOT NULL COMMENT '完成课程学习人数',
  `checked` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已经通过审核',
  `uncheck_reason` text NOT NULL COMMENT '未通过审核原因',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=42 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tp_course_bulletin
-- ----------------------------
DROP TABLE IF EXISTS `tp_course_bulletin`;
CREATE TABLE `tp_course_bulletin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `post_time` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='课程公告';

-- ----------------------------
-- Table structure for tp_course_category
-- ----------------------------
DROP TABLE IF EXISTS `tp_course_category`;
CREATE TABLE `tp_course_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类id',
  `name` varchar(35) NOT NULL COMMENT '分类名称',
  `slug` varchar(37) NOT NULL COMMENT '分类别名 英文',
  `group_id` int(11) NOT NULL COMMENT '所属组id',
  `description` varchar(255) NOT NULL COMMENT '分类描述',
  `count` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tp_course_chapter
-- ----------------------------
DROP TABLE IF EXISTS `tp_course_chapter`;
CREATE TABLE `tp_course_chapter` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程章节ID',
  `course_id` int(10) unsigned NOT NULL COMMENT '章节所属课程ID',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父级id 若是节 父级为章',
  `number` int(10) unsigned NOT NULL COMMENT '章节编号',
  `seq` int(10) unsigned NOT NULL COMMENT '章节序号',
  `title` varchar(255) NOT NULL COMMENT '章节名称',
  `created_time` int(10) NOT NULL COMMENT '章节创建时间',
  PRIMARY KEY (`id`,`seq`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COMMENT='课程章节表';

-- ----------------------------
-- Table structure for tp_course_discuss
-- ----------------------------
DROP TABLE IF EXISTS `tp_course_discuss`;
CREATE TABLE `tp_course_discuss` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(11) unsigned NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `post_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='课程讨论';

-- ----------------------------
-- Table structure for tp_course_discuss_reply
-- ----------------------------
DROP TABLE IF EXISTS `tp_course_discuss_reply`;
CREATE TABLE `tp_course_discuss_reply` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `discuss_id` int(11) unsigned NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `quotes` int(255) NOT NULL COMMENT '引用回复id',
  `post_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='课程讨论回复';

-- ----------------------------
-- Table structure for tp_course_files
-- ----------------------------
DROP TABLE IF EXISTS `tp_course_files`;
CREATE TABLE `tp_course_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程文件ID',
  `course_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属课程ID\r\n0表示尚未添加到任何课程',
  `title` varchar(255) NOT NULL COMMENT '文件名称',
  `description` varchar(255) NOT NULL COMMENT '文件描述',
  `uri` text NOT NULL COMMENT '文件uri',
  `size` int(12) NOT NULL COMMENT '文件大小',
  `type` varchar(25) NOT NULL DEFAULT 'video' COMMENT '文件类型',
  `created_time` int(10) NOT NULL COMMENT '文件上传时间',
  `user_id` int(11) NOT NULL COMMENT '上传人ID',
  `update_time` int(11) NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=102 DEFAULT CHARSET=utf8 COMMENT='课程文件';

-- ----------------------------
-- Table structure for tp_course_follow
-- ----------------------------
DROP TABLE IF EXISTS `tp_course_follow`;
CREATE TABLE `tp_course_follow` (
  `user_id` int(11) unsigned NOT NULL COMMENT '学生id',
  `course_id` int(11) unsigned NOT NULL COMMENT '课程id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='课程关注';

-- ----------------------------
-- Table structure for tp_course_lesson
-- ----------------------------
DROP TABLE IF EXISTS `tp_course_lesson`;
CREATE TABLE `tp_course_lesson` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '课时ID',
  `course_id` int(11) DEFAULT NULL COMMENT '所属课程 id',
  `chapter_id` int(11) NOT NULL COMMENT '课时所属章节ID',
  `number` int(11) unsigned NOT NULL COMMENT '课时编号',
  `seq` int(11) unsigned NOT NULL COMMENT '课时在课程中的序号',
  `status` enum('unpublished','published') NOT NULL DEFAULT 'unpublished' COMMENT '课时状态',
  `title` varchar(255) NOT NULL COMMENT '课时标题',
  `type` enum('video','audio','text') NOT NULL DEFAULT 'video' COMMENT '课时类型',
  `summary` text NOT NULL COMMENT '课时摘要',
  `content` text NOT NULL COMMENT '课时正文',
  `media_id` int(11) unsigned NOT NULL COMMENT '媒体文件ID',
  `homework_id` int(10) unsigned NOT NULL COMMENT '作业iD',
  `exercise_id` int(10) unsigned NOT NULL COMMENT '练习ID',
  `created_time` int(11) NOT NULL COMMENT '创建时间',
  `updated_time` int(10) unsigned NOT NULL COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=utf8 COMMENT='课程课时表 ';

-- ----------------------------
-- Table structure for tp_course_lesson_learn
-- ----------------------------
DROP TABLE IF EXISTS `tp_course_lesson_learn`;
CREATE TABLE `tp_course_lesson_learn` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '学员课时学习记录ID',
  `user_id` int(10) unsigned NOT NULL COMMENT '学员ID',
  `course_id` int(10) unsigned NOT NULL COMMENT '课程ID',
  `lesson_id` int(10) NOT NULL COMMENT '课时ID',
  `status` enum('learning','finished') NOT NULL COMMENT '学习状态',
  `start_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习开始时间',
  `finished_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习完成时间',
  `learn_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习时间',
  `learn_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时学习次数',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id_course_id` (`user_id`,`course_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='课时学习记录表';

-- ----------------------------
-- Table structure for tp_course_paper
-- ----------------------------
DROP TABLE IF EXISTS `tp_course_paper`;
CREATE TABLE `tp_course_paper` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '试卷id',
  `title` varchar(36) NOT NULL COMMENT '试卷标题',
  `description` varchar(255) NOT NULL COMMENT '试卷描述',
  `type` enum('auto','hand') NOT NULL DEFAULT 'auto' COMMENT '试卷类型 \r\nauto 自动生成\r\nhand 手动添加',
  `questions_list` varchar(255) NOT NULL COMMENT '手工组卷时 为 题目列表 系统自动组卷为空',
  `course_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '所属课程id\r\n0为暂时尚未被任何课程使用',
  `user_id` int(10) unsigned NOT NULL COMMENT '创建人 id',
  `created_time` int(10) unsigned NOT NULL COMMENT '创建日期',
  `status` enum('open','closed') NOT NULL DEFAULT 'closed' COMMENT '状态 默认关闭',
  `settings` varchar(255) NOT NULL COMMENT '试卷设置 \r\n诸多设置\r\n',
  `paper_start_date` int(11) unsigned NOT NULL COMMENT '开始时间',
  `paper_end_date` int(11) unsigned NOT NULL COMMENT '结束时间',
  `time_limit` varchar(255) NOT NULL COMMENT '时间限制 ',
  `total_scores` int(11) NOT NULL DEFAULT '100' COMMENT '总分',
  `pass_line` int(11) NOT NULL DEFAULT '60' COMMENT '及格线',
  `alow_times` int(11) NOT NULL DEFAULT '1' COMMENT '允许答题次数',
  `gener_method` enum('random','difficulty') NOT NULL DEFAULT 'random',
  `diff_setting` varchar(255) NOT NULL,
  `exam_range` varchar(255) NOT NULL COMMENT '出题范围',
  `exam_categ_setting` varchar(255) NOT NULL COMMENT '题目各类数目及分值设置',
  `question_sort` varchar(255) NOT NULL COMMENT '题型排序',
  `exam_password` varchar(255) NOT NULL DEFAULT '0' COMMENT '题目访问密码',
  `limit_net_addr` varchar(255) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=63 DEFAULT CHARSET=utf8 COMMENT='试卷表';

-- ----------------------------
-- Table structure for tp_course_question
-- ----------------------------
DROP TABLE IF EXISTS `tp_course_question`;
CREATE TABLE `tp_course_question` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL COMMENT '题目类型',
  `stem` text NOT NULL COMMENT '题干',
  `metas` text NOT NULL COMMENT '元信息 保存选项 判断项等内容',
  `answer` text NOT NULL COMMENT '参考答案',
  `analysis` text NOT NULL COMMENT '解析',
  `difficulty` enum('easy','normal','hard') NOT NULL,
  `user_id` int(11) NOT NULL COMMENT '教师id',
  `course_id` int(11) NOT NULL COMMENT '所属课程id\r\n0 表示暂不属于任何课程 默认0',
  `created_time` int(11) NOT NULL COMMENT '创建时间',
  `updated_time` int(11) NOT NULL COMMENT '最后更新时间',
  `finished_times` int(11) NOT NULL COMMENT '完成次数',
  `passed_times` int(11) NOT NULL COMMENT '通过次数',
  `remarks` varchar(255) NOT NULL COMMENT '题目备注 ',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=71 DEFAULT CHARSET=utf8 COMMENT='题目表记录';

-- ----------------------------
-- Table structure for tp_email
-- ----------------------------
DROP TABLE IF EXISTS `tp_email`;
CREATE TABLE `tp_email` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `from_uid` int(11) unsigned NOT NULL COMMENT '发送者uid',
  `from_rid` int(11) unsigned NOT NULL,
  `to_uid` int(11) unsigned NOT NULL COMMENT '接收者id',
  `to_rid` int(11) unsigned NOT NULL,
  `subject` varchar(50) NOT NULL COMMENT '主题',
  `content` text NOT NULL COMMENT '正文内容',
  `attachment_id` int(11) NOT NULL COMMENT '文件',
  `post_time` int(11) NOT NULL COMMENT '发送时间',
  `isread` tinyint(2) NOT NULL DEFAULT '0' COMMENT '收件人是否已读',
  `stared` tinyint(2) NOT NULL DEFAULT '0' COMMENT '收藏星标邮件',
  `drafted` tinyint(2) NOT NULL COMMENT '是否为草稿',
  `status` varchar(12) NOT NULL DEFAULT 'normal' COMMENT '邮件状态\r\n1 普通邮件 normal\r\n2 垃圾邮件 trash\r\n3星标邮件 star\r\n4 草稿邮件 draft',
  `tags` varchar(36) NOT NULL COMMENT '标签',
  `category` varchar(36) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COMMENT='站内信 ';

-- ----------------------------
-- Table structure for tp_files
-- ----------------------------
DROP TABLE IF EXISTS `tp_files`;
CREATE TABLE `tp_files` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(36) NOT NULL COMMENT '文件名',
  `type` varchar(36) NOT NULL COMMENT '文件类型',
  `size` int(11) NOT NULL COMMENT '大小 byte',
  `uri` varchar(255) NOT NULL COMMENT '文件保存路径',
  `description` varchar(255) NOT NULL COMMENT '文件描述',
  `upload_user` varchar(36) NOT NULL COMMENT '上传人 uid,role\r\njson 格式',
  `upload_time` int(11) NOT NULL COMMENT '上传时间',
  `domain` varchar(36) NOT NULL COMMENT '文件所属域\r\nmanual 手册\r\nforum  论坛\r\nnews   新闻\r\nuser    用户\r\nlive     直播\r\nchat    聊天\r\nemail 站内信\r\ncourse已经单独放置一张表 最常用',
  `access_role` varchar(36) NOT NULL DEFAULT '0' COMMENT '访问权限角色\r\n0 all\r\n1 admin\r\n2 teacher \r\n3 student\r\n4 visitor\r\n',
  `password` varchar(255) DEFAULT NULL COMMENT '访问密码',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=utf8 COMMENT='文件记录表--普通文件';

-- ----------------------------
-- Table structure for tp_forum
-- ----------------------------
DROP TABLE IF EXISTS `tp_forum`;
CREATE TABLE `tp_forum` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '板块id',
  `title` varchar(255) NOT NULL COMMENT '板块名',
  `description` varchar(255) NOT NULL COMMENT '板块描述',
  `pid` int(11) NOT NULL COMMENT '父板块id',
  `status` enum('close','open') NOT NULL DEFAULT 'open' COMMENT '状态',
  `icon` varchar(255) NOT NULL COMMENT 'fontawesome 图标',
  `slug` varchar(255) NOT NULL COMMENT '别名',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='论坛 板块记录表';

-- ----------------------------
-- Table structure for tp_forum_post
-- ----------------------------
DROP TABLE IF EXISTS `tp_forum_post`;
CREATE TABLE `tp_forum_post` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `forum_id` int(11) NOT NULL COMMENT '所属板块id',
  `title` varchar(36) NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `user_id` int(11) NOT NULL COMMENT '用户 id',
  `role_id` int(11) NOT NULL COMMENT '角色 id \r\n教师 管理员 学生均可发帖 回帖',
  `created_time` int(11) NOT NULL COMMENT '创建时间',
  `updated_time` int(11) DEFAULT NULL COMMENT '最后更新时间',
  `attachment_id` int(11) DEFAULT NULL COMMENT '附件id 多个文件请压缩打包',
  `tags` varchar(255) NOT NULL COMMENT '标签',
  `view_count` int(11) NOT NULL COMMENT '浏览数',
  `reply_count` int(11) NOT NULL COMMENT '回复数',
  `status` enum('essence','stick','normal') NOT NULL DEFAULT 'normal' COMMENT '帖子状态\r\nnormal 正常  默认 \r\nstick 置顶\r\nessence 精华',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='论坛帖子表';

-- ----------------------------
-- Table structure for tp_forum_reply
-- ----------------------------
DROP TABLE IF EXISTS `tp_forum_reply`;
CREATE TABLE `tp_forum_reply` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '回复id',
  `post_id` int(11) NOT NULL COMMENT '帖子id',
  `content` text NOT NULL,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `role_id` int(11) NOT NULL COMMENT '用户角色id',
  `metas` varchar(16) NOT NULL COMMENT '元信息',
  `attachment_id` int(11) DEFAULT NULL COMMENT '附件id',
  `post_time` int(11) NOT NULL COMMENT '回复时间',
  `thumbup_count` int(11) NOT NULL COMMENT '点赞次数',
  `quotes` int(11) DEFAULT NULL COMMENT '引用回复id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COMMENT='论坛帖子回复';

-- ----------------------------
-- Table structure for tp_forum_tag
-- ----------------------------
DROP TABLE IF EXISTS `tp_forum_tag`;
CREATE TABLE `tp_forum_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tp_forum_upvote
-- ----------------------------
DROP TABLE IF EXISTS `tp_forum_upvote`;
CREATE TABLE `tp_forum_upvote` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `reply_id` int(11) unsigned NOT NULL COMMENT '回复id',
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COMMENT='点赞表';

-- ----------------------------
-- Table structure for tp_lesson_answers
-- ----------------------------
DROP TABLE IF EXISTS `tp_lesson_answers`;
CREATE TABLE `tp_lesson_answers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` int(11) unsigned NOT NULL,
  `user_id` int(11) NOT NULL,
  `answer` text NOT NULL,
  `post_time` int(11) NOT NULL,
  `metas` varchar(255) NOT NULL COMMENT '元信息',
  `quotes` int(255) NOT NULL COMMENT '引用他人回复 id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tp_lesson_materials
-- ----------------------------
DROP TABLE IF EXISTS `tp_lesson_materials`;
CREATE TABLE `tp_lesson_materials` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lesson_id` int(11) unsigned NOT NULL,
  `file_id` int(11) NOT NULL COMMENT '文件id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='课时资料表';

-- ----------------------------
-- Table structure for tp_lesson_notes
-- ----------------------------
DROP TABLE IF EXISTS `tp_lesson_notes`;
CREATE TABLE `tp_lesson_notes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lesson_id` int(11) NOT NULL COMMENT '课时id',
  `content` text NOT NULL COMMENT '笔记内容',
  `user_id` int(11) NOT NULL,
  `created_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '最后修改时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='课时笔记表';

-- ----------------------------
-- Table structure for tp_lesson_questions
-- ----------------------------
DROP TABLE IF EXISTS `tp_lesson_questions`;
CREATE TABLE `tp_lesson_questions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '问题id',
  `lesson_id` int(11) NOT NULL COMMENT '课时id',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `user_id` int(11) NOT NULL COMMENT '提问者id',
  `content` text NOT NULL COMMENT '问题内容',
  `created_time` int(11) NOT NULL COMMENT '创建时间',
  `updated_time` int(11) NOT NULL COMMENT '最后修改时间',
  `last_reply_time` int(11) NOT NULL COMMENT '最新回复时间',
  `has_reply` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否有回复 （学生）',
  `teacher_reply` tinyint(2) NOT NULL COMMENT '教师回复',
  `sticktop` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否置顶 ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COMMENT='问题表';

-- ----------------------------
-- Table structure for tp_live
-- ----------------------------
DROP TABLE IF EXISTS `tp_live`;
CREATE TABLE `tp_live` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '直播 id\r\n 一个直播间有多个直播(共)',
  `title` varchar(255) NOT NULL COMMENT '直播间名称',
  `room_id` int(11) unsigned NOT NULL COMMENT '房间id',
  `teacher_id` int(11) NOT NULL COMMENT '教师id （重）',
  `category_id` int(11) NOT NULL COMMENT '分类id',
  `created_time` int(11) NOT NULL COMMENT '创建时间',
  `release_time` int(11) NOT NULL COMMENT '发布时间',
  `start` int(11) NOT NULL COMMENT '开始时间',
  `collect_num` int(11) NOT NULL COMMENT '关注人数',
  `watch_num` int(11) NOT NULL COMMENT '在线观看人数',
  `status` varchar(255) NOT NULL DEFAULT 'open' COMMENT '状态\r\n指的是该次直播的状态 open正常进行 \r\nclosed关闭',
  `description` varchar(255) NOT NULL COMMENT '简介',
  `has_poster` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否有直播封面/海报',
  `poster_uri` varchar(255) NOT NULL COMMENT '封面uri',
  `poster_name` varchar(255) NOT NULL COMMENT '海报文件名',
  `tags` varchar(255) NOT NULL COMMENT '标签',
  `is_over` smallint(2) NOT NULL COMMENT '是否已经结束',
  `rtmp_keys` varchar(255) NOT NULL COMMENT 'rtmp url key',
  `checked` tinyint(1) NOT NULL DEFAULT '0',
  `uncheck_reason` varchar(255) NOT NULL COMMENT '未通过审核原因',
  `vod_uri` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='直播表';

-- ----------------------------
-- Table structure for tp_live_category
-- ----------------------------
DROP TABLE IF EXISTS `tp_live_category`;
CREATE TABLE `tp_live_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='直播分类';

-- ----------------------------
-- Table structure for tp_live_chat_message
-- ----------------------------
DROP TABLE IF EXISTS `tp_live_chat_message`;
CREATE TABLE `tp_live_chat_message` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `message` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `post_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tp_live_room
-- ----------------------------
DROP TABLE IF EXISTS `tp_live_room`;
CREATE TABLE `tp_live_room` (
  `room_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `status` enum('normal','banned') NOT NULL DEFAULT 'normal' COMMENT '直播间状态\r\nnormal 正常\r\nbanned 封禁',
  `collect_num` int(11) NOT NULL COMMENT '收藏人数',
  `rtmp` varchar(255) NOT NULL DEFAULT 'rtmp://fms.zhfsky.com/live' COMMENT 'RTMP推流地址'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='直播间信息表';

-- ----------------------------
-- Table structure for tp_manual
-- ----------------------------
DROP TABLE IF EXISTS `tp_manual`;
CREATE TABLE `tp_manual` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '手册 id',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `type` enum('admin','teacher','student','common') NOT NULL DEFAULT 'common' COMMENT '类型\r\n教师\r\n管理员\r\n学生\r\n公共',
  `created_user` varchar(30) NOT NULL COMMENT '创建人 uid,rid',
  `created_time` int(11) NOT NULL,
  `updated_time` int(11) NOT NULL,
  `content` text NOT NULL,
  `description` varchar(255) NOT NULL COMMENT '手册简介',
  `status` varchar(255) NOT NULL DEFAULT 'open' COMMENT '状态',
  `category_id` int(11) unsigned NOT NULL COMMENT '所属类别id',
  `tags` varchar(255) NOT NULL COMMENT '标签',
  `views` varchar(255) NOT NULL COMMENT '查看数目',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='手册表';

-- ----------------------------
-- Table structure for tp_manual_category
-- ----------------------------
DROP TABLE IF EXISTS `tp_manual_category`;
CREATE TABLE `tp_manual_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'category id',
  `name` varchar(36) NOT NULL COMMENT 'category name',
  `slug` varchar(36) NOT NULL COMMENT '别名 英文',
  `description` varchar(255) NOT NULL COMMENT '描述',
  `pid` int(11) NOT NULL COMMENT '上级id',
  `count` int(11) NOT NULL COMMENT '总数',
  `views` int(11) NOT NULL COMMENT '查看次数',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='手册类型记录表';

-- ----------------------------
-- Table structure for tp_messages
-- ----------------------------
DROP TABLE IF EXISTS `tp_messages`;
CREATE TABLE `tp_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'messge id',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `role_id` int(11) NOT NULL COMMENT '用户角色id',
  `message` text NOT NULL COMMENT '消息内容',
  `post_time` int(11) NOT NULL COMMENT '发送时间',
  `type` varchar(36) NOT NULL COMMENT '消息类型\r\nsystem 系统消息\r\nforum 论坛消息\r\ncourse课程消息\r\naccount 账户消息 ',
  `status` enum('read','unread') NOT NULL DEFAULT 'unread' COMMENT '读取状态',
  `event` varchar(36) NOT NULL COMMENT '事件\r\n',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=180 DEFAULT CHARSET=utf8 COMMENT='系统消息|通知 来自系统 短消息 非站内信';

-- ----------------------------
-- Table structure for tp_news
-- ----------------------------
DROP TABLE IF EXISTS `tp_news`;
CREATE TABLE `tp_news` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(36) NOT NULL,
  `category_id` int(11) NOT NULL COMMENT '分类id',
  `content` text NOT NULL,
  `author` varchar(30) NOT NULL COMMENT '发布人id',
  `tags` int(12) DEFAULT NULL COMMENT '标签id',
  `status` varchar(20) NOT NULL DEFAULT 'publish' COMMENT '状态\r\n',
  `post_time` int(11) NOT NULL COMMENT '发表时间',
  `updated_time` int(11) NOT NULL,
  `views` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='新闻 ';

-- ----------------------------
-- Table structure for tp_news_category
-- ----------------------------
DROP TABLE IF EXISTS `tp_news_category`;
CREATE TABLE `tp_news_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `pid` int(11) NOT NULL,
  `count` int(11) NOT NULL,
  `views` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='新闻分类表';

-- ----------------------------
-- Table structure for tp_oauth
-- ----------------------------
DROP TABLE IF EXISTS `tp_oauth`;
CREATE TABLE `tp_oauth` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `access_token` varchar(255) NOT NULL COMMENT 'access_token',
  `openid` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tp_oj_compileinfo
-- ----------------------------
DROP TABLE IF EXISTS `tp_oj_compileinfo`;
CREATE TABLE `tp_oj_compileinfo` (
  `solution_id` int(11) NOT NULL DEFAULT '0',
  `error` text,
  PRIMARY KEY (`solution_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tp_oj_problem
-- ----------------------------
DROP TABLE IF EXISTS `tp_oj_problem`;
CREATE TABLE `tp_oj_problem` (
  `problem_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '题目标题',
  `description` text COMMENT '题目描述',
  `input` text COMMENT '输入',
  `output` text COMMENT '输出',
  `sample_input` text COMMENT '样板输入',
  `sample_output` text COMMENT '样本输出',
  `spj` char(1) DEFAULT '0' COMMENT 'special oj',
  `hint` text COMMENT '提示',
  `source` varchar(100) DEFAULT NULL,
  `in_date` datetime DEFAULT NULL COMMENT '创建时间',
  `time_limit` int(11) NOT NULL DEFAULT '0' COMMENT '运行时间限制',
  `memory_limit` int(11) NOT NULL DEFAULT '0' COMMENT '运行内存限制',
  `defunct` char(1) NOT NULL DEFAULT 'N',
  `accepted` int(11) DEFAULT '0' COMMENT '结果接受次数',
  `submit` int(11) DEFAULT '0' COMMENT '提交次数',
  `solved` int(11) DEFAULT '0' COMMENT '解决次数',
  `status` varchar(255) DEFAULT 'open' COMMENT '公开|私有',
  PRIMARY KEY (`problem_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1022 DEFAULT CHARSET=utf8 COMMENT='OJ系统题目表';

-- ----------------------------
-- Table structure for tp_oj_runtimeinfo
-- ----------------------------
DROP TABLE IF EXISTS `tp_oj_runtimeinfo`;
CREATE TABLE `tp_oj_runtimeinfo` (
  `solution_id` int(11) NOT NULL DEFAULT '0',
  `error` text CHARACTER SET utf8,
  PRIMARY KEY (`solution_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for tp_oj_solution
-- ----------------------------
DROP TABLE IF EXISTS `tp_oj_solution`;
CREATE TABLE `tp_oj_solution` (
  `solution_id` int(11) NOT NULL AUTO_INCREMENT,
  `problem_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) DEFAULT NULL,
  `time` int(11) NOT NULL DEFAULT '0',
  `memory` int(11) NOT NULL DEFAULT '0',
  `in_date` datetime NOT NULL DEFAULT '2016-05-13 19:24:00',
  `result` smallint(6) NOT NULL,
  `language` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` char(15) NOT NULL,
  `contest_id` int(11) DEFAULT NULL,
  `valid` tinyint(4) NOT NULL DEFAULT '1',
  `num` tinyint(4) NOT NULL DEFAULT '-1',
  `code_length` int(11) NOT NULL DEFAULT '0',
  `judgetime` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `pass_rate` decimal(3,2) NOT NULL DEFAULT '0.00',
  `lint_error` int(10) unsigned NOT NULL DEFAULT '0',
  `judger` char(16) NOT NULL DEFAULT 'LOCAL',
  PRIMARY KEY (`solution_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1059 DEFAULT CHARSET=utf8 COMMENT='OJ系统 solution 表';

-- ----------------------------
-- Table structure for tp_oj_source_code
-- ----------------------------
DROP TABLE IF EXISTS `tp_oj_source_code`;
CREATE TABLE `tp_oj_source_code` (
  `solution_id` int(10) unsigned NOT NULL,
  `source` text NOT NULL COMMENT '代码',
  PRIMARY KEY (`solution_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Oj系统的source code表';

-- ----------------------------
-- Table structure for tp_relation
-- ----------------------------
DROP TABLE IF EXISTS `tp_relation`;
CREATE TABLE `tp_relation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'relation id',
  `user_id` int(11) NOT NULL COMMENT '当前用户id',
  `role_id` int(11) NOT NULL COMMENT '当前用户rid',
  `friend_uid` int(11) NOT NULL,
  `friend_rid` int(11) NOT NULL,
  `status` varchar(255) NOT NULL COMMENT 'friend \r\nfollow\r\n',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COMMENT='用户关系表 \r\n好友 关注 ';

-- ----------------------------
-- Table structure for tp_session
-- ----------------------------
DROP TABLE IF EXISTS `tp_session`;
CREATE TABLE `tp_session` (
  `session_id` varchar(255) NOT NULL,
  `session_expire` int(11) NOT NULL,
  `session_data` blob,
  UNIQUE KEY `session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tp_setting
-- ----------------------------
DROP TABLE IF EXISTS `tp_setting`;
CREATE TABLE `tp_setting` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '键',
  `value` varchar(255) NOT NULL COMMENT '值',
  `group` varchar(255) NOT NULL COMMENT '群组',
  `remarks` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COMMENT='系统设置记录表';

-- ----------------------------
-- Table structure for tp_sms
-- ----------------------------
DROP TABLE IF EXISTS `tp_sms`;
CREATE TABLE `tp_sms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tel` varchar(15) DEFAULT NULL COMMENT '手机号码',
  `verify_code` decimal(6,0) NOT NULL COMMENT '短信验证码',
  `send_time` int(12) DEFAULT '0' COMMENT '验证码发送时间',
  `token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=111 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tp_stu
-- ----------------------------
DROP TABLE IF EXISTS `tp_stu`;
CREATE TABLE `tp_stu` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(25) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '用户密码',
  `nickname` varchar(50) DEFAULT NULL COMMENT '昵称',
  `truename` varchar(30) DEFAULT NULL COMMENT '真实姓名',
  `sex` char(3) NOT NULL COMMENT '性别',
  `email` varchar(30) NOT NULL COMMENT '电子邮箱',
  `tel` varchar(15) NOT NULL COMMENT '手机号码',
  `avatar` varchar(255) NOT NULL DEFAULT '/Public/Home/uassets/img/default_avatar.png' COMMENT '用户头像',
  `description` varchar(255) NOT NULL,
  `registered` int(11) NOT NULL COMMENT '注册时间',
  `last_login_time` int(11) NOT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(255) NOT NULL COMMENT '最后登录IP',
  `remarks` varchar(255) DEFAULT NULL COMMENT '备注信息',
  `salt` varchar(255) NOT NULL DEFAULT '605c3970f09f5c0e3653f8a96247e57e' COMMENT '盐\r\n默认 md5(naihaifoe)',
  `access_send_time` int(11) NOT NULL COMMENT '邮箱注册激活发送时间',
  `access_token` varchar(255) DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT '0' COMMENT '账户是否激活',
  `oauth_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `a` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=39 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tp_stu_setting
-- ----------------------------
DROP TABLE IF EXISTS `tp_stu_setting`;
CREATE TABLE `tp_stu_setting` (
  `user_id` int(11) unsigned NOT NULL COMMENT '教师id ',
  `layout` varchar(255) NOT NULL,
  `theme` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL COMMENT '邮箱订阅与通知设置',
  `privacy` varchar(255) NOT NULL COMMENT '隐私设置',
  `inform` varchar(255) NOT NULL COMMENT '通知设置',
  UNIQUE KEY `uid` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tp_teacher
-- ----------------------------
DROP TABLE IF EXISTS `tp_teacher`;
CREATE TABLE `tp_teacher` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `sex` varchar(6) NOT NULL,
  `password` varchar(255) NOT NULL,
  `school` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL COMMENT '教师简介',
  `avatar` varchar(255) NOT NULL DEFAULT '/Public/Home/uassets/img/default_avatar.png' COMMENT '头像uri地址',
  `major` varchar(50) NOT NULL,
  `tel` varchar(15) NOT NULL COMMENT '手机号码',
  `email` varchar(45) NOT NULL,
  `salt` varchar(255) NOT NULL COMMENT '加密盐',
  `enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否激活',
  `locked` tinyint(1) NOT NULL,
  `registered` int(11) NOT NULL COMMENT '注册时间',
  `last_login_time` int(11) NOT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(40) DEFAULT NULL COMMENT '最后登录IP',
  `access_send_time` int(11) NOT NULL COMMENT '验证邮件最新发送时间',
  `access_token` varchar(255) NOT NULL COMMENT '账号验证 token',
  `session_id` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tp_teacher_forum
-- ----------------------------
DROP TABLE IF EXISTS `tp_teacher_forum`;
CREATE TABLE `tp_teacher_forum` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(36) NOT NULL COMMENT '论坛板块标题',
  `subtitle` varchar(36) NOT NULL COMMENT '论坛板块副标题',
  `description` text NOT NULL COMMENT '论坛板块描述',
  `pid` int(11) NOT NULL COMMENT '上级板块id',
  `domain` enum('other','general') NOT NULL DEFAULT 'general' COMMENT '板块所属域',
  `slug` varchar(36) NOT NULL COMMENT '别名',
  `status` enum('close','open') NOT NULL DEFAULT 'open' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tp_teacher_forum_post
-- ----------------------------
DROP TABLE IF EXISTS `tp_teacher_forum_post`;
CREATE TABLE `tp_teacher_forum_post` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '帖子id',
  `forum_id` int(11) unsigned NOT NULL COMMENT '所属板块id',
  `title` varchar(255) NOT NULL COMMENT '帖子标题',
  `content` text NOT NULL COMMENT '帖子内容',
  `created_time` int(11) NOT NULL COMMENT '创建时间',
  `updated_time` int(11) NOT NULL COMMENT '最后修改时间',
  `attachment_id` int(255) NOT NULL COMMENT '附件 id ',
  `user_id` int(11) NOT NULL COMMENT '用户id 教师 管理员均可发帖',
  `role_id` int(11) NOT NULL DEFAULT '2' COMMENT '角色 id \r\n教师2 管理员1 均可发帖 回帖\r\n默认2 教师 ',
  `view_count` int(11) NOT NULL COMMENT '查看数',
  `reply_count` int(11) NOT NULL COMMENT '回复数',
  `upvote_count` int(11) NOT NULL COMMENT '点赞数',
  `status` enum('essence','stick','normal') NOT NULL DEFAULT 'normal' COMMENT '状态 正常 精华 置顶',
  `admin_reply` tinyint(2) NOT NULL COMMENT '管理人员是否回复',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tp_teacher_forum_reply
-- ----------------------------
DROP TABLE IF EXISTS `tp_teacher_forum_reply`;
CREATE TABLE `tp_teacher_forum_reply` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '回复id',
  `post_id` int(11) NOT NULL COMMENT '回复 原帖id',
  `content` text NOT NULL COMMENT '回复内容',
  `user_id` int(11) NOT NULL COMMENT '回复者id\r\n管理员或者教师\r\n通过metas识别',
  `role_id` int(11) NOT NULL COMMENT '用户角色id 管理员 教师均可回帖',
  `metas` varchar(255) DEFAULT NULL COMMENT '元信息',
  `attachment_id` int(11) DEFAULT NULL COMMENT '附件id',
  `post_time` int(11) NOT NULL,
  `quotes` int(11) DEFAULT NULL COMMENT '引用回复 id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tp_teacher_forum_upvote
-- ----------------------------
DROP TABLE IF EXISTS `tp_teacher_forum_upvote`;
CREATE TABLE `tp_teacher_forum_upvote` (
  `post_id` int(11) NOT NULL,
  `teacher_id` varchar(36) NOT NULL COMMENT '教师id',
  PRIMARY KEY (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='教师论坛点赞记录表';

-- ----------------------------
-- Table structure for tp_teacher_setting
-- ----------------------------
DROP TABLE IF EXISTS `tp_teacher_setting`;
CREATE TABLE `tp_teacher_setting` (
  `teacher_id` int(11) unsigned NOT NULL COMMENT '教师id ',
  `layout` varchar(255) NOT NULL,
  `theme` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL COMMENT '邮箱订阅与通知设置',
  `privacy` varchar(255) NOT NULL COMMENT '隐私设置',
  `inform` varchar(255) NOT NULL COMMENT '通知设置',
  UNIQUE KEY `tid` (`teacher_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tp_university
-- ----------------------------
DROP TABLE IF EXISTS `tp_university`;
CREATE TABLE `tp_university` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '学校id',
  `name` varchar(36) NOT NULL COMMENT '学校名称',
  `slug` varchar(36) NOT NULL COMMENT '英文简称',
  `description` varchar(255) NOT NULL COMMENT '学校简介',
  `banner` varchar(255) NOT NULL COMMENT '学校封面 uri',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='学校';

-- ----------------------------
-- Table structure for tp_user
-- ----------------------------
DROP TABLE IF EXISTS `tp_user`;
CREATE TABLE `tp_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(40) NOT NULL,
  `password` varchar(40) NOT NULL,
  `truename` varchar(40) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像uri',
  `sex` varchar(10) NOT NULL,
  `tel` decimal(11,0) NOT NULL,
  `email` varchar(50) NOT NULL,
  `qq` decimal(25,0) DEFAULT NULL,
  `wechat` varchar(60) DEFAULT NULL,
  `blog` varchar(255) DEFAULT NULL,
  `group_id` int(12) NOT NULL DEFAULT '9' COMMENT '用户组id',
  `last_login_time` int(11) NOT NULL COMMENT '最后一次登录时间',
  `last_login_ip` varchar(255) DEFAULT NULL COMMENT '最后登录ip',
  `access_token` varchar(255) DEFAULT NULL COMMENT '激活码',
  `access_send_time` int(11) DEFAULT NULL COMMENT '激活码最后发送时间',
  `enabled` tinyint(1) DEFAULT '0' COMMENT '账户是否激活',
  `locked` tinyint(1) DEFAULT '0' COMMENT '账户是否被管理员锁定',
  `registered` int(11) NOT NULL COMMENT '管理人员注册日期',
  `salt` varchar(255) NOT NULL DEFAULT 'foe' COMMENT '密盐加',
  `session_id` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
