<?php

namespace DataCue\MagentoModule\Utils;

class Info
{
    private static $packageVersion = null;

    public static function getPackageVersion()
    {
        if (is_null(static::$packageVersion)) {
            $packageInfo = json_decode(file_get_contents(__DIR__ . '/../composer.json'));
            static::$packageVersion = $packageInfo->version;
        }

        return static::$packageVersion;
    }
}