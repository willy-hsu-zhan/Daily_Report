<?php

return [
     "access_key_id"     => env('AWS_ACCESS_KEY_ID'),
     "secret_access_key" => env('AWS_SECRET_ACCESS_KEY'),
     "default_region"    => env('AWS_DEFAULT_REGION'),
     "bucket"            => env('AWS_BUCKET'),
     "bucket_file_path"  => env('AWS_BUCKET_FILE_PATH')
];