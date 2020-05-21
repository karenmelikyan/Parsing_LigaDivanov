<?php

require_once 'lib/Parser.php';
require_once 'lib/MySqlBuilder.php';

class Facade
{
    private static $needUriArr = [];
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
        self::$needUriArr = $ps->getNeedLinks($html);

        /** remove duplicate items */
        self::$needUriArr = array_values(array_unique(self::$needUriArr));

        /**
         * invoke recursive function for
         * collect all the necessary links
         * from whole site, extract data &
         * save it to database
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

        /** create right URL via domain name + `$needUriArr` */
        $url = $ps->getDomain() . self::$needUriArr[$uriIndex];

        /** get page from URL */
        if($html = $ps->getPageByUrl($url)){

            /** get all navigation links from html string */
            if($links = $ps->getNeedLinks($html)){

                /** marge it with `needUriArr` */
                self::$needUriArr = array_merge(self::$needUriArr, $links);

                /** stores only unique URIs */
                self::$needUriArr = array_values(array_unique(self::$needUriArr));

                /** extract all need data from html */
                if ($data = $ps->extractData($html)) {

                    /** save the received data to the database*/
                    self::$db->insert($data);
                }

            }

        }

        //_________________TEST_________________________________________________________________
        file_put_contents('test/links',  $url . "\n", FILE_APPEND);
        file_put_contents('test/iteration',  $uriIndex . "\n");
        file_put_contents('test/links_count', count(self::$needUriArr) - 1 . "\n");
        //______________________________________________________________________________________

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
        if(count(self::$needUriArr) - 1 > $uriIndex){
            self::traversal(++ $uriIndex);
        }
    }

    /**
     * @return Parser
     */
    private static function createParser(): Parser
    {
        return (new Parser('https://ligadivanov.ru', '/catalog/', '?sort', [
            'primary_category' => ['startTag' => 'id="bx_breadcrumb_0" itemprop="itemListElement"', 'finishTag' => '<div class="detail_item_article">Артикул:'],
            'primary_title'    => ['startTag' => '<title>', 'finishTag' => '</title>'],
            'category'         => ['startTag' => '<span itemprop="name">', 'finishTag' => '</span>'],
            'item_title'       => ['startTag' => '<h1 class="detail_item_title">', 'finishTag' => '</h1>'],
            'sku'              => ['startTag' => '<div class="item_aticle">Артикул:', 'finishTag' => '</div>'],
            'primary_price'    => ['startTag' => '<div class="detail_item_price_block">', 'finishTag' => '<div class="detail_item_delivery_block">'],
            'regular_price'    => ['startTag' => '<div class="detail_item_oldprice"><span>', 'finishTag' => '</span>'],
            'sale_price'       => ['startTag' => '<div class="detail_item_price"><span>', 'finishTag' => '</span>'],
            'product_desc'     => ['startTag' => '<div class="desktop">', 'finishTag' => '<div class="img_detail_inner">'],
            'pics'             => ['startTag' => '<img src="/bitrix/images/transparent.png"  data-src="/upload/', 'finishTag' => '.jpg'],
            'charact'          => ['startTag' => '<div class="chars_wrap">', 'finishTag' => '</table>'],
            'attr_name'        => ['startTag' => '<span itemprop="name">', 'finishTag' => '</span>'],
            'attr_value'       => ['startTag' => '<span itemprop="value">', 'finishTag' => '</span>'],
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
                'id'            => "INT NOT NULL AUTO_INCREMENT PRIMARY KEY",
                'category'      => "VARCHAR(200) NULL",
                'item_title'    => "VARCHAR(200) NULL",
                'sku'           => "VARCHAR(100) NULL",
                'regular_price' => "VARCHAR(100) NULL",
                'sale_price'    => "VARCHAR(100) NULL",
                'product_desc'  => "TEXT NULL",
                'pics'          => "TEXT NULL",
                'attr_name_1'   => "VARCHAR(100) NULL",
                'attr_value_1'  => "VARCHAR(100) NULL",
                'attr_name_2'   => "VARCHAR(100) NULL",
                'attr_value_2'  => "VARCHAR(100) NULL",
                'attr_name_3'   => "VARCHAR(100) NULL",
                'attr_value_3'  => "VARCHAR(100) NULL",
                'attr_name_4'   => "VARCHAR(100) NULL",
                'attr_value_4'  => "VARCHAR(100) NULL",
                'attr_name_5'   => "VARCHAR(100) NULL",
                'attr_value_5'  => "VARCHAR(100) NULL",
                'attr_name_6'   => "VARCHAR(100) NULL",
                'attr_value_6'  => "VARCHAR(100) NULL",
                'attr_name_7'   => "VARCHAR(100) NULL",
                'attr_value_7'  => "VARCHAR(100) NULL",
                'attr_name_8'   => "VARCHAR(100) NULL",
                'attr_value_8'  => "VARCHAR(100) NULL",
                'attr_name_9'   => "VARCHAR(100) NULL",
                'attr_value_9'  => "VARCHAR(100) NULL",
                'attr_name_10'  => "VARCHAR(100) NULL",
                'attr_value_10' => "VARCHAR(100) NULL",
                'attr_name_11'  => "VARCHAR(100) NULL",
                'attr_value_11' => "VARCHAR(100) NULL",
                'attr_name_12'  => "VARCHAR(100) NULL",
                'attr_value_12' => "VARCHAR(100) NULL",
                'attr_name_13'  => "VARCHAR(100) NULL",
                'attr_value_13' => "VARCHAR(100) NULL",
                'attr_name_14'  => "VARCHAR(100) NULL",
                'attr_value_14' => "VARCHAR(100) NULL",
                'attr_name_15'  => "VARCHAR(100) NULL",
                'attr_value_15' => "VARCHAR(100) NULL",
                'attr_name_16'  => "VARCHAR(100) NULL",
                'attr_value_16' => "VARCHAR(100) NULL",
                'attr_name_17'  => "VARCHAR(100) NULL",
                'attr_value_17' => "VARCHAR(100) NULL",
                'attr_name_18'  => "VARCHAR(100) NULL",
                'attr_value_18' => "VARCHAR(100) NULL",
                'attr_name_19'  => "VARCHAR(100) NULL",
                'attr_value_19' => "VARCHAR(100) NULL",
                'attr_name_20'  => "VARCHAR(100) NULL",
                'attr_value_20' => "VARCHAR(100) NULL",
                'attr_name_21'  => "VARCHAR(100) NULL",
                'attr_value_21' => "VARCHAR(100) NULL",
                'attr_name_22'  => "VARCHAR(100) NULL",
                'attr_value_22' => "VARCHAR(100) NULL",
                'attr_name_23'  => "VARCHAR(100) NULL",
                'attr_value_23' => "VARCHAR(100) NULL",
                'attr_name_24'  => "VARCHAR(100) NULL",
                'attr_value_24' => "VARCHAR(100) NULL",
                'attr_name_25'  => "VARCHAR(100) NULL",
                'attr_value_25' => "VARCHAR(100) NULL",
                'attr_name_26'  => "VARCHAR(100) NULL",
                'attr_value_26' => "VARCHAR(100) NULL",
                'attr_name_27'  => "VARCHAR(100) NULL",
                'attr_value_27' => "VARCHAR(100) NULL",
                'attr_name_28'  => "VARCHAR(100) NULL",
                'attr_value_28' => "VARCHAR(100) NULL",
                'attr_name_29'  => "VARCHAR(100) NULL",
                'attr_value_29' => "VARCHAR(100) NULL",
                'attr_name_30'  => "VARCHAR(100) NULL",
                'attr_value_30' => "VARCHAR(100) NULL",
            ])->build();
    }

}


















