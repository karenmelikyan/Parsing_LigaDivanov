<?php
if(isset($_POST['start'])){

    require_once 'Parser.php';
    set_time_limit(0);




    $arr = (new Parser('https://ligadivanov.ru/', 'catalog' ))->run();


    foreach ($arr as $elem){
        echo $elem . '</br>';
    }

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


