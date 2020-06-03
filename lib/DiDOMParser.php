<?php

//https://github.com/Imangazaliev/DiDOM

namespace lib;
use DiDom\Document;
use DiDom\Query;


class DIDOMParser implements \parser\ParserInterface
{
    private $domain = '';
    private $needLinksPrefix = '';
    private $exceptLinksPrefix = '';
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
        if($document = new Document($url, true)){
            return $document;
        }

        return null;
    }

    /**
     * @param string $html
     * @return array|null
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function getNeedLinks(string $html): ?array
    {
        $finalData = [];
        $document = new Document($html);
        if($dataArr = $document->find('a[href]::attr(href)')){
            foreach ($dataArr as $item) {
                if(substr($item, 0, strlen($this->needLinksPrefix)) == $this->needLinksPrefix &&
                    stristr($item, $this->exceptLinksPrefix) == false){
                    $finalData[] = $item;
                }
            }

            return $finalData;
        }

        return null;
    }

    /**
     * @param string $html
     * @return array|null
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function extractData(string $html): ?array
    {

        /** get main title of the page */
        $elements = (new Document($html))->find($this->needTags['primary_title'], Query::TYPE_XPATH);
        $title = '';
        foreach($elements as $element){
            $title = $element->text();
        }

        /** checking page for need data via title */
        if (stristr($title, 'руб.')) {
            $extractedData = [];

            /** get item category */
            $elements = (new Document($html))->find($this->needTags['category_parent'], Query::TYPE_XPATH);
            $extractedData['category'] = '';

            foreach ($elements as $item) {
                $document = $item->parent();
            }

            $elements = $document->find($this->needTags['category'], Query::TYPE_XPATH);
            for ($i = 2; $i < count($elements) - 1; $i ++) {
                 $extractedData['category'] .= $elements[$i]->text() . '|';
            }

            $extractedData['category'] = rtrim($extractedData['category'], '|');

            /** get item title */
            $elements = (new Document($html))->find($this->needTags['item_title'], Query::TYPE_XPATH);
            foreach ($elements as $item) {
                $extractedData['item_title'] = $item->text();
            }

            /** get sku */
            $elements = (new Document($html))->find($this->needTags['sku'], Query::TYPE_XPATH);
            foreach ($elements as $item) {
                $extractedData['sku'] = $item->text();
                break;
            }

            /** get product description */
            $elements = (new Document($html))->find($this->needTags['desc_parent'], Query::TYPE_XPATH);
            foreach($elements as $item){
                $document = $item->parent();
            }

            $extractedData['product_desc'] = null;
            $elements = $document->find($this->needTags['product_desc'], Query::TYPE_XPATH);
            foreach($elements as $item){
                $extractedData['product_desc'] = $item;
            }

            /** get item price */
            $elements = (new Document($html))->find($this->needTags['price_parent' ], Query::TYPE_XPATH);
            foreach($elements as $element){
                $document = $element->parent();
            }

            $elements = $document->find($this->needTags['price'], Query::TYPE_XPATH);
            $extractedData['regular_price'] = $elements[0]->text();

            if(isset($elements[1])){
                $extractedData['sale_price'] = $elements[1]->text();
            }else{
                $extractedData['sale_price'] = null;
            }

            /** get item attributes */
            $elements = (new Document($html))->find($this->needTags['attr_parent'], Query::TYPE_XPATH);

            foreach ($elements as $item) {
                $document = $item->parent();
            }

            $names = $document->find($this->needTags['attr_name'], Query::TYPE_XPATH);
            $values = $document->find($this->needTags['attr_value'], Query::TYPE_XPATH);

            for ($i = 0, $index = 1; $i < 30; $i ++, $index ++) {
                if (isset($names[$i]) && isset($values[$i])) {
                     $extractedData['attr_name_' . $index] = $names[$i]->text();
                     $extractedData['attr_value_' . $index] = $values[$i]->text();
                } else {
                     $extractedData['attr_name_' . $index] = null;
                     $extractedData['attr_value_' . $index] = null;
                }
            }

            /** get pics */
            $extractedData['pics'] = '';
            $picsArr = (new Document($html))->find($this->needTags['pics']);
            foreach ($picsArr as $pic) {
                if (stristr($pic, '.jpg')) {
                    $extractedData['pics'] .= $pic . '|';
                }
            }
            $extractedData['pics'] = rtrim($extractedData['pics'], '|');

            return $extractedData;
        }

        return null;
    }
}