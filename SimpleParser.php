<?php

require_once 'library/simple_html_dom.php';

class SimpleParser
{
     private  $domain;
     private  $needLinksPrefix;

     public function __construct($domain, $needLinksPrefix)
     {
         $this->domain = $domain;
         $this->needLinksPrefix = $needLinksPrefix;
     }

     public function run()
     {
         $html = $this->getHtml($this->domain);

         $arr = $html->find('a');

         $arr = $this->getFiltredData($arr);



         $html->clear();
         unset($html);
     }



    /**
     * @param $nextLinkIndex
     */
    private function traversal($nextLinkIndex)
    {
        $html = $this->curlRequest($this->allLinksArr[$nextLinkIndex]);

        if($linksArr = $this->getAllLinksFromPage($html)){
            $this->allLinksArr = array_merge($this->allLinksArr, $linksArr);
        }

        if(count($this->allLinksArr) - 1 > $nextLinkIndex){
            $this->traversal(++ $nextLinkIndex);
        }
    }

    /**
     * @param $dataArr
     * @return array
     *
     * filters an array by property $needLinksPrefix
     */
    private function getFiltredData($dataArr): array
    {
        $finalData = [];
        foreach ($dataArr as $item) {
            if(stristr($item, $this->needLinksPrefix)){
                $finalData[] = $item;
            }
        }

        return $finalData;
    }

    /**
     * @param $url
     * @return string
     */
     private function getHtml($url): object
     {
         $html = $this->curlRequest($url);
         return str_get_html($html);
     }

    /**
     * @param $request
     * @return bool|string
     */
    private function curlRequest($url): ?string
    {
        $curl = curl_init(); // инициализируем cURL
        /*Дальше устанавливаем опции запроса в любом порядке*/
        //Здесь устанавливаем URL к которому нужно обращаться
        curl_setopt($curl, CURLOPT_URL, $url);
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
        curl_setopt($curl, CURLOPT_REFERER, $this->getRandReferer($url));
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