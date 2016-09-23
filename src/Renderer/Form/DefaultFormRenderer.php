<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Form;

use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\IForm;
use Dms\Core\Form\Processor\Validator\FieldComparisonValidator;
use Dms\Core\Form\Processor\Validator\FieldGreaterThanAnotherValidator;
use Dms\Core\Form\Processor\Validator\FieldGreaterThanOrEqualAnotherValidator;
use Dms\Core\Form\Processor\Validator\FieldLessThanAnotherValidator;
use Dms\Core\Form\Processor\Validator\FieldLessThanOrEqualAnotherValidator;
use Dms\Core\Form\Processor\Validator\MatchingFieldsValidator;

/**
 * The default form renderer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DefaultFormRenderer extends FormRenderer
{
    /**
     * @inheritDoc
     */
    public function accepts(FormRenderingContext $renderingContext, IForm $form) : bool
    {
        return true;
    }

    /**
     * Renders the supplied form as a html string.
     *
     * @param FormRenderingContext $renderingContext
     * @param IForm                $form
     *
     * @return string
     * @throws UnrenderableFieldException
     */
    protected function renderFormFields(FormRenderingContext $renderingContext, IForm $form) : string
    {
        $sections = [];

        foreach ($form->getSections() as $section) {
            $title = $section->getTitle();

            foreach ($section->getFields() as $field) {
                $sections[$title][$field->getLabel()] = [
                    'name'     => $field->getName(),
                    'content'  => $this->fieldRenderers->findRendererFor($renderingContext, $field)->render($renderingContext, $field),
                    'hidden'   => $field->getType()->get(FieldType::ATTR_HIDDEN),
                    'helpText' => $field->getType()->get('help-text'),
                ];
            }
        }

        return view('dms::components.form.form-fields')
            ->with([
                'groupedFields'            => $sections,
                'equalFields'              => $this->findFieldsFromValidator($form, MatchingFieldsValidator::class),
                'greaterThanFields'        => $this->findFieldsFromValidator($form, FieldGreaterThanAnotherValidator::class),
                'greaterThanOrEqualFields' => $this->findFieldsFromValidator($form, FieldGreaterThanOrEqualAnotherValidator::class),
                'lessThanFields'           => $this->findFieldsFromValidator($form, FieldLessThanAnotherValidator::class),
                'lessThanOrEqualFields'    => $this->findFieldsFromValidator($form, FieldLessThanOrEqualAnotherValidator::class),
            ])
            ->render();
    }

    /**
     * Renders the supplied form as a html string.
     *
     * @param FormRenderingContext $renderingContext
     * @param IForm                $form
     *
     * @return string
     * @throws UnrenderableFieldException
     */
    protected function renderFormFieldsAsValues(FormRenderingContext $renderingContext, IForm $form) : string
    {
        $sections = [];

        foreach ($form->getSections() as $section) {
            $title = $section->getTitle();

            foreach ($section->getFields() as $field) {
                $sections[$title][$field->getLabel()] = [
                    'name'    => $field->getName(),
                    'content' => $this->fieldRenderers->findRendererFor($renderingContext, $field)->renderValue($renderingContext, $field),
                    'hidden'  => $field->getType()->get(FieldType::ATTR_HIDDEN),
                ];
            }
        }
        
        return view('dms::components.form.form-fields')
            ->with(['groupedFields' => $sections])
            ->render();
    }

    private function findFieldsFromValidator(IForm $form, $validatorClass)
    {
        $fields = [];

        foreach ($form->getProcessors() as $processor) {
            /** @var FieldComparisonValidator $processor */
            if ($processor instanceof $validatorClass) {
                $fields[$processor->getField1()->getName()] = $processor->getField2()->getName();
            }
        }

        return $fields;
    }
}