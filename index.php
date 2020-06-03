<?php

require_once 'vendor/autoload.php';
require_once 'ParserInterface.php';
require_once 'DatabaseInterface.php';
require_once 'lib/MyParser.php';
require_once 'lib/DiDOMParser.php';
require_once 'db/MySqlBuilder.php';
require_once 'Facade.php';

use lib\DIDOMParser;
use lib\MyParser;


if(isset($_POST['start'])) {

    /** create parser instance */
    $parser = new DIDOMParser('https://ligadivanov.ru', '/catalog/', '?sort',[
             'primary_title'   => "//title",
             'category_parent' => "//span[contains(@id, 'bx_breadcrumb_0')]",
             'category'        => "//span[contains(@itemprop, 'name')]",
             'item_title'      => "//h1[contains(@class, 'detail_item_title')]",
             'sku'             => "//div[contains(@class, 'detail_item_article')]",
             'desc_parent'     => "//div[contains(@class, 'text_detail text')]",
             'product_desc'    => "//div[contains(@class, 'mobile')]",
             'price_parent'    =>"//div[contains(@class, 'detail_item_price')]",
             'price'           => "//span",
             'attr_parent'    => "//table[contains(@class, 'chars')]",
             'attr_name'       => "//span[contains(@itemprop, 'name')]",
             'attr_value'      => "//span[contains(@itemprop, 'value')]",
             'pics'            => 'img[src]::attr(data-src)',
    ]);

//    $parser = new MyParser('https://ligadivanov.ru', '/catalog/', '?sort', [
//            'primary_category' => ['startTag' => 'id="bx_breadcrumb_0" itemprop="itemListElement"', 'finishTag' => '<div class="detail_item_article">Артикул:'],
//            'primary_title'    => ['startTag' => '<title>', 'finishTag' => '</title>'],
//            'category'         => ['startTag' => '<span itemprop="name">', 'finishTag' => '</span>'],
//            'item_title'       => ['startTag' => '<h1 class="detail_item_title">', 'finishTag' => '</h1>'],
//            'sku'              => ['startTag' => '<div class="item_aticle">Артикул:', 'finishTag' => '</div>'],
//            'primary_price'    => ['startTag' => '<div class="detail_item_price_block">', 'finishTag' => '<div class="detail_item_delivery_block">'],
//            'regular_price'    => ['startTag' => '<div class="detail_item_oldprice"><span>', 'finishTag' => '</span>'],
//            'sale_price'       => ['startTag' => '<div class="detail_item_price"><span>', 'finishTag' => '</span>'],
//            'product_desc'     => ['startTag' => '<div class="desktop">', 'finishTag' => '<div class="img_detail_inner">'],
//            'pics'             => ['startTag' => '<img src="/bitrix/images/transparent.png"  data-src="/upload/', 'finishTag' => '.jpg'],
//            'charact'          => ['startTag' => '<div class="chars_wrap">', 'finishTag' => '</table>'],
//            'attr_name'        => ['startTag' => '<span itemprop="name">', 'finishTag' => '</span>'],
//            'attr_value'       => ['startTag' => '<span itemprop="value">', 'finishTag' => '</span>'],
//   ]);

    /** create database instance  */
    $database = (new MySqlBuilder())
       ->setDbName('parser')
       ->setDbHost('127.0.0.1')
       ->setDbUserName('root')
       ->setDbPassword('')
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
    
    /** start parsing */
    Facade::run($parser, $database);
}

?>


<html>
<head>
    <title>Parser</title>
</head>
<body >
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<div align="center">
    <form action="/" method="post">
        <input type="hidden" name="start" value="true"/>
        <button type="submit"><h1>Start</h1></button>
    </form>
</div>
</body>
</html>


