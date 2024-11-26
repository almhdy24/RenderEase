<?php
namespace Almhdy\RenderEase;

use Exception;

class RenderEase
{
  protected array $variables = [];
  protected string $errorTemplate = "error.ease"; // Default error template
  protected string $extension = "ease"; // Custom extension

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

  public function clear(): void
  {
    $this->variables = [];
  }

  public function setErrorTemplate(string $template): void
  {
    $this->errorTemplate = $template;
  }

  protected function parseShortHand(string $content): string
  {
    // Convert shorthand {{ if condition }} ... {{ end }} to PHP
    $content = preg_replace(
      "/{{ if (.+?) }}(.+?){{ end }}/s",
      '<?php if ($1): ?>$2<?php endif; ?>',
      $content
    );

    // Convert shorthand {{ for $var in $array }} ... {{ end }} to PHP
    $content = preg_replace(
      "/{{ for (.+?) in (.+?) }}(.+?){{ end }}/s",
      '<?php foreach ($2 as $1): ?>$3<?php endforeach; ?>',
      $content
    );

    return $content;
  }

  public function render(string $template): string
  {
    $templatePath = __DIR__ . "/../views/" . $template . "." . $this->extension;

    try {
      if (!file_exists($templatePath)) {
        throw new Exception("Template not found: " . $template);
      }

      // Load template
      $content = file_get_contents($templatePath);

      // Parse shorthand syntax
      $content = $this->parseShortHand($content);

      // Extract variables into the current scope
      extract($this->variables);

      // Start output buffering
      ob_start();

      // Evaluate the content as PHP
      eval("?>" . $content);

      // Get content from the buffer
      return ob_get_clean();
    } catch (Exception $e) {
      return $this->renderError($e->getMessage());
    }
  }
  protected function renderError(string $errorMessage): string
  {
    $errorTemplatePath = __DIR__ . "/../views/" . $this->errorTemplate;

    if (file_exists($errorTemplatePath)) {
      // Pass error message to the error template
      $this->set("errorMessage", $errorMessage);
      return $this->render($this->errorTemplate);
    }

    // Fallback error message
    return "An error occurred: " . $errorMessage;
  }
}
