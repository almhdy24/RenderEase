<?php
namespace Almhdy\RenderEase\Parsers;

class ShortHandParser implements ParserInterface
{
    public function parse(string $content): string
    {
        // Parse control structures first
        $content = $this->parseControlStructures($content);
        
        // Then parse variables
        $content = $this->parseVariables($content);
        
        // Parse includes (components)
        $content = $this->parseIncludes($content);

        return $content;
    }

    protected function parseControlStructures(string $content): string
    {
        // Convert {{ for item in array }} ... {{ end }}
        $content = preg_replace_callback(
            "/{{\s*for\s+([a-zA-Z0-9_]+)\s+in\s+([a-zA-Z0-9_]+)\s*}}(.+?){{\s*end\s*}}/s",
            function ($matches) {
                $item = '$' . trim($matches[1]);
                $array = '$' . trim($matches[2]);
                $loopContent = $matches[3];
                
                return '<?php foreach (' . $array . ' as ' . $item . '): ?>' . 
                       $loopContent . 
                       '<?php endforeach; ?>';
            },
            $content
        );

        // Convert {{ if condition }} ... {{ end }}
        $content = preg_replace_callback(
            "/{{\s*if\s+(.+?)\s*}}(.+?){{\s*end\s*}}/s",
            function ($matches) {
                $condition = $this->parseCondition(trim($matches[1]));
                $ifContent = $matches[2];
                
                return '<?php if (' . $condition . '): ?>' . 
                       $ifContent . 
                       '<?php endif; ?>';
            },
            $content
        );

        return $content;
    }

    protected function parseCondition(string $condition): string
    {
        // Convert simple variable names to PHP variables with $ prefix
        return preg_replace_callback(
            '/\b([a-zA-Z_][a-zA-Z0-9_]*)\b/',
            function ($matches) {
                $varName = $matches[1];
                
                // Don't add $ to keywords, operators, or special words
                $keywords = ['true', 'false', 'null', 'empty', 'isset', 'end', 'if', 'for', 'in', 
                            'and', 'or', 'xor', 'not'];
                
                $operators = ['===', '!==', '==', '!=', '<', '>', '<=', '>=', '&&', '||', '!'];
                
                if (in_array(strtolower($varName), $keywords) || 
                    in_array($varName, $operators) ||
                    is_numeric($varName)) {
                    return $varName;
                }
                
                return '$' . $varName;
            },
            $condition
        );
    }

    protected function parseVariables(string $content): string
    {
        // Convert {{ variable }} to PHP echo
        return preg_replace_callback(
            "/{{\s*([a-zA-Z0-9_]+)\s*}}/",
            function ($matches) {
                $varName = trim($matches[1]);
                
                // Skip keywords that shouldn't be converted
                if (in_array($varName, ['end', 'if', 'for', 'in'])) {
                    return '{{ ' . $varName . ' }}';
                }
                
                return '<?php echo htmlspecialchars($' . $varName . ', ENT_QUOTES, \'UTF-8\'); ?>';
            },
            $content
        );
    }

    // NEW: Parse include statements
    protected function parseIncludes(string $content): string
    {
        return preg_replace_callback(
            "/{{\s*include\s+([a-zA-Z0-9_]+)(?:\s+with\s+\{([^}]+)\})?\s*}}/",
            function ($matches) {
                $template = trim($matches[1]);
                $variables = [];
                
                // Parse variables if provided
                if (isset($matches[2])) {
                    $varString = trim($matches[2]);
                    $varPairs = explode(',', $varString);
                    
                    foreach ($varPairs as $pair) {
                        if (strpos($pair, ':') !== false) {
                            list($key, $value) = explode(':', $pair, 2);
                            $variables[trim($key)] = trim(trim($value), "'\"");
                        }
                    }
                }
                
                return '<?php echo $this->include(\'' . $template . '\', ' . var_export($variables, true) . '); ?>';
            },
            $content
        );
    }

    public function parseBasicSyntax(string $content): string
    {
        return $this->parse($content);
    }

    // Remove inheritance methods
    public function getExtendedTemplate(): ?string
    {
        return null;
    }

    public function getSections(): array
    {
        return [];
    }
}