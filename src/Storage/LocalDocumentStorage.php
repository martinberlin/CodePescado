<?php

namespace App\Storage;

use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

class LocalDocumentStorage implements Storage
{
    public function __construct(
        private readonly string $basePath,
        private readonly Filesystem $fs = new Filesystem(),
    ) {}

    public function put(string $storageKey, string $tmpFilePath, string $mimeType): bool
    {
        $target = rtrim($this->basePath, '/').'/'.ltrim($storageKey, '/');
        //Validate MIME
        $validMime = false;
        switch ($mimeType) {
            case 'application/pdf':
                $extension = 'pdf';
                $validMime = true;
                break;
        }
        if (!$validMime) {
            return false;
        }

        $this->fs->mkdir(\dirname($target));
        try {
            $this->fs->copy($tmpFilePath, $target.'.'.$extension, true);
        } catch (IOExceptionInterface $exception) {
            throw $exception;
        }
        return true;
    }

    public function readStream(string $storageKey)
    {
        $target = rtrim($this->basePath, '/').'/'.ltrim($storageKey, '/');
        return fopen($target, 'rb');
    }
}
