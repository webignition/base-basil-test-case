<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase\Tests\Functional;

use PHPUnit\Runner\BaseTestRunner;
use Symfony\Component\Panther\DomCrawler\Crawler;
use webignition\BasilModels\Action\Factory\Factory as ActionFactory;
use webignition\BasilModels\Action\InteractionAction;
use webignition\BasilModels\Assertion\ComparisonAssertion;
use webignition\BasilModels\Assertion\Factory\Factory as AssertionFactory;
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

    /**
     * @var WebServerRunner
     */
    private static $webServerRunner;

    /**
     * @var string|null
     */
    protected static $webServerDir;

    /**
     * @var string|null
     */
    protected static $baseUri;

    public static function setUpBeforeClass(): void
    {
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

        $this->assertSame('Test fixture title', self::$client->getTitle());
    }

    public function testCrawlerIsInstantiated()
    {
        $this->assertInstanceOf(Crawler::class, self::$crawler);
        $h1 = self::$crawler->filter('h1');

        $this->assertSame('Test fixture h1 content', $h1->getText());
    }

    public function testNavigatorIsInstantiated()
    {
        $this->assertInstanceOf(Navigator::class, $this->navigator);
        $h1 = $this->navigator->findOne(new ElementIdentifier('h1'));

        $this->assertSame('Test fixture h1 content', $h1->getText());
    }

    public function testInspectorInstantiated()
    {
        $this->assertInstanceOf(Inspector::class, self::$inspector);
        $input = $this->navigator->find(new ElementIdentifier('.input'));

        $this->assertSame('initial value', self::$inspector->getValue($input));
    }

    public function testMutatorInstantiated()
    {
        $this->assertInstanceOf(Mutator::class, self::$mutator);
        $input = $this->navigator->find(new ElementIdentifier('.input'));
        $this->assertSame('initial value', self::$inspector->getValue($input));

        self::$mutator->setValue($input, 'new value');
        $this->assertSame('new value', self::$inspector->getValue($input));
    }

    public function testBasilTestPath()
    {
        $basilTestPath = '/path/to/test.yml';

        self::setBasilTestPath($basilTestPath);

        $this->assertSame($basilTestPath, self::getBasilTestPath());
    }

    public function testBasilStepName()
    {
        $stepName = 'step name';

        $this->setBasilStepName($stepName);

        $this->assertSame($stepName, $this->getBasilStepName());
    }

    public function testHandledStatements()
    {
        $this->assertSame([], $this->getHandledStatements());

        $this->handledStatements[] = $this->actionFactory->createFromJson(
            (string) json_encode(new InteractionAction(
                'click $".selector"',
                'click',
                '$".selector"',
                '$".selector"'
            ))
        );

        $this->handledStatements[] = $this->assertionFactory->createFromJson(
            (string) json_encode(new ComparisonAssertion(
                '$".selector" is "value"',
                '$".selector"',
                'is',
                '"value"'
            ))
        );

        $this->assertSame(
            $this->handledStatements,
            $this->getHandledStatements()
        );
    }

    public function testExaminedValue()
    {
        $this->assertNull($this->getExaminedValue());

        $examinedValue = 'examined value';
        $this->setExaminedValue($examinedValue);

        $this->assertSame($examinedValue, $this->getExaminedValue());

        $this->setExaminedValue(null);
        $this->assertNull($this->getExaminedValue());
    }

    public function testExpectedValue()
    {
        $this->assertNull($this->getExpectedValue());

        $expectedValue = 'expected value';
        $this->setExpectedValue($expectedValue);

        $this->assertSame($expectedValue, $this->getExpectedValue());

        $this->setExpectedValue(null);
        $this->assertNull($this->getExpectedValue());
    }

    public function testBooleanExaminedValue()
    {
        $this->assertNull($this->getBooleanExaminedValue());

        $examinedValue = false;
        $this->setBooleanExaminedValue($examinedValue);

        $this->assertSame($examinedValue, $this->getBooleanExaminedValue());
    }

    public function testBooleanExpectedValue()
    {
        $this->assertNull($this->getBooleanExpectedValue());

        $expectedValue = false;
        $this->setBooleanExpectedValue($expectedValue);

        $this->assertSame($expectedValue, $this->getBooleanExpectedValue());
    }

    public function testExaminedElementIdentifier()
    {
        $this->assertNull($this->getExaminedElementIdentifier());

        $examinedElementIdentifier = new ElementIdentifier('.selector');
        $this->examinedElementIdentifier = $examinedElementIdentifier;

        $this->assertSame($examinedElementIdentifier, $this->examinedElementIdentifier);
    }

    public function testExpectedElementIdentifier()
    {
        $this->assertNull($this->getExpectedElementIdentifier());

        $expectedElementIdentifier = new ElementIdentifier('.selector');
        $this->expectedElementIdentifier = $expectedElementIdentifier;

        $this->assertSame($expectedElementIdentifier, $this->expectedElementIdentifier);
    }
    public function testActionFactoryIsInstantiated()
    {
        $this->assertInstanceOf(ActionFactory::class, $this->actionFactory);
    }

    public function testAssertionFactoryIsInstantiated()
    {
        $this->assertInstanceOf(AssertionFactory::class, $this->assertionFactory);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->assertSame(BaseTestRunner::STATUS_PASSED, $this->getStatus());
    }

    private static function stopWebServer(): void
    {
        self::$webServerRunner->stop();
    }
}
