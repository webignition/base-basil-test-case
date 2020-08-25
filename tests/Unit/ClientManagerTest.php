<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase\Tests\Unit;

use Facebook\WebDriver\WebDriver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\ProcessManager\BrowserManagerInterface;
use Symfony\Component\Panther\ProcessManager\ChromeManager;
use Symfony\Component\Panther\ProcessManager\FirefoxManager;
use webignition\BaseBasilTestCase\ClientManager;
use webignition\BasilModels\Test\Configuration;
use webignition\BasilModels\Test\ConfigurationInterface;
use webignition\ObjectReflector\ObjectReflector;

class ClientManagerTest extends TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(ConfigurationInterface $configuration, string $expectedBrowserManagerClass)
    {
        $clientManager = new ClientManager($configuration);
        self::assertSame($configuration, $clientManager->getConfiguration());
        self::assertNull($clientManager->getLastException());

        $client = $clientManager->getClient();
        self::assertInstanceOf(Client::class, $client);
        self::assertInstanceOf($expectedBrowserManagerClass, $client->getBrowserManager());
    }

    public function createDataProvider(): array
    {
        return [
            'chrome' => [
                'configuration' => new Configuration('chrome', 'http://example.com'),
                'expectedBrowserManagerClass' => ChromeManager::class,
            ],
            'firefox' => [
                'configuration' => new Configuration('firefox', 'http://example.com'),
                'expectedBrowserManagerClass' => FirefoxManager::class,
            ],
            'unknown' => [
                'configuration' => new Configuration('unknown', 'http://example.com'),
                'expectedBrowserManagerClass' => ChromeManager::class,
            ],
        ];
    }

    /**
     * @dataProvider startDataProvider
     */
    public function testStart(
        ?int $startSuccessIteration,
        \Throwable $startFailureException,
        int $expectedBrowserStartState,
        ?\Throwable $expectedLastException,
        int $expectedFailedStartAttemptCount
    ) {
        $configuration = new Configuration('chrome', 'http://example.com');
        $clientManager = new ClientManager($configuration);

        $browserManager = \Mockery::mock(BrowserManagerInterface::class);
        $browserManager
            ->shouldReceive('quit');

        $webDriver = \Mockery::mock(WebDriver::class);
        $webDriver
            ->shouldReceive('quit');

        $browserManager
            ->shouldReceive('start')
            ->andReturnUsing(
                function () use ($startFailureException, $webDriver, $startSuccessIteration) {
                    static $counter = 0;
                    $counter++;

                    if ($counter === $startSuccessIteration) {
                        return $webDriver;
                    }

                    throw $startFailureException;
                }
            );

        $client = new Client($browserManager);

        ObjectReflector::setProperty($clientManager, ClientManager::class, 'client', $client);

        $browserStartState = $clientManager->start();

        $this->assertClientManagerPostStartState(
            $clientManager,
            $expectedBrowserStartState,
            $browserStartState,
            $expectedLastException,
            $expectedFailedStartAttemptCount
        );
    }

    public function startDataProvider(): array
    {
        $startFailureException = new \RuntimeException('Could not start chrome (or it crashed) after 30 seconds.');

        return [
            'succeeds on first try' => [
                'startSuccessIteration' => 1,
                'startFailureException' => $startFailureException,
                'expectedBrowserStartState' => ClientManager::STATE_SUCCEEDED,
                'expectedLastException' => null,
                'expectedFailedStartAttemptCount' => 0,
            ],
            'succeeds on second try' => [
                'startSuccessIteration' => 2,
                'startFailureException' => $startFailureException,
                'expectedBrowserStartState' => ClientManager::STATE_SUCCEEDED,
                'expectedLastException' => $startFailureException,
                'expectedFailedStartAttemptCount' => 1,
            ],
            'succeeds on third try' => [
                'startSuccessIteration' => 3,
                'startFailureException' => $startFailureException,
                'expectedBrowserStartState' => ClientManager::STATE_SUCCEEDED,
                'expectedLastException' => $startFailureException,
                'expectedFailedStartAttemptCount' => 2,
            ],
            'fails after third try' => [
                'startSuccessIteration' => null,
                'startFailureException' => $startFailureException,
                'expectedBrowserStartState' => ClientManager::STATE_FAILED,
                'expectedLastException' => $startFailureException,
                'expectedFailedStartAttemptCount' => 3,
            ],
        ];
    }

    private function assertClientManagerPostStartState(
        ClientManager $clientManager,
        int $expectedBrowserStartState,
        int $browserStartState,
        ?\Throwable $expectedLastException,
        int $expectedFailedStartAttemptCount
    ) {
        self::assertSame($expectedBrowserStartState, $browserStartState);
        self::assertSame($expectedLastException, $clientManager->getLastException());
        self::assertSame(
            $expectedFailedStartAttemptCount,
            ObjectReflector::getProperty($clientManager, 'failedStartAttemptCount')
        );
    }
}
