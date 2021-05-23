<?php

declare(strict_types=1);

namespace App\Eloquents\ValueObjects;

use JsonSerializable;

final class PermissionInfo implements JsonSerializable
{
    /**
     * 権限の識別名
     *
     * @var string
     */
    private $identifier;

    /**
     * 権限の表示名
     *
     * @var string
     */
    private $display_name;

    /**
     * 権限の短縮名
     *
     * @var string
     */
    private $display_short_name;

    /**
     * 権限の説明(HTML可)
     */
    private $description_html;

    public function __construct(
        string $identifier,
        string $display_name,
        string $display_short_name,
        string $description_html
    ) {
        $this->identifier = $identifier;
        $this->display_name = $display_name;
        $this->display_short_name = $display_short_name;
        $this->description_html = $description_html;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getDisplayName()
    {
        return $this->display_name;
    }

    public function getDisplayShortName()
    {
        return $this->display_short_name;
    }

    public function getDescriptionHtml()
    {
        return $this->description_html;
    }

    public function jsonSerialize()
    {
        return [
            'identifier' => $this->identifier,
            'display_name' => $this->display_name,
            'display_short_name' => $this->display_short_name,
            'description_html' => $this->description_html,
        ];
    }
}
