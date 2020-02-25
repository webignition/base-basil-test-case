<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase\Tests\Functional;

use PHPUnit\Runner\BaseTestRunner;
use webignition\BaseBasilTestCase\Statement;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\SymfonyPantherWebServerRunner\Options;
use webignition\SymfonyPantherWebServerRunner\WebServerRunner;

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
        $h1 = self::$crawler->filter('h1');

        $this->assertSame('Test fixture h1 content', $h1->getText());
    }

    public function testNavigatorIsInstantiated()
    {
        $h1 = $this->navigator->findOne(new ElementIdentifier('h1'));

        $this->assertSame('Test fixture h1 content', $h1->getText());
    }

    public function testInspectorInstantiated()
    {
        $input = $this->navigator->find(new ElementIdentifier('.input'));

        $this->assertSame('initial value', self::$inspector->getValue($input));
    }

    public function testMutatorInstantiated()
    {
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

    public function testCurrentStatement()
    {
        $this->assertNull($this->getCurrentStatement());

        $currentStatement = Statement::createAction('click $".selector"');
        $this->currentStatement = $currentStatement;

        $this->assertSame($currentStatement, $this->getCurrentStatement());
    }

    public function testCompletedStatements()
    {
        $this->assertSame([], $this->getCompletedStatements());

        $this->completedStatements[] = Statement::createAction('click $".selector"');
        $this->completedStatements[] = Statement::createAssertion('$page.url is "http://example.com"');
        $this->completedStatements[] = Statement::createAssertion('$page.title is "Page Title"');

        $this->assertSame(
            $this->completedStatements,
            $this->getCompletedStatements()
        );
    }

    public function testExaminedValue()
    {
        $this->assertNull($this->getExaminedValue());

        $examinedValue = 'examined value';
        $this->examinedValue = $examinedValue;

        $this->assertSame($examinedValue, $this->examinedValue);
    }

    public function testExpectedValue()
    {
        $this->assertNull($this->getExpectedValue());

        $expectedValue = 'expected value';
        $this->expectedValue = $expectedValue;

        $this->assertSame($expectedValue, $this->expectedValue);
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
