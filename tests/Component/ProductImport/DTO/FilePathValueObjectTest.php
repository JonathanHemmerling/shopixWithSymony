<?php

declare(strict_types=1);

namespace App\Tests\Component\ProductImport\DTO;

use App\Component\ProductImport\DTO\FilePathValueObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function PHPUnit\Framework\assertSame;

class FilePathValueObjectTest extends TestCase
{
    public function testIfExceptionIsThrown():void
    {
        $this->expectException(RuntimeException::class);
        new FilePathValueObject(__DIR__ . 'asd');
    }
    public function testIfNoExceptionIsThrown():void
    {
        $filePath = new FilePathValueObject(__DIR__ . '/../MOCK_DATA.csv');
        assertSame(__DIR__ . '/../MOCK_DATA.csv', $filePath->filePath);
    }

}
