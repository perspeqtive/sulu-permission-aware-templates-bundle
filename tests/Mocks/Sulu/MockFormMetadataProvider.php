<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluPermissionAwareTemplatesBundle\Tests\Mocks\Sulu;

use Sulu\Bundle\AdminBundle\Exception\MetadataNotFoundException;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\TypedFormMetadata;
use Sulu\Bundle\AdminBundle\Metadata\MetadataInterface;
use Sulu\Bundle\AdminBundle\Metadata\MetadataProviderInterface;

class MockFormMetadataProvider implements MetadataProviderInterface
{
    public function __construct(public ?TypedFormMetadata $metadata = new TypedFormMetadata())
    {
    }

    public function getMetadata(string $key, string $locale, array $metadataOptions): MetadataInterface
    {
        if ($this->metadata === null) {
            throw new MetadataNotFoundException('form', $key);
        }

        return $this->metadata;
    }
}
