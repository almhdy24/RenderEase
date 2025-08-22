<?php
namespace Almhdy\RenderEase\Parsers;

class VariableParser implements ParserInterface
{
    public function parse(string $content): string
    {
        // Enhanced variable replacement supporting complex expressions
        return preg_replace_callback(
            "/{{\s*([^}]+?)\s*}}/",
            function ($matches) {
                $expression = trim($matches[1]);
                
                // Skip control structure keywords that might appear in templates
                $controlKeywords = ['end', 'if', 'for', 'in', 'else', 'elseif', 'include'];
                if (in_array(strtolower($expression), $controlKeywords)) {
                    return '{{ ' . $expression . ' }}';
                }
                
                // Parse the expression to handle variables properly
                $parsedExpression = $this->parseExpression($expression);
                
                return '<?php echo htmlspecialchars(' . $parsedExpression . ', ENT_QUOTES, \'UTF-8\'); ?>';
            },
            $content
        );
    }

    protected function parseExpression(string $expression): string
    {
        // Convert variable names to PHP variables with $ prefix
        return preg_replace_callback(
            '/\b([a-zA-Z_][a-zA-Z0-9_]*)\b(?!\()/', // Don't match function calls
            function ($matches) {
                $varName = $matches[1];
                
                // Don't add $ to keywords, operators, or special words
                $keywords = ['true', 'false', 'null', 'empty', 'isset'];
                $operators = ['===', '!==', '==', '!=', '<', '>', '<=', '>=', '&&', '||', '!'];
                
                if (in_array(strtolower($varName), $keywords) || 
                    in_array($varName, $operators) ||
                    is_numeric($varName) ||
                    function_exists($varName)) {
                    return $varName;
                }
                
                return '$' . $varName;
            },
            $expression
        );
    }
}