<?php

namespace App\Service;

class JsonReader
{
    public function read(string $path): array
    {
        $content = file_get_contents($path);
        return json_decode($content, true);
    }
}