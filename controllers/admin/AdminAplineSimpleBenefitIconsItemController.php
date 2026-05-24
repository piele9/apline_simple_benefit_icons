<?php
/**
 * APLINE Simple Benefit Icons module for PrestaShop 9.
 *
 * @author    APLINE Arkadiusz Pielechowski
 * @copyright APLINE Arkadiusz Pielechowski
 * @license   Custom Attribution License v1.0 - see LICENSE.md
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'apline_simple_benefit_icons/classes/AplineSimpleBenefitIconsItem.php';

class AdminAplineSimpleBenefitIconsItemController extends ModuleAdminController
{
    const MAX_IMG_BYTES = 2097152; // 2 MB
    const MAX_STRING = 255;
    const ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'webp'];
    const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp'];

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'asbi_item';
        $this->className = 'AplineSimpleBenefitIconsItem';
        $this->identifier = 'id_asbi_item';
        $this->position_identifier = 'id_asbi_item';
        $this->lang = false;
        $this->allow_export = false;

        parent::__construct();

        $this->fields_list = [
            'id_asbi_item' => [
                'title' => $this->trans('ID', [], 'Admin.Global'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'image' => [
                'title' => $this->trans('Image / Icon', [], 'Modules.Aplinesimplebenefiticons.Admin'),
                'align' => 'center',
                'callback' => 'printImage',
                'orderby' => false,
                'search' => false,
            ],
            'text' => [
                'title' => $this->trans('Text', [], 'Modules.Aplinesimplebenefiticons.Admin'),
            ],
            'url' => [
                'title' => $this->trans('URL', [], 'Modules.Aplinesimplebenefiticons.Admin'),
            ],
            'active' => [
                'title' => $this->trans('Displayed', [], 'Modules.Aplinesimplebenefiticons.Admin'),
                'align' => 'center',
                'active' => 'active',
                'type' => 'bool',
                'orderby' => false,
            ],
            'position' => [
                'title' => $this->trans('Position', [], 'Modules.Aplinesimplebenefiticons.Admin'),
                'align' => 'center',
                'position' => 'position',
                'search' => false,
            ],
        ];

        $this->_defaultOrderBy = 'position';
        $this->_defaultOrderWay = 'ASC';

        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->bulk_actions = [
            'delete' => [
                'text' => $this->trans('Delete selected', [], 'Admin.Actions'),
                'confirm' => $this->trans('Delete selected items?', [], 'Admin.Notifications.Warning'),
            ],
        ];
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJqueryUI('ui.sortable');
    }

    /**
     * Module configuration URL (so the user can get back from the rows list).
     *
     * @return string
     */
    private function getConfigUrl()
    {
        return $this->context->link->getAdminLink('AdminModules', true, [], [
            'configure' => 'apline_simple_benefit_icons',
            'module_name' => 'apline_simple_benefit_icons',
        ]);
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        $this->page_header_toolbar_btn['back_to_config'] = [
            'href' => $this->getConfigUrl(),
            'desc' => $this->trans('Back to configuration', [], 'Modules.Aplinesimplebenefiticons.Admin'),
            'icon' => 'process-icon-back',
        ];
    }

    public function renderList()
    {
        $list = parent::renderList();

        // Breadcrumb-style back link + mandatory APLINE attribution under the table.
        $back = '<div style="margin:10px 0;"><a class="btn btn-default" href="'
            . htmlspecialchars($this->getConfigUrl(), ENT_QUOTES)
            . '"><i class="icon-chevron-left"></i> '
            . $this->trans('Back to configuration', [], 'Modules.Aplinesimplebenefiticons.Admin')
            . '</a></div>';

        $credit = method_exists($this->module, 'renderAplineFooter')
            ? $this->module->renderAplineFooter()
            : '';

        return $back . $list . $credit;
    }

    /**
     * @param string $image stored public path
     *
     * @return string list cell HTML
     */
    public function printImage($image, $row)
    {
        if (!empty($image)) {
            return '<img src="' . htmlspecialchars($image, ENT_QUOTES) . '" style="max-height:48px;max-width:80px;">';
        }
        if (!empty($row['icon'])) {
            $entity = apline_simple_benefit_icons::normalizeIcon($row['icon']);
            if ($entity !== '') {
                return '<span style="font-size:28px;line-height:1;">' . $entity . '</span>';
            }
        }

        return '<span class="text-muted">&mdash;</span>';
    }

    public function renderForm()
    {
        $this->fields_form = [
            'legend' => [
                'title' => $this->trans('Simple benefit row', [], 'Modules.Aplinesimplebenefiticons.Admin'),
                'icon' => 'icon-th-list',
            ],
            'input' => [
                [
                    'type' => 'file',
                    'label' => $this->trans('Image', [], 'Modules.Aplinesimplebenefiticons.Admin'),
                    'name' => 'image_file',
                    'desc' => $this->trans('Optional. Allowed: JPG, PNG, WEBP. Max 2 MB. Leave empty to keep the current image or to use an icon instead.', [], 'Modules.Aplinesimplebenefiticons.Admin'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->trans('Icon', [], 'Modules.Aplinesimplebenefiticons.Admin'),
                    'name' => 'icon',
                    'desc' => $this->trans('Optional alternative to an image: a unicode hex code (e.g. 1F69A) or an HTML entity (e.g. &#x1F69A;). Used when no image is set.', [], 'Modules.Aplinesimplebenefiticons.Admin'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->trans('Alt', [], 'Modules.Aplinesimplebenefiticons.Admin'),
                    'name' => 'alt',
                    'desc' => $this->trans('Image alternative text. Required when an image is set. If left empty it is auto-filled from the image file name.', [], 'Modules.Aplinesimplebenefiticons.Admin'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->trans('Text', [], 'Modules.Aplinesimplebenefiticons.Admin'),
                    'name' => 'text',
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->trans('URL', [], 'Modules.Aplinesimplebenefiticons.Admin'),
                    'name' => 'url',
                    'desc' => $this->trans('Optional. When set, the whole row becomes a link.', [], 'Modules.Aplinesimplebenefiticons.Admin'),
                ],
                [
                    'type' => 'switch',
                    'label' => $this->trans('Open link in new tab', [], 'Modules.Aplinesimplebenefiticons.Admin'),
                    'name' => 'new_tab',
                    'is_bool' => true,
                    'values' => [
                        ['id' => 'new_tab_on', 'value' => 1, 'label' => $this->trans('Yes', [], 'Admin.Global')],
                        ['id' => 'new_tab_off', 'value' => 0, 'label' => $this->trans('No', [], 'Admin.Global')],
                    ],
                ],
                [
                    'type' => 'switch',
                    'label' => $this->trans('Displayed', [], 'Modules.Aplinesimplebenefiticons.Admin'),
                    'name' => 'active',
                    'is_bool' => true,
                    'values' => [
                        ['id' => 'active_on', 'value' => 1, 'label' => $this->trans('Yes', [], 'Admin.Global')],
                        ['id' => 'active_off', 'value' => 0, 'label' => $this->trans('No', [], 'Admin.Global')],
                    ],
                ],
            ],
            'submit' => ['title' => $this->trans('Save', [], 'Admin.Actions')],
        ];

        // Preview of the current image when editing.
        if (($obj = $this->loadObject(true)) && Validate::isLoadedObject($obj) && !empty($obj->image)) {
            $this->fields_form['input'][0]['image'] =
                '<img src="' . htmlspecialchars($obj->image, ENT_QUOTES) . '" style="max-height:80px;">';
        }

        return parent::renderForm();
    }

    public function postProcess()
    {
        $isAdd = Tools::isSubmit('submitAdd' . $this->table) && !Tools::getValue($this->identifier);
        $isUpdate = Tools::isSubmit('submitAdd' . $this->table) && Tools::getValue($this->identifier);

        if ($isAdd || $isUpdate) {
            $existing = null;
            if ($isUpdate) {
                $existing = new AplineSimpleBenefitIconsItem((int) Tools::getValue($this->identifier));
                if (!Validate::isLoadedObject($existing)) {
                    $this->errors[] = $this->trans('The item you are trying to edit does not exist.', [], 'Modules.Aplinesimplebenefiticons.Admin');

                    return false;
                }
            }

            if (!$this->handleSubmission($existing)) {
                // Errors already pushed to $this->errors: abort before any DB
                // write and keep the form open so the user can fix the input.
                $this->display = $isUpdate ? 'edit' : 'add';

                return false;
            }
        }

        return parent::postProcess();
    }

    /**
     * Validate input and the optional uploaded image, then inject the resulting
     * values into $_POST so the standard ObjectModel save picks them up.
     * On any failure, populate $this->errors and return false (no save happens).
     *
     * @param AplineSimpleBenefitIconsItem|null $existing
     *
     * @return bool
     */
    private function handleSubmission($existing)
    {
        $text = trim((string) Tools::getValue('text'));
        $alt = trim((string) Tools::getValue('alt'));
        $url = trim((string) Tools::getValue('url'));
        $icon = trim((string) Tools::getValue('icon'));

        // 1. Required: text.
        if ($text === '') {
            $this->errors[] = $this->trans('The field "Text" is required.', [], 'Modules.Aplinesimplebenefiticons.Admin');
        }

        // 2. Max length 255 (reject, never truncate).
        foreach (['Text' => $text, 'Alt' => $alt, 'URL' => $url, 'Icon' => $icon] as $label => $value) {
            if (mb_strlen($value) > self::MAX_STRING) {
                $this->errors[] = $this->trans('The field "%s" exceeds the maximum length of 255 characters.', [$label], 'Modules.Aplinesimplebenefiticons.Admin');
            }
        }

        // 3. URL format.
        if ($url !== '' && !Validate::isUrl($url)) {
            $this->errors[] = $this->trans('The URL is not valid.', [], 'Modules.Aplinesimplebenefiticons.Admin');
        }

        // 6. Icon must be a unicode hex or an HTML entity.
        if ($icon !== '' && !preg_match('/^(&#x?[0-9A-Fa-f]+;|&[a-zA-Z]+;|[0-9A-Fa-f]{1,6})$/', $icon)) {
            $this->errors[] = $this->trans('Icon must be a unicode hex code (e.g. 1F69A) or an HTML entity.', [], 'Modules.Aplinesimplebenefiticons.Admin');
        }

        // 4. Image upload validation (only when a file was actually sent).
        $newImagePath = null;
        $autoAlt = null;
        $hasUpload = isset($_FILES['image_file'])
            && isset($_FILES['image_file']['error'])
            && $_FILES['image_file']['error'] !== UPLOAD_ERR_NO_FILE;

        if ($hasUpload) {
            $file = $_FILES['image_file'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $this->errors[] = $this->trans('The image upload failed. Please try again.', [], 'Modules.Aplinesimplebenefiticons.Admin');
            } else {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (!in_array($ext, self::ALLOWED_EXT, true)) {
                    $this->errors[] = $this->trans('Invalid image format. Allowed formats: JPG, PNG, WEBP.', [], 'Modules.Aplinesimplebenefiticons.Admin');
                } elseif ((int) $file['size'] > self::MAX_IMG_BYTES) {
                    $this->errors[] = $this->trans('The image is too large. Maximum size is 2 MB.', [], 'Modules.Aplinesimplebenefiticons.Admin');
                } else {
                    // Inspect real content, not just the extension: blocks an
                    // executable payload renamed with an image extension.
                    $info = @getimagesize($file['tmp_name']);
                    $realMime = is_array($info) && isset($info['mime']) ? $info['mime'] : '';
                    $isRealImage = class_exists('ImageManager')
                        ? ImageManager::isRealImage($file['tmp_name'], $file['type'], self::ALLOWED_MIME)
                        : in_array($realMime, self::ALLOWED_MIME, true);

                    if (!$info || !in_array($realMime, self::ALLOWED_MIME, true) || !$isRealImage) {
                        $this->errors[] = $this->trans('The uploaded file is not a valid image.', [], 'Modules.Aplinesimplebenefiticons.Admin');
                    } else {
                        $fileName = 'asbi_' . uniqid('', true) . '.' . $ext;
                        $dest = $this->module->getUploadDir() . $fileName;

                        if (!@move_uploaded_file($file['tmp_name'], $dest)) {
                            $this->errors[] = $this->trans('Could not save the uploaded image. Check folder permissions.', [], 'Modules.Aplinesimplebenefiticons.Admin');
                        } else {
                            @chmod($dest, 0644);
                            $newImagePath = __PS_BASE_URI__ . 'modules/apline_simple_benefit_icons/views/img/' . $fileName;
                            $autoAlt = pathinfo($file['name'], PATHINFO_FILENAME);
                        }
                    }
                }
            }
        }

        // Resolve the effective image after this submission.
        $effectiveImage = $newImagePath;
        if (null === $effectiveImage && $existing && !empty($existing->image)) {
            $effectiveImage = $existing->image;
        }

        // 5. Alt required when an image is set (auto-fill from file name first).
        if ($effectiveImage) {
            if ($alt === '' && $autoAlt) {
                $alt = mb_substr($autoAlt, 0, self::MAX_STRING);
            }
            if ($alt === '' && $existing && !empty($existing->alt)) {
                $alt = $existing->alt;
            }
            if ($alt === '') {
                $this->errors[] = $this->trans('The field "Alt" is required when an image is set.', [], 'Modules.Aplinesimplebenefiticons.Admin');
            }
        }

        if (!empty($this->errors)) {
            // Clean up a freshly uploaded file if the rest of validation failed.
            if ($newImagePath) {
                @unlink($this->module->getUploadDir() . basename($newImagePath));
            }

            return false;
        }

        // Remove the previous file when it is being replaced.
        if ($newImagePath && $existing && !empty($existing->image)) {
            $old = $this->module->getUploadDir() . basename($existing->image);
            if (is_file($old)) {
                @unlink($old);
            }
        }

        // Feed validated values into the standard ObjectModel save flow.
        $_POST['image'] = $effectiveImage ? $effectiveImage : '';
        $_POST['alt'] = $alt;
        $_POST['text'] = $text;
        $_POST['url'] = $url;
        $_POST['icon'] = $icon;

        return true;
    }

    /**
     * Delete the associated image file when the row is deleted.
     */
    public function processDelete()
    {
        $obj = $this->loadObject(true);
        if (Validate::isLoadedObject($obj) && !empty($obj->image)) {
            $file = $this->module->getUploadDir() . basename($obj->image);
            if (is_file($file)) {
                @unlink($file);
            }
        }

        return parent::processDelete();
    }

    public function ajaxProcessUpdatePositions()
    {
        $positions = Tools::getValue($this->table);

        if (!is_array($positions)) {
            die(json_encode(['success' => false]));
        }

        // Reindex deterministically from the order posted by the sortable list:
        // the array order is the new visual order, so assign 1..n sequentially.
        $pos = 1;
        foreach ($positions as $value) {
            // Row token looks like "<table>_<id>" or "<table>_<x>_<id>";
            // the object id is always the last numeric segment.
            $parts = explode('_', (string) $value);
            $id = (int) end($parts);
            if (!$id) {
                continue;
            }
            Db::getInstance()->update(
                'asbi_item',
                ['position' => $pos++],
                'id_asbi_item = ' . $id
            );
        }

        die(json_encode(['success' => true]));
    }
}
