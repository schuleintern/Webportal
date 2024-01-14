<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit6e43591e69eafd22d8bb152806fbfb3a
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'T' => 
        array (
            'Tests\\Fhp' => 
            array (
                0 => __DIR__ . '/..' . '/nemiah/php-fints/lib',
            ),
        ),
        'F' => 
        array (
            'Fhp' => 
            array (
                0 => __DIR__ . '/..' . '/nemiah/php-fints/lib',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit6e43591e69eafd22d8bb152806fbfb3a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit6e43591e69eafd22d8bb152806fbfb3a::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit6e43591e69eafd22d8bb152806fbfb3a::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit6e43591e69eafd22d8bb152806fbfb3a::$classMap;

        }, null, ClassLoader::class);
    }
}