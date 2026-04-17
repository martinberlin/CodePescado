<?php
namespace App\Storage;

Interface Storage
{
    public function put(string $storageKey, string $tmpFilePath, string $mimeType);

    public function readStream(string $storageKey);
}
