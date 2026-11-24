<?php

namespace EFive\Bale\Safir\FileUpload;

use EFive\Bale\Safir\Exceptions\CouldNotUploadInputFileException;
use GuzzleHttp\Psr7\LazyOpenStream;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Describes a file to send to Safir's multipart upload endpoint.
 */
final class InputFile
{
    private function __construct(
        private readonly string $kind,
        private readonly mixed $source,
        private readonly string $filename,
        private readonly ?string $mimeType = null
    ) {
    }

    public static function fromPath(string $path, ?string $filename = null, ?string $mimeType = null): self
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new CouldNotUploadInputFileException(sprintf('File "%s" does not exist or is not readable.', $path));
        }

        $filename ??= basename($path);
        self::validateFilename($filename);
        self::validateMimeType($mimeType);

        return new self('path', $path, $filename, $mimeType);
    }

    /**
     * @param resource $stream
     */
    public static function fromStream($stream, string $filename, ?string $mimeType = null): self
    {
        if (! is_resource($stream) || get_resource_type($stream) !== 'stream') {
            throw new CouldNotUploadInputFileException('InputFile::fromStream expects an open stream resource.');
        }

        self::validateFilename($filename);
        self::validateMimeType($mimeType);

        return new self('stream', $stream, $filename, $mimeType);
    }

    public static function fromContents(string $contents, string $filename, ?string $mimeType = null): self
    {
        self::validateFilename($filename);
        self::validateMimeType($mimeType);

        return new self('contents', $contents, $filename, $mimeType);
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    /**
     * @return StreamInterface|resource|string
     */
    public function getContents(): mixed
    {
        if ($this->kind === 'path') {
            try {
                return new LazyOpenStream($this->source, 'r');
            } catch (RuntimeException $exception) {
                throw new CouldNotUploadInputFileException('Could not open the file for upload.', 0, $exception);
            }
        }

        return $this->source;
    }

    public function shouldCloseContents(): bool
    {
        return $this->kind === 'path';
    }

    private static function validateFilename(string $filename): void
    {
        if (
            $filename === ''
            || str_contains($filename, '/')
            || str_contains($filename, '\\')
            || preg_match('/[\x00-\x1F\x7F]/', $filename) === 1
        ) {
            throw new CouldNotUploadInputFileException('A non-empty filename without path separators is required.');
        }
    }

    private static function validateMimeType(?string $mimeType): void
    {
        if ($mimeType !== null && ($mimeType === '' || preg_match('/[\x00-\x1F\x7F]/', $mimeType) === 1)) {
            throw new CouldNotUploadInputFileException('The MIME type must be non-empty and contain no control characters.');
        }
    }
}
