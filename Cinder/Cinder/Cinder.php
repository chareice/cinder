<?php
namespace Cinder;

/**
 * Class Cinder
 * 
 * @author Chareice <chareice@live.com>
 */
class Cinder{
    public static function getInstance($options){
        $_isNew = isset($options['value']) ? false : true;

        $options['primary'] = isset($options['primary']) ? $options['primary'] : "id";
        $options['field'] = isset($options['field']) ? $options['field'] : $options['primary'];

        $db = DBC::getInstance();
        if($_isNew){
            return new ORM($options);
        }else{
            if(isset($options['like'])){
                $like = true;
                $stmt = $db->prepare("select count(*) as count from `{$options['table']}` where `{$options['field']}` like :{$options['field']}");
                $stmt->bindValue(":".$options['field'],"%".$options['value']."%");
            }else{
                $stmt = $db->prepare("select count(*) as count from `{$options['table']}` where `{$options['field']}` = :{$options['field']}");
                $stmt->bindValue($options['field'],$options['value']);
            }

            $stmt->execute();
            $count = $stmt->fetchColumn();

            if($count > 1){
                $orms = array();
                if(isset($options['like'])){
                    $stmt = $db->prepare("select `{$options['primary']}` from `{$options['table']}` where `{$options['field']}` like :{$options['field']}");
                    $stmt->bindValue($options['field'],"%{$options['value']}%");
                }else{
                    $stmt = $db->prepare("select `{$options['primary']}` from `{$options['table']}` where `{$options['field']}`= :{$options['field']}");
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
