<?php
require 'vendor/autoload.php';
// SecretsManagerClient,
// GetSecretValueCommand,
use Aws\SecretsManager\SecretsManagerClient; 
use Aws\SecretsManager\GetSecretValueCommand; 
use Aws\Exception\AwsException;
class SecretHandler
{
    private static $obj;
    private static $client;
    private static $configs;
    private  $configKeys;
    public $filepath;


    function __construct() {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
        $this->filepath = "../secrets_mappings.json";
        SecretHandler::$client =  new SecretsManagerClient(["profile" => "default" ,"version" => "2017-10-17", "region" => "ap-south-1"]);
        SecretHandler::$configs  = SecretHandler::refreshConfigKeysDefault();
        $this->configKeys =  ["secrets"=> [], "env"=> []];
    }
    
    public static function getConnect() {
        if (!isset(self::$obj)) {
            self::$obj = new SecretHandler();
        }
         
        return self::$obj;
    }
    public static function secrets() {
        return SecretHandler::$configs;
    }

    public static function get($key) {
        return SecretsHandler::$configs[$key];
    }

    public function  init($initializers = [], $filepath = null) {
        $filepath = $filepath ?? $this->filepath;
        self::initConfig($filepath);
        self::refreshConfigKeys();
        // self::refreshConfigKeysDefault();
        //no initializer code
        foreach ($initializers as $func) {
            // await (func as any)();
        }
    }

    public function initConfig($filepath = null) {
        try{
            $this->filepath = $filepath ?? $this->filepath;
            $env = $_ENV["ENV"]??"dev";
            $json = file_get_contents($this->filepath);
            $aws_json_data = json_decode($json,true);

            if (empty($this->configKeys["env"]) && empty($this->configKeys["secrets"])) {
                $json = file_get_contents($this->filepath);
                $rawData = json_decode($json,true);
                // print_r($rawData[$env]);
                foreach ($rawData[$env] as $key => $value){
                    $keyConfig =  $rawData[$env][$key];
                    if($keyConfig["type"]   === "secret"){
                        $this->configKeys["secrets"][$key] = $keyConfig["key"];
                    }elseif($keyConfig["type"] === "parameter"){
                        $this->configKeys["env"][$key] =  $keyConfig;
                    }
                }

            }
            if ($_ENV["DEBUG"] === "true") {
                print_r($this->configKeys);
            }
        }catch (AwsException $e) {
            echo $e->getMessage();
            echo "\n";
        }
    }
    
    public function isJSON($string){
        return is_string($string) && is_array(json_decode($string, true)) ? true : false;
    }

    public function getValueFromEnv($keyConfig) {
        $value = (self::isJSON($keyConfig)) ? json_decode($_ENV[$keyConfig["key"]] ??"[]",true) : $_ENV[$keyConfig["key"]]?? "";
        if (empty($value)) {
          $value = $keyConfig["defaultValue"];
        }
        return $value;
    }
    public function config($filepath=null) {
        $this->filepath = $filepath ?? $this->filepath;
        self::initConfig($this->filepath);
        return $this->configKeys;
    }
    public function refreshConfigKeysDefault(){
        $config = [];
        $configLists = self::config();
        foreach ($configLists["env"] as $key => $val) {
            $keyConfig = $configLists["env"][$key];
            $config[$key]= self::getValueFromEnv($keyConfig);
        }
        foreach ($configLists["secrets"] as $key2 => $value) {
            $config[$key2]= json_decode($_ENV[$key2]??"[{}]") ?? [];
        }
        return $config;
    }
    public function refreshConfigKeys(){
         $configLists = self::config();
         foreach ($configLists["env"] as $key => $envvalue) {
            $keyconfig = $configLists["env"][$key];
            SecretHandler::$configs[$key] = self::getValueFromEnv($keyconfig);
        }
     
        foreach ($configLists["secrets"] as $secretkey => $secretvalue) {
            SecretHandler::$configs[$secretkey] = self::fetchSecretFromAWS($configLists["secrets"][$secretkey]);
        }
    }

    private  function fetchSecretFromAWS($secretId) {
        $response = SecretHandler::$client->getSecretValue([
            'SecretId' => $secretId,
        ]);
        if (isset($response['SecretString'])) {
            $secret = $response['SecretString'];
        } else {
            $secret = base64_decode($response['SecretBinary']);
        }
        return json_decode($secret,true);
    }

    public function getListSecrets()
    {
        try {
            $result = SecretHandler::$client->listSecrets([
            ]);
        }  catch (AwsException $e) {
            echo $e->getMessage();
            echo "\n";
        }
    }
    // public function getAwsSecretValue()
    // {
    //     $response = SecretHandler::$client ->getSecretValue([
    //         'SecretId' => "stage/test",
    //     ]);
    //     if (isset($response['SecretString'])) {
    //         $secret = $response['SecretString'];
    //     } else {
    //         $secret = base64_decode($response['SecretBinary']);
    //     }
    //     echo $_ENV["USER_NAME"];
        
    //     echo(json_decode($secret)->host);
    //     // var_dump($secret);
    // }

    // public function checkJsonValues(){
    //     try{
    //         // $json = file_get_contents('../secrets_mappings.json');
    //         $json = file_get_contents($this->filepath);
    //         $aws_json_data = json_decode($json,true);
    //         $env = $_ENV["ENV"];
    //         $currentRequestedAwsData = $aws_json_data[$env];
    //         foreach($currentRequestedAwsData as $key => $aws_json_data){
    //             // print_r($aws_json_data["type"]);
    //             if($aws_json_data["type"] === "secret"){
    //                 $this->configKeys["secrets"]["key"] = $aws_json_data["key"] ;    
    //             }elseif($aws_json_data["type"] === "parameter"){
    //                 $this->configKeys["env"]["key"] = $aws_json_data ;
    //             }
    //         }
    //         // print_r($this->configKeys);

    //         if ($_ENV["DEBUG"] === "true") {
    //             echo("Secret KeysMap");
    //             print_r($this->configKeys);
    //           }
    //     }catch (AwsException $e) {
    //         // output error message if fails
    //         echo $e->getMessage();
    //         echo "\n";
    //     }
    // }

    // public function staticValue() {
    //     return self::$my_static;
    // }


    // public function initConfigOld($filepath = null) {
    //     try{
    //         $this->filepath = $filepath ?? $this->filepath;
    //         $env = $_ENV["ENV"]??"dev";
    //         $json = file_get_contents($this->filepath);
    //         $aws_json_data = json_decode($json,true);
    //         $currentRequestedAwsData = $aws_json_data[$env];
    //         foreach($currentRequestedAwsData as $key => $aws_json_data){
    //             if($aws_json_data["type"] === "secret"){
    //                 $this->configKeys["secrets"]["key"][] = $aws_json_data["key"] ;  
    //             }elseif($aws_json_data["type"] === "parameter"){
    //                 $this->configKeys["env"]["key"][]= $aws_json_data["key"] ;
    //             }
    //         }
    //         if ($_ENV["DEBUG"] === "true") {
    //             print_r($this->configKeys);
    //         }
    //     }catch (AwsException $e) {
    //         echo $e->getMessage();
    //         echo "\n";
    //     }
    // }


    // public function refreshConfigKeysOLD(){
    //     $config1 = [];
    //      $configLists = self::config();

    //     foreach ($this->configKeys["env"]["key"] as $envkey => $envvalue) {
    //         $this->configKeys["env"][$envvalue]= self::getValueFromEnv($envvalue);
    //         // var_dump( $this->configKeys["env"]);
    //     }
     
    //     foreach ($this->configKeys["secrets"] as $secretkey => $secretvalue) {
    //         // $keyConfig =$this->configKeys["secrets"][$secretkey];
    //         $this->configKeys["secrets"][$secretkey] = self::fetchSecretFromAWS($secretvalue);
    //         // var_dump($this->configKeys["secrets"] );
    //     }
    //     var_dump(json_encode($this->configKeys));
    // }




}

// $obj1 = SecretHandler::getConnect(); //singleton
// var_dump($obj1); ////singleton
$obj = new SecretHandler();
$obj->init();
var_dump(json_encode($obj->secrets(),true))

?>