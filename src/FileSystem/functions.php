<?php

namespace MGGFLOW\LVMSVC\FileSystem;

use function Illuminate\Filesystem\join_paths;


if (!function_exists('MGGFLOW\LVMSVC\FileSystem\load_json_from_root')) {
    /**
     * Get JSON decoded content from file relatively project root.
     * @param string $rootRelativePath
     * @param bool $assoc
     * @return mixed|null
     */
    function load_json_from_root(string $rootRelativePath, bool $assoc = true): mixed
    {
        $fullPath = join_paths(base_path(), $rootRelativePath);
        if (!is_file($fullPath)) {
            return null;
        }

        $contents = file_get_contents($fullPath);
        if ($contents === false) {
            return null;
        }

        return json_decode($contents, $assoc);
    }
}
