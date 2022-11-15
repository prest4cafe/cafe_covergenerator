<?php

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CoverGenerator extends ObjectModel
{
    public $id_cafe_covergenerator;
    public $id_blog;
    public $titre;
    public $path_image_mini;
    public $path_image_cover;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'cafe_covergenerator',
        'primary' => 'id_cafe_covergenerator',
        'multilang' => true,
        'fields' => [
            'id_cafe_covergenerator' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'length' => 10],
            'id_blog' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'length' => 10],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE,'validate' => 'isDate'],

            /* Lang fields */
            'titre' => ['type' => self::TYPE_STRING, 'validate' => 'isString','lang' => true,'required' => true,],
            'path_image_mini' => ['type' => self::TYPE_STRING, 'validate' => 'isString','lang' => true,],
            'path_image_cover' => ['type' => self::TYPE_STRING, 'validate' => 'isString','lang' => true,],
        ]
    ];

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);
    }

    public function add($autodate = true, $null_values = true)
    {
        $success = parent::add($autodate, $null_values);
        return $success;
    }

    public function update($nullValues = false)
    {
        return parent::update(true);
    }

    public function delete()
    {
        

        return parent::delete();
    }

    public static function installSql(): bool
    {
        try {
            $createTable = Db::getInstance()->execute(
                "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."cafe_covergenerator`(
                `id_cafe_covergenerator` int(10)  NOT NULL AUTO_INCREMENT,
                `id_blog` int(10) NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_cafe_covergenerator`)
                ) ENGINE=InnoDB DEFAULT CHARSET=UTF8;
                CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."cafe_covergenerator_lang`(
                    `id_cafe_covergenerator` int(10)  NOT NULL AUTO_INCREMENT,
                    `id_lang` VARCHAR (255),
                    `titre` VARCHAR (255) NULL,
                    `path_image_mini` VARCHAR (255) NULL,
                    `path_image_cover` VARCHAR (255) NULL,
                    PRIMARY KEY (`id_cafe_covergenerator`,`id_lang`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=UTF8;
                "
            );
        } catch (PrestaShopException $e) {
            return false;
        }

        return $createTable;
    }

    public static function uninstallSql()
    {
        return Db::getInstance()->execute("DROP TABLE IF EXISTS "._DB_PREFIX_."cafe_covergenerator,"._DB_PREFIX_."cafe_covergenerator_lang ");
    }


    public static function createMultiLangField($field)
    {
        $languages = Language::getLanguages(false);
        $res = array();
        foreach ($languages as $lang) {
            $res[$lang['id_lang']] = $field;
        }
        return $res;
    }
}
