<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;
use webignition\BasilModels\Action\Factory\Factory as ActionFactory;
use webignition\BasilModels\Assertion\Factory\Factory as AssertionFactory;
use webignition\DomElementIdentifier\ElementIdentifierInterface;
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
     * @var StatementInterface[]
     */
    protected $handledStatements = [];

    /**
     * @var string|null
     */
    protected $examinedValue;

    /**
     * @var string|null
     */
    protected $expectedValue;

    /**
     * @var ElementIdentifierInterface|null
     */
    protected $examinedElementIdentifier;

    /**
     * @var ElementIdentifierInterface|null
     */
    protected $expectedElementIdentifier;

    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @var AssertionFactory
     */
    protected $assertionFactory;

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
        $this->actionFactory = ActionFactory::createFactory();
        $this->assertionFactory = AssertionFactory::createFactory();
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

    public function getHandledStatements(): array
    {
        return $this->handledStatements;
    }

    public function getExaminedValue()
    {
        return $this->examinedValue;
    }

    public function getExpectedValue(): ?string
    {
        return $this->expectedValue;
    }

    public function getExaminedElementIdentifier(): ?ElementIdentifierInterface
    {
        return $this->examinedElementIdentifier;
    }

    public function getExpectedElementIdentifier(): ?ElementIdentifierInterface
    {
        return $this->expectedElementIdentifier;
    }
}
