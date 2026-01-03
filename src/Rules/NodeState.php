<?php

namespace Sholokhov\Sitemap\Rules;

/**
 * Состояния элемента принимающего участие в генерации карты сайта
 */
enum NodeState: int
{
    /**
     * Правило не задано
     */
    case Inherit = 0;

    /**
     * Правило разрешено
     */
    case Allow = 1;

    /**
     * Правило запрещено
     */
    case Deny = 2;
}
