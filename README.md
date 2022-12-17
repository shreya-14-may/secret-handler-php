# secret-handler-php
AWS Secret Handler for PHP Applications


// Load the module
// php

///php env initialized in constructor
   $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
   $dotenv->load();

// packages used
use Aws\SecretsManager\SecretsManagerClient; 
use Aws\SecretsManager\GetSecretValueCommand; 
use Aws\Exception\AwsException;

// init the secrets
$obj = new SecretHandler();
$obj->init();
var_dump(json_encode($obj->secrets(),true))

// initFunctions = list of init functions to be reloaded after secrets are initialized
// mapping_filepath = mappings.json file

*sample mappings of key secrets file => default filepath "secrets_mappings.json"
