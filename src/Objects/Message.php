<?php

namespace EFive\Bale\Safir\Objects;

use JsonSerializable;

final class Message implements JsonSerializable
{
    private readonly ?ReplyMarkup $replyMarkup;

    /**
     * @param ReplyMarkup|array|null $replyMarkup Inline markup object or API-shaped markup array.
     */
    public function __construct(
        private readonly ?string $text = null,
        private readonly ?string $fileId = null,
        ReplyMarkup|array|null $replyMarkup = null,
        private readonly ?string $copyText = null
    ) {
        $this->replyMarkup = is_array($replyMarkup) ? ReplyMarkup::fromArray($replyMarkup) : $replyMarkup;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function getFileId(): ?string
    {
        return $this->fileId;
    }

    public function getReplyMarkup(): ?ReplyMarkup
    {
        return $this->replyMarkup;
    }

    public function getCopyText(): ?string
    {
        return $this->copyText;
    }

    /** @return array{text?:string, file_id?:string, copy_text?:string, reply_markup?:array} */
    public function toArray(): array
    {
        $data = [];

        if ($this->text !== null) {
            $data['text'] = $this->text;
        }

        if ($this->fileId !== null) {
            $data['file_id'] = $this->fileId;
        }

        if ($this->copyText !== null) {
            $data['copy_text'] = $this->copyText;
        }

        if ($this->replyMarkup !== null) {
            $data['reply_markup'] = $this->replyMarkup->toArray();
        }

        return $data;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
