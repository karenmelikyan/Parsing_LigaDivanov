<?php

class Facade
{
    private static $needUriArr = [];
    private static $parser;
    private static $database;

    public static function run(\parser\ParserInterface $parser, \parser\DatabaseInterface $database)
    {
        set_time_limit(0);

        /** instances init */
        self::$parser = $parser;
        self::$database = $database;

        /** get main page of site */
        $html = self::$parser->getPageByUrl(self::$parser->getDomain());

        /** save first links from page in array */
        self::$needUriArr = self::$parser->getNeedLinks($html);

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

        /** create another parser instance*/
        $parser = self::$parser;

        /** create right URL via domain name + `$needUriArr` */
        $url = $parser->getDomain() . self::$needUriArr[$uriIndex];

        /** get page from URL */
        if ($html = $parser->getPageByUrl($url)) {

            /** get all necessary links from html */
            if ($links = $parser->getNeedLinks($html)) {

                /** marge it with `needUriArr` */
                self::$needUriArr = array_merge(self::$needUriArr, $links);

                /** stores only unique URIs */
                self::$needUriArr = array_values(array_unique(self::$needUriArr));

                /** extract all need data from html */
                if ($data = $parser->extractData($html)) {

                    /** save received data to the database*/
                    self::$database->insert($data);
                }

            }

        }

        //_________________TEST_________________________________________________________________
        file_put_contents('test/links', $url . "\n", FILE_APPEND);
        file_put_contents('test/iteration', $uriIndex . "\n");
        file_put_contents('test/links_count', count(self::$needUriArr) - 1 . "\n");
        //______________________________________________________________________________________

        /** free memory */
        $url    = null;
        $html   = null;
        $data   = null;
        $links  = null;
        $parser = null;

        /**
         * if in URI array exist missed elements -
         * to do recursive invoke again.
         */
        if (count(self::$needUriArr) - 1 > $uriIndex) {
            self::traversal(++ $uriIndex);
        }

    }

}
















