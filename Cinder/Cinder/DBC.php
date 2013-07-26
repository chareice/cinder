<?php
namespace Cinder;

/**
 * PDO Singleton Class
 * 
 * @author Chareice <chareice@live.com>
 */
class DBC{
    /**
    * $_DBC : The PDO Object
    */
    private static $_DBC = NULL;

    /**
    * $_DNS : The Data Source Name
    * $_USER: The username of database
    * $_PASS: The password of the database user
    */
    private static $_DNS = NULL;
    private static $_USER = NULL;
    private static $_PASS = NULL;

    /**
    * Config the parameter of Database Connection
    */
    public static function config($dns,$user,$pass){
        self::$_DNS = $dns;
        self::$_USER = $user;
        self::$_PASS = $pass;
    }

    /**
    * Validate Database Connection Parameter 
    */
    private static function checkConfig(){
        if(isset(self::$_DNS) && isset(self::$_USER) && isset(self::$_PASS)){
            return true;
        }else{
            throw new \Exception("Please config your database first", 1);
        }
    }

    /**
    * Return The PDO Object
    */
    public static function getInstance(){
        if(self::$_DBC === NULL){
            try{
                self::checkConfig();
                self::$_DBC = new \PDO(self::$_DNS,self::$_USER,self::$_PASS);
            }catch(\PDOException $e){
                echo "Error:".$e->getMessage();
                die();
            }
        }
        return self::$_DBC;
    }

    /**
    * Bind An array to a PDO prepare statement
    *
    * @param PDOStatement $stmt The prepare statement
    *
    * @param Array $arr consist of the key => value pair
    * want bind to the statement 
    */
    public static function bindArray($stmt,$arr){
        foreach ($arr as $key => $value) {
            $stmt->bindValue($key,$value);
        }
    }
    
    private function __construct(){ 

    }
}   