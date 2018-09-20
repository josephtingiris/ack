<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9a4cb7ea8e391061ec6aed23a8efb959
{
    public static $prefixLengthsPsr4 = array (
        'j' => 
        array (
            'josephtingiris\\Ack\\' => 19,
            'josephtingiris\\' => 15,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'josephtingiris\\Ack\\' => 
        array (
            0 => __DIR__ . '/../..' . '/include/josephtingiris/Ack',
            1 => __DIR__ . '/../..' . '/include/josephtingiris',
            2 => __DIR__ . '/../..' . '/include',
        ),
        'josephtingiris\\' => 
        array (
            0 => __DIR__ . '/../..' . '/include/josephtingiris',
            1 => __DIR__ . '/../..' . '/include/debug-php',
            2 => __DIR__ . '/../..' . '/include',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit9a4cb7ea8e391061ec6aed23a8efb959::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit9a4cb7ea8e391061ec6aed23a8efb959::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}