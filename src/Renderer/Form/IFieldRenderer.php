<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Form;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\IField;

/**
 * The field renderer interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IFieldRenderer
{
    /**
     * Gets the expected class of the field type for the field.
     *
     * @return array
     */
    public function getFieldTypeClasses() : array;

    /**
     * Returns whether this renderer can render the supplied field.
     *
     * @param IField $field
     *
     * @return bool
     */
    public function accepts(IField $field) : bool;

    /**
     * Renders the supplied field input as a html string.
     *
     * @param IField $field
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function render(IField $field) : string;

    /**
     * Renders the supplied field value display as a html string.
     *
     * @param IField     $field
     * @param mixed|null $overrideValue
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function renderValue(IField $field, $overrideValue = null) : string;

    /**
     * Sets the parent field renderer.
     *
     * @param FieldRendererCollection $fieldRenderer
     *
     * @return void
     */
    public function setRendererCollection(FieldRendererCollection $fieldRenderer);
}