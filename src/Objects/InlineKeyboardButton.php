<?php

namespace EFive\Bale\Safir\Objects;

use JsonSerializable;

final class InlineKeyboardButton implements JsonSerializable
{
    /**
     * @param array{url?:string}|null $webApp
     */
    public function __construct(
        private readonly string $text,
        private readonly ?string $url = null,
        private readonly ?array $webApp = null,
        private readonly ?string $copyText = null
    ) {
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    /** @return array{url?:string}|null */
    public function getWebApp(): ?array
    {
        return $this->webApp;
    }

    public function getCopyText(): ?string
    {
        return $this->copyText;
    }

    /** @return array{text:string, url?:string, web_app?:array, copy_text?:string} */
    public function toArray(): array
    {
        $data = ['text' => $this->text];

        if ($this->url !== null) {
            $data['url'] = $this->url;
        }

        if ($this->webApp !== null) {
            $data['web_app'] = $this->webApp;
        }

        if ($this->copyText !== null) {
            $data['copy_text'] = $this->copyText;
        }

        return $data;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
