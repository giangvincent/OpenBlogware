<?php
declare(strict_types=1);

namespace Werkl\OpenBlogware\Controller;

use Shopware\Core\Framework\Adapter\Cache\CacheValueCompressor;
use Shopware\Core\Framework\DataAbstractionLayer\Cache\EntityCacheKeyGenerator;
use Shopware\Core\Framework\Util\Json;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Shopware\Core\Framework\Event\AddCacheTags;

/**
 * Handle Cache for BlogController
 */
#[\Symfony\Component\Routing\Attribute\Route(defaults: ['_routeScope' => ['storefront']])]
class CachedBlogController extends StorefrontController
{
    public function __construct(
        private readonly BlogController $decorated,
        private readonly CacheInterface $cache,
        private readonly EntityCacheKeyGenerator $generator,
        private readonly EventDispatcherInterface $eventDispatcher
    )
    {
    }

    public static function buildName(string $articleId): string
    {
        return 'werkl-blog-detail-' . $articleId;
    }

    #[\Symfony\Component\Routing\Attribute\Route(path: '/werkl_blog/{articleId}', name: 'werkl.frontend.blog.detail', methods: ['GET'])]
    public function detailAction(Request $request, SalesChannelContext $context): Response
    {
        $articleId = $request->attributes->get('articleId');
        $key = $this->generateKey($articleId, $context);

        $value = $this->cache->get($key, function (ItemInterface $item) use ($articleId, $request, $context) {
            $response = $this->decorated->detailAction($request, $context);

            $item->tag($this->generateTags($articleId));

            return CacheValueCompressor::compress($response);
        });

        return CacheValueCompressor::uncompress($value);
    }

    private function generateKey(string $articleId, SalesChannelContext $context): string
    {
        $parts = [
            $this->generator->getSalesChannelContextHash($context),
        ];

        return self::buildName($articleId) . '-' . md5(Json::encode($parts));
    }

    /**
     * @return array<string>
     */
    private function generateTags(string $articleId): array
    {
        $tags = [self::buildName($articleId)];
        
        // Use the new event system for collecting cache tags
        $event = new AddCacheTags();
        $name = self::buildName($articleId);
        
        $this->eventDispatcher->dispatch($event, $name);
        
        if (method_exists($event, 'getTags')) {
            $tags = array_merge($event->getTags(), $tags);
        }

        return array_unique(array_filter($tags));
    }
}
