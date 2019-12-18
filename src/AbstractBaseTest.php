<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;
use webignition\SymfonyDomCrawlerNavigator\Navigator;
use webignition\WebDriverElementInspector\Inspector;
use webignition\WebDriverElementMutator\Mutator;

abstract class AbstractBaseTest extends TestCase
{
    /**
     * @var Navigator
     */
    protected $navigator;

    /**
     * @var Inspector
     */
    protected static $inspector;

    /**
     * @var Mutator
     */
    protected static $mutator;

    /**
     * @var Client
     */
    protected static $client;

    /**
     * @var Crawler
     */
    protected static $crawler;

    public static function setUpBeforeClass(): void
    {
        self::$client = Client::createChromeClient();
        self::$client->start();

        self::$inspector = Inspector::create();
        self::$mutator = Mutator::create();
    }

    public static function tearDownAfterClass(): void
    {
        self::$client->quit();
    }

    protected function setUp(): void
    {
        parent::setUp();

        self::$crawler = self::$client->refreshCrawler();

        $this->navigator = Navigator::create(self::$crawler);
    }
}
