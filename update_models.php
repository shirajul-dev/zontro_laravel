<?php
$dir = 'laravel-app/app/Models';
$files = glob("$dir/Pp*.php");

foreach ($files as $file) {
    if (basename($file) === 'PpAdmin.php') continue;

    $content = file_get_contents($file);
    
    // Replace use Model with nothing (BaseModel is in same namespace)
    $content = preg_replace('/use Illuminate\\\\Database\\\\Eloquent\\\\Model;/', '', $content);
    
    // Replace class X extends Model with class X extends BaseModel
    $content = preg_replace('/class (\w+) extends Model/', 'class $1 extends BaseModel', $content);
    
    // Remove redundant properties
    $content = preg_replace('/\s+public \$timestamps = false;/', '', $content);
    $content = preg_replace('/\s+protected \$guarded = \[\];/', '', $content);
    
    // Clean up double newlines
    $content = preg_replace("/\n\n+/", "\n\n", $content);
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}
