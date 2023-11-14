<?php

require_once _PS_MODULE_DIR_ . '/mdn_blog/classes/BlogCategoryModel.php';


class AdminBlogCategoryController extends ModuleAdminController
{
    public function __construct()
    {
        $this->toolbar_title = "Catégories";
        $this->table = BlogCategoryModel::$definition['table']; //Table de l'objet
        $this->identifier = BlogCategoryModel::$definition['primary']; //Clé primaire de l'objet
        $this->className = BlogCategoryModel::class; //Classe de l'objet
        $this->bootstrap = true;
        $this->multiple_fieldsets = true;
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

    public function postProcess()
    {
        parent::postProcess();
    }

    public function beforeAdd($object)
    {
        $this->object->root = 0;
        parent::beforeAdd($object);
    }

    public function renderForm()
    {
        //Définition du formulaire d'édition
        $this->fields_form[0]['form'] = [
            //Entête
            'legend' => [
                'title' => $this->module->l('Contenu'),
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
                    'label' => ('Catégorie parent'),
                    'name' => 'parent_id',
                    'required' => false,
                    'options' => array(
                    'query' =>  $this->object->root == 1 ? [] : array_map(function ($v) {
                            return [
                                'id_option' => $v->id,
                                'name' => $v->name[$this->context->language->id],
                            ];
                        }, array_filter(BlogCategoryModel::getAllCategories(null, true), function ($v) {
                            return $v->id != $this->id_object;
                    })),
                    'id' => 'id_option',
                    'name' => 'name',
                    ),
                ),
                [
                    'label' => ('Description'),
                    'type' => 'textarea',
                    'name' => 'description',
                    'required' => false,
                    'lang' => true, //Flag pour utilisation des langues
                    'rows' => 5,
                    'cols' => 40,
                    'autoload_rte' => true,
                ],
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
        ];
        $this->fields_form[1]['form'] = [
            //Entête
            'legend' => [
                'title' => $this->module->l('SEO'),
                'icon' => 'icon-cog'
            ],
            'input' => [
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
                ]
            ],
            'submit' => [
                'name' => 'slider',
                'title' => $this->l('Save'), //On garde volontairement la traduction de l'admin par défaut
            ]
        ];

        // Default, article is visible
        if($this->object->id == 0)
            $this->fields_value['active'] = 1;

        return parent::renderForm();
    }
}