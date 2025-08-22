<?php
namespace Almhdy\RenderEase\Cache;

use Exception;

class CacheManager
{
    protected string $cacheDirectory;
    protected int $cacheTime;
    protected bool $enabled = false;

    public function __construct(int $cacheTime = 3600)
    {
        $this->cacheTime = $cacheTime;
    }

    public function setCacheDirectory(string $directory): void
    {
        $this->cacheDirectory = rtrim($directory, '/') . '/';
        if (!is_dir($this->cacheDirectory)) {
            mkdir($this->cacheDirectory, 0755, true);
        }
    }

    public function enable(bool $enable = true): void
    {
        $this->enabled = $enable;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setCacheTime(int $seconds): void
    {
        $this->cacheTime = $seconds;
    }

    public function get(string $key): ?string
    {
        if (!$this->enabled) {
            return null;
        }

        if (!isset($this->cacheDirectory)) {
            throw new Exception("Cache directory must be set before using caching");
        }

        $cacheFile = $this->cacheDirectory . $key . '.php';
        
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $this->cacheTime) {
            return file_get_contents($cacheFile);
        }
        
        return null;
    }

    public function put(string $key, string $content): void
    {
        if ($this->enabled) {
            if (!isset($this->cacheDirectory)) {
                throw new Exception("Cache directory must be set before using caching");
            }

            $cacheFile = $this->cacheDirectory . $key . '.php';
            file_put_contents($cacheFile, $content);
        }
    }

    public function clear(): void
    {
        if (isset($this->cacheDirectory) && is_dir($this->cacheDirectory)) {
            $files = glob($this->cacheDirectory . '*.php');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }

    public function generateKey(string $template, array $variables): string
    {
        try {
            // Try to serialize the entire array
            serialize($variables);
            return md5($template . serialize($variables));
        } catch (\Exception $e) {
            // If serialization fails, use a simplified key
            $simpleVars = [];
            foreach ($variables as $key => $value) {
                if (is_scalar($value) || is_null($value)) {
                    $simpleVars[$key] = $value;
                } else {
                    $simpleVars[$key] = gettype($value);
                }
            }
            return md5($template . serialize($simpleVars));
        }
    }

    public function validateCacheSetup(): void
    {
        if ($this->enabled && !isset($this->cacheDirectory)) {
            throw new Exception("Cache directory must be set when caching is enabled");
        }
    }
}