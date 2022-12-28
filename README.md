Usage
```php

//default parameter path => env
//default mapping path => secrets_mappings.json

// secrets_mappings.json
{
  "stage": {
    "dbRead": {
      "type": "secret",
      "key": "stage/db/read"
    },
    "logLevel": {
      "type": "parameter",
      "key": "LOG_LEVEL",
      "defaultValue": "all"
    }
  },
  "prod": {
    "dbRead": {
      "type": "secret",
      "key": "prod/db/read"
    },
    "logLevel": {
      "type": "parameter",
      "key": "LOG_LEVEL",
      "defaultValue": "all"
    }
  },
  "dev": {
    "dbRead": {
      "type": "parameter",
      "key": "dbRead",
      "isJSON": true,
      "defaultValue": {
        "host": "localhost",
        "port": "3306",
        "username": "dev",
        "password": "dev"
      }
    },
    "logLevel": {
      "type": "parameter",
      "key": "LOG_LEVEL",
      "defaultValue": "all"
    }
  }
}

//env file
LOG_LEVEL=debug


// Call
SecretHandler\SecretHandler::get(__DIR__,"logLevel")


//Examples
<?php
require_once 'vendor/autoload.php';
var_dump(SecretHandler\SecretHandler::get(__DIR__,"dbRead"));
var_dump(SecretHandler\SecretHandler::get(__DIR__,"logLevel"));

//output:
Fetching from Aws ,Secret IDstage/db/tqs/readarray(4) {
  ["host"]=>
  string(65) "localhost"
  ["port"]=>
  string(4) "3306"
  ["username"]=>
  string(4) "root"
  ["password"]=>
  string(9) "12345"
}
string(4) "debug"
```