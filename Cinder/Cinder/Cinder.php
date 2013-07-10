<?php
namespace Cinder;

/**
 * Class Cinder
 * 
 * @author Chareice <chareice@live.com>
 */
class Cinder{
    private static $_options = NULL;

    private static function tableStmt(){
        return "`".self::$_options['table']."`";
    }

    private static function setOptions($options){
        self::$_options = $options;
    }

    private static function orderStmt(){
        if(isset(self::$_options['order'])){
            return " order by ".self::$_options['order'];
        }
        return "";
    }

    private static function limitStmt(){
        if(isset(self::$_options['limit'])){
            $stmt = " limit ".self::$_options['limit'];
            
            if(isset(self::$_options['offset'])){
                $stmt .= " offset ".self::$_options['offset'];
            }
            return $stmt;
        }
        return "";
    }

    private static function isLimitOne(){
         if(isset(self::$_options['limit'])){
            if(self::$_options['limit'] == 1){
                return true;
            }
         }
         return false;
    }
    
    public static function getInstance($options){
        $_isNew = isset($options['value']) ? false : true;

        $options['primary'] = isset($options['primary']) ? $options['primary'] : "id";
        $options['field'] = isset($options['field']) ? $options['field'] : $options['primary'];

        self::setOptions($options);

        $db = DBC::getInstance();

        if($_isNew){
            return new ORM($options);
        }else{
            /**
            * select row count from database
            */
            if(isset($options['like'])){
                $like = true;
                $sql = "select count(*) as count from ".self::tableStmt()." where `{$options['field']}` like :{$options['field']}";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(":".$options['field'],"%".$options['value']."%");
            }else{
                $sql = "select count(*) as count from ".self::tableStmt()." where `{$options['field']}` = :{$options['field']}";
                $stmt = $db->prepare($sql);
                $stmt->bindValue($options['field'],$options['value']);
            }

            $stmt->execute();
            $count = $stmt->fetchColumn();

            if($count > 1){
                /**
                * If row count greater than one,will return an Array consist of ORM Object
                */
                $orms = array();
                if(isset($options['like'])){
                    $sql = "select `{$options['primary']}` from ".self::tableStmt()." where `{$options['field']}` like :{$options['field']}".self::orderStmt().self::limitStmt();
                    $stmt = $db->prepare($sql);
                    $stmt->bindValue($options['field'],"%{$options['value']}%");
                }else{
                    $sql = "select `{$options['primary']}` from ".self::tableStmt()." where `{$options['field']}`= :{$options['field']}".self::orderStmt().self::limitStmt();
                    $stmt = $db->prepare($sql);
                    $stmt->bindValue($options['field'],$options['value']);
                }

                $stmt->execute();
                
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                foreach ($result as $row) {
                    $orm = new ORM($options);
                    $orm->load($row[$options['primary']],$options['primary']);
                    array_push($orms, $orm);
                }
                return $orms;
            }else if($count == 1){
                $orm = new ORM($options);
                $orm->load($options['value'],$options['field']);
                return $orm;
            }else{
                throw new \Exception("Error disappre when create Cinder", 1);
            }
        }
    }

    public static function config($dns,$user,$pass){
        DBC::config($dns,$user,$pass);
    }
    private function __construct(){

    }
}
