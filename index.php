<?php

define("TOKEN", "1935595093");

require_once("lib" . DIRECTORY_SEPARATOR . "Reply.php");
require_once("lib" . DIRECTORY_SEPARATOR . "Valid.php");

if ($_GET)
{
    if (isset($_GET["signature"]) && isset($_GET["timestamp"]) && isset($_GET["nonce"]) && isset($_GET["echostr"]))
    {
        $validResult = Valid::check($_GET["signature"], $_GET["timestamp"], $_GET["nonce"], TOKEN);
        if ($validResult)
        {
            echo $_GET["echostr"];
        }
    }
}

$postData = null;
if (isset($GLOBALS["HTTP_RAW_POST_DATA"]))
{
    $postData = $GLOBALS["HTTP_RAW_POST_DATA"];
}
else if (isset($_GET["trying"]))
{
    $postData = "
    <xml>
        <ToUserName><![CDATA[toUser]]></ToUserName>
        <FromUserName><![CDATA[fromUser]]></FromUserName>
        <CreateTime>1348831860</CreateTime>
        <MsgType><![CDATA[text]]></MsgType>
        <Content></Content>
    </xml>";
}


// Robot
if ($postData)
{
    header("Content-Type: text/xml");
    $reply = new Reply();
    $retConnect = $reply->responseMsg($postData);
    echo $retConnect;
}