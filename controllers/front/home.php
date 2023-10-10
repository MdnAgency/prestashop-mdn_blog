<?php
require_once _PS_MODULE_DIR_ . '/mdn_blog/classes/BlogCategoryModel.php';
require_once _PS_MODULE_DIR_ . '/mdn_blog/classes/BlogArticleModel.php';
require_once _PS_MODULE_DIR_ . '/mdn_blog/src/BlogHelpers.php';
require_once _PS_MODULE_DIR_ . '/mdn_blog/src/BlogPagination.php';
class Mdn_BlogHomeModuleFrontController extends ModuleFrontController {
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

        return $breadcrumb;
    }

    public function init()
    {
        parent::init();
        $category = BlogCategoryModel::getRootCategory($this->context->language->id);

        $this->context->smarty->assign([
            // Articles
            'articles' => BlogHelpers::getFrontArticlesFor(null, $this->context->language->id, $this->getPage()),
            'category' => $category->toFront(),
            // Catégories
            'current_category' => 0,
            'categories' => array_map(
                function ($v) {
                    return $v->toFront();
                },
                BlogCategoryModel::getAllCategories($this->context->language->id, false)
            ),
            'root' => BlogCategoryModel::getRootCategory($this->context->language->id)->toFront(),
            'current_page' =>  $this->getPage(),
            'pagination' => BlogHelpers::buildPagination($this->getPage(), null)
        ]);

        $this->setTemplate('module:mdn_blog/views/templates/front/pages/home.tpl');
    }

    public function getPage() {
        return Tools::getValue("page", 1);
    }
}