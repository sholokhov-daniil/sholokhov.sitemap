<?php

namespace Sholokhov\Sitemap\Helpers;

use Bitrix\Main\IO\Path;
use Bitrix\Main\IO\File;
use Bitrix\Main\IO\InvalidPathException;

class Http
{
    /**
     * Формирование полного URL пути до файла
     *
     * @param File $file
     * @param string $documentRoot
     * @return string
     * @throws InvalidPathException
     */
    public static function getFileUrl(File $file, string $documentRoot)
    {
        $documentRoot  = Path::normalize($documentRoot);
        $path = '/';

        if (mb_substr($file->getPath(), 0, mb_strlen($documentRoot)) === $documentRoot)
        {
            $path = '/'.mb_substr($file->getPath(), mb_strlen($documentRoot));
        }

        $path = Path::convertLogicalToUri($path);

        $path = in_array($file->getName(), GetDirIndexArray())
            ? str_replace('/'.$file->getName(), '/', $path)
            : $path;

        return '/'.ltrim($path, '/');
    }

}