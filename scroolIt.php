<?php

/**
 * 2007-2021 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2021 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ScroolIt extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'scroolIt';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Mohamed Habib ALOUI';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Scroll it');
        $this->description = $this->l('Add a scrolling message');

        $this->confirmUninstall = $this->l('');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('SCROOLIT_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayHeader');
    }

    public function uninstall()
    {
        Configuration::deleteByName('SCROOLIT_LIVE_MODE');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {

        if (((bool)Tools::isSubmit('submitScroolItModule')) == true) {
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
        $helper->submit_action = 'submitScroolItModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
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
                    'title' => $this->l('Scrool text setting'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('The text'),
                        'name' => 'SCROOLIT_TEXT',
                        'desc' => $this->l('The text that will be displayed ')
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'SCROOLIT_TEXT_SPEED',
                        'label' => $this->l('The speed of the scrolling text'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'select',
                        'options' => array(
                            'query' => array(
                                array(
                                    'id_option' => "left",       // The value of the 'value' attribute of the <option> tag.
                                    'name' => 'Left to right'    // The value of the text content of the  <option> tag.
                                ),
                                array(
                                    'id_option' => 'Right',
                                    'name' => 'Right to left'
                                ),
                                array(
                                    'id_option' => "up",       // The value of the 'value' attribute of the <option> tag.
                                    'name' => 'Up to bottom'    // The value of the text content of the  <option> tag.
                                ),
                                array(
                                    'id_option' => 'bottom',
                                    'name' => 'Bottom to up'
                                ),
                            ),                           // $options contains the data itself.
                            'id' => 'id_option',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
                            'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
                        ),
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'name' => 'SCROOLIT_TEXT_DIRECTION',
                        'label' => $this->l('The direction of the scrolling text'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save changes'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'SCROOLIT_TEXT' => Configuration::get('SCROOLIT_TEXT', true),
            'SCROOLIT_TEXT_SPEED' => Configuration::get('SCROOLIT_TEXT_SPEED', 1),
            'SCROOLIT_TEXT_DIRECTION' => Configuration::get('SCROOLIT_TEXT_DIRECTION', 1)
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    public function hookDisplayHeader()
    {
        /// get data from DB
        $text =  Configuration::get("SCROOLIT_TEXT");
        $textSpeed =  Configuration::get("SCROOLIT_TEXT_SPEED");
        $textDirection =  Configuration::get("SCROOLIT_TEXT_DIRECTION");
        // assign vars
        $this->context->smarty->assign(
            [
                'text' => $text,
                'textSpeed' => $textSpeed,
                'textDirection' => $textDirection
            ]
        );
        return $this->display(__FILE__, "/views/templates/hook/header.tpl");
    }
}
