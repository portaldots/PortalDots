<?php

declare(strict_types=1);

namespace App\GridMakers\Filter;

use JsonSerializable;

class FilterableKeyBelongsToManyOptions implements JsonSerializable
{
    /**
     * @var string
     */
    private $pivot;

    /**
     * @var string
     */
    private $foreign_key;

    /**
     * @var string
     */
    private $related_key;

    /**
     * @var array
     */
    private $choices;

    /**
     * @var string
     */
    private $choices_name;

    public function __construct(
        string $pivot,
        string $foreign_key,
        string $related_key,
        array $choices,
        string $choices_name
    ) {
        $this->pivot = $pivot;
        $this->foreign_key = $foreign_key;
        $this->related_key = $related_key;
        $this->choices = $choices;
        $this->choices_name = $choices_name;
    }

    public function getPivot(): string
    {
        return $this->pivot;
    }

    public function getForeignKey(): string
    {
        return $this->foreign_key;
    }

    public function getRelatedKey(): string
    {
        return $this->related_key;
    }

    public function getChoices(): array
    {
        return $this->choices;
    }

    public function getChoicesName(): string
    {
        return $this->choices_name;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'pivot' => $this->pivot,
            'foreign_key' => $this->foreign_key,
            'related_key' => $this->related_key,
            'choices' => $this->choices,
            'choices_name' => $this->choices_name,
        ];
    }
}
