<?php

namespace App\Http\Controllers\API;

use Aws\S3\S3Client;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Traits\SanitizeTrait;

class ImageController extends Controller {

    use SanitizeTrait;

    public function show($type,$name){

        Cache::flush();
        
        $type = $this->sanitizeField($type);
        $name = $this->sanitizeField($name);
        
        // $image = Cache::remember("image_{$type}_{$name}", 86400, function () use($type,$name) {
            
            try {
                $aws_config = (object)config('aws');

                $s3 = new S3Client([
                    'version' => $aws_config->version,
                    'region'  => $aws_config->region,
                    'credentials' => $aws_config->credentials
                ]);	
                    
                // Get the object.
                $result = $s3->getObject([
                    'Bucket' => $aws_config->bucket,
                    'Key'    => "$type/$name"
                ]);
    
                $image = $result['Body'];
            } catch(Exception $e){
                $image = Storage::get('public/images/no_image.png');
            }

        //     return $image;

        // });


        return response($image, 200)->header('Content-Type', 'image/gif');

    }

    private function image_name($id, $size,$type){
        return "$type/{$id}_{$size}.jpg";
    }
}
