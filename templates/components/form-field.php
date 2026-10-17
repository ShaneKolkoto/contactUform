<?php

declare(strict_types=1);

/**
 * Renders a form field: label, input/textarea, optional hint,
 * optional inline error.
 *
 * Expected keys in $props:
 *   name        string   Required. Field name and input id.
 *   label       string   Required. Displayed in the <label>.
 *   type        string   Input type (text/email/tel). Ignored for textarea.
 *   value       string   Current value (already trimmed by caller).
 *   error       string   Error message, or empty string.
 *   hint        string   Helper text below the field.
 *   required    bool     Shows a red asterisk and sets required attr.
 *   maxlength   int      Max characters.
 *   placeholder string   Placeholder text.
 *   textarea    bool     Renders as <textarea> if true.
 *   rows        int      Textarea row count (default 5).
 *   autocomplete string  Browser autocomplete hint.
 */
function render_form_field(array $props): void
{
    $name = $props['name'];
    $label = $props['label'];
    $type = $props['type'] ?? 'text';
    $value = $props['value'] ?? '';
    $error = $props['error'] ?? '';
    $hint = $props['hint'] ?? '';
    $required = $props['required'] ?? false;
    $maxlength = $props['maxlength'] ?? null;
    $placeholder = $props['placeholder'] ?? '';
    $isTextarea = $props['textarea'] ?? false;
    $rows = $props['rows'] ?? 5;
    $autocomplete = $props['autocomplete'] ?? '';

    $inputId = $name;
    $errorId = "{$name}-error";
    $hintId = "{$name}-hint";

    // Build aria-describedby from whatever is present
    $describedBy = [];
    if ($hint !== '') {
        $describedBy[] = $hintId;
    }
    if ($error !== '') {
        $describedBy[] = $errorId;
    }

    $classNames = 'form-control' . ($error !== '' ? ' is-invalid' : '');
    ?>
    <div class="mb-3">
        <label for="<?= e($inputId) ?>" class="form-label">
            <?= e($label) ?>
            <?php if ($required): ?><span class="text-danger">*</span><?php endif; ?>
        </label>

        <?php if ($isTextarea): ?>
            <textarea
                id="<?= e($inputId) ?>"
                name="<?= e($name) ?>"
                class="<?= e($classNames) ?>"
                rows="<?= (int) $rows ?>"
                <?= $maxlength !== null ? 'maxlength="' . (int) $maxlength . '"' : '' ?>
                <?= $required ? 'required' : '' ?>
                <?= $error !== '' ? 'aria-invalid="true"' : '' ?>
                <?= $describedBy !== [] ? 'aria-describedby="' . e(implode(' ', $describedBy)) . '"' : '' ?>
            ><?= e($value) ?></textarea>
        <?php else: ?>
            <input
                type="<?= e($type) ?>"
                id="<?= e($inputId) ?>"
                name="<?= e($name) ?>"
                class="<?= e($classNames) ?>"
                value="<?= e($value) ?>"
                <?= $placeholder !== '' ? 'placeholder="' . e($placeholder) . '"' : '' ?>
                <?= $maxlength !== null ? 'maxlength="' . (int) $maxlength . '"' : '' ?>
                <?= $autocomplete !== '' ? 'autocomplete="' . e($autocomplete) . '"' : '' ?>
                <?= $required ? 'required' : '' ?>
                <?= $error !== '' ? 'aria-invalid="true"' : '' ?>
                <?= $describedBy !== [] ? 'aria-describedby="' . e(implode(' ', $describedBy)) . '"' : '' ?>
            >
        <?php endif; ?>

        <?php if ($hint !== ''): ?>
            <div class="form-text" id="<?= e($hintId) ?>"><?= e($hint) ?></div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="invalid-feedback" id="<?= e($errorId) ?>"><?= e($error) ?></div>
        <?php endif; ?>
    </div>
    <?php
}