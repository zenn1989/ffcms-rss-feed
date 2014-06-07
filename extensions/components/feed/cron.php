<?php

use engine\database;
use engine\property;
use engine\system;

class cron_feed {
    protected static $instance = null;
    const UPDATE_INTERVAL = 1200; // 10 min (10 * 60)

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function make() {
        $time_diff = time() - self::UPDATE_INTERVAL;
        $stmt = database::getInstance()->con()->prepare("SELECT `id`,`url`,`update` FROM ".property::getInstance()->get('db_prefix')."_com_feed_list WHERE `update` <= ?");
        $stmt->bindParam(1, $time_diff, \PDO::PARAM_INT);
        $stmt->execute();
        $dbdata = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if(sizeof($dbdata) < 1)
            return null;
        $stmt = null;
        $id_array = system::getInstance()->extractFromMultyArray('id', $dbdata);
        $idlist = system::getInstance()->DbPrepareListdata($id_array);
        $save_data = array();
        foreach($dbdata as $feed) {
            $data = system::getInstance()->url_get_contents($feed['url']);
            if($data == null || system::getInstance()->length($data) < 1) {
                continue;
            }

            // check version of xml document (1.0 / 2.0 support)
            $xml = simplexml_load_string($data);

            if(is_object($xml->channel->item)) {
                foreach($xml->channel->item as $item) {
                    $url = (string)$item->{'link'};
                    if($url == null)
                        $url = (string)$item->{'guid'};
                    $title = (string)$item->{'title'};
                    $desc = (string)$item->{'description'};
                    $date = system::getInstance()->toUnixTime((string)$item->{'pubDate'});
                    $namespaces = $item->getNameSpaces(true);
                    $yandex = $item->children($namespaces['yandex']);
                    $fulltext = (string)$yandex->{'full-text'};
                    $save_data[] = array(
                        'url' => $url,
                        'title' => $title,
                        'desc' => $desc,
                        'fulltext' => $fulltext,
                        'date' => $date,
                        'category' => $feed['id']
                    );
                }
            } elseif(is_object($xml->entry)) {
                foreach($xml->entry as $item) {
                    $title = (string)$item->{'title'};
                    $desc = (string)$item->{'summary'};
                    $url = (string)$item->link['href'];
                    if($url == null)
                        $url = (string)$item->{'id'};
                    $date = system::getInstance()->toUnixTime((string)$item->{'published'});
                    $save_data[] = array(
                        'url' => $url,
                        'title' => $title,
                        'desc' => $desc,
                        'fulltext' => '',
                        'date' => $date,
                        'category' => $feed['id']
                    );
                }
            }
        }
        // get urls arrays, compare with available in db, remove exists & make insert query's
        if(sizeof($save_data) > 0) {
            $urls = system::getInstance()->extractFromMultyArray('url', $save_data);
            $url_list = system::getInstance()->DbPrepareListdata($urls);
            $stmt = database::getInstance()->con()->query("SELECT source_url FROM ".property::getInstance()->get('db_prefix')."_com_feed_item WHERE source_url in ({$url_list})");
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $stmt = null;
            foreach($result as $exclude_item) {
                $exist_url = $exclude_item['source_url'];
                foreach($save_data as $key=>$object) { // remove exist objects
                    if($object['url'] == $exist_url && $exist_url != null) {
                        unset($save_data[$key]);
                    }
                }
            }
            // inset value's
            foreach($save_data as $row) {
                $stmt = database::getInstance()->con()->prepare("INSERT INTO ".property::getInstance()->get('db_prefix')."_com_feed_item (`target_list`, `item_title`, `item_desc`, `source_url`, `item_date`, `fulltext`)
                        VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bindParam(1, $row['category'], \PDO::PARAM_INT);
                $stmt->bindParam(2, $row['title'], \PDO::PARAM_STR);
                $stmt->bindParam(3, $row['desc'], \PDO::PARAM_STR);
                $stmt->bindParam(4, $row['url'], \PDO::PARAM_STR);
                $stmt->bindParam(5, $row['date'], \PDO::PARAM_INT);
                $stmt->bindParam(6, $row['fulltext'], \PDO::PARAM_STR|\PDO::PARAM_NULL);
                $stmt->execute();
                $stmt = null;
            }
        }
        // refresh last check time
        $time = time();
        $stmt = database::getInstance()->con()->prepare("UPDATE ".property::getInstance()->get('db_prefix')."_com_feed_list SET `update` = ? WHERE id in({$idlist})");
        $stmt->bindParam(1, $time, \PDO::PARAM_INT);
        $stmt->execute();
        $stmt = null;
    }
}