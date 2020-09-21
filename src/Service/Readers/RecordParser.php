<?php


namespace App\Service\Reader;


use App\Exceptions\LogParsingErrorException;

class RecordParser implements ParserRecordInterface
{
    /**
     * @var string record
     */
    protected string $record;

    /**
     * @var string[] $urls
     */
    protected array $urls = [];

    /**
     * @var int $traffic
     */
    protected int $traffic = 0;

    /**
     * @var int[] $statusCodes
     */
    protected array $statusCodes = [];

    /**
     * @var int $views
     */
    protected int $views = 0;

    /**
     * @var array $result
     */
    protected array $result = [
            'views' => 0,
            'urls' => 0,
            'traffic' => 0,
            'crawlers' => [],
            'statusCodes' => [],
    ];

    /**
     * @param string $record
     * @return void
     */
    public function parsingRecord(string $record): void
    {
        $this->record = $record;

        /**
         * В данном варианте наличие ip адреса я использую как индикатор корректной записи. Скорее всего, это
         * неправильно, но решения лучше пока что у меня нет.
         */
        $this->getIp();

        $this->result['views']++;

        if ($this->urlIsUnique()) {
            $this->result['urls']++;
        }

        $statusCode = $this->getStatusCode();
        if (!is_null($statusCode)) {
            if (array_key_exists($statusCode, $this->result['statusCodes'])) {
                $this->result['statusCodes'][$statusCode]++;
            } else {
                $this->result['statusCodes'][$statusCode] = 1;
            }
        }

        $this->result['traffic'] += $this->statusIsRedirect($statusCode) ? 0 : $this->getTraffic();

        $crawler = $this->getCrawler();
        if (!is_null($crawler)) {
            if (array_key_exists($crawler, $this->result['crawlers'])) {
                $this->result['crawlers'][$crawler]++;
            } else {
                $this->result['crawlers'][$crawler] = 1;
            }
        }

    }

    /**
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * @return string
     */
    protected function getIp(): string
    {
        return $this->findByRegExp('/(\d{1,3}\.){3}\d{1,3}/', 'ip');
    }

    /**
     * @return bool
     */
    protected function urlIsUnique(): bool
    {
        $result = $this->findByRegExp('#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#iS', 'url');
        if (!in_array($result, $this->urls, true)) {
            $this->urls[] = $result;
            return true;
        }

        return false;
    }

    /**
     * Поскольку я не могу получить код статуса иначе, чем определить последовательность из 3 цифр после ",
     * обработка статуса у меня идет в два этапа - сначала нахожу необходимую последовательность,
     * потом обрезаю найденный результат до 3 цифр.
     * P.S. Я другого выхода тут не вижу
     *
     * @throws LogParsingErrorException
     */
    protected function getStatusCode(): ?int
    {
        $result = $this->findByRegExp('/\" \d{1,3}/', 'status code');

        return (int) preg_replace('/\" /', '', $result);
    }

    /**
     * Поскольку я не могу получить трафик иначе, чем определить последовательность из неопределенного количества
     * цифр после последовательности " \d{1,3}, обработка трафика идет в два этапа - сначала нахожу необходимую
     * последовательность, потом обрезаю найденный результат до последних цифр.
     * @return int|null
     */
    protected function getTraffic(): ?int
    {
        $result = $this->findByRegExp('/\" \d{1,3} \d{1,}/', 'traffic');

        return (int) preg_replace('/\" \d{1,3}/', '', $result);
    }

    /**
     * @return string|null
     */
    protected function getCrawler(): ?string
    {
        /**
         * Поскольку у каждого поискового движка множество "подписей", полагаю, поиска на основе простого названия
         * будет достаточно.
         */
        $result = null;
        try {
            $result = $this->findByRegExp('/(Google)|(Baidu)|(Yandex)|(Bing)/', 'crawler');
        } catch (LogParsingErrorException $exception) {
            /**
             * Поскольку поисковый движок - это необязательный параметр записи, то отлавливаем сообщение об ошибке
             * тут и просто ничего не делаем.
             */
        }
        return $result;
    }

    /**
     * @param $pattern
     * @param $element
     * @return string
     */
    protected function findByRegExp($pattern, $element): string
    {
        $result = preg_match($pattern, $this->record, $matches);
        if (!$result) {
            throw new LogParsingErrorException("Элемент {$element} не найден в записи");
        }
        return $matches[0];
    }

    /**
     * @param int $statusCode
     * @return bool
     */
    protected function statusIsRedirect(int $statusCode): bool
    {
        $code = (string) $statusCode;
        return (int) $code[0] === 3;
    }

}