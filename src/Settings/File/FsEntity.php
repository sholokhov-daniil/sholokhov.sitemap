<?php

namespace Sholokhov\Sitemap\Settings\File;

class FsEntity
{
    /**
     * @param bool $active Добавить в карту сайта
     * @param string $path Путь до файла\директории
     * @param string $type Тип элемента
     */
    public function __construct(
        public bool $active,
        public string $path,
        public string $type
    )
    {
    }
}