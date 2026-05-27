/**
 * Formula Builder - Helper for formula construction
 * Provides UI widgets and validation for formula fields
 */
class FormulaBuilder {
    constructor(textareaElement, options = {}) {
        this.textarea = textareaElement;
        this.availableFields = options.availableFields || [];
        this.availableFunctions = options.availableFunctions || ['round', 'abs', 'min', 'max', 'sqrt', 'floor', 'ceil'];

        this.init();
    }

    init() {
        this.createHelperUI();
        this.setupEventListeners();
    }

    /**
     * Create helper UI (buttons for variables and functions)
     */
    createHelperUI() {
        if (!this.textarea) return;

        const container = document.createElement('div');
        container.className = 'formula-builder-helper mt-2';

        // Variables section
        const varsSection = document.createElement('div');
        varsSection.className = 'mb-2';
        varsSection.innerHTML = '<label class="form-label font-0"><small>Variables:</small></label>';

        const varsButtonGroup = document.createElement('div');
        varsButtonGroup.className = 'btn-group-vertical w-100 gap-1';

        this.availableFields.forEach(field => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-secondary btn-sm text-start';
            btn.innerHTML = `<code>${field.field_key}</code> - ${field.label}`;
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.insertVariable(field.field_key);
            });
            varsButtonGroup.appendChild(btn);
        });

        varsSection.appendChild(varsButtonGroup);

        // Functions section
        const funcsSection = document.createElement('div');
        funcsSection.className = 'mt-2';
        funcsSection.innerHTML = '<label class="form-label"><small>Functions:</small></label>';

        const funcsButtonGroup = document.createElement('div');
        funcsButtonGroup.className = 'btn-group-vertical w-100 gap-1';

        this.availableFunctions.forEach(func => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-info btn-sm text-start';
            btn.innerHTML = `<code>${func}()</code>`;
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.insertFunction(func);
            });
            funcsButtonGroup.appendChild(btn);
        });

        funcsSection.appendChild(funcsButtonGroup);

        container.appendChild(varsSection);
        container.appendChild(funcsSection);

        // Insert after textarea
        this.textarea.parentNode.insertBefore(container, this.textarea.nextSibling);
    }

    /**
     * Insert variable into formula
     */
    insertVariable(fieldKey) {
        this.insertAtCursor(fieldKey);
    }

    /**
     * Insert function template
     */
    insertFunction(functionName) {
        const args = this.getFunctionArgs(functionName);
        this.insertAtCursor(`${functionName}(${args})`);
    }

    /**
     * Get function argument template
     */
    getFunctionArgs(functionName) {
        const templates = {
            round: 'value, 2',
            abs: 'value',
            min: 'value1, value2',
            max: 'value1, value2',
            sqrt: 'value',
            floor: 'value',
            ceil: 'value'
        };
        return templates[functionName] || 'args';
    }

    /**
     * Insert text at cursor position
     */
    insertAtCursor(text) {
        const start = this.textarea.selectionStart;
        const end = this.textarea.selectionEnd;
        const before = this.textarea.value.substring(0, start);
        const after = this.textarea.value.substring(end);

        this.textarea.value = before + text + after;
        this.textarea.selectionStart = start + text.length;
        this.textarea.selectionEnd = start + text.length;
        this.textarea.focus();

        // Trigger change event
        this.textarea.dispatchEvent(new Event('change'));
    }

    /**
     * Validate formula (static method)
     */
    static async validateFormula(formula, csrfToken = '') {
        try {
            const validateUrl = (document.querySelector('[data-validate-url]') ? document.querySelector('[data-validate-url]').dataset.validateUrl : null) || '/dashboard/templates/fields/validate';

            const response = await fetch(validateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || (document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '') || ''
                },
                body: JSON.stringify({ formula })
            });

            const data = await response.json();
            return data;
        } catch (err) {
            console.error('Formula validation error:', err);
            return { valid: false, error: 'Validation failed' };
        }
    }
}

/**
 * Initialize formula builders for all formula type fields
 */
document.addEventListener('DOMContentLoaded', function() {
    // This will be called by TemplateEditor when needed
    window.initFormulaBuilders = function(fields = []) {
        const formulaTextareas = document.querySelectorAll('[data-formula-field]');

        formulaTextareas.forEach(textarea => {
            if (!textarea.dataset.builderInitialized) {
                const nonFormulaFields = fields.filter(f => f.field_type !== 'formula');
                new FormulaBuilder(textarea, {
                    availableFields: nonFormulaFields
                });
                textarea.dataset.builderInitialized = 'true';
            }
        });
    };
});
