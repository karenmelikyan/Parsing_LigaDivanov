<?php

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class GoutteParser implements \parser\ParserInterface
{
    private $domain;
    private $needLinksPrefix;
    private $exceptLinksPrefix;
    private $needTags = [];

    public function __construct($domain, $needLinksPrefix, $exceptLinksPrefix,  $needTags)
    {
        /** properties initialization */
        $this->domain = $domain;
        $this->needLinksPrefix = $needLinksPrefix;
        $this->exceptLinksPrefix = $exceptLinksPrefix;
        $this->needTags = $needTags;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @param $url
     * @return string|null
     */
    public function getPageByUrl(string $url): ?string
    {
        return (new Client())->request('GET', $url)->html();
    }

    /**
     * @param $html
     * @return array|null
     */
    public function getNeedLinks(string $html): ?array
    {





//        $finalData = [];
//        if($dataArr = $this->getAllLinksFromPage($html)){
//            foreach ($dataArr as $item) {
//                if(substr($item, 0, 9) == $this->needLinksPrefix &&
//                    stristr($item, $this->exceptLinksPrefix) == false){
//                    $finalData[] = $item;
//                }
//            }
//
//            return $finalData;
//        }
//
//        return null;


        return [];
    }

    /**
     * @param $html
     * @return array|null
     */
    public function extractData($html): ?array
    {
        return [];
    }

}