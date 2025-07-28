<?php

return [

 'paths' => ['api/*', 'sanctum/csrf-cookie'],

'allowed_methods' => ['*'],

'allowed_origins' => ['http://preprod.hellowap.com'],

'allowed_headers' => ['*'],

'supports_credentials' => true,


];
