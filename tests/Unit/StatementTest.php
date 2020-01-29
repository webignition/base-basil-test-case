<?php

declare(strict_types=1);

namespace webignition\BaseBasilTestCase\Tests\Unit;

use PHPUnit\Framework\TestCase;
use webignition\BaseBasilTestCase\Statement;

class StatementTest extends TestCase
{
    public function testCreateAction()
    {
        $content = 'click $".selector"';

        $statement = Statement::createAction($content);

        $this->assertSame('action', $statement->getType());
        $this->assertSame($content, $statement->getContent());
    }

    public function testCreateAssertion()
    {
        $content = '$".selector" exists';

        $statement = Statement::createAssertion($content);

        $this->assertSame('assertion', $statement->getType());
        $this->assertSame($content, $statement->getContent());
    }
}
