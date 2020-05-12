<?php

if(isset($_POST['start'])){

     require_once 'SimpleParser.php';
     set_time_limit(0);

    (new SimpleParser('https://ligadivanov.ru/', 'catalog'))->run();



//    require_once 'Parser.php';
//    set_time_limit(0);
//
//
//    $arr = (new Parser('', 'catalog', [
//        'item_title' => ['startTag' => '<span itemprop="name">'],
//                        ['finishTag' => '</span>'],
//
//        'old price' =>  ['startTag' => '<div class="detail_item_oldprice"><span>'],
//                        ['finishTag' => '</span>'],
//
//        'new price' =>  ['startTag' => '<div class="detail_item_price"><span>'],
//                        ['finishTag' => '</span>'],
//
//        'pic' => ['startTag' => '<div class="detail_item_color_tooltip">'],
//                 ['finishTag' => 'alt='],
//
//    ]))->run();
//
//    //var_dump($arr);
//
//    for($i = 0; $i < count($arr); $i ++){
//        echo $i . ' = ' . $arr[$i] . '</br>';
//    }

}

?>


<html>
<head>
    <title>Parser</title>
    <!--<link href="views/css/site.css" rel="stylesheet">-->
</head>
<body >
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


