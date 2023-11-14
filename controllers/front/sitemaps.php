<?php
require_once _PS_MODULE_DIR_ . '/mdn_blog/classes/BlogCategoryModel.php';
require_once _PS_MODULE_DIR_ . '/mdn_blog/classes/BlogArticleModel.php';
require_once _PS_MODULE_DIR_ . '/mdn_blog/src/BlogHelpers.php';

class Mdn_BlogSitemapsModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        $urls = [
            $this->context->link->getModuleLink('mdn_blog', 'home', ['page' => null])
        ];

        $categories = BlogCategoryModel::getAllCategories($this->context->language->id, false);
        foreach ($categories as $category) {
            $urls[] = $this->context->link->getModuleLink('mdn_blog', 'category', ['page' => null, 'slug' => $category->slug]);
        }

        $articles = BlogHelpers::getFrontArticlesFor(null, $this->context->language->id, 1, 10000);
        foreach ($articles as $article) {
            $urls[] = $article['url'];
        }

        header("Content-type: text/xml");
        die('<urlset xmlns="http://www.google.com/schemas/sitemap/0.84" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://www.google.com/schemas/sitemap/0.84/sitemap.xsd">'
            . implode("", array_map(function ($v) {
                        return '<url>
                <loc>' . $v . '</loc>
                <changefreq>weekly</changefreq>
                <priority>0.8</priority>
            </url>';
                    }, $urls)) . '
        </urlset>');
    }
}