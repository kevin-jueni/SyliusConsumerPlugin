<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Event;

final class ProductUpdated
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var array
     */
    private $taxons;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var array
     */
    private $associations;

    /**
     * @var array
     */
    private $assets;

    /**
     * @var ?\DateTime
     */
    private $createdAt;

    /**
     * @var ?string
     */
    private $family;

    /**
     * @var array
     */
    private $groups;

    /**
     * @var null|string
     */
    private $parentCode;

    public function __construct(
        string $code,
        bool $enabled,
        array $taxons,
        array $attributes,
        array $associations,
        array $assets,
        ?string $family,
        array $groups,
        ?\DateTime $createdAt,
        ?string $parentCode = null
    ) {
        $this->code = $code;
        $this->enabled = $enabled;
        $this->taxons = $taxons;
        $this->attributes = $attributes;
        $this->associations = $associations;
        $this->assets = $assets;
        $this->family = $family;
        $this->groups = $groups;
        $this->createdAt = $createdAt;
        $this->parentCode = $parentCode;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function taxons(): array
    {
        return $this->taxons;
    }

    public function attributes(): array
    {
        return $this->attributes;
    }

    public function associations(): array
    {
        return $this->associations;
    }

    public function assets(): array
    {
        return $this->assets;
    }

    public function family(): ?string
    {
        return $this->family;
    }

    public function groups(): array
    {
        return $this->groups;
    }

    public function createdAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return null|string
     */
    public function getParentCode(): ?string
    {
        return $this->parentCode ? $this->parentCode : $this->code;
    }
}
