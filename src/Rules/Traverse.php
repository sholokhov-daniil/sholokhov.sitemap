<?php

namespace Sholokhov\Sitemap\Rules;

/**
 * Состояние пересечений правила
 */
enum Traverse: int
{
    /**
     * Правило может быть выполненым
     */
    case CanTraverse = 0;

    /**
     * Правило не может быть выполненном из-за запрета
     */
    case Stop = 1;
}