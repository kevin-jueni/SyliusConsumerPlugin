<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Event;

final class AssetUpdated
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $path;

    /**
     * @param string $code
     * @param string $path
     */
    public function __construct(string $code, string $path)
    {
        $this->code = $code;
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
