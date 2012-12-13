<?php

require_once("UserCache.php");

class Reply
{
    private $myUserName = "";
    private $needTipsCount = 3;
    
    public function responseMsg($content)
    {
        $content = trim($content);
        $retText = "";
        if (!empty($content)) 
        {
            $reqObj = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
            $msgType = strtolower((string)$reqObj->MsgType);
            $this->myUserName = (string)$reqObj->ToUserName;
            $toUserName = (string)$reqObj->FromUserName;
            if ($msgType == "text")
            {
                return $this->replyTextType($reqObj, $toUserName);
            }
            else if ($msgType == "image")
            {
                return $this->replyImageType($reqObj, $toUserName);
            }
            else if ($msgType == "location")
            {
                return $this->replyLocationType($reqObj, $toUserName);
            }
            else
            {
                return $this->buildTextData($toUserName, "类型？ " . $msgType);
            }
        }
        
        return $retText;
    }
    
    
    private function replyImageType(SimpleXMLElement $reqObj, $toUserName)
    {
        $picUrl = (string)$reqObj->PicUrl;
        return $this->buildTextData($toUserName, "图片不能识别！！ {$picUrl}");
    }
    
    
    private function getListText(UserCache $userCache)
    {
        $listData = $userCache->getTodoList();
        $text = "";
        for ($i=0; $i<count($listData); $i++)
        {
            if ($text)
            {
                $text .= "\r\n";
            }
            $text .= (string)($i + 1) . ". " . $listData[$i]["TodoStr"];
        }
        return $text;
    }
    
    
    private function getHelpText()
    {
        $textArr = array();
        $textArr[] = "-: 输入普通文本, 添加新的「记事」.";
        $textArr[] = "-: 输入相应的数字, 删除对应的「记事」.";
        $textArr[] = "-: 如有任何建立, 请发语音留言, 谢谢.";
        return implode("\r\n", $textArr);
    }
    
    
    private function getDoneTips(UserCache $userCache)
    {
        $count = $userCache->getTodoCount(null);
        if ($count <= $this->needTipsCount)
        {
            return "-: 输入记事列表的序号, 删除对应的「记事」条目.";
        }
        return "";
    }
    
    
    private function replyTextType(SimpleXMLElement $reqObj, $toUserName)
    {
        $msgContent = trim(strip_tags((string)$reqObj->Content));

        $exCludeReg = "/^Hello2BizUser$/i";
        if (preg_match($exCludeReg, $msgContent))
        {
            return null;
        }

        $userCache = new UserCache($toUserName);
        $regNum = "/^\d+$/";
        $helpStrArr = array("?", "？");
        $pushStr = "";
        if (empty($msgContent) || in_array($msgContent, $helpStrArr))
        {
             $pushStr .= $this->getHelpText();
        }
        else if (preg_match($regNum, $msgContent))
        {
            $doneResult = $userCache->doneTodo($msgContent);
            if (is_string($doneResult))
            {
                $pushStr .= "已完成 『{$doneResult}』";
                $doingCount = $userCache->getTodoCount();
                if ($doingCount < 1)
                {
                    $pushStr .= "\r\n\r\n您的所有事情已完成. (*^__^*) ";
                }
            }
            else if ($doneResult === null)
            {
                $pushStr .= "您要完成的项不存在...";
            }
            else
            {
                $pushStr .= "DB[清理]数据失败???";
            }
        }
        else
        {
            $newResult = $userCache->newTodo($msgContent);
            if (is_null($newResult))
            {
                $pushStr .= "记事列表已经超过限定的「" . UserCache::MAX_TODO . "」条, 无法继续添加新数据.";
            }
            else if ($newResult)
            {
                $pushStr .= "『{$msgContent}』 已经添加进记事列表.";
                $doneTips = $this->getDoneTips($userCache);
                if ($doneTips)
                {
                    $pushStr .= "\r\n({$doneTips})";
                }
            }
            else
            {
                $pushStr .= "DB添加数据失败???";
            }
        }
        $doingListStr = $this->getListText($userCache);
        if ($doingListStr)
        {
            $pushStr .= "\r\n\r\n" . $doingListStr;
        }
        
        return $this->buildTextData($toUserName, $pushStr);
    }
    
    
    private function replyLocationType(SimpleXMLElement $reqObj, $toUserName)
    {
        $lat = (float)$reqObj->Location_X;
        $lng = (float)$reqObj->Location_Y;
        $label = (string)$reqObj->Label;
        $scale = (int)$reqObj->Scale;
        return $this->buildTextData($toUserName, "地理位置信息无法识别！！ Lat:{$lat}, Lng:{$lng} ; Label:{$label} ; Scale:{$scale}");
    }
    
    
    private function createTextNode(DOMDocument $dom, DOMNode $parentNode, $name, $content)
    {
        $theNode = $dom->createElement($name);
        $textNode = $dom->createCDATASection($content);
        $theNode->appendChild($textNode);
        $parentNode->appendChild($theNode);
    }
    
    
    private function buildImageData()
    {
//        $dom = new DOMDocument();
//        $root = $dom->createElement("xml");
//        $dom->appendChild($root);
    }
    
    
    private function buildTextData($toUserName, $content)
    {
        $dom = new DOMDocument();
        $root = $dom->createElement("xml");
        $dom->appendChild($root);
        
        $this->createTextNode($dom, $root, "ToUserName", $toUserName);
        $this->createTextNode($dom, $root, "FromUserName", $this->myUserName);
        $this->createTextNode($dom, $root, "CreateTime", time());
        $this->createTextNode($dom, $root, "MsgType", "text");
        $this->createTextNode($dom, $root, "Content", $content);
        $this->createTextNode($dom, $root, "FuncFlag", "0");
        
        return $dom->saveXML();
    }

}