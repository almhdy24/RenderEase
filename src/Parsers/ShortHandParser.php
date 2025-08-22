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

        // Convert {{ if condition }} ... {{ else if condition }} ... {{ else }} ... {{ end }}
        $content = preg_replace_callback(
            "/{{\s*if\s+(.+?)\s*}}(.+?)({{\s*else\s+if\s+(.+?)\s*}}(.+?))?({{\s*else\s*}}(.+?))?{{\s*end\s*}}/s",
            function ($matches) {
                $condition = $this->parseCondition(trim($matches[1]));
                $ifContent = $matches[2];
                $elseIfConditions = [];
                $elseIfContents = [];
                $elseContent = '';
                
                // Parse elseif blocks if present
                if (!empty($matches[3])) {
                    $elseIfConditions[] = $this->parseCondition(trim($matches[4]));
                    $elseIfContents[] = $matches[5];
                }
                
                // Parse else block if present
                if (!empty($matches[6])) {
                    $elseContent = $matches[7];
                }
                
                $result = '<?php if (' . $condition . '): ?>' . $ifContent;
                
                // Add elseif blocks
                foreach ($elseIfConditions as $index => $elseIfCondition) {
                    $result .= '<?php elseif (' . $elseIfCondition . '): ?>' . $elseIfContents[$index];
                }
                
                // Add else block
                if (!empty($elseContent)) {
                    $result .= '<?php else: ?>' . $elseContent;
                }
                
                $result .= '<?php endif; ?>';
                return $result;
            },
            $content
        );

        return $content;
    }

    protected function parseCondition(string $condition): string
    {
        // Convert simple variable names to PHP variables with $ prefix
        return preg_replace_callback(
            '/\b([a-zA-Z_][a-zA-Z0-9_]*)\b(?!\()/', // Don't match function calls
            function ($matches) {
                $varName = $matches[1];
                
                // Don't add $ to keywords, operators, or special words
                $keywords = ['true', 'false', 'null', 'empty', 'isset', 'end', 'if', 'for', 'in', 
                            'and', 'or', 'xor', 'not', 'else', 'elseif'];
                
                $operators = ['===', '!==', '==', '!=', '<', '>', '<=', '>=', '&&', '||', '!'];
                
                if (in_array(strtolower($varName), $keywords) || 
                    in_array($varName, $operators) ||
                    is_numeric($varName) ||
                    function_exists($varName)) {
                    return $varName;
                }
                
                return '$' . $varName;
            },
            $condition
        );
    }

    protected function parseVariables(string $content): string
    {
        // Convert {{ variable }} to PHP echo, supporting complex expressions
        return preg_replace_callback(
            "/{{\s*([^}]+?)\s*}}/",
            function ($matches) {
                $expression = trim($matches[1]);
                
                // Skip control structure keywords
                if (in_array(strtolower($expression), ['end', 'if', 'for', 'in', 'else', 'elseif', 'include'])) {
                    return '{{ ' . $expression . ' }}';
                }
                
                // Parse complex expressions
                $parsedExpression = $this->parseCondition($expression);
                
                return '<?php echo htmlspecialchars(' . $parsedExpression . ', ENT_QUOTES, \'UTF-8\'); ?>';
            },
            $content
        );
    }

    protected function parseIncludes(string $content): string
    {
        return preg_replace_callback(
            "/{{\s*include\s+([a-zA-Z0-9_\.]+)(?:\s+with\s+\{([^}]+)\})?\s*}}/",
            function ($matches) {
                $template = trim($matches[1]);
                
                // Validate template name to prevent directory traversal
                if (!preg_match('/^[a-zA-Z0-9_\.\-]+$/', $template)) {
                    throw new \InvalidArgumentException("Invalid template name: " . $template);
                }
                
                $variables = [];
                
                // Parse variables if provided
                if (isset($matches[2])) {
                    $varString = trim($matches[2]);
                    $varPairs = array_filter(explode(',', $varString));
                    
                    foreach ($varPairs as $pair) {
                        $pair = trim($pair);
                        if (strpos($pair, ':') !== false) {
                            list($key, $value) = explode(':', $pair, 2);
                            $key = trim($key);
                            $value = trim(trim($value), "'\"");
                            
                            // Validate key name
                            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)) {
                                throw new \InvalidArgumentException("Invalid variable name in include: " . $key);
                            }
                            
                            $variables[$key] = $value;
                        }
                    }
                }
                
                return '<?php echo $this->include(\'' . addslashes($template) . '\', ' . var_export($variables, true) . '); ?>';
            },
            $content
        );
    }

    public function parseBasicSyntax(string $content): string
    {
        return $this->parse($content);
    }

    public function getExtendedTemplate(): ?string
    {
        return null;
    }

    public function getSections(): array
    {
        return [];
    }
}