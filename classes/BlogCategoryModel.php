<?php
require_once _PS_MODULE_DIR_ . '/mdn_blog/classes/BlogArticleModel.php';
class BlogCategoryModel extends ObjectModel
{
    public static $definition = array(
        'table' => 'mdn_blog_category',
        'primary' => 'id',
        'multilang' => true,
        'multilang_shop' => true,
        'fields' => array(
            'id' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false),
            'root' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
            'parent_id' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false),
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'lang' => true),
            'slug' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'lang' => true),
            'description' => array('type' => self::TYPE_HTML, 'validate' => 'isString', 'required' => false, 'lang' => true),
            'active' =>       array('type' => self::TYPE_BOOL,    'validate' => 'isUnsignedInt', 'required' => false),

            // meta
            'meta_title' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'lang' => true),
            'meta_description' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'lang' => true),
            'meta_keywords' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'lang' => true),
        ),
    );

    public $id;
    public $parent_id;
    public $root;
    public $name;
    public $slug;
    public $description;
    public $active;
    public $meta_title;
    public $meta_description;
    public $meta_keywords;

    public function __construct($id_primario = null, $id_lang = null)
    {
        parent::__construct($id_primario, $id_lang);
    }

    public static function createContentTable()
    {
        $sq1 = 'CREATE TABLE IF NOT EXISTS `' . self::getTableName() . '`(
            `id` int(10) unsigned NOT NULL auto_increment,
            `id_shop` int(10) unsigned NOT NULL  , 
            `root` int(10) unsigned NOT NULL,  
            `parent_id` int(10) unsigned,  
            `active` int(1) NOT NULL, 
            PRIMARY KEY (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        $sq3 = 'CREATE TABLE IF NOT EXISTS `' . self::getTableName() . '_lang`(
            `id` int(10) unsigned NOT NULL auto_increment,
            `id_shop` int(10) unsigned NOT NULL,
            `id_lang` int(10) NOT NULL, 
            `name` VARCHAR(256) NOT NULL,
            `slug` VARCHAR(256) NOT NULL,
            `description` TEXT NOT NULL,
            `meta_title` VARCHAR(256) NOT NULL,
            `meta_description` TEXT NOT NULL,
            `meta_keywords` TEXT NOT NULL,
            PRIMARY KEY (`id`, `id_lang`) 
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        // Category Mock Data
        foreach (Shop::getShops(true, null, true) as $shop_id) {
            if (Db::getInstance()->getValue("SELECT 'x' FROM  `" . self::getTableName() . "` WHERE id = 0 AND id_shop = '$shop_id'") == null) {
                // Let create a default category
                Db::getInstance()->execute("INSERT INTO `" . self::getTableName() . "` (id, id_shop, root, parent_id, active) VALUES(
                0, $shop_id, 1, 0, true
            )");

                foreach (Language::getLanguages(true, false, true) as $language) {
                    Db::getInstance()->execute("INSERT INTO `" . self::getTableName() . "_lang` (id, id_shop, id_lang, name, slug, description) VALUES(
                        0, $shop_id, $language, 'Home', 'home', 'Blog home category'
                    )");
                }
            }
        }

        $result = Db::getInstance()->execute($sq1)
            && Db::getInstance()->execute($sq3);

        return $result;
    }

    static function getTableName() {
        return _DB_PREFIX_ . self::$definition['table'];
    }

    /**
     * @param $id_lang
     * @param $slug
     * @return BlogCategoryModel|null
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    static function getBySlug($id_lang, $slug) {
        $id = Db::getInstance()->getValue('SELECT id FROM  `' . self::getTableName() . '_lang` 
                WHERE slug = "'.Db::getInstance()->escape($slug).'"');

        if($id) {
            return new BlogCategoryModel($id, $id_lang);
        }

        return null;
    }

    /**
     * Return blog root category
     * @param $id_lang
     * @return BlogCategoryModel|null
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    static function getRootCategory($id_lang) {
        $id = Db::getInstance()->getValue('SELECT id FROM  `' . self::getTableName() . '` 
                WHERE root = "1"');
        if($id) {
            return new BlogCategoryModel($id, $id_lang);
        }
        return null;
    }


    function getUrl() {
        return (new Link())->getModuleLink("mdn_blog", "category", [
            'slug' => $this->slug,
            'page' => null
        ]);
    }

    /**
     * @return BlogCategoryModel[]
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    static function getAllCategories($lang = null, $include_root = false) {
        return array_map(
            function ($v) use ($lang) {
                return new BlogCategoryModel($v['id'], $lang);
            },
            Db::getInstance()->executeS("SELECT id FROM `".self::getTableName()."` ".($include_root ? "WHERE 1" : "WHERE root = '0'"))
        );
    }
    function toFront() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->getUrl(),
            'description' => $this->description,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
        ];
    }

    /**
     * We cancel deletion of parent category
     * @return bool|int
     * @throws PrestaShopException
     */
    public function delete()
    {
        if($this->root == 1)
            return false;
        return parent::delete();
    }
}
