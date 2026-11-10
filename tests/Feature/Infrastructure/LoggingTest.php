<?php

namespace Tests\Feature\Infrastructure;

use Illuminate\Support\Facades\Log;
use Monolog\Handler\StreamHandler;
use Tests\TestCase;

/**
 * Em containers (Render, Docker) o disco é efêmero e a plataforma coleta os
 * logs de stdout/stderr — por isso precisa existir um canal "stderr".
 */
final class LoggingTest extends TestCase
{
    public function test_stderr_channel_writes_to_the_process_stderr(): void
    {
        $handlers = Log::channel('stderr')->getLogger()->getHandlers();

        $this->assertCount(1, $handlers);
        $this->assertInstanceOf(StreamHandler::class, $handlers[0]);
        $this->assertSame('php://stderr', $handlers[0]->getUrl());
    }
}
