<?php
class BlogImageModel extends ObjectModel
{
    public static $definition = array(
        'table' => 'mdn_blog_image',
        'primary' => 'id',
        'multilang' => false,
        'multilang_shop' => false,
        'fields' => array(
            'id' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false),
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'lang' => true),
            'width' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'height' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false),
            'active' =>	array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
        ),
    );

    public $id;
    public $name;
    public $width;
    public $height;
    public $active;

    public function __construct($id_primario = null)
    {
        parent::__construct($id_primario);
    }

    public static function createContentTable()
    {
        $sq1 = 'CREATE TABLE IF NOT EXISTS `' . self::getTableName() . '`(
            `id` int(10) unsigned NOT NULL auto_increment,
            `width` int(10) unsigned NOT NULL  , 
            `height` int(10) unsigned NOT NULL,  
            `name` VARCHAR(256) NOT NULL,
            `active` int(1) NOT NULL, 
            PRIMARY KEY (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        $result = Db::getInstance()->execute($sq1);

        // Create default image sizes
        if(Db::getInstance()->getValue("SELECT 'x' FROM ".self::getTableName()." WHERE name = 'thumb'") == null) {
            Db::getInstance()->execute('INSERT INTO `' . self::getTableName() . '` (width, height, name, active) VALUES("384", "216", "thumb", 1)');
        }

        if(Db::getInstance()->getValue("SELECT 'x' FROM ".self::getTableName()." WHERE name = 'article'") == null) {
            Db::getInstance()->execute('INSERT INTO `' . self::getTableName() . '` (width, height, name, active) VALUES("700", "393", "article", 1)');
        }

        return $result;
    }

    static function getTableName() {
        return _DB_PREFIX_ . self::$definition['table'];
    }

    /**
     * @return BlogImageModel[]
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    static function getAllImageSizes() {
        return array_map(
            function ($v) {
                return new BlogImageModel($v['id']);
            },
            Db::getInstance()->executeS("SELECT id FROM `".self::getTableName()."`WHERE active = '1'")
        );
    }
}
