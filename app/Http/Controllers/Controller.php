<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function saveImagesToS3AndDatabase(array $images)
        {
            $s3 = app('s3');
            $bucket = config('services.aws.bucket');

            $imageLinks = [];

            foreach ($images as $image) {
                $path = "images/{$image->getClientOriginalName()}";

                $s3->putObject([
                    'Bucket' => $bucket,
                    'Key' => $path,
                    'Body' => file_get_contents($image->getRealPath()),
                    'ACL' => 'public-read',
                ]);

                // Get the S3 URL using the AWS SDK method
                $imageLinks[] = $s3->getObjectUrl($bucket, $path);
            }

            return json_encode($imageLinks);

            // Save image links to the database
            // Assuming you have an 'images' table with a 'url' column
            // Adjust this part based on your database structure
            // YourImageModel::insert([
            //     'url' => json_encode($imageLinks),
            //     // Add other relevant columns if needed
            // ]);
        }
}
