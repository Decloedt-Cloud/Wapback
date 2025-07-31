<?php

return [

 'paths' => ['api/*', 'sanctum/csrf-cookie'],

'allowed_methods' => ['*'],

'allowed_origins' => ['http://preprod.hellowap.com', 'http://localhost:3000', 'http://localhost:8000', 'http://localhost:5173'],

'allowed_headers' => ['*'],

'supports_credentials' => true,


];

