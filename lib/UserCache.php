<?php
require_once("DBAct.php");

class UserCache
{
    const MAX_TODO = 9;
    
    private $tableName = "UserCache";
    private $userKey = "";
    
    public function __construct($userKey)
    {
        $this->userKey = $userKey;
    }
    
    
    private function getTodoCount($flag="1")
    {
        $sqlStr = "SELECT COUNT(1) AS CN FROM {$this->tableName} WHERE UserKey='{$this->userKey}' ";
        if (!empty($flag))
        {
            $sqlStr .= " AND TodoFlag='{$flag}' ";
        }
        $result = DBAct::getOne($sqlStr);
        return (int)$result["CN"];
    }
    
    
    public function newTodo($content)
    {
        $todoCount = $this->getTodoCount();
        if ($todoCount < self::MAX_TODO)
        {
            $content = str_replace(array("\n", "\r"), array(" ", ""), $content);
            $content = DBAct::escapeString($content);
            $nowStr = date("Y-m-d H:i:s");
            $sqlStr = "INSERT INTO {$this->tableName}(UserKey, AddTime, TodoStr, TodoFlag)
                            VALUES('{$this->userKey}', '{$nowStr}', '{$content}', '1')";
            return DBAct::execute($sqlStr);
        }
        return null;
    }
    
    
    public function getTodoList($flag="1")
    {
        $sqlStr = "SELECT * FROM {$this->tableName} WHERE UserKey='{$this->userKey}' ";
        if (!empty($flag))
        {
            $sqlStr .= " AND TodoFlag='{$flag}' ";
        }
        $sqlStr .= "ORDER BY ID ASC";
        return DBAct::getAll($sqlStr);
    }
    
    
    public function doneTodo($sIndex)
    {
        $sIndex = (int)$sIndex;
        if ($sIndex > 0 && $sIndex <= self::MAX_TODO)
        {
            $getIdSql = "SELECT ID, TodoStr FROM {$this->tableName} WHERE UserKey='{$this->userKey}' AND TodoFlag='1' ORDER BY ID ASC LIMIT " . (string)($sIndex - 1) . ", 1";
            $doneRow = DBAct::getOne($getIdSql);
            if ($doneRow)
            {
                $doneID = $doneRow["ID"];
                $nowStr = date("Y-m-d H:i:s");
                $doneSql = "UPDATE {$this->tableName} SET TodoFlag='0' , DoneTime='{$nowStr}' WHERE ID='{$doneID}'";
                if (DBAct::execute($doneSql))
                {
                    return $doneRow["TodoStr"];
                }
                return false;
            }
        }
        return null;
    }
}
