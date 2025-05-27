<?php
declare(strict_types=1);

namespace Werkl\OpenBlogware\Content\Blog;

use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Werkl\OpenBlogware\Content\Blog\Events\BlogIndexerEvent;

class BlogEntitiesIndexer extends EntityIndexer
{
    private EventDispatcherInterface $eventDispatcher;

    private IteratorFactory $iteratorFactory;

    private EntityRepository $repository;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        IteratorFactory $iteratorFactory,
        EntityRepository $repository
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->iteratorFactory = $iteratorFactory;
        $this->repository = $repository;
    }

    public function getName(): string
    {
        return 'werkl_blog.entities.indexer';
    }

    public function update(EntityWrittenContainerEvent $event): ?EntityIndexingMessage
    {
        $blogEntriesUpdates = $event->getPrimaryKeys(BlogEntriesDefinition::ENTITY_NAME);
        if (\count($blogEntriesUpdates) === 0) {
            return null;
        }

        return new BlogEntriesIndexingMessage(array_values($blogEntriesUpdates), null, $event->getContext());
    }

    public function handle(EntityIndexingMessage $message): void
    {
        $ids = $message->getData();

        if (!\is_array($ids)) {
            return;
        }

        $ids = array_unique(array_filter($ids));
        if (empty($ids)) {
            return;
        }

        $this->eventDispatcher->dispatch(new BlogIndexerEvent($ids, $message->getContext(), $message->getSkip()));
    }

    public function iterate($offset): ?EntityIndexingMessage
    {
        $iterator = $this->iteratorFactory->createIterator($this->repository->getDefinition(), $offset);

        $ids = $iterator->fetch();

        if (empty($ids)) {
            return null;
        }

        return new BlogEntriesIndexingMessage(array_values($ids), $iterator->getOffset());
    }

    public function getTotal(): int
    {
        return $this->iteratorFactory->createIterator($this->repository->getDefinition())->fetchCount();
    }

    public function getDecorated(): EntityIndexer
    {
        throw new DecorationPatternException(static::class);
    }
}
