<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;
use webignition\BaseBasilTestCase\Mutator\Mutator;
use webignition\BasilModels\Model\Assertion\Factory as AssertionFactory;
use webignition\SymfonyDomCrawlerNavigator\Navigator;
use webignition\WebDriverElementInspector\Inspector;

abstract class AbstractBaseTest extends TestCase implements BasilTestCaseInterface
{
    protected Navigator $navigator;
    protected static Inspector $inspector;
    protected static Mutator $mutator;
    protected static Client $client;
    protected static Crawler $crawler;
    protected AssertionFactory $assertionFactory;
    private static ?ClientManager $clientManager = null;

    /**
     * @throws \Throwable
     */
    public static function setUpBeforeClass(): void
    {
        self::$inspector = Inspector::create();
        self::$mutator = new Mutator();

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

        $this->assertionFactory = AssertionFactory::createFactory();
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
