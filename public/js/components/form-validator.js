/**
 * UniPrint Form Validator
 * Enhanced form validation with real-time feedback
 */

class FormValidator {
    constructor(form, options = {}) {
        this.form = typeof form === 'string' ? document.querySelector(form) : form;
        this.options = {
            validateOnInput: true,
            validateOnBlur: true,
            showSuccessState: true,
            debounceDelay: 300,
            ...options
        };
        
        this.rules = new Map();
        this.errors = new Map();
        this.isValid = false;
        this.debounceTimers = new Map();
        
        this.init();
    }
    
    init() {
        if (!this.form) {
            console.error('Form not found');
            return;
        }
        
        this.setupEventListeners();
        this.parseFormRules();
    }
    
    /**
     * Set up event listeners
     */
    setupEventListeners() {
        if (this.options.validateOnInput) {
            this.form.addEventListener('input', (e) => {
                this.debounceValidation(e.target);
            });
        }
        
        if (this.options.validateOnBlur) {
            this.form.addEventListener('blur', (e) => {
                this.validateField(e.target);
            }, true);
        }
        
        this.form.addEventListener('submit', (e) => {
            if (!this.validateForm()) {
                e.preventDefault();
                this.focusFirstError();
            }
        });
    }
    
    /**
     * Parse validation rules from form attributes
     */
    parseFormRules() {
        const fields = this.form.querySelectorAll('[data-validate]');
        
        fields.forEach(field => {
            const rules = this.parseRules(field.dataset.validate);
            this.addFieldRules(field.name || field.id, rules);
        });
    }
    
    /**
     * Parse validation rules string
     */
    parseRules(rulesString) {
        const rules = [];
        const ruleParts = rulesString.split('|');
        
        ruleParts.forEach(rule => {
            const [name, ...params] = rule.split(':');
            rules.push({
                name: name.trim(),
                params: params.length > 0 ? params[0].split(',') : []
            });
        });
        
        return rules;
    }
    
    /**
     * Add validation rules for a field
     */
    addFieldRules(fieldName, rules) {
        this.rules.set(fieldName, rules);
    }
    
    /**
     * Add custom validation rule
     */
    addRule(name, validator, message) {
        this.customRules = this.customRules || new Map();
        this.customRules.set(name, { validator, message });
    }
    
    /**
     * Debounced validation
     */
    debounceValidation(field) {
        const fieldName = field.name || field.id;
        
        if (this.debounceTimers.has(fieldName)) {
            clearTimeout(this.debounceTimers.get(fieldName));
        }
        
        const timer = setTimeout(() => {
            this.validateField(field);
        }, this.options.debounceDelay);
        
        this.debounceTimers.set(fieldName, timer);
    }
    
    /**
     * Validate single field
     */
    validateField(field) {
        const fieldName = field.name || field.id;
        const rules = this.rules.get(fieldName);
        
        if (!rules) return true;
        
        const value = field.value;
        const errors = [];
        
        for (const rule of rules) {
            const result = this.applyRule(rule, value, field);
            if (result !== true) {
                errors.push(result);
                break; // Stop at first error
            }
        }
        
        if (errors.length > 0) {
            this.errors.set(fieldName, errors);
            this.showFieldError(field, errors[0]);
            return false;
        } else {
            this.errors.delete(fieldName);
            this.showFieldSuccess(field);
            return true;
        }
    }
    
    /**
     * Apply validation rule
     */
    applyRule(rule, value, field) {
        const { name, params } = rule;
        
        // Check custom rules first
        if (this.customRules && this.customRules.has(name)) {
            const customRule = this.customRules.get(name);
            const isValid = customRule.validator(value, params, field);
            return isValid ? true : customRule.message;
        }
        
        // Built-in rules
        switch (name) {
            case 'required':
                return value.trim() !== '' ? true : 'This field is required.';
                
            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(value) ? true : 'Please enter a valid email address.';
                
            case 'min':
                const minLength = parseInt(params[0]);
                return value.length >= minLength ? true : `Minimum ${minLength} characters required.`;
                
            case 'max':
                const maxLength = parseInt(params[0]);
                return value.length <= maxLength ? true : `Maximum ${maxLength} characters allowed.`;
                
            case 'numeric':
                return /^\d+$/.test(value) ? true : 'Please enter numbers only.';
                
            case 'alpha':
                return /^[a-zA-Z]+$/.test(value) ? true : 'Please enter letters only.';
                
            case 'alphanumeric':
                return /^[a-zA-Z0-9]+$/.test(value) ? true : 'Please enter letters and numbers only.';
                
            case 'url':
                try {
                    new URL(value);
                    return true;
                } catch {
                    return 'Please enter a valid URL.';
                }
                
            case 'confirmed':
                const confirmField = this.form.querySelector(`[name="${params[0]}"]`);
                return confirmField && value === confirmField.value ? true : 'Fields do not match.';
                
            case 'regex':
                const regex = new RegExp(params[0]);
                return regex.test(value) ? true : 'Invalid format.';
                
            default:
                return true;
        }
    }
    
    /**
     * Show field error
     */
    showFieldError(field, message) {
        this.clearFieldState(field);
        
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        
        // Create or update error message
        let errorElement = field.parentNode.querySelector('.invalid-feedback');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'invalid-feedback';
            field.parentNode.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
        errorElement.style.display = 'block';
        
        // Add error icon
        this.addFieldIcon(field, 'bi-exclamation-circle', 'text-danger');
    }
    
    /**
     * Show field success
     */
    showFieldSuccess(field) {
        if (!this.options.showSuccessState) return;
        
        this.clearFieldState(field);
        
        field.classList.add('is-valid');
        field.classList.remove('is-invalid');
        
        // Add success icon
        this.addFieldIcon(field, 'bi-check-circle', 'text-success');
    }
    
    /**
     * Clear field state
     */
    clearFieldState(field) {
        field.classList.remove('is-valid', 'is-invalid');
        
        // Remove error message
        const errorElement = field.parentNode.querySelector('.invalid-feedback');
        if (errorElement) {
            errorElement.style.display = 'none';
        }
        
        // Remove icon
        const iconElement = field.parentNode.querySelector('.field-icon');
        if (iconElement) {
            iconElement.remove();
        }
    }
    
    /**
     * Add field icon
     */
    addFieldIcon(field, iconClass, colorClass) {
        // Remove existing icon
        const existingIcon = field.parentNode.querySelector('.field-icon');
        if (existingIcon) {
            existingIcon.remove();
        }
        
        // Create new icon
        const icon = document.createElement('i');
        icon.className = `bi ${iconClass} field-icon ${colorClass}`;
        icon.style.cssText = 'position: absolute; right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5;';
        
        // Make parent relative if not already
        if (getComputedStyle(field.parentNode).position === 'static') {
            field.parentNode.style.position = 'relative';
        }
        
        field.parentNode.appendChild(icon);
    }
    
    /**
     * Validate entire form
     */
    validateForm() {
        let isValid = true;
        const fields = this.form.querySelectorAll('input, textarea, select');
        
        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        
        this.isValid = isValid;
        return isValid;
    }
    
    /**
     * Focus first error field
     */
    focusFirstError() {
        const firstErrorField = this.form.querySelector('.is-invalid');
        if (firstErrorField) {
            firstErrorField.focus();
            firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
    
    /**
     * Get all errors
     */
    getErrors() {
        return Object.fromEntries(this.errors);
    }
    
    /**
     * Clear all errors
     */
    clearErrors() {
        this.errors.clear();
        const fields = this.form.querySelectorAll('.is-invalid, .is-valid');
        fields.forEach(field => this.clearFieldState(field));
    }
    
    /**
     * Set field error manually
     */
    setFieldError(fieldName, message) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        if (field) {
            this.errors.set(fieldName, [message]);
            this.showFieldError(field, message);
        }
    }
    
    /**
     * Get form data
     */
    getFormData() {
        const formData = new FormData(this.form);
        const data = {};
        
        for (const [key, value] of formData.entries()) {
            if (data[key]) {
                // Handle multiple values (checkboxes, etc.)
                if (Array.isArray(data[key])) {
                    data[key].push(value);
                } else {
                    data[key] = [data[key], value];
                }
            } else {
                data[key] = value;
            }
        }
        
        return data;
    }
    
    /**
     * Reset form
     */
    reset() {
        this.form.reset();
        this.clearErrors();
    }
    
    /**
     * Destroy validator
     */
    destroy() {
        this.clearErrors();
        this.debounceTimers.forEach(timer => clearTimeout(timer));
        this.debounceTimers.clear();
    }
}

// Export for global usage
window.FormValidator = FormValidator;

// Auto-initialize forms with data-validate-form attribute
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('[data-validate-form]');
    forms.forEach(form => {
        new FormValidator(form);
    });
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FormValidator;
}
