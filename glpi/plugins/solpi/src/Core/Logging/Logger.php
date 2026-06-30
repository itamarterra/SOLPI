<?php

declare(strict_types=1);

namespace SOLPI\Core\Logging;

use InvalidArgumentException;

final class Logger implements LoggerInterface
{
    private FileLogger $writer;

    private string $logFile;

    public function __construct(?string $logFile = null)
    {
        $this->writer = new FileLogger();

        $this->logFile = $logFile
            ?? GLPI_LOG_DIR . DIRECTORY_SEPARATOR . 'solpi.log';
    }

    public function emergency(string $message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert(string $message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice(string $message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function log(
        string $level,
        string $message,
        array $context = []
    ): void {

        if (!LogLevel::exists($level)) {
            throw new InvalidArgumentException(
                "Nível de log inválido: {$level}"
            );
        }

        $this->writer->write(
            $this->logFile,
            $level,
            $message,
            $context
        );
    }

    public function setLogFile(string $file): self
    {
        $this->logFile = $file;

        return $this;
    }

    public function logFile(): string
    {
        return $this->logFile;
    }
}