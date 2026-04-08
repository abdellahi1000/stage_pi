<?php
$dir = __DIR__;
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

foreach ($files as $file) {
    if ($file->isDir()) continue;
    $path = $file->getRealPath();
    if (strpos($path, '.git') !== false) continue;
    if (basename($path) === 'fix_offres_names_all.php') continue;
    if ($file->getExtension() !== 'php' && $file->getExtension() !== 'js') continue;

    $content = file_get_contents($path);
    $newContent = str_replace('offres_stage', 'offres', $content);
    // Be careful with 'titre' -> 'title', only in specific contexts or just use Aliases in SQL
    // Let's only do 'offres_stage' -> 'offres' for now to be safe, then specific fixes
    
    if ($content !== $newContent) {
        file_put_contents($path, $newContent);
        echo "Updated $path\n";
    }
}
echo "Done replacing table names.\n";
?>
