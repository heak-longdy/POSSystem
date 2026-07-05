<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class UploadFile extends Model
{
    public static function saveFile($destination, $image, $temperature = "")
    {
        if ($image != null) {
            $logoPath = public_path('uploads') . $destination;
            $logoName = $image->getClientOriginalName();
            $unique_logoName = time() . "-" . $logoName;
            if ($image->move($logoPath, $unique_logoName)) {
                $logo_url = Request::root() . '/public/uploads' . $destination . '/' . $unique_logoName;
            }
            if ($temperature != '') {
                $image_path = public_path() . '/uploads' . $destination . '/' . $temperature;
                if (File::exists($image_path)) {
                    File::delete($image_path);
                }
            }
        } else {
            $unique_logoName = $temperature;
        }

        return collect(['uniqueName' => $unique_logoName, 'originalName' => $logoName]);
    }

    public static function saveFile2($destination, $image, $temperature = "")
    {
        $logoName = null;
        $unique_logoName = null;

        if ($image != null) {
            $logoPath = public_path('uploads') . $destination;

            // Ensure the destination directory exists
            if (!File::exists($logoPath)) {
                File::makeDirectory($logoPath, 0755, true);
            }

            $logoName = $image->getClientOriginalName();
            $unique_logoName = time() . "-" . $logoName;

            // Move the file and generate the URL
            if ($image->move($logoPath, $unique_logoName)) {
                $logo_url = asset('uploads' . $destination . '/' . $unique_logoName);
            }

            // Delete old file if a temperature file is provided
            if (!empty($temperature)) {
                $image_path = public_path('uploads' . $destination . '/' . $temperature);
                if (File::exists($image_path)) {
                    File::delete($image_path);
                }
            }
        } else {
            // Use the temperature file name if no new image was uploaded
            $unique_logoName = $temperature;
        }

        // Return an array containing both values
        return ['unique_name' => $unique_logoName, 'original_name' => $logoName];
    }

    public static function uploadFile3($destination, $image, $temperature = "", $getFileName = false)
    {
        if ($image != null) {
            $logoPath = public_path('uploads') . $destination;

            // Ensure the directory exists
            if (!File::exists($logoPath)) {
                File::makeDirectory($logoPath, 0755, true);
            }
            $logoName = $image->getClientOriginalName();

            // $unique_logoName = time() . "-" . $logoName;
            $unique_logoName = Str::random() . "-slash-" . $logoName;
            $fileUrl = $getFileName ? $unique_logoName : $destination . '/' . $unique_logoName;
            try {
                $image->move($logoPath, $unique_logoName);
            } catch (Exception $error) {
                echo $error->getMessage();
            }

            if ($temperature != '') {
                $oldFilePath = public_path() . '/' . parse_url($temperature, PHP_URL_PATH);
                File::delete($oldFilePath);
            }
        } else {

            $fileUrl = $temperature;
        }

        return $fileUrl;
    }

    public static function uploadFile4($destination, $image, $temperature = "", $getFileName = false)
    {
        if ($image != null) {
            $logoPath = public_path('uploads/' . $destination);

            // Ensure the directory exists
            if (!File::exists($logoPath)) {
                File::makeDirectory($logoPath, 0755, true);
            }

            // Generate a unique name for the file
            $logoName = $image->getClientOriginalName();
            $unique_logoName = Str::random(10) . "-slash-" . $logoName;

            // Determine the URL for the file
            $fileUrl = $getFileName ? $unique_logoName : $destination . '/' . $unique_logoName;

            try {
                // Move the uploaded file to the specified path
                $image->move($logoPath, $unique_logoName);
            } catch (Exception $error) {
                return response()->json(['error' => 'File upload failed: ' . $error->getMessage()], 500);
            }

            // Handle the temperature logic
            if ($temperature != '') {
                $oldFilePath = public_path(parse_url($temperature, PHP_URL_PATH));
                if (File::exists($oldFilePath)) {
                    File::delete($oldFilePath);
                }
            }
        } else {
            $fileUrl = $temperature; // Return the old file URL if no new file is uploaded
        }

        return $fileUrl; // Return the URL of the uploaded file
    }


    public static function uploadFile($destination, $image, $temperature = '')
    {
        if ($image != null) {
            // $logoPath = public_path('uploads') . $destination;
            $logoName = $image->getClientOriginalName();
            $name = Str::random() . "-slash-" . $logoName;
            $image->storeAs('public/' . $destination, $name);
            $path = $destination . '/' . $name;
            if ($temperature != '') {
                $image_path = public_path('app/public/' . $temperature);
                if (File::exists($image_path)) {
                    File::delete($image_path);
                }
            }
        } else {
            $path = $temperature;
        }
        return $path;
    }

    public static function deleteFile($destination, $temperature)
    {
        if ($temperature) {
            $image_path = public_path() . '/uploads' . $destination . '/' . $temperature;
            if (File::exists($image_path)) {
                File::delete($image_path);
            }
        }
    }

    public static function uploadPhoto($destination, $image, $temperature)
    {
        if ($image) {
            $logoPath = public_path('images') . $destination;
            $logoName = $image->getClientOriginalName();
            $unique_logoName = time() . "-" . $logoName;
            $fileUrl = 'images' . $destination . '/' . $unique_logoName;
            try {
                $image->move($logoPath, $unique_logoName);
            } catch (Exception $error) {
                dd($error);
            }

            if ($temperature != '') {
                $oldFilePath = public_path() . parse_url($temperature, PHP_URL_PATH);
                File::delete($oldFilePath);
            }
        } else {
            $fileUrl = $temperature;
        }

        return $fileUrl;
    }
}
