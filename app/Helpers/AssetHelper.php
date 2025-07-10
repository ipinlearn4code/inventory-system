<?php

namespace App\Helpers;

class AssetHelper
{
    /**
     * Get the versioned asset path for a given file.
     *
     * @param string $path
     * @return string
     */
    public static function versionedAsset($path)
    {
        // Get the manifest file
        $manifestPath = public_path('build/manifest.json');
        
        if (!file_exists($manifestPath)) {
            return asset($path);
        }
        
        $manifest = json_decode(file_get_contents($manifestPath), true);
        
        // Look for the asset in the manifest
        if (isset($manifest[$path])) {
            return asset('build/' . $manifest[$path]['file']);
        }
        
        // For CSS files that might be referenced by base name
        foreach ($manifest as $key => $value) {
            if (str_contains($key, $path) && str_ends_with($key, '.css')) {
                return asset('build/' . $value['file']);
            }
        }
        
        return asset($path);
    }
    
    /**
     * Get the latest CSS file for a given base name.
     *
     * @param string $baseName
     * @return string
     */
    public static function getLatestCSS($baseName)
    {
        $assetsDir = public_path('build/assets');
        $files = scandir($assetsDir);
        
        foreach ($files as $file) {
            if (str_contains($file, $baseName) && str_ends_with($file, '.css')) {
                return asset('build/assets/' . $file);
            }
        }
        
        return asset('css/' . $baseName . '.css');
    }
}
