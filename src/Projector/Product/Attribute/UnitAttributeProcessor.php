<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Projector\Product\Attribute;

use Sylake\SyliusConsumerPlugin\Model\Attribute;
use Sylius\Component\Attribute\Model\AttributeValueInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Symfony\Component\Translation\TranslatorInterface;

final class UnitAttributeProcessor implements AttributeProcessorInterface
{
    /** @var AttributeValueProviderInterface */
    private $attributeValueProvider;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(
        AttributeValueProviderInterface $attributeValueProvider,
        TranslatorInterface $translator
    ) {
        $this->attributeValueProvider = $attributeValueProvider;
        $this->translator = $translator;
    }

    /** {@inheritdoc} */
    public function process(ProductInterface $product, Attribute $attribute): array
    {
        if (!$this->supports($attribute)) {
            return [];
        }

        /** @var array $data */
        $data = $attribute->data();
        if (null === $data['amount']) {
            return [];
        }

        /** @var AttributeValueInterface|null $attributeValue */
        $attributeValue = $this->attributeValueProvider->provide($product, $attribute->attribute(),
            $attribute->locale());
        if (null === $attributeValue) {
            return [];
        }

        $value = $this->formatValue($data['amount']);

        $attributeValue->setValue(
            $this->translator->trans('cloudtec.ui.' . strtolower($data['unit']), ['%value%' => $value], null,
                $attribute->locale())
        );

        return [$attributeValue];
    }

    private function supports(Attribute $attribute): bool
    {
        return is_array($attribute->data())
            && count($attribute->data()) === 2
            && array_key_exists('amount', $attribute->data())
            && array_key_exists('unit', $attribute->data());
    }

    /**
     * @param string $value
     * @return string
     */
    private function formatValue(string $value): string
    {
        return preg_replace(
            '!\.?[0]{1,4}$!',
            '',
            $value
        );
    }

}
