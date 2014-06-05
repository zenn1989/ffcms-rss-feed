<?php

use engine\system;
use engine\template;
use engine\database;
use engine\property;
use engine\admin;
use engine\language;
use engine\extension;

class components_feed_back {

    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $content = null;
        switch(system::getInstance()->get('make')) {
            case null:
                $content = $this->viewFeedCategorys();
                break;
            case 'edit':
                $content = $this->viewEditFeed();
                break;
            case 'add':
                $content = $this->viewAddFeed();
                break;
            case 'delete':
                $content = $this->viewDeleteFeed();
                break;
            case 'settings':
                $content = $this->viewSettings();
                break;
        }
        template::getInstance()->set(template::TYPE_CONTENT, 'body', $content);
    }

    private function viewSettings() {
        $params = array();
        if(system::getInstance()->post('submit')) {
            if(admin::getInstance()->saveExtensionConfigs()) {
                $params['notify']['save_success'] = true;
            }
        }
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $params['config']['item_per_page'] = extension::getInstance()->getConfig('item_per_page', 'feed', extension::TYPE_COMPONENT, 'int');

        return template::getInstance()->twigRender('components/feed/settings.tpl', $params);
    }

    private function viewDeleteFeed() {
        $id = (int)system::getInstance()->get('id');
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_feed_list WHERE id = ?");
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();

        if($stmt->rowCount() != 1)
            return null;

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt = null;

        if(system::getInstance()->post('rss_submit')) {
            $stmt = database::getInstance()->con()->prepare("DELETE FROM ".property::getInstance()->get('db_prefix')."_com_feed_list WHERE id = ?");
            $stmt->bindParam(1, $id, \PDO::PARAM_INT);
            $stmt->execute();
            system::getInstance()->redirect($_SERVER['PHP_SELF']."?object=components&action=feed");
        }

        $title = unserialize($result['title']);

        $params['rssfeed']['title'] = $title[language::getInstance()->getUseLanguage()];
        $params['rssfeed']['url'] = $result['url'];

        return template::getInstance()->twigRender('components/feed/delete.tpl', $params);
    }

    private function viewAddFeed() {
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        if(system::getInstance()->post('rss_submit')) {
            $title = system::getInstance()->nohtml(system::getInstance()->post('rss_title'));
            $desc = system::getInstance()->nohtml(system::getInstance()->post('rss_desc'));
            $url = system::getInstance()->post('rss_url');

            if(system::getInstance()->length($title[language::getInstance()->getUseLanguage()]) < 1 || system::getInstance()->length($title[language::getInstance()->getUseLanguage()]) > 150)
                $params['notify']['incorrent_title'] = true;
            if(system::getInstance()->length($url) < 1)
                $params['notify']['incorrent_url'] = true;

            if(sizeof($params['notify']) == 0) {
                $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_com_feed_list (`title`, `desc`, `url`) VALUES (?, ?, ?)");
                $stmt->bindParam(1, serialize($title), \PDO::PARAM_STR);
                $stmt->bindParam(2, serialize($desc), \PDO::PARAM_STR);
                $stmt->bindParam(3, $url, \PDO::PARAM_STR);
                $stmt->execute();
                system::getInstance()->redirect($_SERVER['PHP_SELF']."?object=components&action=feed");
            } else {
                $params['rssfeed'] = array(
                    'title' => $title,
                    'desc' => $desc,
                    'url' => $url
                );
            }
        }

        return template::getInstance()->twigRender('components/feed/edit.tpl', $params);
    }

    private function viewEditFeed() {
        $id = (int)system::getInstance()->get('id');
        $params = array();
        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $stmt = database::getInstance()->con()->prepare("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_feed_list WHERE id = ?");
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();
        if($stmt->rowCount() != 1)
            return null;

        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt = null;

        if(system::getInstance()->post('rss_submit')) {
            $title = system::getInstance()->nohtml(system::getInstance()->post('rss_title'));
            $desc = system::getInstance()->nohtml(system::getInstance()->post('rss_desc'));
            $url = system::getInstance()->post('rss_url');

            if(system::getInstance()->length($title[language::getInstance()->getUseLanguage()]) < 1 || system::getInstance()->length($title[language::getInstance()->getUseLanguage()]) > 150)
                $params['notify']['incorrent_title'] = true;
            if(system::getInstance()->length($url) < 1)
                $params['notify']['incorrent_url'] = true;

            if(sizeof($params['notify']) == 0) {
                $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_com_feed_list SET `title` = ?, `desc` = ?, `url` = ? WHERE id = ?");
                $stmt->bindParam(1, serialize($title), \PDO::PARAM_STR);
                $stmt->bindParam(2, serialize($desc), \PDO::PARAM_STR);
                $stmt->bindParam(3, $url, \PDO::PARAM_STR);
                $stmt->bindParam(4, $id, \PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                system::getInstance()->redirect($_SERVER['PHP_SELF']."?object=components&action=feed");
            }
        }

        $params['rssfeed'] = array(
            'title' => unserialize($res['title']),
            'desc' => unserialize($res['desc']),
            'url' => $res['url']
        );

        return template::getInstance()->twigRender('components/feed/edit.tpl', $params);
    }

    private function viewFeedCategorys() {
        $params = array();

        $params['extension']['title'] = admin::getInstance()->viewCurrentExtensionTitle();

        $stmt = database::getInstance()->con()->query("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_feed_list");
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach($result as $row) {
            $title = unserialize($row['title']);
            $params['rssfeed'][] = array(
                'id' => $row['id'],
                'title' => $title[language::getInstance()->getUseLanguage()],
                'url' => $row['url']
            );
        }

        return template::getInstance()->twigRender('components/feed/list.tpl', $params);
    }

    public function install() {
        $lang_ru = array(
            'ru' => array(
                'front' => array(
                    'feed_global_title' => 'Лента материалов',
                    'feed_breadcrumb_main' => 'Лента',
                    'feed_breadcrumb_category' => 'Список лент',
                    'feed_category_title' => 'Список каналов',
                    'feed_category_header' => 'Канал',
                    'feed_category_list' => 'Список лент',
                    'feed_category_allitem' => 'Все материалы'
                ),
                'back' => array(
                    'admin_components_feed.name' => 'RSS ленты',
                    'admin_components_feed.desc' => 'Реализация компонента сборщика информации из разных RSS потоков на сайт',
                    'admin_components_feed_list_title' => 'Список лент',
                    'admin_components_feed_settings' => 'Настройки',
                    'admin_components_feed_edit_title' => 'Редактирование фида',
                    'admin_components_feed_config_count_title' => 'Кол-во на страницу',
                    'admin_components_feed_config_count_desc' => 'Количество записей отображаемых на 1 странице',
                    'admin_components_feed_th_title' =>'Название',
                    'admin_components_feed_th_source' => 'Источник',
                    'admin_components_feed_th_actions' => 'Операции',
                    'admin_components_feed_button_add' => 'Добавить ленту',
                    'admin_components_feed_edit_form_title' => 'Название фида',
                    'admin_components_feed_edit_form_desc' => 'Описание',
                    'admin_components_feed_edit_form_desc_helper' => 'Краткое описание ленты, которое будет отображено на сайте в разделе данной ленты',
                    'admin_components_feed_edit_form_url' => 'RSS источник',
                    'admin_components_feed_edit_form_url_helper' => 'Ссылка на RSS ленту источника, которая будет обрабатываться сайтом',
                    'admin_components_feed_edit_button_save' => 'Сохранить',
                    'admin_components_feed_notify_length' => 'Длина заголовка некоректна',
                    'admin_components_feed_notify_source_wrong' => 'Длина ссылки на источник RSS некоректна',
                    'admin_components_feed_delete_title' => 'Удаление ленты',
                    'admin_components_feed_delete_desc' => 'Вы уверены что хотите удалить данную ленту?',
                    'admin_components_feed_delete_button' => 'Удалить'
                )
            )
        );
        $lang_en = array(
            'en' => array(
                'front' => array(
                    'feed_global_title' => 'Rss feed',
                    'feed_breadcrumb_main' => 'Feed',
                    'feed_breadcrumb_category' => 'Feed list',
                    'feed_category_title' => 'Channel list',
                    'feed_category_header' => 'Channel',
                    'feed_category_list' => 'List of feeds',
                    'feed_category_allitem' => 'All materials'
                ),
                'back' => array(
                    'admin_components_feed.name' => 'RSS feeds',
                    'admin_components_feed.desc' => 'This component allow to make your own rss catalog',
                    'admin_components_feed_list_title' => 'Feeds list',
                    'admin_components_feed_settings' => 'Settings',
                    'admin_components_feed_edit_title' => 'Edit feed',
                    'admin_components_feed_config_count_title' => 'Count per page',
                    'admin_components_feed_config_count_desc' => 'Count of items displayed on 1 page',
                    'admin_components_feed_th_title' =>'Title',
                    'admin_components_feed_th_source' => 'Source',
                    'admin_components_feed_th_actions' => 'Actions',
                    'admin_components_feed_button_add' => 'Add feed',
                    'admin_components_feed_edit_form_title' => 'Feed title',
                    'admin_components_feed_edit_form_desc' => 'Description',
                    'admin_components_feed_edit_form_desc_helper' => 'Short description of feed what be displayed on website',
                    'admin_components_feed_edit_form_url' => 'RSS source',
                    'admin_components_feed_edit_form_url_helper' => 'Link to URL of RSS feed to parse',
                    'admin_components_feed_edit_button_save' => 'Save',
                    'admin_components_feed_notify_length' => 'Title length is incorrent',
                    'admin_components_feed_notify_source_wrong' => 'Rss source URL is wrong',
                    'admin_components_feed_delete_title' => 'Delete feed',
                    'admin_components_feed_delete_desc' => 'Are you sure to delete this feed?',
                    'admin_components_feed_delete_button' => 'Delete'
                )
            )
        );
        language::getInstance()->add($lang_en);
        language::getInstance()->add($lang_ru);
        database::getInstance()->con()->exec("CREATE TABLE IF NOT EXISTS `".property::getInstance()->get('db_prefix')."_com_feed_list` (
          `id` int(12) NOT NULL AUTO_INCREMENT,
          `title` text NOT NULL,
          `desc` text NOT NULL,
          `url` varchar(512) NOT NULL,
          `update` int(16) NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
        CREATE TABLE IF NOT EXISTS `".property::getInstance()->get('db_prefix')."_com_feed_item` (
          `item_id` int(32) NOT NULL AUTO_INCREMENT,
          `target_list` int(12) NOT NULL,
          `item_title` text NOT NULL,
          `item_desc` text NOT NULL,
          `source_url` varchar(512) NOT NULL,
          `item_date` int(16) NOT NULL,
          `fulltext` text NOT NULL,
          PRIMARY KEY (`item_id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
        ");
        $cfgs = 'a:1:{s:13:"item_per_page";s:2:"10";}';
        $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_extensions SET `configs` = ? WHERE `type` = 'components' AND `dir` = 'feed'");
        $stmt->bindParam(1, $cfgs, \PDO::PARAM_STR);
        $stmt->execute();
        $stmt = null;
    }
}