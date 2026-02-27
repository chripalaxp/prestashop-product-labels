<?php
/**
* 2007-2026 PrestaShop
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
*  @copyright 2007-2026 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Product_Labels extends Module
{
    public function __construct()
    {
        $this->name = 'product_labels';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Christos Palaiologou';
        $this->need_instance = 1;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Product Labels');
        $this->description = $this->l('Product Labels (NEW,OFFER ETC)');

        $this->confirmUninstall = $this->l('Uninstall Module?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.7.8.3');
    }


    public function install()
    {
        Configuration::updateValue('PRODUCT_LABELS_ENABLED', false);

        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayAdminProductsExtra') &&
            $this->registerHook('actionProductSave') &&
            $this->registerHook('displayProductListFunctionalButtons') &&
            $this->registerHook('displayProductListReviews') &&
            $this->registerHook('displayProductActions');
    }

    public function uninstall()
    {
        Configuration::deleteByName('PRODUCT_LABELS_ENABLED');

        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
    }

    public function getContent()
    {

        if (((bool)Tools::isSubmit('submitProduct_LabelsModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitProduct_LabelsModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), 
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable Product Labels'),
                        'name' => 'PRODUCT_LABELS_ENABLED',
                        'is_bool' => true,
                        'desc' => $this->l('When disabled, labels will not be shown on the front office.'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    protected function getConfigFormValues()
    {
        return array(
            'PRODUCT_LABELS_ENABLED' => Configuration::get('PRODUCT_LABELS_ENABLED', true),
        );
    }

    protected function postProcess()
    {
        $enabled = Tools::getValue('PRODUCT_LABELS_ENABLED');

        $enabled = ($enabled === '1' || $enabled === 1 || $enabled === true) ? 1 : 0;

        Configuration::updateValue('PRODUCT_LABELS_ENABLED', $enabled);
    }


    public function hookHeader()
    {
        if (!(bool) Configuration::get('PRODUCT_LABELS_ENABLED', true)) {
            return '';
        }
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function getAvailableLabels()
    {
        return array(
            '' => $this->l('None'),
            'best_seller' => $this->l('Best Seller'),
            'new_arrival' => $this->l('New Arrival'),
            'limited_offer' => $this->l('Limited Offer'),
        );
    }


    public function getProductLabel($id_product)
    {
        $id_shop = (int) $this->context->shop->id;
        $sql = 'SELECT `label_type` FROM `' . _DB_PREFIX_ . 'product_labels`
                WHERE `id_product` = ' . (int) $id_product . ' AND `id_shop` = ' . $id_shop;
        $result = Db::getInstance()->getValue($sql);
        return $result !== false ? (string) $result : '';
    }


    protected function saveProductLabel($id_product, $label_type)
    {
        $id_product = (int) $id_product;
        $id_shop = (int) $this->context->shop->id;
        $label_type = pSQL($label_type);

        $existing = Db::getInstance()->getValue(
            'SELECT `id_product_labels` FROM `' . _DB_PREFIX_ . 'product_labels`
             WHERE `id_product` = ' . $id_product . ' AND `id_shop` = ' . $id_shop
        );

        if ($label_type === '' || $label_type === 'none') {
            if ($existing) {
                Db::getInstance()->execute(
                    'DELETE FROM `' . _DB_PREFIX_ . 'product_labels`
                     WHERE `id_product` = ' . $id_product . ' AND `id_shop` = ' . $id_shop
                );
            }
            return;
        }

        if ($existing) {
            Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'product_labels` SET `label_type` = \'' . $label_type . '\'
                 WHERE `id_product` = ' . $id_product . ' AND `id_shop` = ' . $id_shop
            );
        } else {
            Db::getInstance()->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'product_labels` (`id_product`, `id_shop`, `label_type`)
                 VALUES (' . $id_product . ', ' . $id_shop . ', \'' . $label_type . '\')'
            );
        }
    }


    public function hookDisplayAdminProductsExtra($params)
    {
        $id_product = isset($params['id_product']) ? (int) $params['id_product'] : (int) Tools::getValue('id_product');
        if (!$id_product) {
            return '';
        }
        $current = $this->getProductLabel($id_product);
        $labels = $this->getAvailableLabels();

        $this->context->smarty->assign(array(
            'product_labels_current' => $current,
            'product_labels_options' => $labels,
        ));

        return $this->display(__FILE__, 'views/templates/admin/product_extra.tpl');
    }


    public function hookActionProductSave($params)
    {
        $id_product = isset($params['id_product']) ? (int) $params['id_product'] : (int) Tools::getValue('id_product');
        if (!$id_product) {
            return;
        }
        $label_type = Tools::getValue('product_labels_label');
        if ($label_type === null || $label_type === false) {
            return;
        }
        if ($label_type === 'none') {
            $label_type = '';
        }
        $available = $this->getAvailableLabels();
        $allowed = array_keys($available);
        if (!in_array($label_type, $allowed, true)) {
            return;
        }
        $this->saveProductLabel($id_product, $label_type);
    }

    protected function renderProductLabelForProduct($id_product)
    {
        if (!(bool) Configuration::get('PRODUCT_LABELS_ENABLED', true)) {
            return '';
        }

        $id_product = (int) $id_product;
        if (!$id_product) {
            return '';
        }

        $label_type = $this->getProductLabel($id_product);
        if ($label_type === '' || $label_type === 'none') {
            return '';
        }

        $labels = $this->getAvailableLabels();
        $label_text = isset($labels[$label_type]) ? $labels[$label_type] : $label_type;

        $this->context->smarty->assign(array(
            'product_labels_text' => $label_text,
            'product_labels_type' => $label_type,
        ));

        return $this->display(__FILE__, 'views/templates/front/product_label.tpl');
    }


    public function hookDisplayProductListFunctionalButtons($params)
    {
        $id_product = 0;

        if (isset($params['product'])) {
            $p = $params['product'];
            if (is_object($p)) {
                if (isset($p->id_product)) {
                    $id_product = (int) $p->id_product;
                } elseif (isset($p->id)) {
                    $id_product = (int) $p->id;
                }
            } else {
                if (isset($p['id_product'])) {
                    $id_product = (int) $p['id_product'];
                } elseif (isset($p['id'])) {
                    $id_product = (int) $p['id'];
                }
            }
        }

        return $this->renderProductLabelForProduct($id_product);
    }


    public function hookDisplayProductListReviews($params)
    {
        $id_product = 0;

        if (isset($params['product'])) {
            $p = $params['product'];
            if (is_object($p)) {
                if (isset($p->id_product)) {
                    $id_product = (int) $p->id_product;
                } elseif (isset($p->id)) {
                    $id_product = (int) $p->id;
                }
            } else {
                if (isset($p['id_product'])) {
                    $id_product = (int) $p['id_product'];
                } elseif (isset($p['id'])) {
                    $id_product = (int) $p['id'];
                }
            }
        }

        return $this->renderProductLabelForProduct($id_product);
    }

    public function hookDisplayProductActions($params)
    {
        $id_product = 0;

        if (isset($params['product'])) {
            $p = $params['product'];
            if (is_object($p)) {
                if (isset($p->id_product)) {
                    $id_product = (int) $p->id_product;
                } elseif (isset($p->id)) {
                    $id_product = (int) $p->id;
                }
            } else {
                if (isset($p['id_product'])) {
                    $id_product = (int) $p['id_product'];
                } elseif (isset($p['id'])) {
                    $id_product = (int) $p['id'];
                }
            }
        }

        if (!$id_product) {
            $id_product = (int) Tools::getValue('id_product');
        }

        return $this->renderProductLabelForProduct($id_product);
    }

}
