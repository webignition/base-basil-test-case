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
    private static ?ClientManager $clientManager = null;

    public static function setUpBeforeClass(): void
    {
        self::$inspector = Inspector::create();
        self::$mutator = Mutator::create();

        if (null === self::$clientManager) {
            throw new \RuntimeException('Call self::setClientManager() first');
        }

        self::$client = self::$clientManager->getClient();
        $browserStartState = self::$clientManager->start();

        if (ClientManager::STATE_FAILED === $browserStartState) {
            self::$lastException = self::$clientManager->getLastException();
            self::fail('Browser failed to start: ' . self::$lastException->getMessage());
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

        $this->actionFactory = ActionFactory::createFactory();
        $this->assertionFactory = AssertionFactory::createFactory();
    }

    protected function refreshCrawlerAndNavigator(): void
    {
        self::$crawler = self::$client->refreshCrawler();
        $this->navigator = Navigator::create(self::$crawler);
    }

    public function setBasilStepName(string $stepName): void
    {
        $this->basilStepName = $stepName;
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

    public static function staticSetLastException(\Throwable $exception): void
    {
        self::$lastException = $exception;
    }

    public function setLastException(\Throwable $exception): void
    {
        self::$lastException = $exception;
    }

    public static function staticGetLastException(): ?\Throwable
    {
        return self::$lastException;
    }

    public function getLastException(): ?\Throwable
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

    public static function setClientManager(ClientManager $clientManager): void
    {
        self::$clientManager = $clientManager;
    }

    public static function hasException(): bool
    {
        return self::$lastException instanceof \Throwable;
    }
}
