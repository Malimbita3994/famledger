<?php

/**
 * Strip $family / $currentFamily from route() / ->route() for /account/* URLs.
 * Preserves: families.show, families.edit, families.update, families.destroy, families.currency.switch
 */
$dirs = [
    __DIR__.'/../app/Http/Controllers',
    __DIR__.'/../resources/views',
];

function fixFile(string $path): bool
{
    $c = file_get_contents($path);
    $orig = $c;

    $protected = [];
    $i = 0;
    $save = function (array $m) use (&$protected, &$i) {
        $k = '___RP'.$i.'___';
        $protected[$k] = $m[0];
        $i++;

        return $k;
    };

    $p = '(?:[ \t\r\n]*->route|[ \t\r\n]*route)';

    $c = preg_replace_callback(
        "/{$p}\\('families\\.(show|edit|update|destroy)',\\s*\\\$[a-zA-Z_]+\\)/",
        $save,
        $c
    );
    $c = preg_replace_callback(
        "/{$p}\\('families\\.currency\\.switch',\\s*\\\$[a-zA-Z_]+\\)/",
        $save,
        $c
    );

    $c = preg_replace_callback(
        "/({$p})\\('families\\.([^']+)',\\s*\\[\\s*'family'\\s*=>\\s*\\\$(?:family|currentFamily)\\s*,\\s*/",
        fn ($m) => $m[1]."('families.".$m[2]."', [",
        $c
    );

    $c = preg_replace_callback(
        "/({$p})\\('families\\.([^']+)',\\s*\\[\\s*\\\$(?:family|currentFamily)\\s*,\\s*('filter'\\s*=>\\s*'[^']+')\\s*\\]\\)/",
        fn ($m) => $m[1]."('families.".$m[2]."', [".$m[3].'])',
        $c
    );

    $c = preg_replace_callback(
        "/({$p})\\('families\\.([^']+)',\\s*\\[\\s*\\\$(?:family|currentFamily)\\s*,\\s*(.+?)\\s*\\]\\)/s",
        fn ($m) => $m[1]."('families.".$m[2]."', ".$m[3].')',
        $c
    );

    $c = preg_replace_callback(
        "/({$p})\\('families\\.([^']+)',\\s*\\\$(?:family|currentFamily)\\)/",
        fn ($m) => $m[1]."('families.".$m[2]."')",
        $c
    );

    foreach ($protected as $k => $v) {
        $c = str_replace($k, $v, $c);
    }

    if ($c !== $orig) {
        file_put_contents($path, $c);

        return true;
    }

    return false;
}

$changed = 0;
foreach ($dirs as $dir) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $file) {
        if (! $file->isFile()) {
            continue;
        }
        $path = $file->getPathname();
        if (! str_ends_with($path, '.php') && ! str_ends_with($path, '.blade.php')) {
            continue;
        }
        if (fixFile($path)) {
            $changed++;
            echo "Updated: $path\n";
        }
    }
}
echo "Done. Files changed: $changed\n";
