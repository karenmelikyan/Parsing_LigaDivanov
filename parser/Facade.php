<?php

require_once 'lib/Parser.php';
require_once 'lib/MySqlBuilder.php';

class Facade
{
    private static $allUriArr = [];
    private static MySqlBuilder $db;

    public static function run()
    {
        set_time_limit(0);

        /** create database instance */
        self::$db = self::createDatabase();

        /** create parser instance */
        $ps = self::createParser();

        /** get main page of site */
        $html = $ps->getPageByUrl('https://ligadivanov.ru');

        /** save first links from page in array */
        self::$allUriArr = $ps->getAllLinks($html);

        /**
         * invoke recursive function
         * for traversal all pages
         */
        self::traversal(0);
    }

    /**
     * @param $uriIndex
     */
    private static function traversal($uriIndex)
    {
        $url   = '';
        $html  = '';
        $data  = [];
        $links = [];

        /** create parser instance*/
        $ps = self::createParser();

        /** create right URL via `$allUriArr`*/
        $url = $ps->getDomain() . self::$allUriArr[$uriIndex];

        if($html = $ps->getPageByUrl($url)){

            /** extract all need data from html */
            if($data = $ps->extractData($html)){

                /** save the received data to the database*/
                self::$db->insert($data);
            }

            /** get all links from current page */
            if($links = $ps->getAllLinks($html)){

                /** marge it to allUriArr */
                self::$allUriArr = array_merge(self::$allUriArr, $links);

                /** stores only unique URIs */
                self::$allUriArr = array_values(array_unique(self::$allUriArr));
            }

        }

        /** free memory */
        $ps    = null;
        $url   = null;
        $html  = null;
        $data  = null;
        $links = null;

        /**
         * if in URI array exist missed elements -
         * to do recursive invoke again.
         */
        if(count(self::$allUriArr) - 1 > $uriIndex){
            self::traversal(++ $uriIndex);
        }

    }

    /**
     * @return Parser
     */
    private static function createParser(): Parser
    {
        return (new Parser('https://ligadivanov.ru', 'catalog', [
            'primaryTitle' => ['startTag' => '<title>', 'finishTag' => '</title>'],
            'itemTitle'=> ['startTag' => '<h1 class="detail_item_title">', 'finishTag' => '</h1>'],
            'oldPrice' => ['startTag' => '<div class="detail_item_oldprice"><span>', 'finishTag' => '</span>'],
            'newPrice' => ['startTag' => '<div class="detail_item_price"><span>', 'finishTag' => '</span>'],
            'pics'     => ['startTag' => '<img src="/bitrix/images/transparent.png"  data-src="/upload/', 'finishTag' => '.jpg'],
        ]));
    }

    /**
     * @return MySqlBuilder
     */
    private static function createDatabase(): MySqlBuilder
    {
        return (new MySqlBuilder())
            ->setDbName('parser-test')
            ->setDbHost('127.0.0.1')
            ->setDbUserName('root')
            ->setDbPassword('root')
            ->setDbPort(3306)
            ->setDbTableName('products')
            ->setDbColumnProperties([
                'id' => "INT NOT NULL AUTO_INCREMENT PRIMARY KEY",
                'itemTitle' => "VARCHAR(200) NOT NULL",
                'oldPrice'  => "VARCHAR(100) NOT NULL",
                'newPrice'  => "VARCHAR(100) NOT NULL",
                'pics'      => "TEXT NOT NULL",
            ])->build();
    }

}