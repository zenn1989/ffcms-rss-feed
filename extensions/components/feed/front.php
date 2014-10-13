<?php

use engine\router;
use engine\template;
use engine\database;
use engine\property;
use engine\system;
use engine\language;
use engine\extension;
use engine\meta;

class components_feed_front {

    protected static $instance = null;
    const ITEM_PER_PAGE = 10;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $way = router::getInstance()->shiftUriArray();
        $content = null;
        switch($way[0]) {
            case null:
            case 'list':
                $content = $this->viewFeedMain();
                break;
            case 'item':
                $content = $this->viewItem($way[1]);
                break;
            case 'category':
                $content = $this->viewCategorySelect($way[1]);
                break;
        }
        template::getInstance()->set(template::TYPE_CONTENT, 'body', $content);
    }

    private function viewCategorySelect($id) {
        if($id == null)
            return $this->viewCategoryMain();
        else
            return $this->viewCategory($id);
    }

    private function viewCategoryMain() {
        $params = array();

        $stmt = database::getInstance()->con()->query("SELECT * FROM ".property::getInstance()->get('db_prefix')."_com_feed_list");
        if($stmt->rowCount() < 1)
            return null;

        while($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $title = unserialize($result['title']);
            $desc = unserialize($result['desc']);
            $params['rsscat'][] = array(
                'id' => $result['id'],
                'title' => $title[language::getInstance()->getUseLanguage()],
                'desc' => $desc[language::getInstance()->getUseLanguage()],
            );
        }

        meta::getInstance()->add('title', language::getInstance()->get('feed_category_title'));

        return template::getInstance()->twigRender('components/feed/list.tpl', $params);
    }

    private function viewCategory($id) {
        $params = array();

        $way = router::getInstance()->shiftUriArray();

        $item_per_page = extension::getInstance()->getConfig('item_per_page', 'feed', extension::TYPE_COMPONENT, 'int');
        if($item_per_page < 1)
            $item_per_page = 1;

        $index = (int)$way[2];
        $db_index = $index * $item_per_page;

        $stmt = database::getInstance()->con()->prepare("SELECT a.item_title,a.item_id,a.target_list,a.item_date,b.title,b.desc FROM ".property::getInstance()->get('db_prefix')."_com_feed_item a,
                ".property::getInstance()->get('db_prefix')."_com_feed_list b WHERE b.id = a.target_list AND a.target_list = ? ORDER BY a.item_date DESC LIMIT ?,?");
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->bindParam(2, $db_index, \PDO::PARAM_INT);
        $stmt->bindParam(3, $item_per_page, \PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt = null;

        foreach($result as $row) {
            $cat_title = unserialize($row['title']);
            $params['rssfeed'][] = array(
                'title' => system::getInstance()->nohtml($row['item_title']),
                'id' => $row['item_id'],
                'cat_title' => $cat_title[language::getInstance()->getUseLanguage()],
                'cat_id' => $row['target_list'],
                'cat_desc' => $row['desc'],
                'date' => system::getInstance()->toDate($row['item_date'], 'h')
            );
            $cat_desc = unserialize($row['desc']);
            $params['rsscat']['id'] = (int)$id;
            $params['rsscat']['title'] = $cat_title[language::getInstance()->getUseLanguage()];
            $params['rsscat']['desc'] = $cat_desc[language::getInstance()->getUseLanguage()];
        }

        // get total count of item in category for pagination
        $stmt = database::getInstance()->con()->prepare("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_feed_item WHERE target_list = ?");
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch();
        $stmt = null;
        $total_count = $res[0];

        meta::getInstance()->add('title', $params['rsscat']['title']);
        meta::getInstance()->add('description', $params['rsscat']['desc']);

        $params['pagination'] = template::getInstance()->showFastPagination($index, $item_per_page, $total_count, 'feed/category/'.$id);

        return template::getInstance()->twigRender('components/feed/category.tpl', $params);


    }

    private function viewItem($item_id) {
        $item_id = system::getInstance()->noextention($item_id);
        $params = array();
        if(!system::getInstance()->isInt($item_id) || $item_id < 1)
            return null;
        $stmt = database::getInstance()->con()->prepare("SELECT a.item_title,a.target_list,a.item_desc,a.item_date,a.source_url,a.fulltext,b.id,b.title FROM
                ".property::getInstance()->get('db_prefix')."_com_feed_item a, ".property::getInstance()->get('db_prefix')."_com_feed_list b
                WHERE a.target_list = b.id AND a.item_id = ?");
        $stmt->bindParam(1, $item_id, \PDO::PARAM_INT);
        $stmt->execute();
        if($stmt->rowCount() != 1)
            return null;
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt = null;
        $channel_title = unserialize($result['title']);
        $item_image = null;

        if(file_exists(root . '/upload/feed/poster_' . $item_id . '.jpg'))
            $item_image = 'upload/feed/poster_' . $item_id . '.jpg';

        $params['rssfeed'] = array(
            'item_id' => $item_id,
            'item_title' => $result['item_title'],
            'item_desc' => system::getInstance()->nohtml(html_entity_decode($result['item_desc'], ENT_NOQUOTES, 'UTF-8')),
            'item_fulltext' => system::getInstance()->nohtml(html_entity_decode($result['fulltext'], ENT_NOQUOTES, 'UTF-8')),
            'item_date' => system::getInstance()->toDate($result['item_date'], 'h'),
            'item_image' => $item_image,
            'source_url' => $result['source_url'],
            'channel_title' => $channel_title[language::getInstance()->getUseLanguage()],
            'channel_id' => $result['target_list']
        );
        meta::getInstance()->add('title', $params['rssfeed']['item_title']);
        meta::getInstance()->add('description', system::getInstance()->sentenceSub($params['rssfeed']['item_desc'], 250));
        return template::getInstance()->twigRender('components/feed/item.tpl', $params);
    }

    private function viewFeedMain() {
        $params = array();

        $way = router::getInstance()->shiftUriArray();

        meta::getInstance()->add('title', language::getInstance()->get('feed_global_title'));

        $item_per_page = extension::getInstance()->getConfig('item_per_page', 'feed', extension::TYPE_COMPONENT, 'int');
        if($item_per_page < 1)
            $item_per_page = 1;

        $index = (int)$way[1];
        $db_index = $index * $item_per_page;

        $stmt = database::getInstance()->con()->prepare("SELECT a.item_title,a.item_id,a.target_list,a.item_date,b.title FROM ".property::getInstance()->get('db_prefix')."_com_feed_item a,
                ".property::getInstance()->get('db_prefix')."_com_feed_list b WHERE b.id = a.target_list ORDER BY a.item_date DESC LIMIT ?,?");
        $stmt->bindParam(1, $db_index, \PDO::PARAM_INT);
        $stmt->bindParam(2, $item_per_page, \PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt = null;
        foreach($result as $row) {
            $cat_title = unserialize($row['title']);
            $params['rssfeed'][] = array(
                'title' => system::getInstance()->nohtml($row['item_title']),
                'id' => $row['item_id'],
                'cat_title' => $cat_title[language::getInstance()->getUseLanguage()],
                'cat_id' => $row['target_list'],
                'date' => system::getInstance()->toDate($row['item_date'], 'h')
            );
        }

        // get total count for pagination
        $stmt = database::getInstance()->con()->query("SELECT COUNT(*) FROM ".property::getInstance()->get('db_prefix')."_com_feed_item");
        $res = $stmt->fetch();
        $stmt = null;
        $total_count = $res[0];

        $params['pagination'] = template::getInstance()->showFastPagination($index, $item_per_page, $total_count, 'feed/list');

        return template::getInstance()->twigRender('components/feed/stream.tpl', $params);
    }

}