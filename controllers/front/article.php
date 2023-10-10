<?php
require_once _PS_MODULE_DIR_ . '/mdn_blog/src/BlogArticleParseProductComponent.php';
require_once _PS_MODULE_DIR_ . '/mdn_blog/src/BlogArticleParseCategoryComponent.php';

class Mdn_BlogArticleModuleFrontController extends ModuleFrontController {
    private $_category;
    private $_article;

    public function __construct()
    {
        parent::__construct();
    }


    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = [
            'title' => $this->getTranslator()->trans('Blog', [], 'Modules.Mdnblog.Mdnblog'),
            'url' => $this->context->link->getModuleLink('mdn_blog', 'home', ['page' =>1 ]),
        ];

        $breadcrumb['links'][] = [
            'title' => $this->_category->name,
            'url' => $this->context->link->getModuleLink('mdn_blog', 'category', ['slug' => $this->_category->slug, 'page' =>1 ]),
        ];

        $breadcrumb['links'][] = [
            'title' => $this->_article->name,
            'url' => $this->context->link->getModuleLink('mdn_blog', 'article', ['cat_slug' => $this->_category->slug, 'slug' => $this->_article->slug]),
        ];

        return $breadcrumb;
    }

    public function init()
    {
        parent::init();
        $this->_article = BlogArticleModel::getBySlug($this->context->language->id, $this->getSlug());
        if($this->_article == null)
            return header('Location: /blog/');

        $this->_category = $this->_article->getMainCategory($this->context->language->id);

        $this->context->smarty->assign([
            // Articles
            'article' => array_merge(
                $this->_article->toFront(true),
                [
                    'article' => $this->parsedArticle($this->_article->article)
                ]
            ),
            'related_articles' => BlogHelpers::getFrontArticlesFor($this->_category->id, $this->context->language->id, 1, 2, null, [$this->_article->id]),
            // Catégories
            'current_category' => $this->_category->id,
            'category' => $this->_category->toFront(),
            'root' => BlogCategoryModel::getRootCategory($this->context->language->id)->toFront(),
            'categories' => array_map(
                function ($v) {
                    return $v->toFront();
                },
                BlogCategoryModel::getAllCategories($this->context->language->id)
            )
        ]);


        $this->setTemplate('module:mdn_blog/views/templates/front/pages/article.tpl');
    }

    /**
     * Parsed articles
     * @param $article
     * @return array|string|string[]|null
     * @throws SmartyException
     */
    public function parsedArticle($article) {
        $article = BlogArticleParseProductComponent::parseArticle($article, $this->context);
        $article = BlogArticleParseCategoryComponent::parseArticle($article, $this->context);
        return $article;
    }

    public function getSlug() {
        return Tools::getValue("slug");
    }

}