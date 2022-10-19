<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class UploadFile{

    /**
     * Undocumented function
     *
     * @param UploadedFile $file
     * @param string $directory
     * @return string|array<string,string>
     */
    public static function saveFile(UploadedFile $file, string $directory): string|array
    {
        try {
            $pictureName = md5(uniqid()).'.'. $file->guessExtension();
            $file->move(
                $directory,
                $pictureName
            );
            return $pictureName;
            
        } catch (FileException $e) {
            return [
                'type' => "danger",
                'message' => "L'image n'a pas pu être enregistrée à cause de l'erreur suivante: " . $e->getMessage()];
        }
    }
}