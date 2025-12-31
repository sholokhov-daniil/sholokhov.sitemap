<?php

namespace Sholokhov\Sitemap\Validator;

use Sholokhov\Sitemap\Entry;

use Bitrix\Main\IO\File;
use Bitrix\Main\IO\FileNotFoundException;
use Bitrix\Main\SiteTable;
use Bitrix\Main\Web\Uri;
use Ga\Main\Helper\Str;

/**
 * Производит поиск редиректа в рамках файла .htaccess
 *
 * Производит подмены для команд:
 * <li>Redirect</li>
 * <li>RedirectMatch</li>
 *
 * Учитывает статусы редиректа:
 * <li>301</li>
 * <li>302</li>
 * <li>303</li>
 * <li>304</li>
 * <li>305</li>
 * <li>306</li>
 * <li>307</li>
 * <li>308</li>
 */
class HtaccessValidator implements ValidatorInterface
{
    /**
     * Список ролей.
     *
     * @var array
     */
    protected static array $rules = [];

    protected readonly string $siteId;

    public function __construct(string $siteId)
    {
        $this->siteId = $siteId;
    }

    /**
     * Произвести проверку
     *
     * @param Entry $entry
     * @return bool
     */
    public function validate(Entry $entry): bool
    {
        $result = true;
        $ruleList = $this->getRules();
        $uri = new Uri($entry->url);

        foreach ($ruleList as $rule) {
            if (
                ($rule['TYPE'] === 'RedirectMatch' && Str::validUrlRegex('^' . $rule['FROM'], $uri->getPath()))
                || str_starts_with($uri->getPath(), $rule['FROM'])
            ) {
                $result = false;
                break;
            }
        }

        return $result;
    }

    /**
     * Получение доступных ролей.
     *
     * @return array
     * @throws FileNotFoundException
     */
    protected function getRules(): array
    {
        if (!array_key_exists($this->siteId, static::$rules)) {
            $this->load();
        }

        return static::$rules[$this->siteId];
    }

    /**
     * Загрузка редиректов.
     *
     * @return void
     * @throws FileNotFoundException
     */
    protected function load(): void
    {
        static::$rules[$this->siteId] = [];
        $root = SiteTable::getDocumentRoot($this->siteId) . DIRECTORY_SEPARATOR . ".htaccess";
        $htaccess = new File($root, $this->siteId);

        if (!$htaccess->isExists()) {
            return;
        }

        $contents = $htaccess->getContents();
        $rows = preg_split("/\\n+/", $contents);

        array_walk($rows, function (string $line) {
            $line = trim($line);

            $clearLine = mb_strripos($line, '#') !== false ? mb_stristr($line, '#', true) : $line;
            $pattern = '#^(Redirect|RedirectMatch) (301|302|303|304|305|306|307|308) (.+) (.+)$#';
            preg_match($pattern, $clearLine, $matches);

            if (empty($matches)) {
                return;
            }

            static::$rules[$this->siteId][] = [
                'TYPE' => $matches[1],
                'FROM' => $matches[3],
                'TO' => $matches[4]
            ];
        });
    }
}