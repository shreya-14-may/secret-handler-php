
<?php
 
 class DataBaseConnector {
                  
     private static $obj;
                  
     private final function __construct() {
         echo __CLASS__ . " initialize only once ";
     }
      
     public static function getConnect() {
         if (!isset(self::$obj)) {
             self::$obj = new DataBaseConnector();
         }
          
         return self::$obj;
     }
 }
  
 $obj1 = DataBaseConnector::getConnect();
 $obj2 = DataBaseConnector::getConnect();
  
 var_dump($obj1 == $obj2);
 ?>