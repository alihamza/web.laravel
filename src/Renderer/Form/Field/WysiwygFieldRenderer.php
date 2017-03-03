<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Renderer\Form\Field;

use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\Field\Type\StringType;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldType;
use Dms\Web\Laravel\Renderer\Form\FormRenderingContext;

/**
 * The wysiwyg field renderer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class WysiwygFieldRenderer extends BladeFieldRenderer
{
    /**
     * Gets the expected class of the field type for the field.
     *
     * @return array
     */
    public function getFieldTypeClasses() : array
    {
        return [StringType::class];
    }

    protected function canRender(FormRenderingContext $renderingContext, IField $field, IFieldType $fieldType) : bool
    {
        return !$fieldType->has(FieldType::ATTR_OPTIONS)
        && $fieldType->get(StringType::ATTR_STRING_TYPE) === StringType::TYPE_HTML;
    }

    protected function renderField(FormRenderingContext $renderingContext, IField $field, IFieldType $fieldType) : string
    {
        return $this->renderView(
            $field,
            'dms::components.field.wysiwyg.input',
            [
                StringType::ATTR_EXACT_LENGTH => 'exactLength',
                StringType::ATTR_MIN_LENGTH   => 'minLength',
                StringType::ATTR_MAX_LENGTH   => 'maxLength',
            ],
            [
                'lightMode' => $fieldType->get('light-mode')
            ]
        );
    }

    protected function renderFieldValue(FormRenderingContext $renderingContext, IField $field, $value, IFieldType $fieldType) : string
    {
        return $this->renderValueViewWithNullDefault(
            $field, $value,
            'dms::components.field.wysiwyg.value'
        );
    }
}