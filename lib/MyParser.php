<?php

class MyParser implements \parser\ParserInterface
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
        if($html = $this->curlRequest($url)){
            return $html;
        }

        return null;
    }

    /**
     * @param $html
     * @return array|null
     */
    public function getNeedLinks(string $html): ?array
    {
        $finalData = [];
        if($dataArr = $this->getAllLinksFromPage($html)){
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
     * @param $html
     * @return array|null
     */
    public function extractData(string $html): ?array
    {
        $extractedData = [];
        $data = '';
        $char = '';

        /**  checking: is it the necessary item page? */
        if($data = $this->getBetweenFirstTags($html, $this->needTags['primary_title']['startTag'], $this->needTags['primary_title']['finishTag'])){
            if(stristr($data, 'руб.')){

                /** getting category of item */
                $extractedData['category'] = '';
                $char = $this->getBetweenFirstTags($html, $this->needTags['primary_category']['startTag'], $this->needTags['primary_category']['finishTag']);
                if($char){
                    $categoryArr = $this->getBetweenAllTags($char, $this->needTags['category']['startTag'], $this->needTags['category']['finishTag']);
                    $data = '';
                    foreach( $categoryArr as $item){
                        if($item != 'Каталог' && $item != 'Главная'){
                            $data .=  $item . '|';
                            $extractedData['category'] = rtrim($data, '|');
                        }
                    }

                    $char = null;
                    $data = null;
                }

                /**  getting item title  */
                $extractedData['item_title'] = '';
                $data = $this->getBetweenFirstTags($html, $this->needTags['item_title']['startTag'], $this->needTags['item_title']['finishTag']);
                if($data){
                    $extractedData['item_title'] = $data;
                    $data = null;
                }else {
                    $extractedData['item_title'] = null;
                }

                /**  getting item sku */
                $extractedData['sku'] = '';
                $data = $this->getBetweenFirstTags($html, $this->needTags['sku']['startTag'], $this->needTags['sku']['finishTag']);
                if($data){
                    $extractedData['sku'] = $data;
                    $data = null;
                }else {
                    $extractedData['sku'] = null;
                }


                /**  getting regular price & sale price */
                $char = $this->getBetweenFirstTags($html, $this->needTags['primary_price']['startTag'], $this->needTags['primary_price']['finishTag']);
                if(stristr($char, 'detail_item_oldprice')){
                    $data = $this->getBetweenFirstTags($char, $this->needTags['regular_price']['startTag'], $this->needTags['regular_price']['finishTag']);
                    if($data){
                        $extractedData['regular_price'] = $data;
                        $data = null;
                    }
                    $data = $this->getBetweenFirstTags($char, $this->needTags['sale_price']['startTag'], $this->needTags['sale_price']['finishTag']);
                    if($data){
                        $extractedData['sale_price'] = $data;
                        $data = null;
                    }

                }else{
                    $data = $this->getBetweenFirstTags($char, $this->needTags['sale_price']['startTag'], $this->needTags['sale_price']['finishTag']);
                    if($data){
                        $extractedData['regular_price'] = $data;
                        $extractedData['sale_price'] = null;
                        $data = null;
                    }
                }

                /** getting all description of item */
                $extractedData['product_desc'] = '';
                $data = $this->getBetweenAllTags($html, $this->needTags['product_desc']['startTag'], $this->needTags['product_desc']['finishTag']);
                if($data) {
                    foreach($data as $value) {
                        $extractedData['product_desc'] .= $value;
                    }

                    $data = null;
                }else{
                    $extractedData['product_desc'] = null;
                }

                /** getting all pictures of item*/
                $extractedData['pics'] = '';
                $data = $this->getBetweenAllTags($html, $this->needTags['pics']['startTag'], $this->needTags['pics']['finishTag']);
                if($data) {
                    foreach($data as $value) {
                        $extractedData['pics'] .= $this->domain . '/upload/' . $value . '.jpg|';
                    }

                    $data = null;
                }else{
                    $extractedData['pics'] = null;
                }

                /** getting attributes of item */
                $char = $this->getBetweenFirstTags($html, $this->needTags['charact']['startTag'], $this->needTags['charact']['finishTag']);
                if($char){
                    $attributeKeyArr = $this->getBetweenAllTags($char, $this->needTags['attr_name']['startTag'], $this->needTags['attr_name']['finishTag']);
                    $attributeValArr = $this->getBetweenAllTags($char, $this->needTags['attr_value']['startTag'], $this->needTags['attr_value']['finishTag']);

                    for($i = 0, $index = 1; $i < 30; $i ++, $index ++){
                        if(isset($attributeKeyArr[$i]) && isset($attributeValArr[$i])){
                            $extractedData['attr_name_' . $index] = $attributeKeyArr[$i];
                            $extractedData['attr_value_' . $index] = $attributeValArr[$i];
                        }else{
                            $extractedData['attr_name_' . $index] = null;
                            $extractedData['attr_value_' . $index] = null;
                        }
                    }
                }

                return $extractedData;
            }
        }

        return null;
    }

    /**
     * @param $html_text
     * @return mixed|null
     */
    private function getAllLinksFromPage($html): ?array
    {
        /* Вызываем функцию, которая все совпадения помещает в массив $matches */
        preg_match_all("/<[Aa][\s]{1}[^>]*[Hh][Rr][Ee][Ff][^=]*=[ '\"\s]*([^ \"'>\s#]+)[^>]*>/", $html, $matches);

        if($matches[1] == null)
            return null;

        else // Берём то место, где сама ссылка (благодаря группирующим скобкам в регулярном выражении)
            return $matches[1];
    }

    /**
     * @param $text
     * @param $startTag
     * @param $finishTag
     * @return array
     */
    private function getBetweenAllTags($text, $startTag, $finishTag): ?array
    {
        $textArr = [];
        $arr1 = explode($startTag, $text);

        for($i = 1; $i < count($arr1); $i ++) {
            $arr2 = explode($finishTag, $arr1[$i]);
            $textArr[] = $arr2[0];
        }

        if($textArr){
            return $textArr;
        }

        return null;
    }

    /**
     * @param $text
     * @param $startTag
     * @param $finishTag
     * @return array|null
     */
    private function getBetweenFirstTags($text, $startTag, $finishTag): ?string
    {
        $arr1 = explode($startTag, $text);

        for($i = 1; $i < count($arr1); $i ++) {
            $arr2 = explode($finishTag, $arr1[$i]);
            return $arr2[0];
        }

        return null;
    }

    /**
     * @param $request
     * @return bool|string
     */
    private function curlRequest($request): ?string
    {
        $curl = curl_init(); // инициализируем cURL
        /*Дальше устанавливаем опции запроса в любом порядке*/
        //Здесь устанавливаем URL к которому нужно обращаться
        curl_setopt($curl, CURLOPT_URL, $request);
        //Настойка опций cookie
        curl_setopt($curl, CURLOPT_COOKIEJAR, 'cook.txt');//сохранить куки в файл
        curl_setopt($curl, CURLOPT_COOKIEFILE, 'cook.txt');//считать куки из файла
        //устанавливаем наш вариат клиента (браузера) и вид ОС
        curl_setopt($curl, CURLOPT_USERAGENT, $this->getRandUserAgent());
        //Установите эту опцию в ненулевое значение, если вы хотите, чтобы PHP завершал работу скрыто,
        curl_setopt($curl, CURLOPT_HEADER, 1);
        //если возвращаемый HTTP-код имеет значение выше 300. По умолчанию страница возвращается нормально с игнорированием кода.
        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        //Устанавливаем значение referer - адрес последней активной страницы
        curl_setopt($curl, CURLOPT_REFERER, $this->getRandReferer($request));
        //Максимальное время в секундах, которое вы отводите для работы CURL-функций.
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        //Внимание, важный момент, сертификатов, естественно, у нас нет, так что все отключаем
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);// не проверять SSL сертификат
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);// не проверять Host SSL сертификата
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);// разрешаем редиректы
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl); // выполняем запрос и записываем в переменную
        curl_close($curl); // заканчиваем работу curl

        return $result;
    }

    /**
     * @return mixed
     */
    private function getRandUserAgent()
    {
        $user_agents = [
            "Mozilla/5.0 (X11; Linux i686; rv:7.0.1) Gecko/20100101 Firefox/7.0.1",
            "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.109 Safari/537.36",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.0.1) Gecko/2008070208",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20041107 Firefox/1.0",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.0.1) Gecko/2008070208",
            "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.47 Safari/535.11 MRCHROME",
            "Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.1.963.51 Safari/535.11 MRCHROME SOC",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/530.5  (KHTML,like Gecko) Chrome/2.0.173.1 Safari/530.5",
            "Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/532.0  (KHTML,like Gecko) Chrome/3.0.195.27 Safari/532.0",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.14 (KHTML,like Gecko) Chrome/9.0.600.0 Safari/534.14",
            "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/534.20 (KHTML,like Gecko) Chrome/11.0.672.2 Safari/534.20",
            "Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/534.4  (KHTML,like Gecko) Chrome/6.0.481.0 Safari/534.4",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.19 (KHTML,like Gecko) Chrome/1.0.154.36 Safari/525.19",
            "Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/534.10 (KHTML,like Gecko) Chrome/8.0.558.0 Safari/534.10",
            "Mozilla/5.0 (Windows; U; Windows NT 5.0; es-ES; rv:1.8.0.3) Gecko/20060426 Firefox/1.5.0.3",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; cs; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8",
            "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.792.0 Safari/535.1",
            "Opera/9.80  (Windows NT 5.1; U; cs) Presto/2.2.15 Version/10.00",
            "Opera/9.80  (Windows NT 5.1) Presto/2.12.388 Version/12.14",
            "Opera/10.00 (Windows NT 5.1; U; ru) Presto/2.2.0"
        ];

        return $user_agents[rand(0, count($user_agents) - 1)];
    }

    /**
     * @param $page_url
     * @return mixed
     */
    private function getRandReferer($page_url)
    {
        $referers = [
            "https://www.yahoo.com/",
            "https://www.rambler.ru/",
            "https://www.yandex.ru/",
            "http://www.msn.com/ru-ru/",
            "https://www.google.com/",
            "https://duckduckgo.com/",
            "https://www.google.ru/",
            "http://www.entireweb.com/",
            "https://www.wikipedia.org/",
            "https://www.search.com/",
            parse_url($page_url, PHP_URL_SCHEME) . '://' . parse_url($page_url, PHP_URL_HOST)
        ];

        return $referers[rand(0, count($referers) - 1)];
    }

}