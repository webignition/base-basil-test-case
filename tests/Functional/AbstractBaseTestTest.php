<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase\Tests\Functional;

use webignition\BasePantherTestCase\Options;
use webignition\BasePantherTestCase\WebServerRunner;
use webignition\DomElementLocator\ElementLocator;

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
        $h1 = $this->navigator->findOne(new ElementLocator('h1'));

        $this->assertSame('Test fixture h1 content', $h1->getText());
    }

    public function testInspectorInstantiated()
    {
        $input = $this->navigator->find(new ElementLocator('.input'));

        $this->assertSame('initial value', self::$inspector->getValue($input));
    }

    public function testMutatorInstantiated()
    {
        $input = $this->navigator->find(new ElementLocator('.input'));
        $this->assertSame('initial value', self::$inspector->getValue($input));

        self::$mutator->setValue($input, 'new value');
        $this->assertSame('new value', self::$inspector->getValue($input));
    }

    private static function stopWebServer()
    {
        self::$webServerRunner->stop();
    }
}
