<?php

namespace EFive\Bale\Safir\Tests\Unit;

use EFive\Bale\Safir\Exceptions\CouldNotUploadInputFileException;
use EFive\Bale\Safir\FileUpload\InputFile;
use PHPUnit\Framework\TestCase;

final class InputFileTest extends TestCase
{
    public function testPathInputUsesAStreamAndIsMarkedForClosing(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'safir-upload-');
        self::assertNotFalse($path);

        try {
            file_put_contents($path, 'temporary upload');
            $file = InputFile::fromPath($path, 'payload.txt', 'text/plain');
            $contents = $file->getContents();

            self::assertTrue($file->shouldCloseContents());
            self::assertSame('payload.txt', $file->getFilename());
            self::assertSame('text/plain', $file->getMimeType());
            self::assertSame('temporary upload', (string) $contents);
            $contents->close();
        } finally {
            unlink($path);
        }
    }

    public function testNonexistentPathIsRejected(): void
    {
        $this->expectException(CouldNotUploadInputFileException::class);
        InputFile::fromPath(sys_get_temp_dir().'/safir-file-that-does-not-exist.txt');
    }

    public function testCallerOwnedStreamIsNotMarkedForClosing(): void
    {
        $stream = fopen('php://temp', 'r+');
        self::assertIsResource($stream);
        fwrite($stream, 'stream payload');
        rewind($stream);

        $file = InputFile::fromStream($stream, 'stream.txt');

        self::assertFalse($file->shouldCloseContents());
        self::assertSame('stream payload', stream_get_contents($file->getContents()));
        self::assertIsResource($stream);
        fclose($stream);
    }

    public function testFilenameCannotContainPathSeparatorsOrControlCharacters(): void
    {
        foreach (['../secret.txt', '..\\secret.txt', "file\r\nname.txt"] as $filename) {
            try {
                InputFile::fromContents('x', $filename);
                self::fail('Expected an unsafe filename to be rejected.');
            } catch (CouldNotUploadInputFileException) {
                self::assertTrue(true);
            }
        }
    }

    public function testMimeTypeCannotContainControlCharacters(): void
    {
        $this->expectException(CouldNotUploadInputFileException::class);
        InputFile::fromContents('x', 'safe.txt', "text/plain\r\nX-Injected: yes");
    }
}
