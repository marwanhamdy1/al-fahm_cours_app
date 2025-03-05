<?php

namespace App\Traits;

trait ImageUploadTrait
{
    public function saveImage($image, $folder = 'images')
    {
        // Generate a unique name for the file
        $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

        // Move the image to the specified folder within the storage directory
        $imagePath = $image->storeAs($folder, $imageName, 'public');

        // Return the path to the stored image
        return $imagePath;
    }
}