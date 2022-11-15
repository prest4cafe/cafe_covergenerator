<?php

require_once(_PS_MODULE_DIR_ . 'cafe_covergenerator/classes/CoverGenerator.php');

class AdminCoverGeneratorController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'cafe_covergenerator';
        $this->className = 'CoverGenerator';
        $this->deleted = false;
        $this->list_no_link = true;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->context = Context::getContext();
        $this->required_database = false;
        $this->allow_export = false;
        $this->_use_found_rows = true;
        $this->_orderBy = 'id_cafe_covergenerator';
        $this->_orderWay = 'DESC';
        $this->lang = true;


        $this->fields_list = array(
            'id_cafe_covergenerator' => array('title' => 'ID', 'align' => 'text-left', 'class' => 'fixed-width-xs'),
            'id_blog' => array('title' => 'ID Blog', 'align' => 'text-left', 'class' => 'fixed-width-xs'),
            'titre' => array('title' => 'Titre', 'search' => false,'lang' => true,),
            'path_image_cover' => array('title' => 'Couverture', 'search' => false, 'callback' => 'printImgCover'),
        );

        parent::__construct();
    }

    public function initContent()
    {
        parent::initContent();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia();
    }

    public function renderForm()
    {
        if (!($obj = $this->loadObject(true))) {
            return;
        }

        $this->fields_form = array('legend' => array('title' => 'Cover generator', 'icon' => 'icon-user'),
                    'input' =>[
                        ['name'=>'titre','label' => 'Titre','type' => 'text','required' => true,'lang' => true,],
                        ['name'=>'id_blog','label' => 'Id blog','type' => 'text',],
                        [
                            'name'=>'logo',
                            'label' => 'Logo',
                            'type' => 'select',
                                'options' => [
                                    'query' => [
                                        ['id_logo' => '','name' => $this->l('--------')],
                                        ['id_logo' => 'prestashop-logo.png','name' => $this->l('Prestashop')],
                                        ['id_logo' => 'scrapy-logo.png','name' => $this->l('Scrapy')],

                                    ],
                                    'id' => 'id_logo',
                                    'name' => 'name',

                                ],

                        ],
                        ['name'=>'TK_THEME_BG_COLOR','label' => $this->l('Couleur du claque'),'type' => 'color',],
                        ['name'=>'image','label' => 'Image principale','type' => 'file','required' => true,],

                      ],
                    );


        $this->fields_form['submit'] = array('title' => $this->l('Save'),);

        return parent::renderForm();
    }

    public function postProcess()
    {
        parent::postProcess();

        if (Tools::isSubmit('deletecafe_covergenerator')) {
            $cover = new CoverGenerator((int)Tools::getValue('id_cafe_covergenerator'));

            if (!$cover->delete()) {
                return false;
            }
            return true;
        }
    }

	public function processAdd() {

		parent::processAdd();

	}

    public function initToolbarTitle()
    {
        parent::initToolbarTitle();
        switch ($this->display) {
            case '':
            case 'list':

                array_pop($this->toolbar_title);
                $this->toolbar_title[] = 'Gestion des couvertures';
                break;
            case 'view':

                if (($CoverGenerator = $this->loadObject(true)) && Validate::isLoadedObject($CoverGenerator)) {
                    $this->toolbar_title[] = sprintf('Editer une couverture:');
                }
                break;
            case 'add':
            case 'edit':
                array_pop($this->toolbar_title);

                if (($CoverGenerator = $this->loadObject(true)) && Validate::isLoadedObject($CoverGenerator)) {
                    $this->toolbar_title[] = sprintf('Editer une couverture:');
                    $this->page_header_toolbar_btn['new_cafe_covergenerator'] = array('href' => self::$currentIndex . '&addcafe_covergenerator&token=' . $this->token, 'desc' => $this->l('Générer une nouvelle image de couverture', null, null, false), 'icon' => 'process-icon-new');
                } else {
                    $this->toolbar_title[] = 'Générer une nouvelle image de couverture';
                }
                break;
        }
        array_pop($this->meta_title);
        if (count($this->toolbar_title) > 0) {
            $this->addMetaTitle($this->toolbar_title[count($this->toolbar_title) - 1]);
        }
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_cafe_covergenerator'] = array('href' => self::$currentIndex . '&addcafe_covergenerator&token=' . $this->token, 'desc' => $this->l('Générer une nouvelle image de couverture', null, null, false), 'icon' => 'process-icon-new');
        }
    }

    public function printImgMini($tr, $value)
    {
        $path= _PS_BASE_URL_.__PS_BASE_URI__.'modules/cafe_covergenerator/images/miniatures/'.$value['path_image_mini'];

        return '<img class="col-md-4 img-fluid" src="'.$path.'">
        <a href="'.$path.'" download="'.Tools::str2url($value['titre']).'">download</a>
        ';
    }
    public function printImgCover($tr, $value)
    {

        $path= _PS_BASE_URL_.__PS_BASE_URI__.'modules/cafe_covergenerator/images/covers/'.$value['path_image_cover'];

        return '<img class="col-md-4 img-fluid" src="'.$path.'">
        <a href="'.$path.'" download="'.Tools::str2url($value['titre']).'">download</a>
        ';
    }

    
}
