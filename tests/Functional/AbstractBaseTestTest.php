<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase\Tests\Functional;

use PHPUnit\Runner\BaseTestRunner;
use Symfony\Component\Panther\DomCrawler\Crawler;
use webignition\BaseBasilTestCase\ClientManager;
use webignition\BasilModels\Action\Action;
use webignition\BasilModels\Action\Factory as ActionFactory;
use webignition\BasilModels\Assertion\Assertion;
use webignition\BasilModels\Assertion\Factory as AssertionFactory;
use webignition\BasilModels\DataSet\DataSet;
use webignition\BasilModels\Test\Configuration;
use webignition\BasilModels\Test\ConfigurationInterface;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\SymfonyDomCrawlerNavigator\Navigator;
use webignition\SymfonyPantherWebServerRunner\Options;
use webignition\SymfonyPantherWebServerRunner\WebServerRunner;
use webignition\WebDriverElementInspector\Inspector;
use webignition\WebDriverElementMutator\Mutator;

class AbstractBaseTestTest extends \webignition\BaseBasilTestCase\AbstractBaseTest
{
    private const FIXTURES_RELATIVE_PATH = '/Fixtures';
    private const FIXTURES_HTML_RELATIVE_PATH = '/html';

    private static WebServerRunner $webServerRunner;
    protected static ?string $webServerDir;
    protected static ?string $baseUri = '';

    private static ConfigurationInterface $basilTestConfiguration;

    public static function setUpBeforeClass(): void
    {
        self::$basilTestConfiguration = new Configuration('chrome', 'http://example.com');
        self::setClientManager(new ClientManager(self::$basilTestConfiguration));

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

        static::stopWebServer();
    }

    public function testClientIsInstantiated()
    {
        self::$client->request('GET', 'http://127.0.0.1:9080/index.html');

        self::assertSame('Test fixture title', self::$client->getTitle());
    }

    public function testCrawlerIsInstantiated()
    {
        self::assertInstanceOf(Crawler::class, self::$crawler);
        $h1 = self::$crawler->filter('h1');

        self::assertSame('Test fixture h1 content', $h1->getText());
    }

    public function testNavigatorIsInstantiated()
    {
        self::assertInstanceOf(Navigator::class, $this->navigator);
        $h1 = $this->navigator->findOne(new ElementIdentifier('h1'));

        self::assertSame('Test fixture h1 content', $h1->getText());
    }

    public function testInspectorInstantiated()
    {
        self::assertInstanceOf(Inspector::class, self::$inspector);
        $input = $this->navigator->find(new ElementIdentifier('.input'));

        self::assertSame('initial value', self::$inspector->getValue($input));
    }

    public function testMutatorInstantiated()
    {
        self::assertInstanceOf(Mutator::class, self::$mutator);
        $input = $this->navigator->find(new ElementIdentifier('.input'));
        self::assertSame('initial value', self::$inspector->getValue($input));

        self::$mutator->setValue($input, 'new value');
        self::assertSame('new value', self::$inspector->getValue($input));
    }

    public function testBasilTestPath()
    {
        $basilTestPath = '/path/to/test.yml';

        self::setBasilTestPath($basilTestPath);

        self::assertSame($basilTestPath, self::getBasilTestPath());
    }

    public function testBasilStepName()
    {
        $stepName = 'step name';

        $this->setBasilStepName($stepName);

        self::assertSame($stepName, $this->getBasilStepName());
    }

    public function testHandledStatements()
    {
        self::assertSame([], $this->getHandledStatements());

        $this->handledStatements[] = $this->actionFactory->createFromJson(
            (string) json_encode(new Action(
                'click $".selector"',
                'click',
                '$".selector"',
                '$".selector"'
            ))
        );

        $this->handledStatements[] = $this->assertionFactory->createFromJson(
            (string) json_encode(new Assertion(
                '$".selector" is "value"',
                '$".selector"',
                'is',
                '"value"'
            ))
        );

        self::assertSame(
            $this->handledStatements,
            $this->getHandledStatements()
        );
    }

    public function testExaminedValue()
    {
        self::assertNull($this->getExaminedValue());

        $examinedValue = 'examined value';
        $this->setExaminedValue($examinedValue);

        self::assertSame($examinedValue, $this->getExaminedValue());

        $this->setExaminedValue(null);
        self::assertNull($this->getExaminedValue());
    }

    public function testExpectedValue()
    {
        self::assertNull($this->getExpectedValue());

        $expectedValue = 'expected value';
        $this->setExpectedValue($expectedValue);

        self::assertSame($expectedValue, $this->getExpectedValue());

        $this->setExpectedValue(null);
        self::assertNull($this->getExpectedValue());
    }

    public function testBooleanExaminedValue()
    {
        self::assertNull($this->getBooleanExaminedValue());

        $examinedValue = false;
        $this->setBooleanExaminedValue($examinedValue);

        self::assertSame($examinedValue, $this->getBooleanExaminedValue());
    }

    public function testBooleanExpectedValue()
    {
        self::assertNull($this->getBooleanExpectedValue());

        $expectedValue = false;
        $this->setBooleanExpectedValue($expectedValue);

        self::assertSame($expectedValue, $this->getBooleanExpectedValue());
    }

    public function testExaminedElementIdentifier()
    {
        self::assertNull($this->getExaminedElementIdentifier());

        $examinedElementIdentifier = new ElementIdentifier('.selector');
        $this->examinedElementIdentifier = $examinedElementIdentifier;

        self::assertSame($examinedElementIdentifier, $this->examinedElementIdentifier);
    }

    public function testExpectedElementIdentifier()
    {
        self::assertNull($this->getExpectedElementIdentifier());

        $expectedElementIdentifier = new ElementIdentifier('.selector');
        $this->expectedElementIdentifier = $expectedElementIdentifier;

        self::assertSame($expectedElementIdentifier, $this->expectedElementIdentifier);
    }

    public function testActionFactoryIsInstantiated()
    {
        self::assertInstanceOf(ActionFactory::class, $this->actionFactory);
    }

    public function testAssertionFactoryIsInstantiated()
    {
        self::assertInstanceOf(AssertionFactory::class, $this->assertionFactory);
    }

    public function testLastException()
    {
        self::assertNull(self::getLastException());

        $exception = new \Exception();

        self::staticSetLastException($exception);
        self::assertSame($exception, self::staticGetLastException());
        self::assertSame($exception, $this->getLastException());

        self::clearLastException();
        self::assertNull(self::staticGetLastException());
        self::assertNull($this->getLastException());
    }

    public function testCurrentDataSet()
    {
        self::assertNull($this->getCurrentDataSet());

        $dataSet = new DataSet('name', []);

        $this->setCurrentDataSet($dataSet);
        self::assertSame($dataSet, $this->getCurrentDataSet());

        $this->setCurrentDataSet(null);
        self::assertNull($this->getCurrentDataSet());
    }

    public function testGetStatus()
    {
        self::assertSame(BaseTestRunner::STATUS_UNKNOWN, $this->getStatus());

        $this->setLastException(new \Exception());
        self::assertSame(BaseTestRunner::STATUS_FAILURE, $this->getStatus());

        $this->clearLastException();
        self::assertSame(BaseTestRunner::STATUS_UNKNOWN, $this->getStatus());
    }

    public function testGetBasilTestConfiguration()
    {
        self::assertSame(self::$basilTestConfiguration, $this->getBasilTestConfiguration());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        self::assertSame(BaseTestRunner::STATUS_PASSED, $this->getStatus());
    }

    private static function stopWebServer(): void
    {
        self::$webServerRunner->stop();
    }
}
