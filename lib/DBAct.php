<?php
define("LOCAL_DB_PATH", realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "cache") . DIRECTORY_SEPARATOR . "cache.sqlite");


class DBAct extends SQLite3
{
    /**
     * @var SQLite3
     */
    private static $obj = null;
    private $dbPath = LOCAL_DB_PATH;
    
    public function __construct()
    {
        parent::__construct($this->dbPath, SQLITE3_OPEN_READWRITE);
    }
    
    private static function instantiation()
    {
        if (self::$obj == null)
        {
            self::$obj = new DBAct();
        }
    }
    
    
    public static function lastInsertID()
    {
        self::instantiation();
        return self::$obj->lastInsertRowID();
    }
    
    
//    public static function escapeStr($str)
//    {
//        self::instantiation();
//        return self::$obj->escapeString($str);
//    }
    
    
    public static function execute($sqlStr)
    {
        self::instantiation();
        $result = self::$obj->exec($sqlStr);
        if ($result)
        {
            $sqlStr = strtoupper(trim($sqlStr));
            if (strpos($sqlStr, "INSERT") === 0)
            {
                return self::lastInsertID();
            }
        }
        return $result;
    }
    
    
    public static function getOne($sqlStr)
    {
        self::instantiation();
        return self::$obj->querySingle($sqlStr, true);
    }
    
    
    public static function getAll($sqlStr)
    {
        self::instantiation();
        $results = self::$obj->query($sqlStr);
        $retArr = array();
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $retArr[] = $row;
        }
        return $retArr;
    }
}
