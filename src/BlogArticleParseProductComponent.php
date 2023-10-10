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
class BlogArticleParseProductComponent extends BlogArticleParseComponent {
    static function parseArticle($article, $context) {
        $article = preg_replace_callback("/\[product\](.*)\[\/product\]/", function ($v) use ($context) {
            $products_id = $v[1];
            return $context->smarty->fetch('module:mdn_blog/views/templates/front/parts/article-component-product.tpl', [
                'page' => 0,
                'products' => self::getFrontendProductInformation(explode(",", $products_id), $context->language->id, $context)
            ]);
        }, $article);

        return $article;
    }


    /**
     * creates relevant product information for frontend output
     *
     * @param array $allSelectedProductIds array with all id's of the selected products
     * @param int $languageId language id of the shop you are in
     *
     * @return array all product information we need for our frontend rendering
     */
    static public function getFrontendProductInformation($allSelectedProductIds, $languageId, $context)
    {
        // set default category Home
        $category = new Category((int)2);

        // create new product search proider
        $searchProvider = new CategoryProductSearchProvider(
            $context->getTranslator(),
            $category
        );

        // set actual context
        $contextX = new ProductSearchContext($context);

        // create new search query
        $query = new ProductSearchQuery();
        $query->setResultsPerPage(PHP_INT_MAX)->setPage(1);
        $query->setSortOrder(new SortOrder('product', 'position', 'asc'));

        $result = $searchProvider->runQuery(
            $contextX,
            $query
        );

        // Product handling - to get relevant data
        $assembler = new ProductAssembler($context);
        $presenterFactory = new ProductPresenterFactory($context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        $presenter = new ProductListingPresenter(
            new ImageRetriever(
                $context->link
            ),
            $context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $context->getTranslator()
        );

        $products = array();
        foreach ($result->getProducts() as $rawProduct) {
            $productId = $rawProduct['id_product'];
            if(in_array($productId, $allSelectedProductIds)) {
                $product = $presenter->present(
                    $presentationSettings,
                    $assembler->assembleProduct($rawProduct),
                    $context->language
                );
                array_push($products, $product);
            }
        }

        return $products;
    }
}