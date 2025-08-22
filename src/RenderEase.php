<?php
namespace Almhdy\RenderEase;

use Almhdy\RenderEase\Contracts\TemplateRendererInterface;
use Almhdy\RenderEase\Exceptions\TemplateNotFoundException;
use Almhdy\RenderEase\Cache\CacheManager;
use Almhdy\RenderEase\Parsers\ShortHandParser;
use Exception;

class RenderEase implements TemplateRendererInterface
{
    protected array $variables = [];
    protected string $errorTemplate = "error";
    protected string $templateDirectory = __DIR__ . "/../views/";
    protected string $extension = "ease";
    
    protected CacheManager $cacheManager;
    protected ShortHandParser $parser;

    public function __construct()
    {
        $this->cacheManager = new CacheManager();
        $this->parser = new ShortHandParser();
    }

    public function setTemplateDirectory(string $directory): void
    {
        $this->templateDirectory = rtrim($directory, '/') . '/';
    }

    public function setCacheDirectory(string $directory): void
    {
        $this->cacheManager->setCacheDirectory($directory);
    }

    public function enableCaching(bool $enable = true): void
    {
        $this->cacheManager->enable($enable);
    }

    public function setCacheTime(int $seconds): void
    {
        $this->cacheManager->setCacheTime($seconds);
    }

    public function setExtension(string $extension): void
    {
        $this->extension = $extension;
    }

    public function set(string $key, mixed $value): void
    {
        $this->variables[$key] = $value;
    }

    public function setMultiple(array $vars): void
    {
        foreach ($vars as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function get(string $key): mixed
    {
        return $this->variables[$key] ?? null;
    }

    public function clear(): void
    {
        $this->variables = [];
    }

    public function setErrorTemplate(string $template): void
    {
        $this->errorTemplate = $template;
    }

    public function clearCache(): void
    {
        $this->cacheManager->clear();
    }

    public function render(string $template): string
{
    $templatePath = $this->templateDirectory . $template . "." . $this->extension;

    try {
        if (!file_exists($templatePath)) {
            throw new TemplateNotFoundException($template, $templatePath);
        }

        // Validate cache setup before proceeding (outside of try-catch)
        if ($this->cacheManager->isEnabled()) {
            $this->cacheManager->validateCacheSetup();
        }

        // Generate cache key
        try {
            $cacheKey = $this->cacheManager->generateKey($template, $this->variables);
        } catch (\Exception $e) {
            $cacheKey = null;
            $originalCachingState = $this->cacheManager->isEnabled();
            $this->cacheManager->enable(false);
        }

        // Check cache
        if ($this->cacheManager->isEnabled() && $cacheKey) {
            $cachedContent = $this->cacheManager->get($cacheKey);
            if ($cachedContent !== null) {
                return $cachedContent;
            }
        }

        // Load template
        $content = file_get_contents($templatePath);

        // Parse template
        $content = $this->parser->parse($content);

        // Extract variables and render
        extract($this->variables);
        ob_start();
        eval("?>" . $content);
        $output = ob_get_clean();

        // Save to cache
        if ($this->cacheManager->isEnabled() && $cacheKey) {
            $this->cacheManager->put($cacheKey, $output);
        }

        if (isset($originalCachingState)) {
            $this->cacheManager->enable($originalCachingState);
        }

        return $output;
    } catch (Exception $e) {
        return $this->renderError($e->getMessage());
    }
}

    // NEW: Component/include system
    public function include(string $template, array $variables = []): string
    {
        // Save current variables
        $currentVariables = $this->variables;
        
        // Merge new variables with existing ones
        $this->variables = array_merge($currentVariables, $variables);
        
        // Render the component
        $output = $this->render($template);
        
        // Restore original variables
        $this->variables = $currentVariables;
        
        return $output;
    }

    protected function renderError(string $errorMessage): string
    {
        $errorTemplatePath = $this->templateDirectory . $this->errorTemplate . "." . $this->extension;

        if (file_exists($errorTemplatePath)) {
            $currentVars = $this->variables;
            $this->variables = ['errorMessage' => $errorMessage];
            $output = $this->render($this->errorTemplate);
            $this->variables = $currentVars;
            return $output;
        }

        return "An error occurred: " . htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8');
    }

    public function getCacheManager(): CacheManager
    {
        return $this->cacheManager;
    }

    public function getParser(): ShortHandParser
    {
        return $this->parser;
    }
}