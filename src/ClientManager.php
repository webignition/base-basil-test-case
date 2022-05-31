<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase;

use Symfony\Component\Panther\Client;
use webignition\BasilModels\Model\Test\ConfigurationInterface;

class ClientManager
{
    public const STATE_UNKNOWN = 0;
    public const STATE_SUCCEEDED = 1;
    public const STATE_FAILED = 2;
    public const START_ATTEMPT_LIMIT = 3;

    private const BROWSER_CHROME = 0;
    private const BROWSER_FIREFOX = 1;
    private const LABEL_CHROME = 'chrome';
    private const LABEL_FIREFOX = 'firefox';

    private const CLIENT_ID_MAP = [
        self::LABEL_FIREFOX => self::BROWSER_FIREFOX,
        self::LABEL_CHROME => self::BROWSER_CHROME,
    ];

    private ?\Throwable $lastException = null;
    private ConfigurationInterface $configuration;
    private Client $client;
    private int $failedStartAttemptCount = 0;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;

        $browserLabel = $this->configuration->getBrowser();
        $clientId = self::CLIENT_ID_MAP[$browserLabel] ?? self::BROWSER_CHROME;

        if (self::BROWSER_FIREFOX === $clientId) {
            $this->client = Client::createFirefoxClient();
        } else {
            $this->client = Client::createChromeClient();
        }
    }

    public function getConfiguration(): ConfigurationInterface
    {
        return $this->configuration;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function start(): int
    {
        $browserStartState = self::STATE_UNKNOWN;

        while (
            self::STATE_UNKNOWN === $browserStartState
            && false === $this->hasStartAttemptLimitBeenReached()
        ) {
            try {
                $this->client->start();
                $browserStartState = self::STATE_SUCCEEDED;
            } catch (\Throwable $exception) {
                $this->lastException = $exception;

                if (false === $this->isBrowserStartException($exception)) {
                    $browserStartState = self::STATE_FAILED;
                } else {
                    ++$this->failedStartAttemptCount;
                }
            }
        }

        if ($this->hasStartAttemptLimitBeenReached()) {
            $browserStartState = self::STATE_FAILED;
        }

        return $browserStartState;
    }

    public function getLastException(): ?\Throwable
    {
        return $this->lastException;
    }

    private function isBrowserStartException(\Throwable $exception): bool
    {
        $message = $exception->getMessage();

        if (substr_count($message, 'failed to start') > 0) {
            return true;
        }

        if (substr_count($message, 'Could not start') > 0) {
            return true;
        }

        return false;
    }

    private function hasStartAttemptLimitBeenReached(): bool
    {
        return self::START_ATTEMPT_LIMIT === $this->failedStartAttemptCount;
    }
}
