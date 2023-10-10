<?php

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

require_once _PS_MODULE_DIR_ . '/mdn_blog/src/BlogHelpers.php';


class Mdn_Blog extends Module implements WidgetInterface
{
    const MDN_BLOG_CONFIG_ARTICLES_PER_PAGES = "MDN_BLOG_CONFIG_ARTICLES_PER_PAGES";
    const MDN_BLOG_CONFIG_CSS_FILE = "MDN_BLOG_CONFIG_CSS_FILE";

    public $tabs = [
        [
            'name' => 'Blog by MDN',
            'class_name' => 'AdminBlog',
            'visible' => true,
            'parent_class_name' => 'IMPROVE',
            'icon' => 'desktop_mac'
        ],
        [
            'name' => 'Articles',
            'class_name' => 'AdminBlogArticle',
            'visible' => true,
            'parent_class_name' => 'AdminBlog',
            'icon' => 'desktop_mac'
        ],
        [
            'name' => 'Catégories',
            'class_name' => 'AdminBlogCategory',
            'visible' => true,
            'parent_class_name' => 'AdminBlog',
            'icon' => 'desktop_mac'
        ],
        [
            'name' => 'Tailles d\'images',
            'class_name' => 'AdminBlogImage',
            'visible' => true,
            'parent_class_name' => 'AdminBlog',
            'icon' => 'desktop_mac'
        ]
    ];


    public function __construct()
    {
        $this->name = 'mdn_blog';
        $this->tab = 'seo';
        $this->version = '1.0.0';
        $this->author = 'Loris Pinna';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->displayName = $this->trans('MDN Blog', array(), 'Modules.Mdnblog.Mdnblog');
        $this->description = $this->trans('Blog prestashop but better', array(), 'Modules.Mdnblog.Mdnblog');
        $this->ps_versions_compliancy = array(
            'min' => '1.7.1.0',
            'max' => _PS_VERSION_,
        );
        parent::__construct();
    }

    public function install()
    {
        $this->_installSql();

        Configuration::set(self::MDN_BLOG_CONFIG_ARTICLES_PER_PAGES, 12);
        Configuration::set(self::MDN_BLOG_CONFIG_CSS_FILE, "default");

        return parent::install() && $this->registerHook([
                'actionDispatcher',
                'moduleRoutes',
                'actionAdminControllerSetMedia',
                'actionFrontControllerSetMedia',
                "actionRegisterBlock",
                "beforeRenderingMdnBlog",
                'actionFrontControllerSetVariables',
            ]);
    }


    protected function _installSql()
    {
        BlogCategoryModel::createContentTable();
        BlogArticleModel::createContentTable();
        BlogImageModel::createContentTable();
        return true;
    }



    //---------------------------------------------------------------------------------
    //
    //      ROUTES
    //
    //---------------------------------------------------------------------------------


    public function hookModuleRoutes() {
        return [
            'module-mdn_blog-home' => array(
                'controller' =>  'home',
                'rule' =>        'blog/{page:/}',
                'keywords' => array(
                    'page' => [
                        'regexp' => '[0-9]*',
                        'param' => 'page'
                    ],
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'mdn_blog',
                    'controller' => 'home',
                )
            ),
            'module-mdn_blog-category' => array(
                'controller' =>  'category',
                'rule' =>        'blog/{slug}/{page:/}',
                'keywords' => [
                    'slug' => [
                        'regexp' => '[A-z0-9\-_]+',
                        'param' => 'slug',
                    ],
                    'page' => [
                        'regexp' => '[0-9]*',
                        'param' => 'page'
                    ],
                ],
                'params' => array(
                    'fc' => 'module',
                    'module' => 'mdn_blog',
                    'controller' => 'category',
                )
            ),
            'module-mdn_blog-article' => array(
                'controller' =>  'article',
                'rule' =>        'blog/{cat_slug}/{slug}/',
                'keywords' => [
                    'cat_slug' => [
                        'regexp' => '([A-z0-9\-_])*',
                        'param' => 'cat_slug',
                    ],
                    'slug' => [
                        'regexp' => '([A-z0-9\-_])*',
                        'param' => 'slug',
                    ]
                ],
                'params' => array(
                    'fc' => 'module',
                    'module' => 'mdn_blog',
                    'controller' => 'category',
                )
            ),
        ];
    }

    //---------------------------------------------------------------------------------
    //
    //      FRONT
    //
    //---------------------------------------------------------------------------------

    public function hookActionFrontControllerSetMedia($params)
    {
        $this->context->controller->registerStylesheet(
            'mdn_blog_front',
            'modules/' . $this->name . '/views/css/front/style.css',
            [
                'media' => 'all',
                'priority' => 100,
            ]
        );
    }

    public function renderWidget($hookName, array $configuration)
    {
        $variables = $this->getWidgetVariables($hookName, $configuration);

        $this->smarty->assign($variables);

        return $this->display(__FILE__, 'views/templates/front/widgets/' . $variables['template']. '.tpl');
    }

    public function getWidgetVariables($hookName, array $configuration)
    {
        $category_id = !empty($configuration['category_id']) ? $configuration['category_id'] : null;
        $product_category_id = !empty($configuration['product_category_id']) ? $configuration['product_category_id'] : null;
        $amount = !empty($configuration['amount']) ? $configuration['amount'] : 2;
        $template = !empty($configuration['template']) ? $configuration['template'] : "row-recent-articles";

        $articles = BlogHelpers::getFrontArticlesFor(
            !empty($category_id) ? new BlogCategoryModel($category_id, $this->context->language->id) : null,
            $this->context->language->id,
            1,
            $amount,
            $product_category_id
        );

        return [
            'template' => $template,
            'articles' => $articles
        ];
    }


    //---------------------------------------------------------------------------------
    //
    //      BACKOFFICE & CONFIGURATION PAGE
    //
    //---------------------------------------------------------------------------------

    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('blog')) {
            foreach ( [self::MDN_BLOG_CONFIG_ARTICLES_PER_PAGES/*, self::MDN_BLOG_CONFIG_CSS_FILE*/] as $item ) {
                Configuration::updateValue($item, Tools::getValue($item));
            }
        }

        return $output.$this->displayForm();
    }


    public function displayForm()
    {
        // Get default language
        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $fieldsForm[0]['form'] = [
            'legend' => [
                'title' => $this->l('Blog settings'),
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Amount of articles on homepage & category page'),
                    'name' => 'MDN_BLOG_CONFIG_ARTICLES_PER_PAGES',
                    'required' => true
                ]
            ],
            'submit' => [
                'title' => $this->l('Save'),
                "name" => 'blog',
                'class' => 'btn btn-default pull-right'
            ]
        ];


        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        // Language
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                    '&token='.Tools::getAdminTokenLite('AdminModules'),
            ],
            'back' => [
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            ]
        ];

        foreach ( [self::MDN_BLOG_CONFIG_ARTICLES_PER_PAGES/*, self::MDN_BLOG_CONFIG_CSS_FILE*/] as $item ) {
            $helper->fields_value[$item] = Configuration::get($item);
        }

        return $helper->generateForm($fieldsForm);
    }

    public function hookActionAdminControllerSetMedia($params)
    {
        //$this->context->controller->addCSS($this->_path . 'views/admin/css/style.css');
        $this->context->controller->addJS($this->_path . 'views/js/admin/admin.js');
    }


    //---------------------------------------------------------------------------------
    //
    //      PRETTY BLOCKS MODULE SUPPORT
    //
    //---------------------------------------------------------------------------------

    /**
     * Register Block for compatibility with PRETTYBLOCKS module
     * @return array
     * @throws PrestaShopException
     */
    public function hookActionRegisterBlock()
    {
        $blocks = [];


        // get all of collection
        $Collection = new PrestaShopCollection("BlogCategoryModel", $this->context->language->id);
        $results = $Collection
            ->getAll();

        // work around for the module unless they fix
        $choices = [
            "0" => 'Toutes les catégories',

        ];

        // add any line
        foreach ($results as $result) {
            $choices["dd_".$result->id] = $result->name;
        }

        $blocks[] = [
            'name' => "MDN Blog - Listing articles",
            'description' => "Show a list of articles",
            'code' => 'mdn_blog',
            'tab' => 'general',
            'icon' => 'ArchiveBoxIcon',
            'need_reload' => true,
            'templates' => [
                // dynamic template
                'default' => 'module:' . $this->name . '/views/templates/front/prettyblocks/default.tpl'
            ],
            'config' => [
                'fields' => [
                    'title' => [
                        'type' => 'text', // type of field
                        'label' => 'Title', // label to display
                        'default' => 'Des articles à découvrir' // default value
                    ],
                    /*'category_id' => [
                        'type' => 'select', // type of field
                        'label' => 'Catégorie du blog', // label to display
                        'default' => '0',
                        'choices' => $choices
                    ],*/
                ],
            ],
        ];

        // get block listing
        return $blocks;
    }

    public function hookbeforeRenderingMdnBlog($params) {
        return [
            'articles' => BlogHelpers::getFrontArticlesFor(null, $this->context->language->id, 1, 2)
        ];
    }

}