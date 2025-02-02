<?php

declare(strict_types=1);

namespace Tests\Qossmic\Deptrac\Subscriber;

use PHPUnit\Framework\TestCase;
use Qossmic\Deptrac\Ast\Event\PostCreateAstMapEvent;
use Qossmic\Deptrac\Ast\Event\PreCreateAstMapEvent;
use Qossmic\Deptrac\Ast\Parser\Cache\AstFileReferenceFileCache;
use Qossmic\Deptrac\Subscriber\CacheableFileSubscriber;

final class CacheableFileSubscriberTest extends TestCase
{
    public function testSubscribedEvents(): void
    {
        self::assertSame(
            [
                PreCreateAstMapEvent::class => 'onPreCreateAstMapEvent',
                PostCreateAstMapEvent::class => 'onPostCreateAstMapEvent',
            ],
            CacheableFileSubscriber::getSubscribedEvents()
        );
    }

    public function testOnPreCreateAstMapEvent(): void
    {
        $cache = $this->createMock(AstFileReferenceFileCache::class);
        $cache->expects(self::once())->method('load');

        (new CacheableFileSubscriber($cache))->onPreCreateAstMapEvent(new PreCreateAstMapEvent(1));
    }

    public function testOnPostCreateAstMapEvent(): void
    {
        $cache = $this->createMock(AstFileReferenceFileCache::class);
        $cache->expects(self::once())->method('write');

        (new CacheableFileSubscriber($cache))->onPostCreateAstMapEvent(new PostCreateAstMapEvent());
    }
}
