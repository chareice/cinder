<?php
namespace Cinder;

/**
 * Mapping a row of database to an object
 * 
 * 
 * 
 * @author Chareice <chareice@live.com>
 */
class ORM{
    private $_loaded = NULL;
    private $_loadField = NULL;
    private $_loadValue = NULL;

    private $_DBC = NULL;
    private $_table = NULL;
    private $_properties = array();

    private $_options = array();

    public function __construct($options){
        $this->_DBC = DBC::getInstance();
        try{
            if(!isset($options['table'])){
                throw new \Exception("construct without special a table name", 1);
            }
            $this->_table = $options['table'];
        }catch(\Exception $e){
            echo $e->getMessage();
            die();
        }
        $this->_options = $options;
        $this->_setProperties();
    }

    public function __get($name) {
        return array_key_exists($name, $this->_properties) ? $this->_properties[$name]['value'] : null;
    }

    public function __set($name, $value) {
        if (array_key_exists($name, $this->_properties) && (!$this->_many)) {
               $this->_properties[$name]['value']   = $value;
               $this->_properties[$name]['updated'] = true;
               return true;
            }
        return false;
    }

    public function load($v,$field=null){
        $this->_loaded = true;
        $this->_loadField = $field ? $field : "id";
        $this->_loadValue = $v;

        $sql = "select {$this->getFieldString()} from {$this->tableStmt()} where {$this->whereStmt()}";
        $stmt = $this->getDB()->prepare($sql);
        $stmt->bindParam($this->_loadField,$this->_loadValue);

        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        foreach($result as $k => $v){
            $this->_properties[$k]["value"] = $v;
        }
    }

    public function save(){
        /*update*/
        if($this->isLoaded()){
            $_temp = array();
            $_bindArray = array();
            $_setField = NULL;
            
            foreach ($this->_properties as $k => $v) {
                if ($this->_properties["$k"]["updated"]){
                    $_temp[$k] = $this->_properties[$k];
                    $_bindArray[$k] = $this->_properties[$k]["value"];
                }
            }

            foreach ($_temp as $k => $v) {
                $_setField .= "`{$k}` = :{$k},";
            }
            $_setField = substr($_setField, 0,-1);
            
            $stmt = $this->getDB()->prepare("update {$this->tableStmt()} set {$_setField} where {$this->whereStmt()}");
            
            $_bindArray[$this->_loadField] = $this->_loadValue;

            DBC::bindArray($stmt,$_bindArray);

            try{
                $stmt->execute();
            }catch(\PDOException $e){
                echo $e->getMessage();
                die();
            }
            
            /*return current update primary value*/
            return $this->_options['value'];
        }else{
            /*new record*/
            $_temp = array();
            $_bindArray = array();
            $_insertField = NULL;
            $_insertValue = NULL;

            foreach ($this->_properties as $k => $v) {
                if ($this->_properties["$k"]["updated"]){
                    $_temp[$k] = $this->_properties[$k];
                    $_bindArray[$k] = $this->_properties[$k]["value"];
                }
            }

            foreach ($_temp as $k => $v) {
                $_insertField .= "{$k},";
                $_insertValue .= ":{$k},";
            }
            $_insertField = "(".substr($_insertField, 0,-1).")";
            $_insertValue = "(".substr($_insertValue, 0,-1).")";

            $stmt = $this->getDB()->prepare("insert into {$this->tableStmt()} {$_insertField} values {$_insertValue}");

            DBC::bindArray($stmt,$_bindArray);

            try{
                $stmt->execute();
            }catch(\PDOException $e){
                echo $e->getMessage();
                die();
            }

            if($stmt->rowCount()){
                return $this->getDB()->lastInsertId();
            }else{
                return false;
            }
        }
    }

    public function delete(){
        if($this->isLoaded()){
            $stmt = $this->getDB()->prepare("delete from {$this->tableStmt()} where {$this->whereStmt()}");
            $stmt->bindParam($this->_loadField,$this->_loadValue);
            try{
                $stmt->execute();
            }catch(\PDOException $e){
                echo $e->getMessage();
                die();
            }
            return true;
        }
        return false;
    }

    public function getFieldString(){
        $fs = "";
        foreach ($this->_properties as $field => $value) {
            $fs .= "`{$field}`,";
        }
        return substr($fs, 0,-1);
    }

    private function _setProperties() {
        foreach ($this->_getFields() as $field){
            $this->_properties[$field['Field']] = array(
                'value' => null,
                'updated' => false
            );
        }
    }

    private function _getFields(){
        $sql = "desc ".$this->_table;
        return $this->getDB()->query($sql)->fetchAll();
    }

    private function getDB(){
        return $this->_DBC;
    }

    private function whereStmt(){
        return "`{$this->_loadField}`= :{$this->_loadField}";
    }

    private function tableStmt(){
        return "`{$this->_table}`";
    }

    public function isLoaded(){
        return $this->_loaded;
    }
}

