<?php

if(isset($_POST['start'])) {
    require_once 'Facade.php';
    Facade::run();
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


