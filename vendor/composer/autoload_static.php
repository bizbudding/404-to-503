<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit3efc610b5fab04ea686e4d0db148075c
{
    public static $files = array (
        '49a1299791c25c6fd83542c6fedacddd' => __DIR__ . '/..' . '/yahnis-elsts/plugin-update-checker/load-v4p11.php',
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit3efc610b5fab04ea686e4d0db148075c::$classMap;

        }, null, ClassLoader::class);
    }
}
