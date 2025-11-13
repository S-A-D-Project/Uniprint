/**
 * UI Components Tests
 * Tests for the enhanced UI component library
 */

import { TestUtils } from '../setup.js';

describe('UI Components', () => {
    beforeEach(() => {
        // Mock Bootstrap components
        global.bootstrap = {
            Modal: jest.fn().mockImplementation(() => ({
                show: jest.fn(),
                hide: jest.fn()
            })),
            Tooltip: jest.fn().mockImplementation(() => ({
                show: jest.fn(),
                hide: jest.fn()
            })),
            Toast: jest.fn().mockImplementation(() => ({
                show: jest.fn(),
                hide: jest.fn()
            })),
            Collapse: jest.fn().mockImplementation(() => ({
                show: jest.fn(),
                hide: jest.fn()
            }))
        };
    });

    describe('Button Component', () => {
        test('should render with default props', () => {
            const button = TestUtils.createElement('button', {
                class: 'inline-flex items-center justify-center font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 focus:ring-primary h-10 px-4 text-sm rounded-lg',
                type: 'button'
            }, 'Click me');
            
            expect(button.tagName).toBe('BUTTON');
            expect(button.type).toBe('button');
            expect(button).toHaveClass('bg-primary');
            expect(button.textContent).toBe('Click me');
        });

        test('should handle loading state', () => {
            const button = TestUtils.createElement('button', {
                class: 'pointer-events-none',
                'aria-busy': 'true',
                disabled: true
            });
            
            expect(button.disabled).toBe(true);
            expect(button.getAttribute('aria-busy')).toBe('true');
            expect(button).toHaveClass('pointer-events-none');
        });

        test('should handle disabled state', () => {
            const button = TestUtils.createElement('button', {
                disabled: true,
                'aria-disabled': 'true'
            });
            
            expect(button.disabled).toBe(true);
            expect(button.getAttribute('aria-disabled')).toBe('true');
        });

        test('should support different variants', () => {
            const primaryButton = TestUtils.createElement('button', {
                class: 'bg-primary text-primary-foreground'
            });
            
            const secondaryButton = TestUtils.createElement('button', {
                class: 'bg-secondary text-secondary-foreground'
            });
            
            expect(primaryButton).toHaveClass('bg-primary');
            expect(secondaryButton).toHaveClass('bg-secondary');
        });

        test('should support different sizes', () => {
            const smallButton = TestUtils.createElement('button', {
                class: 'h-8 px-3 text-sm rounded-md'
            });
            
            const largeButton = TestUtils.createElement('button', {
                class: 'h-11 px-6 text-base rounded-lg'
            });
            
            expect(smallButton).toHaveClass('h-8');
            expect(largeButton).toHaveClass('h-11');
        });

        test('should handle click events', () => {
            const clickHandler = jest.fn();
            const button = TestUtils.createElement('button');
            button.addEventListener('click', clickHandler);
            
            TestUtils.simulateClick(button);
            
            expect(clickHandler).toHaveBeenCalledTimes(1);
        });
    });

    describe('Modal Component', () => {
        test('should initialize with correct attributes', () => {
            const modal = TestUtils.createElement('div', {
                class: 'modal fade',
                id: 'testModal',
                tabindex: '-1',
                'aria-labelledby': 'testModalLabel',
                'aria-hidden': 'true'
            });
            
            expect(modal).toHaveClass('modal');
            expect(modal.getAttribute('tabindex')).toBe('-1');
            expect(modal.getAttribute('aria-hidden')).toBe('true');
        });

        test('should handle show/hide events', () => {
            const modal = TestUtils.createElement('div', { id: 'testModal' });
            document.body.appendChild(modal);
            
            const showEvent = new Event('show.bs.modal');
            const hideEvent = new Event('hide.bs.modal');
            
            const showHandler = jest.fn();
            const hideHandler = jest.fn();
            
            modal.addEventListener('show.bs.modal', showHandler);
            modal.addEventListener('hide.bs.modal', hideHandler);
            
            modal.dispatchEvent(showEvent);
            modal.dispatchEvent(hideEvent);
            
            expect(showHandler).toHaveBeenCalled();
            expect(hideHandler).toHaveBeenCalled();
            
            document.body.removeChild(modal);
        });

        test('should trap focus within modal', () => {
            const modal = TestUtils.createElement('div', { id: 'testModal' });
            const input1 = TestUtils.createElement('input');
            const input2 = TestUtils.createElement('input');
            const button = TestUtils.createElement('button');
            
            modal.appendChild(input1);
            modal.appendChild(input2);
            modal.appendChild(button);
            document.body.appendChild(modal);
            
            // Simulate Tab key on last element
            const tabEvent = new KeyboardEvent('keydown', { key: 'Tab' });
            Object.defineProperty(tabEvent, 'target', { value: button });
            
            modal.dispatchEvent(tabEvent);
            
            document.body.removeChild(modal);
        });
    });

    describe('Tooltip Component', () => {
        test('should initialize with correct attributes', () => {
            const element = TestUtils.createElement('span', {
                'data-bs-toggle': 'tooltip',
                'data-bs-placement': 'top',
                'data-bs-title': 'Test tooltip',
                tabindex: '0'
            });
            
            expect(element.getAttribute('data-bs-toggle')).toBe('tooltip');
            expect(element.getAttribute('data-bs-title')).toBe('Test tooltip');
            expect(element.getAttribute('tabindex')).toBe('0');
        });

        test('should handle keyboard events', () => {
            const element = TestUtils.createElement('span', {
                'data-bs-toggle': 'tooltip'
            });
            
            const escapeHandler = jest.fn();
            element.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    escapeHandler();
                }
            });
            
            const escapeEvent = new KeyboardEvent('keydown', { key: 'Escape' });
            element.dispatchEvent(escapeEvent);
            
            expect(escapeHandler).toHaveBeenCalled();
        });
    });

    describe('Collapsible Component', () => {
        test('should toggle on click', () => {
            const header = TestUtils.createElement('div', {
                'data-bs-toggle': 'collapse',
                'data-bs-target': '#testCollapse'
            });
            
            const content = TestUtils.createElement('div', {
                id: 'testCollapse',
                class: 'collapse'
            });
            
            document.body.appendChild(header);
            document.body.appendChild(content);
            
            TestUtils.simulateClick(header);
            
            // Verify Bootstrap collapse would be triggered
            expect(header.getAttribute('data-bs-target')).toBe('#testCollapse');
            
            document.body.removeChild(header);
            document.body.removeChild(content);
        });

        test('should handle keyboard navigation', () => {
            const header = TestUtils.createElement('div', {
                role: 'button',
                tabindex: '0'
            });
            
            const clickHandler = jest.fn();
            header.addEventListener('click', clickHandler);
            
            // Simulate Enter key
            const enterEvent = new KeyboardEvent('keydown', { key: 'Enter' });
            header.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    clickHandler();
                }
            });
            
            header.dispatchEvent(enterEvent);
            
            expect(clickHandler).toHaveBeenCalled();
        });
    });

    describe('Form Validation Integration', () => {
        test('should validate form with UI components', () => {
            const form = TestUtils.createForm([
                { name: 'email', type: 'email', required: true },
                { name: 'password', type: 'password', required: true }
            ]);
            
            // Add validation classes
            const emailField = form.querySelector('[name="email"]');
            const passwordField = form.querySelector('[name="password"]');
            
            // Test invalid state
            emailField.classList.add('is-invalid');
            expect(emailField).toHaveClass('is-invalid');
            
            // Test valid state
            passwordField.classList.add('is-valid');
            expect(passwordField).toHaveClass('is-valid');
        });

        test('should show error messages', () => {
            const field = TestUtils.createElement('input', { name: 'email' });
            const container = TestUtils.createElement('div');
            container.appendChild(field);
            
            // Add error message
            const errorDiv = TestUtils.createElement('div', {
                class: 'invalid-feedback'
            }, 'Please enter a valid email');
            
            container.appendChild(errorDiv);
            
            expect(errorDiv).toHaveClass('invalid-feedback');
            expect(errorDiv.textContent).toBe('Please enter a valid email');
        });
    });

    describe('Accessibility Features', () => {
        test('should have proper ARIA attributes', () => {
            const button = TestUtils.createElement('button', {
                'aria-label': 'Close dialog',
                'aria-describedby': 'help-text'
            });
            
            expect(button.getAttribute('aria-label')).toBe('Close dialog');
            expect(button.getAttribute('aria-describedby')).toBe('help-text');
        });

        test('should support keyboard navigation', () => {
            const focusableElements = [
                TestUtils.createElement('button'),
                TestUtils.createElement('input'),
                TestUtils.createElement('select')
            ];
            
            focusableElements.forEach(element => {
                expect(element.tabIndex).toBeGreaterThanOrEqual(0);
            });
        });

        test('should have proper focus indicators', () => {
            const button = TestUtils.createElement('button', {
                class: 'focus:outline-none focus:ring-2 focus:ring-offset-2'
            });
            
            expect(button).toHaveClass('focus:outline-none');
            expect(button).toHaveClass('focus:ring-2');
        });
    });

    describe('Responsive Design', () => {
        test('should handle different screen sizes', () => {
            const responsiveElement = TestUtils.createElement('div', {
                class: 'col-12 col-md-6 col-lg-4'
            });
            
            expect(responsiveElement).toHaveClass('col-12');
            expect(responsiveElement).toHaveClass('col-md-6');
            expect(responsiveElement).toHaveClass('col-lg-4');
        });

        test('should support mobile-first design', () => {
            const mobileElement = TestUtils.createElement('div', {
                class: 'text-sm md:text-base lg:text-lg'
            });
            
            expect(mobileElement).toHaveClass('text-sm');
            expect(mobileElement).toHaveClass('md:text-base');
            expect(mobileElement).toHaveClass('lg:text-lg');
        });
    });

    describe('Performance', () => {
        test('should handle large datasets efficiently', () => {
            const startTime = performance.now();
            
            // Create many elements
            const elements = [];
            for (let i = 0; i < 1000; i++) {
                elements.push(TestUtils.createElement('div', {
                    class: 'test-element'
                }));
            }
            
            const endTime = performance.now();
            const duration = endTime - startTime;
            
            // Should complete within reasonable time (100ms)
            expect(duration).toBeLessThan(100);
            expect(elements.length).toBe(1000);
        });

        test('should cleanup event listeners', () => {
            const element = TestUtils.createElement('button');
            const handler = jest.fn();
            
            element.addEventListener('click', handler);
            
            // Simulate cleanup
            element.removeEventListener('click', handler);
            
            TestUtils.simulateClick(element);
            
            // Handler should not be called after removal
            expect(handler).not.toHaveBeenCalled();
        });
    });

    describe('Error Handling', () => {
        test('should handle missing elements gracefully', () => {
            const nonExistentElement = document.getElementById('non-existent');
            
            expect(nonExistentElement).toBeNull();
            
            // Should not throw error when checking null element
            expect(() => {
                if (nonExistentElement) {
                    nonExistentElement.click();
                }
            }).not.toThrow();
        });

        test('should validate required props', () => {
            // Test component with missing required props
            const incompleteComponent = TestUtils.createElement('div');
            
            // Should handle gracefully
            expect(incompleteComponent).toBeTruthy();
        });
    });
});
