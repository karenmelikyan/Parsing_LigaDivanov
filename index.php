<?php


if(isset($_POST['start'])){

    require_once 'Parser.php';
    set_time_limit(0);

    (new Parser('https://ligadivanov.ru', 'catalog', [
            'primaryTitle' => ['startTag' => '<title>', 'finishTag' => '</title>'],
            'itemTitle'=> ['startTag' => '<h1 class="detail_item_title">', 'finishTag' => '</h1>'],
            'oldPrice' => ['startTag' => '<div class="detail_item_oldprice"><span>', 'finishTag' => '</span>'],
            'newPrice' => ['startTag' => '<div class="detail_item_price"><span>', 'finishTag' => '</span>'],
            'pics'     => ['startTag' => '<img src="/bitrix/images/transparent.png"  data-src="/upload/', 'finishTag' => '.jpg'],
    ]))->run();

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


