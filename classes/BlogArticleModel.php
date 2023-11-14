<?php
require_once _PS_MODULE_DIR_ . '/mdn_blog/classes/BlogCategoryModel.php';
class BlogArticleModel extends ObjectModel
{
    public static $definition = array(
        'table' => 'mdn_blog_article',
        'primary' => 'id',
        'multilang' => true,
        'multilang_shop' => true,
        'fields' => array(
            'id' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false),
            'id_category' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false),
            'date' => array('type' => self::TYPE_DATE, 'required' => true, 'lang' => false),
            'active' =>       array('type' => self::TYPE_BOOL,    'validate' => 'isUnsignedInt', 'required' => false),

            // article
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'lang' => true),
            'slug' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'lang' => true),
            'description' => array('type' => self::TYPE_HTML, 'validate' => 'isString', 'required' => false, 'lang' => true),
            'article' => array('type' => self::TYPE_HTML, 'validate' => 'isString', 'required' => false, 'lang' => true),
            'thumbnail' => array('type' => self::TYPE_HTML, 'validate' => 'isString', 'required' => false, 'lang' => true),

            // meta
            'meta_title' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'lang' => true),
            'meta_description' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'lang' => true),
            'meta_keywords' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'lang' => true),
        ),
    );

    public $id;
    public $id_category;
    public $name;
    public $slug;
    public $description;
    public $article;
    public $thumbnail;
    public $active;
    public $date;
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
            `id_category` int(10) unsigned NOT NULL  ,  
            `date` DATETIME NOT NULL,
            `active` int(1) NOT NULL, 
            PRIMARY KEY (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        $sq3 = 'CREATE TABLE IF NOT EXISTS `' . self::getTableName() . '_lang`(
            `id` int(10) unsigned NOT NULL auto_increment,
            `id_shop` int(10) unsigned NOT NULL,
            `id_lang` int(10) NOT NULL, 
            `name` VARCHAR(256) NOT NULL,
            `slug` VARCHAR(256) NOT NULL,
            `thumbnail` VARCHAR(256) NOT NULL,
            `description` TEXT NOT NULL,
            `article` TEXT NOT NULL,
            `meta_title` VARCHAR(256) NOT NULL,
            `meta_description` TEXT NOT NULL,
            `meta_keywords` TEXT NOT NULL, 
            PRIMARY KEY (`id`, `id_lang`) 
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        $sq4 = 'CREATE TABLE IF NOT EXISTS `' . self::getTableName() . '_product_cat`(
            `id_article` int(10) unsigned NOT NULL,
            `id_product_cat` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id_article`, `id_product_cat`) 
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';


        $sq5 = 'CREATE TABLE IF NOT EXISTS `' . self::getTableName() . '_cat`(
            `id_article` int(10) unsigned NOT NULL,
            `id_category` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id_article`, `id_category`) 
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

        $result =
            Db::getInstance()->execute($sq1)
            && Db::getInstance()->execute($sq3)
            && Db::getInstance()->execute($sq4)
            && Db::getInstance()->execute($sq5)
        ;

        return $result;
    }

    /**
     * The DB table name
     * @return string
     */
    static function getTableName() {
        return _DB_PREFIX_ . self::$definition['table'];
    }

    /**
     * Get main category
     * @param $id_lang
     * @return BlogCategoryModel
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    function getMainCategory($id_lang) {
        return new BlogCategoryModel($this->id_category, $id_lang);
    }

    /**
     * Return URL of current article
     * @return string
     */
    function getUrl() {
        return (new Link())->getModuleLink("mdn_blog", "article", [
            'slug' => $this->slug,
            'cat_slug' => $this->getMainCategory($this->id_lang)->slug
        ]);
    }

    /**
     * Return URL of the thumbnail
     * @return string|null
     */
    function getThumbnail($format = null)
    {
        return $this->thumbnail ? "/img/blog/".($format ? $format . "/": "").$this->thumbnail : null;
    }

    /**
     * Find an article by a slug
     * @param $id_lang
     * @param $slug
     * @return BlogArticleModel|null
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    static function getBySlug($id_lang, $slug) {
        $id = Db::getInstance()->getValue('SELECT id FROM  `' . self::getTableName() . '_lang` 
                WHERE slug = "'.Db::getInstance()->escape($slug).'"');

        if($id) {
            return new BlogArticleModel($id, $id_lang);
        }

        return null;
    }

    /**
     * Get a list of articles based on criterias
     * @param null $id_lang
     * @param int $page
     * @param int $amount
     * @param null $category
     * @param null $product_category_id
     * @param null $article_id_exclude
     * @return BlogArticleModel[]
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    static function getArticles($id_lang = null, $page = 1, $amount = 12, $category = null, $product_category_id = null, $article_id_exclude = null) {
        $request = Db::getInstance()->executeS("SELECT p.id FROM ".self::getTableName()." p
            ".($product_category_id !== null ? "JOIN ".(self::getTableName() . '_product_cat')." pc ON pc.id_article = p.id " : "")." 
            ".($category !== null ? "JOIN ".(self::getTableName() . '_cat')." pbc ON pbc.id_article = p.id " : "")." 
            WHERE p.active = '1' AND (p.date) < (NOW()) 
                ".($product_category_id !== null ? "AND pc.id_product_cat = '".$product_category_id."'" : "")." 
                ".($category !== null ? "AND pbc.id_category = '".$category."'" : "")."  
                ".($article_id_exclude !== null ? "AND p.id NOT IN('".implode("','", $article_id_exclude)."')" : "")."  
            ORDER by p.id DESC 
            LIMIT ".$amount * ($page - 1).", $amount");
        return array_map(function ($v) use ($id_lang) {
            return new BlogArticleModel($v['id'], $id_lang);
        }, $request);
    }

    /**
     * Calculate the reading duration
     * @param $text
     * @param $wpm
     * @return float
     */
    function readTime($text, $wpm = 200) {
        $totalWords = str_word_count(strip_tags($text));
        $minutes = ceil($totalWords / $wpm);
        return  $minutes;
    }

    /**
     * Return list of producted categories linked for this article
     * @return array
     * @throws PrestaShopDatabaseException
     */
    function getProductCategories() {
        return array_map(function ($v) {
            return $v['id_product_cat'];
        }, Db::getInstance()->executeS("SELECT * FROM ".(self::getTableName() . '_product_cat')." WHERE id_article = '".$this->id."'"));
    }

    /**
     * Update listing of related categories
     * @param $listing
     * @return void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    function updateProductCategory($listing) {
        Db::getInstance()->query("DELETE FROM ".self::getTableName() . '_product_cat'." WHERE (`id_article`) = '".$this->id."'");

        if(is_array($listing)) {
            // Insert
            foreach ($listing as $value) {
                Db::getInstance()->query("INSERT INTO " . self::getTableName() . '_product_cat' . " (`id_article`, `id_product_cat`) 
                VALUES ('" . $this->id . "', '" . ((int)$value) . "')");
            }
        }
    }


    /**
     * Return list of producted categories linked for this article
     * @return array
     * @throws PrestaShopDatabaseException
     */
    function getBlogCategories() {
        return array_map(function ($v) {
            return $v['id_category'];
        }, Db::getInstance()->executeS("SELECT * FROM ".(self::getTableName() . '_cat')." WHERE id_article = '".$this->id."'"));
    }

    /**
     * Update listing of related categories
     * @param $listing
     * @return void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    function updateBlogCategories($listing) {
        Db::getInstance()->query("DELETE FROM ".self::getTableName() . '_cat'." WHERE (`id_article`) = '".$this->id."'");

        // adding the main category
        $listing[] = $this->id_category;

        // clean duplicates
        $listing = array_unique($listing);

        // Insert
        foreach ($listing as $value) {
            Db::getInstance()->query("INSERT INTO ".self::getTableName() . '_cat'." (`id_article`, `id_category`) 
                VALUES ('".$this->id."', '".((int) $value)."')");
        }
    }

    /**
     * Return array for front-end variables
     * @param $full
     * @return array
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    function toFront($full = false)
    {
        return array_merge([
            'name' => $this->name,
            'date' => $this->date,
            'description' => $this->description,
            'url' => $this->getUrl(),
            'main_category' => [
                'name' =>   $this->getMainCategory($this->id_lang)->name,
                'url' =>    $this->getMainCategory($this->id_lang)->getUrl(),
            ],
            'thumbnail' => $this->getThumbnail(!$full ? "thumb": "article"),
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'read_time' => $this->readTime($this->article)
        ], $full ? [] : []);
    }
}
