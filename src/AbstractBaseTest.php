<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase;

use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\BaseTestRunner;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;
use webignition\BasilModels\Action\Factory as ActionFactory;
use webignition\BasilModels\Assertion\Factory as AssertionFactory;
use webignition\BasilModels\DataSet\DataSetInterface;
use webignition\BasilModels\StatementInterface;
use webignition\BasilModels\Test\ConfigurationInterface;
use webignition\DomElementIdentifier\ElementIdentifierInterface;
use webignition\SymfonyDomCrawlerNavigator\Navigator;
use webignition\WebDriverElementInspector\Inspector;
use webignition\WebDriverElementMutator\Mutator;

abstract class AbstractBaseTest extends TestCase implements BasilTestCaseInterface
{
    public const BROWSER_CHROME = 0;
    public const BROWSER_FIREFOX = 1;
    private const LABEL_CHROME = 'chrome';
    private const LABEL_FIREFOX = 'firefox';

    private const CLIENT_ID_MAP = [
        self::LABEL_FIREFOX => self::BROWSER_FIREFOX,
        self::LABEL_CHROME => self::BROWSER_CHROME,
    ];

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
    private static ?\Throwable $lastException = null;
    private ?DataSetInterface $currentDataSet = null;
    private static ?ConfigurationInterface $basilTestConfiguration = null;

    public static function setUpBeforeClass(): void
    {
        self::$inspector = Inspector::create();
        self::$mutator = Mutator::create();

        if (null === self::$basilTestConfiguration) {
            throw new \RuntimeException('Call self::setBasilTestConfiguration() first');
        }

        $browserLabel = self::$basilTestConfiguration->getBrowser();
        $clientId = self::CLIENT_ID_MAP[$browserLabel] ?? self::BROWSER_CHROME;

        if (self::BROWSER_FIREFOX === $clientId) {
            self::$client = Client::createFirefoxClient();
        } else {
            self::$client = Client::createChromeClient();
        }

        self::$client->start();
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

    public static function setLastException(\Throwable $exception): void
    {
        self::$lastException = $exception;
    }

    public static function getLastException(): ?\Throwable
    {
        return self::$lastException;
    }

    public function clearLastException(): void
    {
        self::$lastException = null;
    }

    public function setCurrentDataSet(?DataSetInterface $dataSet): void
    {
        $this->currentDataSet = $dataSet;
    }

    public function getCurrentDataSet(): ?DataSetInterface
    {
        return $this->currentDataSet;
    }

    public function getStatus(): int
    {
        return self::$lastException instanceof \Throwable
            ? BaseTestRunner::STATUS_FAILURE
            : parent::getStatus();
    }

    public static function setBasilTestConfiguration(ConfigurationInterface $configuration): void
    {
        self::$basilTestConfiguration = $configuration;
    }

    public static function getBasilTestConfiguration(): ?ConfigurationInterface
    {
        return self::$basilTestConfiguration;
    }
}
