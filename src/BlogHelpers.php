<?php
require_once _PS_MODULE_DIR_ . '/mdn_blog/src/BlogPagination.php';
require_once _PS_MODULE_DIR_ . '/mdn_blog/classes/BlogCategoryModel.php';
require_once _PS_MODULE_DIR_ . '/mdn_blog/classes/BlogArticleModel.php';
require_once _PS_MODULE_DIR_ . '/mdn_blog/classes/BlogImageModel.php';

class BlogHelpers {
    /**
     * @param BlogCategoryModel $category
     */
    static function getFrontArticlesFor($category, $id_lang = null, $page = 1, $max = null, $product_category_id = null, $article_id_exclude = null)
    {
        return array_map(
            function ($v) {
                return $v->toFront();
            },
            BlogArticleModel::getArticles(
                $id_lang,
                $page,
                $max ? $max : self::getMaxArticlePerPage(),
                $category,
                $product_category_id,
                $article_id_exclude
            )
        );
    }

    static function getMaxArticlePerPage() {
        return Configuration::get(Mdn_Blog::MDN_BLOG_CONFIG_ARTICLES_PER_PAGES, null, null, null, "12");
    }

    /**
     * @param BlogCategoryModel $category
     */
    static function countArticleIn($category) {
        return Db::getInstance()->getValue(
            "SELECT COUNT('x') FROM `".BlogArticleModel::getTableName()."` WHERE active = 1 ".($category == null ? "" : " AND id_category = '".$category->id."'")
        );
    }

    static function buildPagination($current_page, $category) {
        return (new BlogPagination(
            $current_page,
            ceil(BlogHelpers::countArticleIn($category) / BlogHelpers::getMaxArticlePerPage()),
            $category == null ? 'home' : 'category',
            $category == null ? [] : ['slug' => $category->slug]
        ))->buildLinks();
    }
}