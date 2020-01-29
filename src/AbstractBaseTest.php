<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;
use webignition\SymfonyDomCrawlerNavigator\Navigator;
use webignition\WebDriverElementInspector\Inspector;
use webignition\WebDriverElementMutator\Mutator;

abstract class AbstractBaseTest extends TestCase implements BasilTestCaseInterface
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

    /**
     * @var string
     */
    private static $basilTestPath;

    /**
     * @var string
     */
    private $basilStepName;

    /**
     * @var StatementInterface|null
     */
    protected $currentStatement;

    /**
     * @var StatementInterface[]
     */
    protected $completedStatements = [];

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

    public static function setBasilTestPath(string $testPath): void
    {
        self::$basilTestPath = $testPath;
    }

    public function setBasilStepName(string $stepName): void
    {
        $this->basilStepName = $stepName;
    }

    public static function getBasilTestPath(): string
    {
        return self::$basilTestPath ?? '';
    }

    public function getBasilStepName(): string
    {
        return $this->basilStepName ?? '';
    }

    public function getCurrentStatement(): ?StatementInterface
    {
        return $this->currentStatement;
    }

    public function getCompletedStatements(): array
    {
        return $this->completedStatements;
    }
}
