<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;
use webignition\BaseBasilTestCase\Inspector\Inspector;
use webignition\BaseBasilTestCase\Message\Factory;
use webignition\BaseBasilTestCase\Mutator\Mutator;
use webignition\SymfonyDomCrawlerNavigator\Navigator;

abstract class AbstractBaseTest extends TestCase
{
    protected Navigator $navigator;
    protected static Inspector $inspector;
    protected static Mutator $mutator;
    protected static Factory $messageFactory;
    protected static Client $client;
    protected static Crawler $crawler;
    private static ?ClientManager $clientManager = null;

    /**
     * @throws \Throwable
     */
    public static function setUpBeforeClass(): void
    {
        self::$inspector = new Inspector();
        self::$mutator = new Mutator();
        self::$messageFactory = Factory::createFactory();

        if (null === self::$clientManager) {
            throw new \RuntimeException('Call self::setClientManager() first');
        }

        self::$client = self::$clientManager->getClient();
        $browserStartState = self::$clientManager->start();

        if (ClientManager::STATE_FAILED === $browserStartState) {
            $exception = self::$clientManager->getLastException();

            if ($exception instanceof \Throwable) {
                throw $exception;
            }
        }
    }

    public static function tearDownAfterClass(): void
    {
        self::$client->quit();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->refreshCrawlerAndNavigator();
    }

    public static function setClientManager(ClientManager $clientManager): void
    {
        self::$clientManager = $clientManager;
    }

    protected function refreshCrawlerAndNavigator(): void
    {
        self::$crawler = self::$client->refreshCrawler();
        $this->navigator = Navigator::create(self::$crawler);
    }
}
