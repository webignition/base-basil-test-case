<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;
use webignition\BasilModels\Action\Factory as ActionFactory;
use webignition\BasilModels\Assertion\Factory as AssertionFactory;
use webignition\BasilModels\DataSet\DataSetInterface;
use webignition\BasilModels\StatementInterface;
use webignition\DomElementIdentifier\ElementIdentifierInterface;
use webignition\SymfonyDomCrawlerNavigator\Navigator;
use webignition\WebDriverElementInspector\Inspector;
use webignition\WebDriverElementMutator\Mutator;

abstract class AbstractBaseTest extends TestCase implements BasilTestCaseInterface
{
    protected Navigator $navigator;
    protected static Inspector $inspector;
    protected static Mutator $mutator;
    protected static Client $client;
    protected static Crawler $crawler;
    private static string $basilTestPath;
    private string $basilStepName;

    /**
     * @var StatementInterface[]
     */
    protected array $handledStatements = [];

    private ?string $examinedValue = null;
    private ?string $expectedValue = null;
    private ?bool $booleanExaminedValue = null;
    private ?bool $booleanExpectedValue = null;
    protected ?ElementIdentifierInterface $examinedElementIdentifier = null;
    protected ?ElementIdentifierInterface $expectedElementIdentifier = null;
    protected ActionFactory $actionFactory;
    protected AssertionFactory $assertionFactory;
    private ?\Throwable $lastException = null;
    private ?DataSetInterface $currentDataSet = null;

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

        $this->refreshCrawlerAndNavigator();

        $this->actionFactory = ActionFactory::createFactory();
        $this->assertionFactory = AssertionFactory::createFactory();
    }

    protected function refreshCrawlerAndNavigator(): void
    {
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

    public function getHandledStatements(): array
    {
        return $this->handledStatements;
    }

    public function setExaminedValue(?string $examinedValue): void
    {
        $this->examinedValue = $examinedValue;
    }

    public function getExaminedValue(): ?string
    {
        return $this->examinedValue;
    }

    public function setExpectedValue(?string $expectedValue): void
    {
        $this->expectedValue = $expectedValue;
    }

    public function getExpectedValue(): ?string
    {
        return $this->expectedValue;
    }

    public function setBooleanExaminedValue(bool $examinedValue): void
    {
        $this->booleanExaminedValue = $examinedValue;
    }

    public function getBooleanExaminedValue(): ?bool
    {
        return $this->booleanExaminedValue;
    }

    public function setBooleanExpectedValue(bool $expectedValue): void
    {
        $this->booleanExpectedValue = $expectedValue;
    }

    public function getBooleanExpectedValue(): ?bool
    {
        return $this->booleanExpectedValue;
    }

    public function getExaminedElementIdentifier(): ?ElementIdentifierInterface
    {
        return $this->examinedElementIdentifier;
    }

    public function getExpectedElementIdentifier(): ?ElementIdentifierInterface
    {
        return $this->expectedElementIdentifier;
    }

    public function setLastException(\Throwable $exception): void
    {
        $this->lastException = $exception;
    }

    public function getLastException(): ?\Throwable
    {
        return $this->lastException;
    }

    public function setCurrentDataSet(DataSetInterface $dataSet): void
    {
        $this->currentDataSet = $dataSet;
    }

    public function getCurrentDataSet(): ?DataSetInterface
    {
        return $this->currentDataSet;
    }
}
