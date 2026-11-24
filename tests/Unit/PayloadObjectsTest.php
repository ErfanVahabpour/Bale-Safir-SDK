<?php

namespace EFive\Bale\Safir\Tests\Unit;

use EFive\Bale\Safir\Objects\InlineKeyboardButton;
use EFive\Bale\Safir\Objects\Message;
use EFive\Bale\Safir\Objects\MessageData;
use EFive\Bale\Safir\Objects\OTPMessage;
use EFive\Bale\Safir\Objects\ReplyMarkup;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PayloadObjectsTest extends TestCase
{
    public function testMessagePayloadObjectsSerializeSafirFieldNames(): void
    {
        $markup = new ReplyMarkup([
            [new InlineKeyboardButton('Open Bale', url: 'https://bale.ai')],
        ]);
        $data = new MessageData(
            message: new Message(text: 'Hello', fileId: 'file-123', replyMarkup: $markup, copyText: 'Copy this'),
            isSecure: true
        );
        $expected = [
            'message' => [
                'text' => 'Hello',
                'file_id' => 'file-123',
                'copy_text' => 'Copy this',
                'reply_markup' => [
                    'inline_keyboard' => [[['text' => 'Open Bale', 'url' => 'https://bale.ai']]],
                ],
            ],
            'is_secure' => true,
        ];

        self::assertSame($expected, $data->toArray());
        self::assertSame($expected, json_decode(json_encode($data, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR));
    }

    public function testOtpMessagePreservesStringDigitsAndSerializes(): void
    {
        $otpMessage = new OTPMessage('001234');

        self::assertSame('001234', $otpMessage->getOtp());
        self::assertSame(['otp' => '001234'], $otpMessage->toArray());
        self::assertSame(['otp' => '001234'], json_decode(json_encode($otpMessage, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR));
    }

    public function testOtpMessageRejectsNonNumericCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new OTPMessage('12AB34');
    }

    public function testReplyMarkupBuildsFromApiShapedArray(): void
    {
        $markup = ReplyMarkup::fromArray([
            'inline_keyboard' => [[['text' => 'Open Bale', 'url' => 'https://bale.ai']]],
        ]);

        self::assertSame([
            'inline_keyboard' => [[['text' => 'Open Bale', 'url' => 'https://bale.ai']]],
        ], $markup->toArray());
    }
}
