<?php

namespace EFive\Bale\Safir\Objects;

use InvalidArgumentException;
use JsonSerializable;

final class ReplyMarkup implements JsonSerializable
{
    /** @param array<array<InlineKeyboardButton|array>> $inlineKeyboard */
    public function __construct(private readonly array $inlineKeyboard)
    {
        foreach ($inlineKeyboard as $row) {
            if (! is_array($row)) {
                throw new InvalidArgumentException('Each inline keyboard row must be an array.');
            }

            foreach ($row as $button) {
                if (! $button instanceof InlineKeyboardButton && ! is_array($button)) {
                    throw new InvalidArgumentException('Inline keyboard buttons must be objects or arrays.');
                }
            }
        }
    }

    /**
     * Build from either an API-shaped array or the inline keyboard rows directly.
     *
     * @param array $data
     */
    public static function fromArray(array $data): self
    {
        $inlineKeyboard = $data['inline_keyboard'] ?? $data;

        if (! is_array($inlineKeyboard)) {
            throw new InvalidArgumentException('inline_keyboard must be an array of rows.');
        }

        return new self($inlineKeyboard);
    }

    /** @return array<array<InlineKeyboardButton|array>> */
    public function getInlineKeyboard(): array
    {
        return $this->inlineKeyboard;
    }

    public function toArray(): array
    {
        $rows = [];

        foreach ($this->inlineKeyboard as $row) {
            $rows[] = array_map(
                static fn (InlineKeyboardButton|array $button): array => $button instanceof InlineKeyboardButton
                    ? $button->toArray()
                    : $button,
                $row
            );
        }

        return ['inline_keyboard' => $rows];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
