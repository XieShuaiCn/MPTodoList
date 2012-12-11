<?php

class Valid
{
    /**
     * 检查访问是否合法
     * @param string $signature
     * @param string $timestamp
     * @param string $nonce
     * @param string $token
     * @return boolean
     */
    public static function check($signature, $timestamp, $nonce, $token)
    {
        $signatureCode = self::getSignature($timestamp, $nonce, $token);
        if ($signatureCode == $signature) {
            return true;
        } else {
            return false;
        }
    }
    
    
    /**
     * 生成验证字 (token + timestamp + nonce 排序后拼字符串，sha1生成）
     * @param string $timestamp
     * @param string $nonce
     * @param string $token
     * @return string
     */
    private static function getSignature($timestamp, $nonce, $token)
    {
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = sha1(implode("", $tmpArr));
        return $tmpStr;
    }

}
