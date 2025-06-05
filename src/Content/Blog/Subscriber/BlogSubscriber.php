<?php
declare(strict_types=1);

namespace Werkl\OpenBlogware\Content\Blog\Subscriber;

use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Media\MediaCollection;
use Shopware\Core\Content\Product\SalesChannel\Listing\Filter;
use Shopware\Core\Content\Product\SalesChannel\Listing\FilterCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Bucket\FilterAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\EntityAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Werkl\OpenBlogware\Content\Blog\BlogEntriesDefinition;
use Werkl\OpenBlogware\Content\Blog\BlogEntriesEntity;
use Werkl\OpenBlogware\Content\Blog\BlogListingFilterBuildEvent;
use Werkl\OpenBlogware\Content\Blog\Events\BlogMainFilterEvent;

class BlogSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'werkl_blog_entries.loaded' => 'onBlogEntriesLoaded',
            BlogListingFilterBuildEvent::BLOG_MAIN_FILTER_EVENT => 'onBlogMainFilter',
        ];
    }

    public function __construct(
        private readonly EntityRepository $mediaRepository,
    ) {
    }

    public function onBlogEntriesLoaded(EntityLoadedEvent $event): void
    {
        /** @var BlogEntriesEntity[] $blogEntries */
        $blogEntries = $event->getEntities();

        $mediaIds = array_unique(array_filter(array_map(
            fn (BlogEntriesEntity $blogEntry) => $blogEntry->getTranslation('mediaId'),
            $blogEntries
        )));

        if (empty($mediaIds)) {
            return;
        }

        /** @var MediaCollection $media */
        $media = $this->mediaRepository->search(new Criteria($mediaIds), $event->getContext())->getEntities();

        foreach ($blogEntries as $blogEntry) {
            $mediaId = $blogEntry->getTranslation('mediaId');

            if ($mediaId === null) {
                continue;
            }

            $blogEntry->setMedia($media->get($mediaId));
        }
    }

    public function onBlogMainFilter(BlogMainFilterEvent $event): void
    {
        $dateTime = new \DateTime();

        $criteria = $event->getCriteria();
        $criteria->addFilter(
            new EqualsFilter('active', true),
            new RangeFilter('publishedAt', [RangeFilter::LTE => $dateTime->format(\DATE_ATOM)])
        );

        $criteriaCollection = new CriteriaCollection();
        $criteriaCollection->add(
            'werkl_blog',
            BlogEntriesDefinition::class,
            $criteria
        );

        $request = $event->getRequest();

        $filters = $this->getFilters($request);
        $aggregations = $this->getAggregations($request, $filters);

        foreach ($aggregations as $aggregation) {
            $criteria->addAggregation($aggregation);
        }

        foreach ($filters as $filter) {
            if ($filter->isFiltered()) {
                $criteria->addPostFilter($filter->getFilter());
            }
        }

        $criteria->addExtension('filters', $filters);
    }

    private function getAggregations(Request $request, FilterCollection $filters): array
    {
        $aggregations = [];

        if ($request->get('reduce-aggregations', null) === null) {
            foreach ($filters as $filter) {
                $aggregations = array_merge($aggregations, $filter->getAggregations());
            }

            return $aggregations;
        }

        foreach ($filters as $filter) {
            $excluded = $filters->filtered();

            if ($filter->exclude()) {
                $excluded = $excluded->blacklist($filter->getName());
            }

            foreach ($filter->getAggregations() as $aggregation) {
                if ($aggregation instanceof FilterAggregation) {
                    $aggregation->addFilters($excluded->getFilters());

                    $aggregations[] = $aggregation;

                    continue;
                }

                $aggregation = new FilterAggregation(
                    $aggregation->getName() . '-filtered',
                    $aggregation,
                    $excluded->getFilters()
                );

                $aggregations[] = $aggregation;
            }
        }

        return $aggregations;
    }

    private function getFilters(Request $request): FilterCollection
    {
        $filters = new FilterCollection();
        $filters->add($this->getCategoriesFilter($request));
        $filters->add($this->getAuthorsFilter($request));
        $filters->add($this->getTagsFilter($request));

        return $filters;
    }

    private function getCategoriesFilter(Request $request): Filter
    {
        $ids = $this->getFilterByCustomIds('categories', $request);

        return new Filter(
            'categories',
            !empty($ids),
            [
                new EntityAggregation('blogCategories', 'blogCategories.id', 'werkl_blog_category'),
            ],
            new EqualsAnyFilter('blogCategories.id', $ids),
            $ids
        );
    }

    private function getAuthorsFilter(Request $request): Filter
    {
        $ids = $this->getFilterByCustomIds('authors', $request);

        return new Filter(
            'authors',
            !empty($ids),
            [
                new EntityAggregation('authors', 'authorId', 'werkl_blog_author'),
            ],
            new EqualsAnyFilter('authorId', $ids),
            $ids
        );
    }

    private function getTagsFilter(Request $request): Filter
    {
        $ids = $this->getFilterByCustomIds('tags', $request);

        return new Filter(
            'tags',
            !empty($ids),
            [
                new EntityAggregation('tags', 'tags.id', 'tag'),
            ],
            new EqualsAnyFilter('tags.id', $ids),
            $ids
        );
    }

    private function getFilterByCustomIds(string $input, Request $request): array
    {
        $ids = $request->query->get($input, '');

        if ($request->isMethod(Request::METHOD_POST)) {
            $ids = $request->request->get($input, '');
        }

        if (!\is_string($ids)) {
            return [];
        }

        $ids = explode('|', $ids);

        return array_filter($ids);
    }
}
