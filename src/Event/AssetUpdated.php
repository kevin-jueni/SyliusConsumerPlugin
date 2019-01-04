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
     * @param string|null $path
     */
    public function __construct(string $code, ?string $path)
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
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }
}
