<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;
use webignition\BasilModels\Model\Assertion\Factory as AssertionFactory;
use webignition\BasilModels\Model\DataSet\DataSetInterface;
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
    protected ?ElementIdentifierInterface $examinedElementIdentifier = null;
    protected ?ElementIdentifierInterface $expectedElementIdentifier = null;
    protected AssertionFactory $assertionFactory;
    private ?string $expectedValue = null;
    private ?bool $booleanExaminedValue = null;
    private ?bool $booleanExpectedValue = null;
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
        }
    }

    public static function tearDownAfterClass(): void
    {
        self::$client->quit();
    }

    protected function setUp(): void
    {
        if (self::hasException()) {
            return;
        }

        parent::setUp();

        $this->refreshCrawlerAndNavigator();

        $this->assertionFactory = AssertionFactory::createFactory();
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
        // @todo: fix in #151 by returning an instance of \PHPUnit\Framework\TestStatus\TestStatus
        return self::$lastException instanceof \Throwable
            ? 3 // value of now-removed PHPUnit\Runner\BaseTestRunner::STATUS_FAILURE
            : parent::status()->asInt();
    }

    public static function setClientManager(ClientManager $clientManager): void
    {
        self::$clientManager = $clientManager;
    }

    public static function hasException(): bool
    {
        return self::$lastException instanceof \Throwable;
    }

    protected function refreshCrawlerAndNavigator(): void
    {
        self::$crawler = self::$client->refreshCrawler();
        $this->navigator = Navigator::create(self::$crawler);
    }
}
