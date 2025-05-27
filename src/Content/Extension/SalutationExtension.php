<?php
declare(strict_types=1);

namespace Werkl\OpenBlogware\Content\Extension;

use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SetNullOnDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Salutation\SalutationDefinition;
use Werkl\OpenBlogware\Content\BlogAuthor\BlogAuthorDefinition;

class SalutationExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField('blogAuthors', BlogAuthorDefinition::class, 'salutation_id', 'id'))->addFlags(new SetNullOnDelete()),
        );
    }

    public function getDefinitionClass(): string
    {
        return SalutationDefinition::class;
    }

    public function getEntityName(): string
    {
        return SalutationDefinition::ENTITY_NAME;
    }
}
