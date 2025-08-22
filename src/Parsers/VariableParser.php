<?php
namespace Almhdy\RenderEase\Parsers;

class VariableParser implements ParserInterface
{
    public function parse(string $content): string
    {
        // Simple variable replacement for basic use cases
        return preg_replace_callback(
            "/{{ ([a-zA-Z0-9_]+) }}/",
            function ($matches) {
                return '<?php echo htmlspecialchars($' . $matches[1] . ', ENT_QUOTES, \'UTF-8\'); ?>';
            },
            $content
        );
    }
}
