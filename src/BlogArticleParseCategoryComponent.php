<?php
use PrestaShop\PrestaShop\Adapter\Category\CategoryProductSearchProvider;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;

require_once _PS_MODULE_DIR_ . '/mdn_blog/src/BlogArticleParseComponent.php';
class BlogArticleParseCategoryComponent extends BlogArticleParseComponent {
    static function parseArticle($article, $context) {
        $article = preg_replace_callback("/\[category\](.*)\[\/category\]/", function ($v) use ($context) {
            $products_id = $v[1]; 
            return $context->smarty->fetch('module:mdn_blog/views/templates/front/parts/article-component-category.tpl', [
                'categories' => array_map(function ($v) use ($context) {
                    $category =  new Category($v, $context->language->id);
                    return [
                        'id' => $category->id,
                        'name' => $category->name
                    ];
                }, explode(",", $products_id))
            ]);
        }, $article);


        return $article;
    }
}