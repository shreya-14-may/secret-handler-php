# secret-handler-php
AWS Secret Handler for PHP Applications


#Load the php class

#php env initialized in init
   $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
   $dotenv->load();

#packages used
   use Aws\SecretsManager\SecretsManagerClient; 
   use Aws\SecretsManager\GetSecretValueCommand; 
   use Aws\Exception\AwsException;

#init the secrets
   $secrets = SecretHandler::secrets();
   var_dump(json_encode($secrets,true));


*sample mappings of key secrets file => default filepath "secrets_mappings.json"
