<?php
namespace verbb\formie\fields\formfields;

use craft\base\ElementInterface;
use craft\fields\data\MultiOptionsFieldData;
use craft\fields\data\OptionData;
use craft\fields\data\SingleOptionFieldData;
use verbb\formie\base\FormFieldTrait;

use Craft;
use craft\fields\BaseOptionsField as CraftBaseOptionsField;

abstract class BaseOptionsField extends CraftBaseOptionsField
{
    // Traits
    // =========================================================================

    use FormFieldTrait {
        getFrontEndInputOptions as traitGetFrontendInputOptions;
    }


    // Public Properties
    // =========================================================================

    /**
     * @var string vertical or horizontal layout
     */
    public $layout;


    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        foreach ($this->options as &$option) {
            unset($option['isNew']);
        }
    }

    /**
     * @inheritDoc
     */
    public function getValue(ElementInterface $element)
    {
        $value = $element->getFieldValue($this->handle);

        if ($value instanceof SingleOptionFieldData) {
            return $value->value;
        } elseif ($value instanceof MultiOptionsFieldData) {
            $values = [];
            foreach ($value as $selectedValue) {
                /** @var OptionData $selectedValue */
                $values[] = $selectedValue->value;
            }
        }

        return null;
    }

    /**
     * Validates the options.
     */
    public function validateOptions()
    {
        $labels = [];
        $values = [];
        $hasDuplicateLabels = false;
        $hasDuplicateValues = false;
        $optgroup = '__root__';

        foreach ($this->options as &$option) {
            // Ignore optgroups
            if (array_key_exists('optgroup', $option)) {
                $optgroup = $option['optgroup'];
                continue;
            }

            $label = (string)$option['label'];
            $value = (string)$option['value'];

            if (isset($labels[$optgroup][$label])) {
                $option['hasDuplicateLabels'] = true;
                $hasDuplicateLabels = true;
            }

            if (isset($values[$value])) {
                $option['hasDuplicateValues'] = true;
                $hasDuplicateValues = true;
            }
            $labels[$optgroup][$label] = $values[$value] = true;
        }

        if ($hasDuplicateLabels) {
            $this->addError('options', Craft::t('app', 'All option labels must be unique.'));
        }
        if ($hasDuplicateValues) {
            $this->addError('options', Craft::t('app', 'All option values must be unique.'));
        }
    }

    /**
     * @inheritDoc
     */
    public function getSavedSettings(): array
    {
        return $this->getSettings();
    }
}
