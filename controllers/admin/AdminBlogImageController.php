<?php

require_once _PS_MODULE_DIR_ . '/mdn_blog/classes/BlogCategoryModel.php';


class AdminBlogImageController extends ModuleAdminController
{
    public function __construct()
    {
        $this->toolbar_title = "Tailles d'image";
        $this->table = BlogImageModel::$definition['table']; //Table de l'objet
        $this->identifier = BlogImageModel::$definition['primary']; //Clé primaire de l'objet
        $this->className = BlogImageModel::class; //Classe de l'objet
        $this->bootstrap = true;
        $this->lang = false;
        //Liste des champs de l'objet à afficher dans la liste
        $this->fields_list = [
            'id' => [
                'title' => "Id",
                'align' => 'left',
            ],
            'name' => [
                'title' => "Name",
                'align' => 'left'
            ],
            'width' => [
                'title' => "Width",
                'align' => 'left'
            ],
            'height' => [
                'title' => "Height",
                'align' => 'left'
            ],
            'active' => [
                'title' => "Active",
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
        $this->fields_form = [
            //Entête
            'legend' => [
                'title' => $this->module->l('Taille d\'image'),
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
                    'label' => ('Width'),
                    'type' => 'text',
                    'name' => 'width',
                    'required' => true,
                    'lang' => true,
                ],
                [
                    'label' => ('Height'),
                    'type' => 'text',
                    'name' => 'height',
                    'required' => true,
                    'lang' => true,
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
            //Boutton de soumission
            'submit' => [
                'name' => 'slider',
                'title' => $this->l('Save'), //On garde volontairement la traduction de l'admin par défaut
            ]
        ];
        return parent::renderForm();
    }
}