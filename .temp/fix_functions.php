<?php
$file = 'app/Support/zp-functions.php';
$content = file_get_contents($file);

$functions = [
    'getCurrentDatetime',
    'pp_parse_sql_segments',
    'get_env',
    'canAccessPage',
    'hasPermission',
    'getNameChars',
    'timeAgo',
    'convertUTCtoUserTZ',
    'getParam',
    'senderWhitelist',
    'permissionSchema'
];

foreach ($functions as $func) {
    // We expect "function funcName(" or similar.
    $pattern = '/^(\s*)function\s+' . preg_quote($func, '/') . '\s*\(/m';
    if (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
        // If it's already wrapped implicitly by looking at previous lines, we verify:
        $offset = $matches[0][1];
        $before = substr($content, max(0, $offset - 50), 50);
        if (strpos($before, "function_exists") === false) {
            // Find the end of the function block using brace counting
            $bodyOffset = strpos($content, '{', $offset);
            $braceCount = 1;
            for ($i = $bodyOffset + 1; $i < strlen($content); $i++) {
                if ($content[$i] === '{') $braceCount++;
                if ($content[$i] === '}') $braceCount--;
                if ($braceCount === 0) {
                    $endOffset = $i;
                    break;
                }
            }
            
            if (isset($endOffset)) {
                $indent = $matches[1][0];
                $replacement = "{$indent}if (!function_exists('{$func}')) {\n" 
                             . substr($content, $offset, $endOffset - $offset + 1)
                             . "\n{$indent}}";
                $content = substr_replace($content, $replacement, $offset, $endOffset - $offset + 1);
            }
        }
    }
}

file_put_contents($file, $content);
echo "Re-wrapped PHP functions successfully.\n";
