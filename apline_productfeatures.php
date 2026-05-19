<?php
/**
 * Product Features module for PrestaShop.
 *
 * A lightweight, distributable block of rows (image OR HTML-entity icon + text,
 * optional clickable URL). Classic PrestaShop API, no front build step.
 *
 * @author    APLINE Arkadiusz Pielechowski
 * @copyright APLINE Arkadiusz Pielechowski
 * @license   Custom Attribution License v1.0 - see LICENSE.md
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/classes/ProductFeatureItem.php';

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class apline_productfeatures extends Module implements WidgetInterface
{
    const HOOK_KEY = 'APF_HOOK';
    const TEXT_COLOR_KEY = 'APF_TEXT_COLOR';

    const ADMIN_CONTROLLER = 'AdminProductFeatureItem';

    /** @var string */
    private $templateFile = 'module:apline_productfeatures/views/templates/hook/block.tpl';

    /**
     * Hooks the block may be displayed on. Key = hook name, value = admin label.
     *
     * @return array
     */
    public static function getAvailableHooks()
    {
        return [
            'displayProductAdditionalInfo' => 'Product page (reassurance area)',
            'displayLeftColumn' => 'Left column',
            'displayRightColumn' => 'Right column',
            'displayFooterProduct' => 'Product page footer',
        ];
    }

    public function __construct()
    {
        $this->name = 'apline_productfeatures';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'APLINE Arkadiusz Pielechowski';
        $this->need_instance = false;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('APLINE Product Benefit Icons for PrestaShop 9', [], 'Modules.Aplineproductfeatures.Admin');
        $this->description = $this->trans('Display a configurable block of benefit rows (image or icon + text, optional link) on the product page.', [], 'Modules.Aplineproductfeatures.Admin');
        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall this module? All rows will be deleted.', [], 'Modules.Aplineproductfeatures.Admin');

        $this->ps_versions_compliancy = ['min' => '9.0', 'max' => _PS_VERSION_];
    }

    /**
     * @return string absolute path to the upload directory
     */
    public function getUploadDir()
    {
        return _PS_MODULE_DIR_ . $this->name . '/views/img/';
    }

    /**
     * @return bool whether the upload directory is writable
     */
    public function isUploadDirWritable()
    {
        $dir = $this->getUploadDir();

        return is_dir($dir) && is_writable($dir);
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        if (!$this->installDb()
            || !$this->installConfiguration()
            || !$this->installHooks()
            || !$this->installTab()
        ) {
            // Roll back to a clean state so the shop is never left half-installed.
            $this->uninstall();
            $this->_errors[] = $this->trans('Installation failed and was rolled back. Please check folder permissions and try again.', [], 'Modules.Aplineproductfeatures.Admin');

            return false;
        }

        return true;
    }

    public function uninstall()
    {
        // Each step is idempotent; uninstall must not fail because something is already gone.
        $this->uninstallTab();
        $this->deleteUploadedFiles();

        Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'apline_productfeature`');

        Configuration::deleteByName(self::HOOK_KEY);
        Configuration::deleteByName(self::TEXT_COLOR_KEY);

        return parent::uninstall();
    }

    /**
     * @return bool
     */
    private function installDb()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'apline_productfeature` (
            `id_apline_productfeature` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `image` VARCHAR(255) NULL,
            `icon` VARCHAR(255) NULL,
            `alt` VARCHAR(255) NULL,
            `text` VARCHAR(255) NOT NULL,
            `url` VARCHAR(255) NULL,
            `new_tab` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
            `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
            `position` INT(10) UNSIGNED NOT NULL DEFAULT 0,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NULL,
            PRIMARY KEY (`id_apline_productfeature`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        if (!Db::getInstance()->execute($sql)) {
            return false;
        }

        // Demo rows (icon-entity based so no file dependency is needed).
        $now = date('Y-m-d H:i:s');
        $demo = [
            ['icon' => '1F69A', 'text' => 'Free delivery'],
            ['icon' => '23F1', 'text' => 'Same day shipping'],
            ['icon' => '21A9', 'text' => '30 day return'],
        ];
        $pos = 1;
        foreach ($demo as $row) {
            $ok = Db::getInstance()->insert('apline_productfeature', [
                'image' => '',
                'icon' => pSQL($row['icon']),
                'alt' => '',
                'text' => pSQL($row['text']),
                'url' => '',
                'new_tab' => 1,
                'active' => 1,
                'position' => $pos++,
                'date_add' => $now,
                'date_upd' => $now,
            ]);
            if (!$ok) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    private function installConfiguration()
    {
        return Configuration::updateValue(self::HOOK_KEY, 'displayProductAdditionalInfo')
            && Configuration::updateValue(self::TEXT_COLOR_KEY, '#000000');
    }

    /**
     * @return bool
     */
    private function installHooks()
    {
        $ok = $this->registerHook('actionFrontControllerSetMedia');
        foreach (array_keys(self::getAvailableHooks()) as $hook) {
            $ok = $ok && $this->registerHook($hook);
        }

        return $ok;
    }

    /**
     * @return bool
     */
    private function installTab()
    {
        if (Tab::getIdFromClassName(self::ADMIN_CONTROLLER)) {
            return true;
        }

        $tab = new Tab();
        $tab->class_name = self::ADMIN_CONTROLLER;
        $tab->module = $this->name;
        $tab->active = 1;
        // Hidden tab (no visible parent): managed from the module configuration page.
        $tab->id_parent = -1;
        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[$lang['id_lang']] = 'Product Benefit Icons';
        }

        return (bool) $tab->add();
    }

    /**
     * @return bool
     */
    private function uninstallTab()
    {
        $id = (int) Tab::getIdFromClassName(self::ADMIN_CONTROLLER);
        if (!$id) {
            return true;
        }

        try {
            $tab = new Tab($id);

            return (bool) $tab->delete();
        } catch (\Throwable $e) {
            return true;
        }
    }

    /**
     * Remove uploaded images. Only ever touches files inside the module folder.
     */
    private function deleteUploadedFiles()
    {
        $dir = $this->getUploadDir();
        if (!is_dir($dir)) {
            return;
        }

        foreach ((array) glob($dir . 'apf_*') as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }

    /**
     * Normalize the icon field to a renderable HTML entity.
     * Accepts a bare unicode hex (e.g. "1F69A") or an already-formed entity.
     *
     * @param string $icon
     *
     * @return string
     */
    public static function normalizeIcon($icon)
    {
        $icon = trim((string) $icon);
        if ($icon === '') {
            return '';
        }
        if (preg_match('/^[0-9A-Fa-f]{1,6}$/', $icon)) {
            return '&#x' . strtoupper($icon) . ';';
        }
        // Already an entity: keep as-is (validated by a whitelist on save).
        if (preg_match('/^(&#x?[0-9A-Fa-f]+;|&[a-zA-Z]+;)$/', $icon)) {
            return $icon;
        }

        return '';
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitApfConfig')) {
            $hook = (string) Tools::getValue(self::HOOK_KEY);
            $color = (string) Tools::getValue(self::TEXT_COLOR_KEY);

            if (!array_key_exists($hook, self::getAvailableHooks())) {
                $output .= $this->displayError($this->trans('Invalid display hook selected.', [], 'Modules.Aplineproductfeatures.Admin'));
            } elseif ($color !== '' && !Validate::isColor($color)) {
                $output .= $this->displayError($this->trans('The text color is not a valid color.', [], 'Modules.Aplineproductfeatures.Admin'));
            } else {
                Configuration::updateValue(self::HOOK_KEY, $hook);
                Configuration::updateValue(self::TEXT_COLOR_KEY, $color !== '' ? $color : '#000000');
                $output .= $this->displayConfirmation($this->trans('Settings updated.', [], 'Modules.Aplineproductfeatures.Admin'));
            }
        }

        if (!$this->isUploadDirWritable()) {
            $output .= $this->displayWarning($this->trans('The upload folder is not writable: %s. Image uploads will fail until you fix its permissions (e.g. chmod 0775).', [$this->getUploadDir()], 'Modules.Aplineproductfeatures.Admin'));
        }

        $manageUrl = $this->context->link->getAdminLink(self::ADMIN_CONTROLLER);

        $this->context->smarty->assign([
            'apf_manage_url' => $manageUrl,
        ]);
        $output .= $this->display(__FILE__, 'views/templates/admin/configure.tpl');

        return $output . $this->renderConfigForm() . $this->renderLikeBox() . $this->renderAplineFooter();
    }

    /**
     * APLINE attribution block. Required by the module license to stay visible
     * on the configuration page with a working link to https://apline.pl.
     * Rendered server-side as a standalone component (not CSS-only) so it
     * cannot be trivially stripped.
     *
     * @return string
     */
    public function renderAplineFooter()
    {
        return '
        <style>
            .apline-credit { margin-top: 24px; font-size: 12px; opacity: 0.9; }
            .apline-credit a { font-weight: 600; }
        </style>
        <div class="apline-credit">
            ' . $this->trans('Module created by', [], 'Modules.Aplineproductfeatures.Admin') . '
            <a href="https://apline.pl" target="_blank" rel="noopener noreferrer">APLINE</a>
        </div>';
    }

    /**
     * Subtle "need custom development?" box shown on the configuration page.
     *
     * @return string
     */
    public function renderLikeBox()
    {
        return '
        <div class="panel">
            <h3>&#9749; ' . $this->trans('Like this module?', [], 'Modules.Aplineproductfeatures.Admin') . '</h3>
            <p>' . $this->trans('Need custom PrestaShop development, performance optimization or integrations?', [], 'Modules.Aplineproductfeatures.Admin') . '</p>
            <a class="btn btn-default" href="https://apline.pl" target="_blank" rel="noopener noreferrer">&#8594; APLINE.PL</a>
        </div>';
    }

    /**
     * @return string
     */
    private function renderConfigForm()
    {
        $hookOptions = [];
        foreach (self::getAvailableHooks() as $hookName => $label) {
            $hookOptions[] = ['id' => $hookName, 'name' => $label];
        }

        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Display settings', [], 'Modules.Aplineproductfeatures.Admin'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'select',
                        'label' => $this->trans('Display location', [], 'Modules.Aplineproductfeatures.Admin'),
                        'name' => self::HOOK_KEY,
                        'options' => ['query' => $hookOptions, 'id' => 'id', 'name' => 'name'],
                        'desc' => $this->trans('You can also display the block anywhere with {widget name=\'apline_productfeatures\'}.', [], 'Modules.Aplineproductfeatures.Admin'),
                    ],
                    [
                        'type' => 'color',
                        'label' => $this->trans('Text color', [], 'Modules.Aplineproductfeatures.Admin'),
                        'name' => self::TEXT_COLOR_KEY,
                    ],
                ],
                'submit' => ['title' => $this->trans('Save', [], 'Admin.Actions')],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitApfConfig';
        $helper->fields_value = [
            self::HOOK_KEY => Configuration::get(self::HOOK_KEY),
            self::TEXT_COLOR_KEY => Configuration::get(self::TEXT_COLOR_KEY),
        ];

        return $helper->generateForm([$fields_form]);
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'apline-productfeatures',
            'modules/' . $this->name . '/views/css/front.css'
        );
    }

    public function hookDisplayProductAdditionalInfo($params)
    {
        return $this->renderForHook('displayProductAdditionalInfo');
    }

    public function hookDisplayLeftColumn($params)
    {
        return $this->renderForHook('displayLeftColumn');
    }

    public function hookDisplayRightColumn($params)
    {
        return $this->renderForHook('displayRightColumn');
    }

    public function hookDisplayFooterProduct($params)
    {
        return $this->renderForHook('displayFooterProduct');
    }

    /**
     * Render the block only on the hook selected in configuration.
     * Wrapped so any failure yields an empty block instead of a 500.
     *
     * @param string $hookName
     *
     * @return string
     */
    private function renderForHook($hookName)
    {
        try {
            if (Configuration::get(self::HOOK_KEY) !== $hookName) {
                return '';
            }

            $this->smarty->assign($this->buildVariables());

            return $this->display(__FILE__, 'views/templates/hook/block.tpl');
        } catch (\Throwable $e) {
            PrestaShopLogger::addLog('apline_productfeatures: ' . $e->getMessage(), 3);

            return '';
        }
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        try {
            $this->smarty->assign($this->buildVariables());

            return $this->fetch($this->templateFile);
        } catch (\Throwable $e) {
            PrestaShopLogger::addLog('apline_productfeatures: ' . $e->getMessage(), 3);

            return '';
        }
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        return $this->buildVariables();
    }

    /**
     * @return array
     */
    private function buildVariables()
    {
        $items = [];
        foreach (ProductFeatureItem::getActiveItems() as $row) {
            $row['icon'] = self::normalizeIcon(isset($row['icon']) ? $row['icon'] : '');
            $items[] = $row;
        }

        return [
            'items' => $items,
            'textColor' => Configuration::get(self::TEXT_COLOR_KEY) ?: '#000000',
        ];
    }
}
