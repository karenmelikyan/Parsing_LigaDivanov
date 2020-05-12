<?php   

//--------------------------------------------------------------------------------------------------
//Poisk v baze dannyx $database po zaprosu $request. Vozvrashaet massiv
// s relevantnymi dannymi. V sluchae neudachi vozvrashaet NULL.
//------------------------------------------------------------------------------------------------------
function Get_Search_Request($request, $database)
{
    $basic_index_right = array();
	$basic_index_left  = array();
	$basic_index_words = array();
	$basic_index       = array();
	
	//vsegda perevodit simvoly v verxniy registr
	//$request = strtoupper($request);
	
	//ochishaem polzovatelskiy vvod i poluchaem stroku
    $str = preg_replace('/[<>\/?%|,.~`{}!@#$%^&*()+-="]/', '', $request);
	
	//razbivaem stroku na massiv i derjim tak je i v takom vide
	$str = trim($str);
	$str_arr = preg_split("/\s+/", $str);
	
	$words_count = count($str_arr);
	if($words_count > 7)//esli v polzovztelsom zaprose mnogo slov
	{
		echo "Very long query or wrong phrase";
		return;
	}
	
	//sostavlyaem iskomuyu stroku vnov iz
	//elementov massiva daby isklyuchit 
	//lishnie probely vvodimie polzovotelem
	$str = implode(' ', $str_arr);
	
	//poluchaem dannie iz bazy i perezapisyvaem v massiv
	$data = file_get_contents($database);
	$data_arr = explode('|*|', $data);
	
	//ishem nujnuyu stroku v massive i vozvrashaem indexy sootvetstviy
	//ukorachivaya stroku s PRAVA pri kajdoy iteracii na odno slovo
	for($i = 1; $i < $words_count; $i ++)
	{   
		$indexes = preg_grep('/'. $str .'/', $data_arr);
		$basic_index_right = array_merge($basic_index_right, $indexes);
	    $str = str_replace(' ' . $str_arr[$words_count - $i], '', $str);
	}
	
	//vosstanavlivaem povrejdennuyu stroku poiska
	$str = implode(' ', $str_arr);
	
	//ishem nujnuyu stroku v massive i vozvrashaem indexy sootvetstviy
	//ukorachivaya stroku s LEVA pri kajdoy iteracii na odno slovo
	for($i = 0; $i < $words_count - 1; $i ++)
	{   
		$indexes = preg_grep('/'. $str .'/', $data_arr);
		$basic_index_left = array_merge($basic_index_left, $indexes);
	    $str = str_replace($str_arr[$i] . ' ', '', $str);
	}
	
	//ishem otdelno kajdoe slovo
	for($i = 0; $i < $words_count; $i ++)
	{   
        $indexes = preg_grep('/'. $str_arr[$i] .'/', $data_arr);
		$basic_index_words = array_merge($basic_index_words, $indexes);
	}
	
	if($basic_index_words != null)// esli naydeno xot chto to
	{
		//soedenyaem vse ne pustie massivy voedino.
		if($basic_index_right != null && $basic_index_left != null)
	        $basic_index = array_merge($basic_index_right, $basic_index_left, $basic_index_words);
		
        else if($basic_index_right != null && $basic_index_left == null)
			$basic_index = array_merge($basic_index_right, $basic_index_words);
		
        else if($basic_index_left != null && $basic_index_right == null)
			$basic_index = array_merge($basic_index_left, $basic_index_words);
		
		else if($basic_index_left == null && $basic_index_right == null)
			$basic_index = array_merge($basic_index, $basic_index_words);
		
		//udalyaem povtory iz sformirovannogo massiva i vozvrashaem gotoviy resultat
		return $basic_index = array_values(array_unique($basic_index));
	}
	
	return null;
}


//_________________________________________________________________________________________________________________
function Get_Bing_Search_Results($request)                                    //results[][0] = link of snippet
{                                                                             //results[][1] = text of snippet
	$request = str_replace(" ", "+", $request);
	
	$request = "http://www.bing.com/search?q=" . $request;
	$content = Get_Curl_Request($request);
	$snipets = Get_Between_Tags($content, '<li class="b_algo">', '</li>');
	
	for($i = 0; $i < count($snipets); $i ++)
	{
		$buff = Get_Between_Tags($snipets[$i], '<h2><a href="', '" h=');
		$results[$i][0] = strip_tags($buff[0]);
		
		$buff = Get_Between_Tags($snipets[$i], '<p>', '</p>');
		$results[$i][1] = strip_tags($buff[0]);
	}
	
	return $results;
}

//_________________________________________________________________________________________________________________
function Get_Ankors_Text($ankor_list, $ref, $text)
{
	$count = 0;
	$old   = array();
	$new   = array();
	$ankor_ends;
	
	for($i = 0; $i < count($ankor_list); $i ++)
	{
	    $ankor_ends = Get_Between_Tags($text, $ankor_list[$i], ' ');
		
	    for($j = 0; $j < count($ankor_ends); $j ++)
		{
		    $old[$count] = $ankor_list[$i] . $ankor_ends[$j];
            $new[$count] = '<a href="' . $ref . '">' . $old[$count] . '</a>';
			$count ++;
	    }
	}
		$old = GetUnduplicate($old);  
		$new = GetUnduplicate($new); 
		 
		return str_replace($old, $new, $text);
}

//______________________________________________________________________________________________________________________________
function Get_Between_Tags($text, $startTag, $finishTag)             
{                                                                      
	                                                         
	$textArr = array();
	$arr1 = explode($startTag, $text);
	
    for($i = 1; $i < count($arr1); $i ++)
	{
        $arr2 = explode($finishTag, $arr1[$i]);
	    $textArr[] = $arr2[0];
    }

    return $textArr;
}

//_________________________________________________________________________________________________________________
function Get_Between_First_Tags($text, $startTag, $finishTag)             
{                                                                                                                     
	$arr1 = explode($startTag, $text);
	
    for($i = 1; $i < count($arr1); $i ++)
    {
        $arr2 = explode($finishTag, $arr1[$i]);
	    return $arr2[0];
	}
	
}

//________________________________________________________________________________________________________________________________________________
function Remove_Directory($dir) 
{
    if ($objs = glob($dir."/*")) 
	{
        foreach($objs as $obj) 
	    {
            is_dir($obj) ? removeDirectory($obj) : unlink($obj);
	    }
       
    }
	
    rmdir($dir);
}

///////////////////////////////////////////////////////////////////////////////////////////////////
//Vozvrashaet nepovtoryayushiesya v second_array elementy iz first_array.//////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
function Get_NotRepeted_Elements($first_array, $second_array)
{
    $first_array = array_diff($first_array, $second_array);
    $arr = array();
	$index = 0;
    
    foreach($first_array as $v)
    {
	    if($v !== null)
	    {
		    $arr[$index ++] = $v;
		    $v = '';
	    }
		
    }

    return($arr);
}

//________________________________________________________________________________________________________________________________________
function ArrayFilter($unfiltreds, $exclusion)                       // Находит и удаляет  элементы из             //
{                                                                   // переданного параметром массива $unfiltreds //
                                                                    // по заданным ключевым словам $exclusion     //
                                                                    // которые находит в строке элемента массива. //
                                                                    //============================================//
    $flag = 0;
  
    for($i = 0; $i < count($unfiltreds); $i ++)
    {
	    for($j = 0; $j < count($exclusion); $j ++)
	    {
		    if(strpos($unfiltreds[$i], $exclusion[$j]) === false)
			    $flag ++;
		  
		    else break;
	    }
	  
	    if($flag == count($exclusion))
	        $filtred[] = $unfiltreds[$i];   

	    $flag = 0;
    }
 
   return $filtred;
   
}


//________________________________________________________________________________________________________________________________________
function GetAllLinks($html_text)
{
    /* Вызываем функцию, которая все совпадения помещает в массив $matches */
    preg_match_all("/<[Aa][\s]{1}[^>]*[Hh][Rr][Ee][Ff][^=]*=[ '\"\s]*([^ \"'>\s#]+)[^>]*>/", $html_text, $matches);
  
    if($matches[1] == null)
	    return null;
	  
	else // Берём то место, где сама ссылка (благодаря группирующим скобкам в регулярном выражении)
        return $matches[1]; 
}

//_____________________________________________________________________________________________________
function FileReplace($strSubjects, $strReplaces, $filePaths)
{
	for($i = 0; $i < count($filePaths); $i ++)
    {
	    $htmlText = file_get_contents($filePaths[$i]);
		    
		for($j = 0; $j < count($strSubjects); $j ++)
		{
		    $htmlText = str_replace($strSubjects[$j], $strReplaces[$j], $htmlText);
		}
		
		file_put_contents($filePaths[$i], $htmlText);
	}
}

//_____________________________________________________________________________________________________
function GetFilePaths($paths, $extension)
{
    $filePaths;

    foreach($paths as $fn)
    {
        foreach(glob($fn ."/*." . $extension) as $fn2)
        $filePaths[] = $fn2;
    }
    
    return $filePaths;
}

//________________________________________________________________________________________________________
function GetDirs($folder)
{
    static $dirPaths;
    $files = scandir($folder);
  
    foreach($files as $file)
    {
        if(($file == '.') || ($file == '..')) 
            continue;
	
        if($dirPaths == null)
            $dirPaths[0] = $folder;

        $fo = $folder. '/' .$file;           
      
        if(is_dir($fo))
        {
	        $dirPaths[] = $fo;
	        GetDirs($fo);        
        }
          
    }	

    return $dirPaths;
}

//_______________________________________________________________________________________________________________
//function scaning all $urls every $scan_interval, 
//find new links.
function Scan_For_New_Links($urls, $scan_interval)
{
	$new_url;
	$new_links; 
	$first_links;
	
    //initializing $first_links array after first input.
    for($i = 0; $i < count($urls); $i ++)
    {
        $html_text = Get_Curl_Request($urls[$i]);
		
		$new_links = GetAllLinks($html_text);
		
		//if in page don't found any links - оutput error message.
		if($new_links == null)
			file_put_contents('parsing_errors.html', "ERROR in: parsing.php -> Scan_For_New_Links, host:  " 
				. $urls[$i] . " didn't give links in: " . date("l dS of F Y h:i:s A") . "<br>", FILE_APPEND);
		
		//exclude unnecessary links.
		$new_links = ArrayFilter($new_links, array("facebook","twitter","pinterest","google","mail","instagram","youtube","banner",".jpg"));
		
		//delete repeted links from array.
		$new_links = GetUnduplicate($new_links);
		
		//write all links in first memory.
		for($j = 0; $j < count($new_links); $j ++)
			$first_links[$i][$j] = $new_links[$j];
			
		//wait for no make suspiction.	
		sleep(rand(2,5));
    }
	
	//________________________________________________________________________________________________________________________________________
	
    for(;;)
	{
	    //wait random time: +/- quarter pointed time.
	    sleep(rand($scan_interval - $scan_interval / 4, $scan_interval + $scan_interval / 4));
	
		for($i = 0; $i < count($urls); $i ++)
        {
		    //wait for no make suspiction.
			sleep(rand(2,5));;
			
			$html_text = Get_Curl_Request($urls[$i]);
			
			$new_links = GetAllLinks($html_text);
			
		    //if in page don't found any links - оutput error message.			
            if($new_links == null)
			    file_put_contents('parsing_errors.html', "ERROR in: parsing.php -> Scan_For_New_Links, host:  " 
				. $urls[$i] . " didn't give links in: " . date("l dS of F Y h:i:s A") . "<br>", FILE_APPEND);
		     
			//exclude unnecessary links.
		   	$new_links = ArrayFilter($new_links, array("facebook","twitter","pinterest","google","mail","instagram","youtube","banner",".jpg"));
			
			//delete repeted links form array.
			$new_links = GetUnduplicate($new_links);
			
    		for($j = 0; $j < count($new_links); $j ++)
			{
			    for($l = 0; $l < count($first_links[$i]); $l ++)
			    {
					//if new link = first link or new link = primary page - 
					// - break cycle and passe to next link.
			        if($new_links[$j] == $first_links[$i][$l] || $new_links[$j] == $urls[$i])
					    break;
							
					if($l == count($first_links[$i]) - 1)
				    {	
    					//write new links in buffer.
					    $first_links[$i][] = $new_links[$j];
						
						//check link for host name, and if link without there 
						//automatic add the protocol & host name.
					    if(parse_url($new_links[$j], PHP_URL_HOST) == null)
						    $new_url = parse_url($urls[$i], PHP_URL_SCHEME) . '://' . parse_url($urls[$i], PHP_URL_HOST) . $new_links[$j];
						
						//exclude other domeans except scanned.
						else if(parse_url($new_links[$j], PHP_URL_HOST) == parse_url($urls[$i], PHP_URL_HOST))
					        $new_url = $new_links[$j];
						
						//////////////////////////////////
						//Sent Url in $new_link Anywhere//
						//////////////////////////////////
					}
					
				}
				
			}
			
		}	
		
    }
	
}//function end.

//___________________________________________________________________
function Get_Curl_Request($request)
{
	
   $curl = curl_init(); // инициализируем cURL
   /*Дальше устанавливаем опции запроса в любом порядке*/
   //Здесь устанавливаем URL к которому нужно обращаться
   curl_setopt($curl, CURLOPT_URL, $request);
   //Настойка опций cookie
   curl_setopt($curl, CURLOPT_COOKIEJAR, 'cook.txt');//сохранить куки в файл
   curl_setopt($curl, CURLOPT_COOKIEFILE, 'cook.txt');//считать куки из файла
   //устанавливаем наш вариат клиента (браузера) и вид ОС
   curl_setopt($curl, CURLOPT_USERAGENT, Get_Rand_User_Agent());
   //Установите эту опцию в ненулевое значение, если вы хотите, чтобы PHP завершал работу скрыто, 
   curl_setopt($curl, CURLOPT_HEADER, 1);
   //если возвращаемый HTTP-код имеет значение выше 300. По умолчанию страница возвращается нормально с игнорированием кода.
   curl_setopt($curl, CURLOPT_FAILONERROR, 1);
   //Устанавливаем значение referer - адрес последней активной страницы
   curl_setopt($curl, CURLOPT_REFERER, Get_Rand_Referer($request));
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

/////////////////////////////////////////////////////////////////////////////////////////////////////
///FOR EXAMPLE: $request = "http://site.ru/postform.php"; $string_post_data = "name=Mike&age=25";////
/////////////////////////////////////////////////////////////////////////////////////////////////////
function Get_Curl_Post_Request($request, $string_post_data)
{
	
   $curl = curl_init(); // инициализируем cURL
   /*Дальше устанавливаем опции запроса в любом порядке*/
   //Здесь устанавливаем URL к которому нужно обращаться
   curl_setopt($curl, CURLOPT_URL, $request);
   //Настойка опций cookie
   curl_setopt($curl, CURLOPT_COOKIEJAR, 'cook.txt');//сохранить куки в файл
   curl_setopt($curl, CURLOPT_COOKIEFILE, 'cook.txt');//считать куки из файла
   //устанавливаем наш вариат клиента (браузера) и вид ОС
   curl_setopt($curl, CURLOPT_USERAGENT, Get_Rand_User_Agent());
   //Установите эту опцию в ненулевое значение, если вы хотите, чтобы PHP завершал работу скрыто, 
   curl_setopt($curl, CURLOPT_HEADER, 1);
   //если возвращаемый HTTP-код имеет значение выше 300. По умолчанию страница возвращается нормально с игнорированием кода.
   curl_setopt($curl, CURLOPT_FAILONERROR, 1);
   //Устанавливаем значение referer - адрес последней активной страницы
   curl_setopt($curl, CURLOPT_REFERER, Get_Rand_Referer($request));
   //Максимальное время в секундах, которое вы отводите для работы CURL-функций.
   curl_setopt($curl, CURLOPT_TIMEOUT, 20);
   //Внимание, важный момент, сертификатов, естественно, у нас нет, так что все отключаем
   curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);// не проверять SSL сертификат
   curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);// не проверять Host SSL сертификата
   curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);// разрешаем редиректы
   curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
   
   //seting POST request options.
   curl_setopt($curl, CURLOPT_POST, true);
   curl_setopt($curl, CURLOPT_POSTFIELDS, $string_post_data);
   
   $result = curl_exec($curl); // выполняем запрос и записываем в переменную
   curl_close($curl); // заканчиваем работу curl
 	  
   return $result; 
 
}

//=========================================================================================================================
function Get_Request_From_Tor($request)
{
   
   $fn=$_SERVER['SCRIPT_FILENAME'];
   $pn=basename($fn);
   $fn=str_replace($pn,'',$fn);//расположение файла для записи куки,находится в том же каталоге, что и скрипт

   $ch = curl_init(); 
   curl_setopt($ch, CURLOPT_URL, $request);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
   curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
   curl_setopt($ch, CURLOPT_USERAGENT, Get_Rand_User_Agent());
   curl_setopt($ch, CURLOPT_REFERER, Get_Rand_Referer($request));
   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
   curl_setopt($ch, CURLOPT_COOKIEJAR, $fn.'cookie.txt');
   curl_setopt($ch, CURLOPT_COOKIEFILE, $fn.'cookie.txt'); 
   curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:9050');//дефолтный адрес и порт TOR в Windows
   curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
   curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
   curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
   $result = curl_exec($ch);
   curl_close($ch);
   
   return $result;
}

//=========================================================================================================================
function Get_Post_Request_From_Tor($request, $string_post_data)
{
   
   $fn=$_SERVER['SCRIPT_FILENAME'];
   $pn=basename($fn);
   $fn=str_replace($pn,'',$fn);//расположение файла для записи куки,находится в том же каталоге, что и скрипт

   $ch = curl_init(); 
   curl_setopt($ch, CURLOPT_URL, $request);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
   curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
   curl_setopt($ch, CURLOPT_USERAGENT, Get_Rand_User_Agent());
   curl_setopt($ch, CURLOPT_REFERER, Get_Rand_Referer($request));
   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
   curl_setopt($ch, CURLOPT_COOKIEJAR, $fn.'cookie.txt');
   curl_setopt($ch, CURLOPT_COOKIEFILE, $fn.'cookie.txt'); 
   curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:9050');//дефолтный адрес и порт TOR в Windows
   curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
   curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
   curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
   
   //seting POST request options.
   curl_setopt($ch, CURLOPT_POST, true);
   curl_setopt($ch, CURLOPT_POSTFIELDS, $string_post_data);
   $result = curl_exec($ch);
   curl_close($ch);
   
   return $result;
}


//______________________________________________________________________________________________________________________________________________________
function Get_Rand_User_Agent()
{
	
	$user_agents = array
	(
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
    );
	
	
	return $user_agents[rand(0, count($user_agents) - 1)];
	
}

//______________________________________________________________________________________________________________________________________________________
function Get_Rand_Referer($page_url)
{
	
	$referers = array
	(
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
	);
	
	return $referers[rand(0, count($referers) - 1)];
}
						
?>