<?php
/**
 * Product Features module for PrestaShop.
 *
 * @author    APLINE Arkadiusz Pielechowski
 * @copyright APLINE Arkadiusz Pielechowski
 * @license   Custom Attribution License v1.0 - see LICENSE.md
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class ProductFeatureItem extends ObjectModel
{
    /** @var string */
    public $image;
    /** @var string */
    public $icon;
    /** @var string */
    public $alt;
    /** @var string */
    public $text;
    /** @var string */
    public $url;
    /** @var bool */
    public $new_tab;
    /** @var bool */
    public $active;
    /** @var int */
    public $position;
    /** @var string */
    public $date_add;
    /** @var string */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'apline_productfeature',
        'primary' => 'id_apline_productfeature',
        'multilang' => false,
        'fields' => [
            'image' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255],
            'icon' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255],
            'alt' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255],
            'text' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255],
            'url' => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255],
            'new_tab' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    /**
     * Active items ordered by position, for front rendering.
     * Guarded so a missing table never breaks the shop front.
     *
     * @return array
     */
    public static function getActiveItems()
    {
        try {
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'apline_productfeature`
                WHERE `active` = 1
                ORDER BY `position` ASC, `id_apline_productfeature` ASC';

            $result = Db::getInstance()->executeS($sql);

            return is_array($result) ? $result : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * @return int next free position
     */
    public static function getNextPosition()
    {
        try {
            $max = (int) Db::getInstance()->getValue(
                'SELECT MAX(`position`) FROM `' . _DB_PREFIX_ . 'apline_productfeature`'
            );

            return $max + 1;
        } catch (\Throwable $e) {
            return 1;
        }
    }

    /**
     * @see ObjectModel::add()
     */
    public function add($auto_date = true, $null_values = false)
    {
        if (empty($this->position)) {
            $this->position = self::getNextPosition();
        }

        return parent::add($auto_date, $null_values);
    }
}
