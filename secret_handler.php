<?php
require 'vendor/autoload.php';
use Aws\Exception\AwsException;
use Aws\SecretsManager\SecretsManagerClient;

class SecretHandler
{
    private static $client;
    private static $configs = null;
    private static $configKeys = ["secrets" => [], "env" => []];
    public static $filepath = "./secrets_mappings.json";

    public static function secrets()
    {
        SecretHandler::init();
        return SecretHandler::$configs;
    }

    private static function init()
    {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
        if (is_null(SecretHandler::$configs)) {
            SecretHandler::$client = new SecretsManagerClient(["profile" => "default", "version" => "2017-10-17", "region" => "ap-south-1"]);
            SecretHandler::$configKeys = ["secrets" => [], "env" => []];
            SecretHandler::initConfig();
            SecretHandler::refreshConfigKeys();
        }
    }

    private static function initConfig()
    {
        try {
            $env = $_ENV["ENV"] ?? "dev";
            $json = file_get_contents(SecretHandler::$filepath);
            $aws_json_data = json_decode($json, true);

            if (empty(SecretHandler::$configKeys["env"]) && empty(SecretHandler::$configKeys["secrets"])) {
                $json = file_get_contents(SecretHandler::$filepath);
                $rawData = json_decode($json, true);
                foreach ($rawData[$env] as $key => $value) {
                    $keyConfig = $rawData[$env][$key];
                    if ($keyConfig["type"] === "secret") {
                        SecretHandler::$configKeys["secrets"][$key] = $keyConfig["key"];
                    } elseif ($keyConfig["type"] === "parameter") {
                        SecretHandler::$configKeys["env"][$key] = $keyConfig;
                    }
                }

            }
        } catch (AwsException $e) {
            echo $e->getMessage();
            echo "\n";
        }
    }

    private static function isJSON($string)
    {
        return is_string($string) && is_array(json_decode($string, true)) ? true : false;
    }

    private static function getValueFromEnv($keyConfig)
    {
        $value = (self::isJSON($keyConfig)) ? json_decode($_ENV[$keyConfig["key"]] ?? "[]", true) : $_ENV[$keyConfig["key"]] ?? "";
        if (empty($value)) {
            $value = $keyConfig["defaultValue"];
        }
        return $value;
    }

    private static function refreshConfigKeys()
    {
        $configLists = SecretHandler::$configKeys;
        foreach ($configLists["env"] as $key => $envvalue) {
            $keyconfig = $configLists["env"][$key];
            SecretHandler::$configs[$key] = SecretHandler::getValueFromEnv($keyconfig);
        }
        foreach ($configLists["secrets"] as $secretkey => $secretvalue) {
            SecretHandler::$configs[$secretkey] = SecretHandler::fetchSecretFromAWS($configLists["secrets"][$secretkey]);
        }
    }

    private static function fetchSecretFromAWS($secretId)
    {
        echo "Fetching from Aws ,Secret ID" . $secretId;
        $response = SecretHandler::$client->getSecretValue([
            'SecretId' => $secretId,
        ]);
        if (isset($response['SecretString'])) {
            $secret = $response['SecretString'];
        } else {
            $secret = base64_decode($response['SecretBinary']);
        }
        return json_decode($secret, true);
    }
}
