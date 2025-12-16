<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase\Tests\Functional;

use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BaseBasilTestCase\ClientManager;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\SymfonyPantherWebServerRunner\Options;
use webignition\SymfonyPantherWebServerRunner\WebServerRunner;

class AbstractBaseTestTest extends AbstractBaseTest
{
    private const FIXTURES_RELATIVE_PATH = '/Fixtures';
    private const FIXTURES_HTML_RELATIVE_PATH = '/html';
    protected static ?string $webServerDir;
    protected static ?string $baseUri = '';

    private static WebServerRunner $webServerRunner;

    public static function setUpBeforeClass(): void
    {
        self::setClientManager(new ClientManager('chrome'));

        if (null === self::$baseUri) {
            self::$baseUri = Options::getBaseUri();
        }

        $webServerDir = __DIR__
            . '/..'
            . self::FIXTURES_RELATIVE_PATH
            . self::FIXTURES_HTML_RELATIVE_PATH;

        self::$webServerRunner = new WebServerRunner((string) realpath($webServerDir));
        self::$webServerRunner->start();

        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::stopWebServer();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // PHPUnit\Runner\BaseTestRunner::STATUS_PASSED === 0
        self::assertSame(0, $this->getStatus());
    }

    public function testClientIsInstantiated(): void
    {
        self::$client->request('GET', 'http://127.0.0.1:9080/index.html');

        self::assertSame('Test fixture title', self::$client->getTitle());
    }

    public function testCrawlerIsInstantiated(): void
    {
        $h1 = self::$crawler->filter('h1');

        self::assertSame('Test fixture h1 content', $h1->getText());
    }

    public function testNavigatorIsInstantiated(): void
    {
        $h1 = $this->navigator->findOne(new ElementIdentifier('h1'));

        self::assertSame('Test fixture h1 content', $h1->getText());
    }

    public function testInspectorInstantiated(): void
    {
        $input = $this->navigator->find(new ElementIdentifier('.input'));

        self::assertSame('initial value', self::$inspector->getValue($input));
    }

    public function testMutatorInstantiated(): void
    {
        $input = $this->navigator->find(new ElementIdentifier('.input'));
        self::assertSame('initial value', self::$inspector->getValue($input));

        self::$mutator->setValue($input, 'new value');
        self::assertSame('new value', self::$inspector->getValue($input));
    }

    public function testBooleanExaminedValue(): void
    {
        self::assertNull($this->getBooleanExaminedValue());

        $examinedValue = false;
        $this->setBooleanExaminedValue($examinedValue);

        self::assertSame($examinedValue, $this->getBooleanExaminedValue());
    }

    public function testBooleanExpectedValue(): void
    {
        self::assertNull($this->getBooleanExpectedValue());

        $expectedValue = false;
        $this->setBooleanExpectedValue($expectedValue);

        self::assertSame($expectedValue, $this->getBooleanExpectedValue());
    }

    public function testExaminedElementIdentifier(): void
    {
        self::assertNull($this->getExaminedElementIdentifier());

        $examinedElementIdentifier = new ElementIdentifier('.selector');
        $this->examinedElementIdentifier = $examinedElementIdentifier;

        self::assertSame($examinedElementIdentifier, $this->examinedElementIdentifier);
    }

    public function testExpectedElementIdentifier(): void
    {
        self::assertNull($this->getExpectedElementIdentifier());

        $expectedElementIdentifier = new ElementIdentifier('.selector');
        $this->expectedElementIdentifier = $expectedElementIdentifier;

        self::assertSame($expectedElementIdentifier, $this->expectedElementIdentifier);
    }

    public function testGetStatus(): void
    {
        // PHPUnit\Framework\TestStatus\Unknown()->asInt() === -1
        self::assertSame(-1, $this->getStatus());
    }

    private static function stopWebServer(): void
    {
        self::$webServerRunner->stop();
    }
}
