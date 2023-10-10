<?php

require_once _PS_MODULE_DIR_ . '/mdn_blog/classes/BlogCategoryModel.php';
require_once _PS_MODULE_DIR_ . '/mdn_blog/classes/BlogArticleModel.php';
require_once _PS_MODULE_DIR_ . '/mdn_blog/classes/BlogImageModel.php';

use Symfony\Component\HttpFoundation\File\UploadedFile;

class AdminBlogArticleController extends ModuleAdminController
{
    public function __construct()
    {
        $this->toolbar_title = "Articles";
        $this->table = BlogArticleModel::$definition['table']; //Table de l'objet
        $this->identifier = BlogArticleModel::$definition['primary']; //Clé primaire de l'objet
        $this->className = BlogArticleModel::class; //Classe de l'objet
        $this->bootstrap = true;
        $this->lang = true;
        //Liste des champs de l'objet à afficher dans la liste
        $this->fields_list = [
            'id' => [
                'title' => "Id",
                'align' => 'left',
            ],
            'name' => [
                'title' => "Name",
                'lang' => true,
                'align' => 'left'
            ],
            'slug' => [
                'title' => "Slug",
                'lang' => true,
                'align' => 'left'
            ],
            'active' => [
                'title' => "Active",
                'lang' => true,
                'align' => 'left',
            ]
        ];

        //Ajout d'actions sur chaque ligne
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        parent::__construct();
    }


    public function postProcess() {
        foreach (['thumbnail'] as $img) {
            if (!empty($_FILES[$img]) && !empty($_FILES[$img]['tmp_name'])) {
                /** @var $uploadedFile UploadedFile */
                $uploadedFile = new UploadedFile($_FILES[$img]['tmp_name'], $_FILES[$img]['name']);
                if (!empty($uploadedFile) && $uploadedFile->getBasename() != "") {
                    if (!$uploadedFile->isValid()) {
                    } else {
                        // Create Blog Folder if not exist
                        $folder = _PS_IMG_DIR_ . 'blog' . DIRECTORY_SEPARATOR;
                        if (!file_exists($folder)) {
                            mkdir($folder);
                        }

                        // Upload image and change ID for DB Save
                        $file_name = date("d-m-Y", time()) . "_" . ($uploadedFile->getClientOriginalName());
                        if (!$uploadedFile->move($folder, $file_name)) {
                        } else {
                            $_POST['thumbnail'] = $file_name;
                        }

                        // Generating smaller image
                        foreach (BlogImageModel::getAllImageSizes() as $size) {
                            if (!file_exists($folder . $size->name . DIRECTORY_SEPARATOR)) {
                                mkdir($folder .  $size->name . DIRECTORY_SEPARATOR);
                            }
                            ImageManager::resize($folder . $file_name, $folder .  $size->name . DIRECTORY_SEPARATOR . $file_name, $size->width, $size->height, "jpg",);
                        }
                    }
                }
            }
        }

        if(isset($_POST['thumbnail']) && $_POST['thumbnail'] == "") {
            unset($_POST['thumbnail']);
        }


        parent::postProcess();
    }

    protected function afterUpdate($object)
    {
        if($this->object) {
            // We update the product category table
            $this->object->updateProductCategory(Tools::getValue("related_categories"));

            // We update the blog categories for current article
            $blog_categories = [];
            foreach (Tools::getAllValues() as $key => $value) {
                if(preg_match("/(blog_categories)/", $key)) {
                    $id = explode("_", $key);
                    $last = end($id);
                    $blog_categories[] = $last;
                }
            }
            $this->object->updateBlogCategories($blog_categories);
        }
    }

    protected function beforeAdd($object)
    {
        $object->date = date("Y-m-d H:i:s");
        return parent::beforeAdd($object);
    }

    public function renderForm()
    {
        $selected_categories = $this->object->getProductCategories();
        $blog_categories = $this->object->getBlogCategories();

        //Définition du formulaire d'édition
        $this->fields_form = [
            //Entête
            'legend' => [
                'title' => $this->module->l('Article'),
                'icon' => 'icon-cog'
            ],
            //Champs
            'input' => array_merge([
                [
                    'label' => ('Name'),
                    'type' => 'text',
                    'name' => 'name',
                    'required' => true,
                    'lang' => true,
                ],
                [
                    'label' => ('Slug'),
                    'type' => 'text',
                    'name' => 'slug',
                    'required' => true,
                    'lang' => true,
                ],
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Main category'),
                    'helper' => $this->module->l('Main category of this blog article'),
                    'name' => 'id_category',
                    'required' => true,
                    'options' => array(
                        'query' => array_map(function ($v) {
                            return [
                                'id_option' => $v->id,
                                'name' => $v->name[$this->context->language->id],
                            ];
                        }, BlogCategoryModel::getAllCategories()),
                        'id' => 'id_option',
                        'name' => 'name',
                    ),
                ),
                [
                    'type' => 'checkbox',
                    'label' => $this->module->l('Categories'),
                    'helper' => $this->module->l('Categories of this blog article'),
                    'name' => 'blog_categories',
                    'values' => array(
                        'query' => array_map(function ($v) {
                            return [
                                'id_option' => $v->id,
                                'name' => $v->name[$this->context->language->id],
                            ];
                        }, BlogCategoryModel::getAllCategories()),
                        'id' => 'id_option',
                        'name' => 'name',
                    ),
                ],
                [
                    'type' => 'file',
                    'label' => 'Miniature',
                    'name' => 'thumbnail',
                    'size' => 200,
                    'required' => true,
                    'lang' => true
                ],
                [
                    'label' => ('Description / résumé'),
                    'type' => 'textarea',
                    'name' => 'description',
                    'required' => false,
                    'lang' => true, //Flag pour utilisation des langues
                    'rows' => 5,
                    'cols' => 40,
                ],
                [
                    'label' => ('Contenu de l\'article'),
                    'type' => 'textarea',
                    'name' => 'article',
                    'required' => false,
                    'lang' => true, //Flag pour utilisation des langues
                    'rows' => 5,
                    'cols' => 40,
                    'autoload_rte' => true,
                    "desc" => "Shortcodes : 
                        <br/><span style='color: #a24c4c'>[product]</span>id_product,id_product2,...<span style='color: #a24c4c'>[/product]</span> : Show list of products
                        <br/><span style='color: #a24c4c'>[category]</span>id_category,id_category,...<span style='color: #a24c4c'>[/category]</span> : Show list of categories
                        "
                ],
                [
                    'type'  => 'text',
                    'label' => $this->l('Meta Title'),
                    'name'  => 'meta_title',
                    'desc'  => $this->l('Enter Your Category Meta Title for SEO'),
                    'lang'  => true,
                ],
                [
                    'type'  => 'textarea',
                    'label' => $this->l('Meta Description'),
                    'name'  => 'meta_description',
                    'desc'  => $this->l('Enter Your Category Meta Description for SEO'),
                    'lang'  => true,
                ],
                [
                    'type'  => 'tags',
                    'label' => $this->l('Meta Keyword'),
                    'name'  => 'meta_keywords',
                    'desc'  => $this->l('Enter Your Category Meta Keyword for SEO. Seperate by comma(,)'),
                    'lang'  => true,
                ],
                [
                    'type'  => 'categories',
                    'label' => 'Catégories de produits liées',
                    'name' => 'related_categories',
                    'tree' => [
                        'id' =>  'related_categories',
                        'use_checkbox' => true,
                        'selected_categories' => $selected_categories
                    ]
                ] ,
                array(
                    'type' => 'select',
                    'label' => ('Actif'),
                    'name' => 'active',
                    'required' => true,
                    'options' => array(
                        'query' => $options = array(
                            array(
                                'id_option' => 1,       // The value of the 'value' attribute of the <option> tag.
                                'name' => 'Oui',    // The value of the text content of the  <option> tag.
                            ),
                            array(
                                'id_option' => 0,
                                'name' => 'Non',
                            ),
                        ),
                        'id' => 'id_option',
                        'name' => 'name',
                    ),
                ),
            ]),
            //Boutton de soumission
            'submit' => [
                'name' => 'slider',
                'title' => $this->l('Save'), //On garde volontairement la traduction de l'admin par défaut
            ]
        ];


        foreach ($this->object->getBlogCategories() as $cb) {
            $this->fields_value['blog_categories_'.$cb] = true;
        }



        return parent::renderForm();
    }
}