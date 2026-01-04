<?php

namespace Sholokhov\Sitemap\Validator;

use Bitrix\Main\IO\File;
use Bitrix\Main\IO\FileNotFoundException;
use Bitrix\Main\SiteTable;
use Bitrix\Main\Web\Uri;

use Sholokhov\Sitemap\Entry;

/**
 * Валидатор проверяет, что URL не попадает под редиректы в .htaccess
 *
 * Поддерживаются команды:
 * - Redirect
 * - RedirectMatch
 * - RewriteRule + RewriteCond
 */
class HtaccessValidator implements ValidatorInterface
{
    /**
     * Кэш правил по сайту
     *
     * @var array<string, array<int, array{TYPE:string, FROM:string, TO:string, FLAGS?:array, CONDITIONS?:array}>>
     */
    protected static array $rules = [];

    protected readonly string $siteId;

    public function __construct(string $siteId)
    {
        $this->siteId = $siteId;
    }

    /**
     * Проверка возможности добавить ссылку в sitemap
     *
     * @param Entry $entry
     * @return bool
     * @throws FileNotFoundException
     */
    public function validate(Entry $entry): bool
    {
        $uri = new Uri($entry->url);
        $path = $uri->getPath();

        foreach ($this->getRules() as $rule) {
            if ($this->isRedirected($rule, $path)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Определяет, соответствует ли путь редиректу
     *
     * @param array $rule
     * @param string $path
     * @return bool
     */
    protected function isRedirected(array $rule, string $path): bool
    {
        return match ($rule['TYPE']) {
            'Redirect' => $this->matchRedirect($rule['FROM'], $path),
            'RedirectMatch' => $this->matchRedirectMatch($rule['FROM'], $path),
            'RewriteRule' => $this->matchRewriteRule($rule, $path),
            default => false,
        };
    }

    /**
     * Проверка Redirect с учётом точного совпадения и boundary
     *
     * @param string $from
     * @param string $path
     * @return bool
     */
    protected function matchRedirect(string $from, string $path): bool
    {
        return $path === $from || str_starts_with($path, rtrim($from, '/') . '/');
    }

    /**
     * Проверка RedirectMatch через регулярное выражение
     *
     * @param string $pattern
     * @param string $path
     * @return bool
     */
    protected function matchRedirectMatch(string $pattern, string $path): bool
    {
        return preg_match('#' . $pattern . '#', $path) === 1;
    }

    /**
     * Проверка RewriteRule с учетом RewriteCond
     *
     * @param array $rule
     * @param string $path
     * @return bool
     */
    protected function matchRewriteRule(array $rule, string $path): bool
    {
        // Если нет флага R, значит это не редирект
        if (empty($rule['FLAGS']) || !array_filter($rule['FLAGS'], fn($f) => str_starts_with($f, 'R'))) {
            return false;
        }

        // Проверяем условия RewriteCond
        if (!empty($rule['CONDITIONS'])) {
            foreach ($rule['CONDITIONS'] as $condPattern) {
                if (!$this->matchRedirectMatch($condPattern, $path)) {
                    return false; // условие не выполнено
                }
            }
        }

        // Проверяем сам RewriteRule
        return $this->matchRedirectMatch('^' . $rule['FROM'], $path);
    }

    /**
     * Получение правил редиректов для текущего сайта
     *
     * @return array|array[]
     * @throws FileNotFoundException
     */
    protected function getRules(): array
    {
        if (!isset(static::$rules[$this->siteId])) {
            $this->load();
        }

        return static::$rules[$this->siteId];
    }

    /**
     * Загрузка правил из .htaccess
     *
     * @throws FileNotFoundException
     */
    protected function load(): void
    {
        static::$rules[$this->siteId] = [];

        $htaccessPath = SiteTable::getDocumentRoot($this->siteId) . DIRECTORY_SEPARATOR . ".htaccess";
        $htaccess = new File($htaccessPath, $this->siteId);

        if (!$htaccess->isExists()) {
            return;
        }

        $contents = $htaccess->getContents();
        $lines = preg_split("/\r?\n/", $contents) ?: [];

        $rewriteConds = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Отрезаем inline комментарии
            if (($pos = mb_strpos($line, '#')) !== false) {
                $line = mb_substr($line, 0, $pos);
            }
            $line = trim($line);

            // Redirect / RedirectMatch
            if (preg_match('#^(Redirect|RedirectMatch)\s+(\d{3})\s+(\S+)\s+(\S+)$#i', $line, $matches)) {
                [$full, $type, $status, $from, $to] = $matches;

                if ($type === 'Redirect') {
                    $from = '/' . ltrim($from, '/');
                    $to = '/' . ltrim($to, '/');
                }

                static::$rules[$this->siteId][] = [
                    'TYPE' => $type,
                    'FROM' => $from,
                    'TO' => $to,
                    'STATUS' => (int)$status,
                ];
                continue;
            }

            // RewriteCond
            if (preg_match('#^RewriteCond\s+(\S+)\s+(.*)$#i', $line, $matches)) {
                $condPattern = trim($matches[2]);
                $rewriteConds[] = $condPattern;
                continue;
            }

            // RewriteRule
            if (preg_match('#^RewriteRule\s+(\S+)\s+(\S+)(?:\s+\[(.*)\])?$#i', $line, $matches)) {
                [$full, $pattern, $to, $flagsStr] = array_pad($matches, 4, '');
                $flags = $flagsStr !== '' ? array_map('trim', explode(',', $flagsStr)) : [];

                static::$rules[$this->siteId][] = [
                    'TYPE' => 'RewriteRule',
                    'FROM' => $pattern,
                    'TO' => $to,
                    'FLAGS' => $flags,
                    'CONDITIONS' => $rewriteConds,
                ];

                // Сброс условий после применения RewriteRule
                $rewriteConds = [];
            }
        }
    }
}
