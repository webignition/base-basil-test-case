<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;
use webignition\BasilModels\Model\Assertion\Factory as AssertionFactory;
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
    private ?bool $booleanExaminedValue = null;
    private ?bool $booleanExpectedValue = null;
    private static ?ClientManager $clientManager = null;

    /**
     * @throws \Throwable
     */
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

    public function getStatus(): int
    {
        // @todo: fix in #161 by returning an instance of \PHPUnit\Framework\TestStatus\TestStatus
        return parent::status()->asInt();
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
