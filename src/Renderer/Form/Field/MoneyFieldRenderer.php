<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Form\Field;

use Dms\Common\Structure\Money\Form\MoneyType;
use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldOptions;
use Dms\Core\Form\IFieldType;
use Dms\Web\Laravel\Renderer\Form\FormRenderingContext;

/**
 * The money field renderer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MoneyFieldRenderer extends BladeFieldRenderer
{
    /**
     * Gets the expected class of the field type for the field.
     *
     * @return array
     */
    public function getFieldTypeClasses() : array
    {
        return [MoneyType::class];
    }

    protected function canRender(FormRenderingContext $renderingContext, IField $field, IFieldType $fieldType) : bool
    {
        return !$fieldType->has(FieldType::ATTR_OPTIONS);
    }

    protected function renderField(FormRenderingContext $renderingContext, IField $field, IFieldType $fieldType) : string
    {
        /** @var MoneyType $fieldType */
        /** @var IFieldOptions $currencyOptions */
        $currencyOptions = $fieldType->getForm()
            ->getField('currency')
            ->getType()
            ->get(FieldType::ATTR_OPTIONS);

        return $this->renderView(
            $field,
            'dms::components.field.money.input',
            [

            ],
            [
                'currencyOptions' => $currencyOptions,
                'defaultCurrency' => config('dms.localisation.form.defaults.currency'),
            ]
        );
    }

    protected function renderFieldValue(FormRenderingContext $renderingContext, IField $field, $value, IFieldType $fieldType) : string
    {
        return $this->renderValueViewWithNullDefault(
            $field, $value,
            'dms::components.field.money.value'
        );
    }
}