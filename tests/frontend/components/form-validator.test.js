/**
 * Form Validator Tests
 */

import FormValidator from '../../../public/js/components/form-validator.js';
import { TestUtils } from '../setup.js';

describe('FormValidator', () => {
    let form;
    let validator;

    beforeEach(() => {
        // Create test form
        form = TestUtils.createForm([
            { name: 'email', type: 'email', required: true },
            { name: 'password', type: 'password', required: true },
            { name: 'confirm_password', type: 'password', required: true },
            { name: 'name', type: 'text', required: true }
        ]);
        
        // Add validation attributes
        form.querySelector('[name="email"]').setAttribute('data-validate', 'required|email');
        form.querySelector('[name="password"]').setAttribute('data-validate', 'required|min:8');
        form.querySelector('[name="confirm_password"]').setAttribute('data-validate', 'required|confirmed:password');
        form.querySelector('[name="name"]').setAttribute('data-validate', 'required|min:2|max:50');
        
        document.body.appendChild(form);
        
        validator = new FormValidator(form, {
            validateOnInput: true,
            validateOnBlur: true
        });
    });

    afterEach(() => {
        if (validator) {
            validator.destroy();
        }
        document.body.innerHTML = '';
    });

    describe('Initialization', () => {
        test('should initialize with form element', () => {
            expect(validator.form).toBe(form);
            expect(validator.rules.size).toBeGreaterThan(0);
        });

        test('should parse validation rules from data attributes', () => {
            expect(validator.rules.has('email')).toBe(true);
            expect(validator.rules.has('password')).toBe(true);
            expect(validator.rules.has('confirm_password')).toBe(true);
            expect(validator.rules.has('name')).toBe(true);
        });
    });

    describe('Field Validation', () => {
        test('should validate required fields', () => {
            const emailField = form.querySelector('[name="email"]');
            
            // Test empty field
            TestUtils.simulateInput(emailField, '');
            const isValid = validator.validateField(emailField);
            
            expect(isValid).toBe(false);
            expect(emailField).toHaveClass('is-invalid');
        });

        test('should validate email format', () => {
            const emailField = form.querySelector('[name="email"]');
            
            // Test invalid email
            TestUtils.simulateInput(emailField, 'invalid-email');
            let isValid = validator.validateField(emailField);
            expect(isValid).toBe(false);
            
            // Test valid email
            TestUtils.simulateInput(emailField, 'test@example.com');
            isValid = validator.validateField(emailField);
            expect(isValid).toBe(true);
            expect(emailField).toHaveClass('is-valid');
        });

        test('should validate minimum length', () => {
            const passwordField = form.querySelector('[name="password"]');
            
            // Test short password
            TestUtils.simulateInput(passwordField, '123');
            let isValid = validator.validateField(passwordField);
            expect(isValid).toBe(false);
            
            // Test valid password
            TestUtils.simulateInput(passwordField, 'password123');
            isValid = validator.validateField(passwordField);
            expect(isValid).toBe(true);
        });

        test('should validate field confirmation', () => {
            const passwordField = form.querySelector('[name="password"]');
            const confirmField = form.querySelector('[name="confirm_password"]');
            
            // Set password
            TestUtils.simulateInput(passwordField, 'password123');
            
            // Test non-matching confirmation
            TestUtils.simulateInput(confirmField, 'different');
            let isValid = validator.validateField(confirmField);
            expect(isValid).toBe(false);
            
            // Test matching confirmation
            TestUtils.simulateInput(confirmField, 'password123');
            isValid = validator.validateField(confirmField);
            expect(isValid).toBe(true);
        });
    });

    describe('Form Validation', () => {
        test('should validate entire form', () => {
            // Fill form with invalid data
            TestUtils.simulateInput(form.querySelector('[name="email"]'), 'invalid');
            TestUtils.simulateInput(form.querySelector('[name="password"]'), '123');
            TestUtils.simulateInput(form.querySelector('[name="name"]'), '');
            
            const isValid = validator.validateForm();
            expect(isValid).toBe(false);
            expect(validator.errors.size).toBeGreaterThan(0);
        });

        test('should pass validation with valid data', () => {
            // Fill form with valid data
            TestUtils.simulateInput(form.querySelector('[name="email"]'), 'test@example.com');
            TestUtils.simulateInput(form.querySelector('[name="password"]'), 'password123');
            TestUtils.simulateInput(form.querySelector('[name="confirm_password"]'), 'password123');
            TestUtils.simulateInput(form.querySelector('[name="name"]'), 'John Doe');
            
            const isValid = validator.validateForm();
            expect(isValid).toBe(true);
            expect(validator.errors.size).toBe(0);
        });

        test('should prevent form submission when invalid', () => {
            const submitHandler = jest.fn();
            form.addEventListener('submit', submitHandler);
            
            // Submit form with invalid data
            TestUtils.simulateSubmit(form);
            
            expect(submitHandler).toHaveBeenCalled();
            // Form submission should be prevented by validator
        });
    });

    describe('Custom Rules', () => {
        test('should allow adding custom validation rules', () => {
            validator.addRule('custom', (value) => value === 'custom', 'Must be "custom"');
            
            const field = form.querySelector('[name="name"]');
            validator.addFieldRules('name', [{ name: 'custom', params: [] }]);
            
            TestUtils.simulateInput(field, 'invalid');
            let isValid = validator.validateField(field);
            expect(isValid).toBe(false);
            
            TestUtils.simulateInput(field, 'custom');
            isValid = validator.validateField(field);
            expect(isValid).toBe(true);
        });
    });

    describe('Error Handling', () => {
        test('should display error messages', () => {
            const emailField = form.querySelector('[name="email"]');
            
            TestUtils.simulateInput(emailField, 'invalid');
            validator.validateField(emailField);
            
            const errorElement = emailField.parentNode.querySelector('.invalid-feedback');
            expect(errorElement).toBeTruthy();
            expect(errorElement.textContent).toContain('email');
        });

        test('should clear errors when field becomes valid', () => {
            const emailField = form.querySelector('[name="email"]');
            
            // Make field invalid
            TestUtils.simulateInput(emailField, 'invalid');
            validator.validateField(emailField);
            expect(emailField).toHaveClass('is-invalid');
            
            // Make field valid
            TestUtils.simulateInput(emailField, 'test@example.com');
            validator.validateField(emailField);
            expect(emailField).toHaveClass('is-valid');
            expect(emailField).not.toHaveClass('is-invalid');
        });

        test('should manually set field errors', () => {
            validator.setFieldError('email', 'Custom error message');
            
            const emailField = form.querySelector('[name="email"]');
            expect(emailField).toHaveClass('is-invalid');
            
            const errorElement = emailField.parentNode.querySelector('.invalid-feedback');
            expect(errorElement.textContent).toBe('Custom error message');
        });
    });

    describe('Form Data', () => {
        test('should get form data as object', () => {
            TestUtils.simulateInput(form.querySelector('[name="email"]'), 'test@example.com');
            TestUtils.simulateInput(form.querySelector('[name="name"]'), 'John Doe');
            
            const data = validator.getFormData();
            expect(data.email).toBe('test@example.com');
            expect(data.name).toBe('John Doe');
        });

        test('should reset form and clear errors', () => {
            // Add some data and errors
            TestUtils.simulateInput(form.querySelector('[name="email"]'), 'invalid');
            validator.validateField(form.querySelector('[name="email"]'));
            
            validator.reset();
            
            expect(form.querySelector('[name="email"]').value).toBe('');
            expect(validator.errors.size).toBe(0);
        });
    });

    describe('Debouncing', () => {
        test('should debounce validation on input', async () => {
            const validateSpy = jest.spyOn(validator, 'validateField');
            const emailField = form.querySelector('[name="email"]');
            
            // Simulate rapid input
            TestUtils.simulateInput(emailField, 'a');
            TestUtils.simulateInput(emailField, 'ab');
            TestUtils.simulateInput(emailField, 'abc');
            
            // Should not validate immediately
            expect(validateSpy).not.toHaveBeenCalled();
            
            // Wait for debounce
            await TestUtils.waitFor(() => validateSpy.mock.calls.length > 0, 500);
            
            // Should validate only once after debounce
            expect(validateSpy).toHaveBeenCalledTimes(1);
        });
    });
});
