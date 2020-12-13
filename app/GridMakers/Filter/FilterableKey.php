<?php

declare(strict_types=1);

namespace App\GridMakers\Filter;

use BadMethodCallException;
use InvalidArgumentException;
use JsonSerializable;

class FilterableKey implements JsonSerializable
{
    public const TYPE_STRING = 'string';
    public const TYPE_NUMBER = 'number';
    public const TYPE_DATETIME = 'datetime';
    public const TYPE_BOOL = 'bool';
    public const TYPE_IS_NULL = 'isNull';
    public const TYPE_BELONGS_TO = 'belongsTo';
    public const TYPE_BELONGS_TO_MANY = 'belongsToMany';
    public const TYPE_ENUM = 'enum';

    public const TYPES = [
        self::TYPE_STRING,
        self::TYPE_NUMBER,
        self::TYPE_DATETIME,
        self::TYPE_BOOL,
        self::TYPE_IS_NULL,
        self::TYPE_BELONGS_TO,
        self::TYPE_BELONGS_TO_MANY,
        self::TYPE_ENUM,
    ];

    /**
     * @var string
     */
    private $type;

    /**
     * @var FilterableKeyBelongsToOptions
     */
    private $belongsToOptions;

    /**
     * @var FilterableKeyBelongsToManyOptions
     */
    private $belongsToManyOptions;

    /**
     * @var array
     */
    private $enumChoices;

    private function __construct(string $type, $options = null)
    {
        if (empty($type) || !in_array($type, self::TYPES, true)) {
            throw new InvalidArgumentException('不正な type です。');
        }

        $this->type = $type;

        if ($type === self::TYPE_BELONGS_TO && !$options instanceof FilterableKeyBelongsToOptions) {
            throw new InvalidArgumentException(FilterableKeyBelongsToOptions::class . 'オブジェクトが指定されていません。');
        } else {
            $this->belongsToOptions = $options;
        }

        if ($type === self::TYPE_BELONGS_TO_MANY && !$options instanceof FilterableKeyBelongsToManyOptions) {
            throw new InvalidArgumentException(FilterableKeyBelongsToManyOptions::class . 'オブジェクトが指定されていません。');
        } else {
            $this->belongsToManyOptions = $options;
        }

        if ($type === self::TYPE_ENUM && (empty($options) || !is_array($options))) {
            throw new InvalidArgumentException('配列が指定されていません。');
        } else {
            $this->enumChoices = $options;
        }
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * string 列。文字列検索ができる
     */
    public static function string()
    {
        return new self(self::TYPE_STRING);
    }

    /**
     * 数値列。大小比較ができる
     */
    public static function number()
    {
        return new self(self::TYPE_NUMBER);
    }

    /**
     * 日時列。過去・未来比較ができる
     */
    public static function datetime()
    {
        return new self(self::TYPE_DATETIME);
    }

    /**
     * boolean 列。はいかいいえかで検索できる
     */
    public static function bool()
    {
        return new self(self::TYPE_BOOL);
    }

    /**
     * null か null でないかで検索できる
     */
    public static function isNull()
    {
        return new self(self::TYPE_IS_NULL);
    }

    /**
     * belongsTo を使用している列
     *
     * @param string $to belongsTo先テーブル名
     * @param FilterableKeysDict $keys belongsTo先テーブルにおけるフィルタ可能キー
     */
    public static function belongsTo(string $to, FilterableKeysDict $keys)
    {
        return new self(self::TYPE_BELONGS_TO, new FilterableKeyBelongsToOptions($to, $keys));
    }

    /**
     * belongsToMany を使用している列
     *
     * @param string $pivot belongsToManyに利用している中間テーブル名
     * @param string $foreign_key pivotテーブルにおける、自分側を表すidのカラム名
     * @param string $related_key pivotテーブルにおける、リレーション先を表すidのカラム名
     * @param array $choices 画面上に選択肢として表示する連想配列の配列。配列の各要素はidを含む必要がある
     * @param string $choices_name $choices連想配列内のキーのうち、画面上に選択肢として表示するもの
     */
    public static function belongsToMany(
        string $pivot,
        string $foreign_key,
        string $related_key,
        array $choices,
        string $choices_name
    ) {
        return new self(self::TYPE_BELONGS_TO_MANY, new FilterableKeyBelongsToManyOptions(
            $pivot,
            $foreign_key,
            $related_key,
            $choices,
            $choices_name
        ));
    }

    /**
     * circles.status の rejected / approved / NULL のような類の値。
     * 大文字の "NULL" という文字列をキーとした場合、is null クエリが発行される
     *
     * @var array $choices
     */
    public static function enum($choices)
    {
        return new self(self::TYPE_ENUM, $choices);
    }

    public function jsonSerialize()
    {
        $base_array = [
            'type' => $this->type
        ];

        switch ($this->type) {
            case self::TYPE_BELONGS_TO:
                return array_merge($base_array, $this->belongsToOptions->jsonSerialize());
            case self::TYPE_BELONGS_TO_MANY:
                return array_merge($base_array, $this->belongsToManyOptions->jsonSerialize());
            case self::TYPE_ENUM:
                return array_merge($base_array, ['choices' => $this->enumChoices]);
            default:
                return $base_array;
        }
    }

    public function getBelongsToOptions(): FilterableKeyBelongsToOptions
    {
        if ($this->type !== self::TYPE_BELONGS_TO) {
            throw new BadMethodCallException('type が belongsTo でないため、このメソッドを呼び出すことはできません。');
        }

        return $this->belongsToOptions;
    }

    public function getBelongsToManyOptions(): FilterableKeyBelongsToManyOptions
    {
        if ($this->type !== self::TYPE_BELONGS_TO_MANY) {
            throw new BadMethodCallException('type が belongsToMany でないため、このメソッドを呼び出すことはできません。');
        }

        return $this->belongsToManyOptions;
    }

    public function getEnumChoices(): array
    {
        if ($this->type !== self::TYPE_ENUM) {
            throw new BadMethodCallException('type が enum でないため、このメソッドを呼び出すことはできません。');
        }

        return $this->enumChoices;
    }
}
