<?php
require_once _PS_MODULE_DIR_ . '/mdn_blog/classes/BlogCategoryModel.php';
require_once _PS_MODULE_DIR_ . '/mdn_blog/src/BlogHelpers.php';

class Mdn_BlogCategoryModuleFrontController extends ModuleFrontController {
    private $_category;

    public function __construct()
    {
        // workaround for Presta Crash if no page id
        $page = Tools::getValue("page", false);
        if(empty($page))
            $_GET['page'] = 1;

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
            'url' => $this->context->link->getModuleLink('mdn_blog', 'category', ['slug' => $this->_category->slug, 'page' => $this->getPage()]),
        ];

        return $breadcrumb;
    }

    public function init()
    {
        parent::init();

        $this->_category = BlogCategoryModel::getBySlug($this->context->language->id, $this->getSlug());
        if($this->_category == null)
            return header('Location: /blog/');


        $this->context->smarty->assign([
            // Articles
            'articles' => BlogHelpers::getFrontArticlesFor(
                    $this->_category->id,
                    $this->context->language->id,
                    $this->getPage()
                ) ,
            // Catégories
            'current_category' => $this->_category->id,
            'category' => $this->_category->toFront(),
            'categories' => array_map(
                function ($v) {
                    return $v->toFront();
                },
                BlogCategoryModel::getAllCategories($this->context->language->id, false)
            ),
            'root' => BlogCategoryModel::getRootCategory($this->context->language->id)->toFront(),
            'current_page' =>  $this->getPage(),
            'pagination' => BlogHelpers::buildPagination($this->getPage(), $this->_category)
        ]);

        $this->setTemplate('module:mdn_blog/views/templates/front/pages/category.tpl');
    }

    public function getSlug() {
        return Tools::getValue("slug");
    }

    public function getPage() {
        return Tools::getValue("page", 1);
    }
}