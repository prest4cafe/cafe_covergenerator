<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

use Symfony\Component\Filesystem\Exception\IOException;

require_once __DIR__ . '/classes/CoverGenerator.php';

class Cafe_covergenerator extends Module
{
    public function __construct()
    {
        $this->name = 'cafe_covergenerator';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'presta.cafe';
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('cafe_covergenerator');
        $this->description = $this->l('Generate blog covers');
    }

    /**
     * Installation du module
     * @return bool
     */
    public function install()
    {
        $tab = new Tab();
        foreach (Language::getLanguages() as $language) {
            $tab->name[$language['id_lang']] = 'Cover Generator';
        }
        $tab->class_name = 'AdminCoverGenerator';
        $tab->module = $this->name;
        $idParent = (int)Tab::getIdFromClassName('IMPROVE');
        $tab->id_parent = $idParent;
        $tab->position = Tab::getNbTabs($idParent);
        $tab->icon = 'image';

        if (!$tab->save()) {
            return false;
        }

        if (!parent::install()
        || !CoverGenerator::installSql()
        || !$this->registerHook([
            'actionObjectCoverGeneratorAddAfter',
        ])
        ) {
            return false;
        }
    }
    /**
     * Désinstallation du module
     * @return bool
     */
    public function uninstall()
    {
        // on efface toutes les images lors de la désinstallation
        foreach (glob(_PS_MODULE_DIR_.'cafe_covergenerator/images/miniatures/*.png') as $minis) {
            if (is_file($minis)) {
                @unlink($minis);
            }
        }

        foreach (glob(_PS_MODULE_DIR_.'cafe_covergenerator/images/covers/*.png') as $covers) {
            if (is_file($covers)) {
                @unlink($covers);
            }
        }

        foreach (glob(_PS_MODULE_DIR_.'cafe_covergenerator/images/originals/*.png') as $covers) {
            if (is_file($covers)) {
                @unlink($covers);
            }
        }


        if (
            !parent::uninstall()
            || !CoverGenerator::uninstallSql()
        ) {
            return false;
        }
        return true;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitCoverGenerator')) == true) {
            $this->postProcess();
        }

        return $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitCoverGenerator';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => [
                    ['name'=>'titre','label' => 'Titre','type' => 'text',],
                    ['name'=>'image','label' => 'Image','type' => 'file',],
                  ],

                'submit' => array(
                    'title' => $this->l('Save'),

                ),
            ),
        );
    }


    public function hookActionObjectCoverGeneratorAddAfter($params)
    {
        // path
        $path = _PS_MODULE_DIR_.'cafe_covergenerator/images/originals/';
        $path_font = _PS_MODULE_DIR_.'cafe_covergenerator/fonts/';
        $path_logo = _PS_MODULE_DIR_.'cafe_covergenerator/images/logos/';
        $path_mini = _PS_MODULE_DIR_.'cafe_covergenerator/images/miniatures/';
        $path_cover = _PS_MODULE_DIR_.'cafe_covergenerator/images/covers/';


        $logo = Tools::getValue('logo');
        $hex = Tools::getValue('TK_THEME_BG_COLOR');

        //image preprocess
        // voir try catch
        // retourne le nom de l'image
        try {
            $path_original = $this->uploadImage($_FILES['image']['name'], $path, (int)$params['object']->id);
        } catch(Exception $e) {
            echo $e;
        }

        

        // instancie de l'image d'origine
        $image_cover = new Imagick($path. $path_original);

        // creation d'un rectangle gris avec opacité a 0.8
        $background = new Imagick();

        // on converti la couleur en rgb
        if ($hex) {
            list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
            $background->newImage(750, 750, new ImagickPixel('rgba('.$r.'%, '.$g.'%, '.$b.'%, 0.8)'));
        } else {
            $background->newImage(750, 750, new ImagickPixel('rgba(50.2%, 50.2%, 50.2%, 0.8)'));
        }
        $background->setImageFormat('png');


        // le titre de notre image est multlangue
        // on recupere toutes les langues
        $languages = Language::getLanguages(false);

        foreach ($languages as $lang) {
            $fields['text'][$lang['id_lang']] = Tools::getValue('titre_' . (int) $lang['id_lang']);
        }

        // generation miniature
        //mini image en francais
        $mini = new Imagick();
        $pixel = new ImagickPixel();
        $mini->newImage(750, 750, $pixel);
        $mini->setImageFormat('png');
        // on pose les calques l'un sur l'autre
        $mini->compositeImage($image_cover, Imagick::COMPOSITE_DEFAULT, 0, 0);
        $mini->compositeImage($background, Imagick::COMPOSITE_DEFAULT, 0, 0);

        //mini image en anglais
        $mini2 = new Imagick();
        $pixel2 = new ImagickPixel();
        $mini2->newImage(750, 750, $pixel2);
        $mini2->setImageFormat('png');
        // on pose les calques l'un sur l'autre
        $mini2->compositeImage($image_cover, Imagick::COMPOSITE_DEFAULT, 0, 0);
        $mini2->compositeImage($background, Imagick::COMPOSITE_DEFAULT, 0, 0);

        // injection du logo
        // instance du logo logo
        if ($logo) {
            $logo = new Imagick($path_logo . $logo);
            $logo->resizeImage(207, 167, Imagick::FILTER_LANCZOS, 1);
            //positionnement du logo
            $x = 80;
            $y= 50;
            $mini->compositeImage($logo, Imagick::COMPOSITE_DEFAULT, $x, $y);
            $mini2->compositeImage($logo, Imagick::COMPOSITE_DEFAULT, $x, $y);
        }

        //chemin de la miniature
        //positionnement text

        $obj = new CoverGenerator((int)$params['object']->id);


        // on a un text en deux langues
        $mini_path =[];
        $cover_path =[];
        $titre = [];

        foreach ($fields["text"] as $key => $field) {
            try {
                $rewrite = Tools::str2url($field);


                if ($key==1) {
                    $newtext1 = wordwrap($fields["text"][1], 15, "\n");

                    $newtext1 = mb_strtoupper($newtext1, 'UTF-8');
                    $draw = new ImagickDraw();
                    $draw->setFillColor('black');
                    $draw->setFont($path_font . 'DejaVu_Sans/DejaVuSansCondensed-bold.ttf');
                    $draw->setFontSize(60);
                    $x = 80;
                    $y= 280;
                    $angle = 0;
                    $padding = 10;
                    // on met le text sur l'image
                    $mini->annotateImage($draw, $x, $y+$padding, $angle, $newtext1);
                    // on enregistre l'image miniature
                    $mini->writeImage($path_mini . $key . '-mini-' . $params['object']->id . '-' . $rewrite . '.png');

                    // generation cover

                    $cover = new Imagick($path. $path_original);

                    $cover->compositeImage($mini, Imagick::COMPOSITE_DEFAULT, 0, 0);
                    $cover->cropImage(1920, 750, 0, 0);
                    $cover->writeImage($path_cover . $key . '-cover-' . $params['object']->id . '-' . $rewrite . '.png'); //replace original background
                } elseif ($key==2) {
                    $newtext2 = wordwrap($fields["text"][2], 15, "\n");
                    $newtext2 = strtoupper($newtext2);
                    $draw2 = new ImagickDraw();
                    $draw2->setFillColor('black');
                    $draw2->setFont($path_font . 'DejaVu_Sans/DejaVuSansCondensed-bold.ttf');
                    $draw2->setFontSize(65);
                    $x = 80;
                    $y= 280;
                    $angle = 0;
                    $padding = 10;
                    // on met le text sur l'image
                    $mini2->annotateImage($draw2, $x, $y+$padding, $angle, $newtext2);
                    // on enregistre l'image miniature
                    $mini2->writeImage($path_mini . $key . '-mini-' . $params['object']->id . '-' . $rewrite . '.png');

                    // generation cover
                    $cover2 = new Imagick($path. $path_original);

                    $cover2->compositeImage($mini2, Imagick::COMPOSITE_DEFAULT, 0, 0);
                    $cover2->cropImage(1920, 750, 0, 0);
                    $cover2->writeImage($path_cover . $key . '-cover-' . $params['object']->id . '-' . $rewrite . '.png'); //replace original background
                }

                $titre += [$key => $field];
                $mini_path += [$key => $key . '-mini-' . $params['object']->id . '-' . $rewrite . '.png'];
                $cover_path += [$key => $key . '-cover-' . $params['object']->id . '-' . $rewrite . '.png'];
            } catch(Exception $e) {
                echo $e;
            }
        }



        $obj->titre = $titre;
        $obj->path_image_mini = $mini_path;
        $obj->path_image_cover = $cover_path;
        $obj->path_image_original = $path_original;

        $obj->update();


        /* Output the image with headers */
        //$overlay->destroy();
        //$image->destroy();
    }





    protected function postProcess()
    {
        /*        $obj = new CoverGenerator();

                $path = _PS_MODULE_DIR_.'cafe_covergenerator/images/';
                $path_font = _PS_MODULE_DIR_.'cafe_covergenerator/fonts/';
                $path_logo = _PS_MODULE_DIR_.'cafe_covergenerator/images/logos/';
                $path_mini = _PS_MODULE_DIR_.'cafe_covergenerator/images/miniatures/';
                $path_cover = _PS_MODULE_DIR_.'cafe_covergenerator/images/covers/';

                $uploader = new \Uploader('image');
                $uploader->setSavePath(_PS_MODULE_DIR_.'cafe_covergenerator/images');
                $uploader->process($image_cover);

                $image_cover = $_FILES['image']['name'];

                $image_cover= new Imagick($path. $_FILES['image']['name']);

                $logo = new Imagick($path_logo . 'prestashop-logo.png');
                $logo->resizeImage(207, 167, Imagick::FILTER_LANCZOS, 1);



                $background = new Imagick();
                $background->newImage(750, 750, new ImagickPixel('rgba(50.2%, 50.2%, 50.2%, 0.8)'));
                $background->setImageFormat('png');

                $mini = new Imagick();
                $pixel = new ImagickPixel();


                $mini->newImage(750, 750, $pixel);


                $mini->setImageFormat('png');


                $mini->compositeImage($image_cover, Imagick::COMPOSITE_DEFAULT, 0, 0);
                $mini->compositeImage($background, Imagick::COMPOSITE_DEFAULT, 0, 0);

                $x = 80;
                $y= 50;
                $mini->compositeImage($logo, Imagick::COMPOSITE_DEFAULT, $x, $y);

                $draw = new ImagickDraw();

                $draw->setFillColor('black');
                $draw->setFont($path_font . 'DejaVu_Sans/DejaVuSansCondensed-bold.ttf');
                $draw->setFontSize(65);
                $x = 80;
                $y= 280;
                $angle = 0;
                $padding = 10;

                $text = Tools::getValue('titre');

                $newtext = wordwrap($text, 15, "\n");
                $newtext = strtoupper($newtext);

                $mini_rewrite = 'mini-' . Tools::str2url($newtext);

                $date_id = date("YmdHis");

                $mini->annotateImage($draw, $x, $y+$padding, $angle, $newtext);
                $mini->writeImage($path_mini . $mini_rewrite . '-'. $date_id . '.png'); //replace original background

                $cover = new Imagick($path. $_FILES['image']['name']);
                $cover->compositeImage($mini, Imagick::COMPOSITE_DEFAULT, 0, 0);
                $cover->cropImage(1920, 750, 0, 0);

                $cover_rewrite = 'cover-' . Tools::str2url($newtext);

                $cover->writeImage($path_cover . $cover_rewrite . '-'. $date_id . '.png'); //replace original background


                $obj->path_image_mini = $mini_rewrite. '-'. $date_id;
                $obj->path_image_cover = $cover_rewrite. '-'. $date_id;
                $obj->titre = $newtext;

                $obj->update();*/


        //$image1 = new Imagick($path . $_FILES['image1']['name']);

        //$composition =

        /* Output the image with headers */
        // header('Content-type: image/png');
        // echo $cover;

        //$overlay->destroy();
        //$image->destroy();
    }

    // retourne le nom de l'image
    public function uploadImage($image_cover, $path, $id)
    {
        // on verifie que c'est bien une image
        // autorié jpg, jpeg, png
        //si c une image on upload sinon on cree une exeption
        //on upload l'image d'origine
        $uploader = new \Uploader('image');

       
        $uploader->setSavePath($path);
        $uploader->process($id.'-'.$image_cover);

        return $id.'-'.$image_cover;
    }

    
}
