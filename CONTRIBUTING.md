# Contributing to UniPrint

Thank you for considering contributing to UniPrint! This document provides guidelines for contributing to the project.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [How to Contribute](#how-to-contribute)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Pull Request Process](#pull-request-process)
- [Reporting Bugs](#reporting-bugs)
- [Suggesting Features](#suggesting-features)

---

## Code of Conduct

By participating in this project, you agree to maintain a respectful and inclusive environment for all contributors.

### Our Standards

- Be respectful and inclusive
- Accept constructive criticism gracefully
- Focus on what's best for the community
- Show empathy towards other community members

---

## Getting Started

1. Fork the repository
2. Clone your fork: `git clone https://github.com/your-username/uniprint.git`
3. Add upstream remote: `git remote add upstream https://github.com/original-repo/uniprint.git`
4. Create a feature branch: `git checkout -b feature/your-feature-name`

---

## Development Setup

### Prerequisites

- PHP 8.2+
- Composer 2.0+
- Node.js 18+
- PostgreSQL 14+ or MySQL 8.0+

### Installation

```bash
# Clone repository
git clone https://github.com/your-username/uniprint.git
cd uniprint

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup database
createdb uniprint  # PostgreSQL
php artisan migrate --seed

# Build assets
npm run dev
```

### Running Development Server

```bash
# Option 1: All services
composer run dev

# Option 2: Separate terminals
php artisan serve          # Terminal 1
php artisan queue:work     # Terminal 2
npm run dev                # Terminal 3
```

---

## How to Contribute

### Types of Contributions

- **Bug fixes** - Fix existing issues
- **Features** - Add new functionality
- **Documentation** - Improve docs
- **Tests** - Add or improve tests
- **Performance** - Optimize code
- **Refactoring** - Improve code quality

### Workflow

1. **Check existing issues** - Avoid duplicate work
2. **Create an issue** - Discuss major changes first
3. **Fork and branch** - Create a feature branch
4. **Make changes** - Follow coding standards
5. **Test thoroughly** - Add tests for new features
6. **Commit** - Use clear commit messages
7. **Push** - Push to your fork
8. **Pull Request** - Submit PR with description

---

## Coding Standards

### PHP

Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standard.

```bash
# Check code style
./vendor/bin/pint --test

# Fix code style
./vendor/bin/pint
```

### JavaScript

Follow [Airbnb JavaScript Style Guide](https://github.com/airbnb/javascript).

```bash
# Check code style
npm run lint

# Fix code style
npm run lint:fix
```

### Laravel Best Practices

- Use Eloquent ORM over raw queries
- Use form requests for validation
- Use resource controllers
- Follow RESTful conventions
- Use dependency injection
- Keep controllers thin
- Use service classes for business logic
- Use events and listeners for decoupling

### Naming Conventions

**PHP:**
- Classes: `PascalCase`
- Methods: `camelCase`
- Variables: `camelCase`
- Constants: `UPPER_SNAKE_CASE`

**Database:**
- Tables: `snake_case` (plural)
- Columns: `snake_case`
- Foreign keys: `table_id`
- Pivot tables: `table1_table2` (alphabetical)

**JavaScript:**
- Variables: `camelCase`
- Constants: `UPPER_SNAKE_CASE`
- Components: `PascalCase`

---

## Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/UserTest.php

# Run with coverage
php artisan test --coverage

# Run specific test method
php artisan test --filter test_user_can_login
```

### Writing Tests

**Feature Test Example:**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }
}
```

**Unit Test Example:**

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PricingService;

class PricingServiceTest extends TestCase
{
    public function test_calculates_price_correctly(): void
    {
        $service = new PricingService();
        
        $price = $service->calculatePrice(
            basePrice: 100,
            quantity: 5,
            discount: 10
        );

        $this->assertEquals(450, $price);
    }
}
```

### Test Coverage

Aim for:
- **80%+ overall coverage**
- **100% coverage for critical paths**
- Test happy paths and edge cases
- Test error handling

---

## Pull Request Process

### Before Submitting

1. **Update your branch** with latest upstream changes
   ```bash
   git fetch upstream
   git rebase upstream/main
   ```

2. **Run tests** and ensure they pass
   ```bash
   php artisan test
   npm run test
   ```

3. **Check code style**
   ```bash
   ./vendor/bin/pint
   npm run lint
   ```

4. **Update documentation** if needed

### PR Guidelines

**Title Format:**
```
[Type] Brief description

Examples:
[Feature] Add user profile editing
[Fix] Resolve database connection issue
[Docs] Update installation guide
[Refactor] Improve order processing logic
```

**Description Template:**

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Tests added/updated
- [ ] All tests passing
- [ ] Manual testing completed

## Checklist
- [ ] Code follows project style guidelines
- [ ] Self-review completed
- [ ] Comments added for complex code
- [ ] Documentation updated
- [ ] No new warnings generated
- [ ] Tests added for new features
- [ ] All tests passing

## Related Issues
Closes #123
Related to #456

## Screenshots (if applicable)
[Add screenshots here]
```

### Review Process

1. Maintainers will review your PR
2. Address any requested changes
3. Once approved, PR will be merged
4. Your contribution will be credited

---

## Reporting Bugs

### Before Reporting

1. **Search existing issues** - Check if already reported
2. **Try latest version** - Bug might be fixed
3. **Check documentation** - Might be expected behavior

### Bug Report Template

```markdown
## Bug Description
Clear and concise description

## Steps to Reproduce
1. Go to '...'
2. Click on '...'
3. Scroll down to '...'
4. See error

## Expected Behavior
What you expected to happen

## Actual Behavior
What actually happened

## Environment
- OS: [e.g., Windows 11, macOS 13, Ubuntu 22.04]
- PHP Version: [e.g., 8.2.10]
- Laravel Version: [e.g., 12.0]
- Database: [e.g., PostgreSQL 14.5]
- Browser: [e.g., Chrome 120, Firefox 121]

## Error Messages
```
Paste error messages here
```

## Screenshots
[Add screenshots if applicable]

## Additional Context
Any other relevant information
```

---

## Suggesting Features

### Feature Request Template

```markdown
## Feature Description
Clear and concise description of the feature

## Problem Statement
What problem does this solve?

## Proposed Solution
How should this feature work?

## Alternatives Considered
What other solutions did you consider?

## Additional Context
- Use cases
- Examples from other applications
- Mockups or diagrams

## Implementation Considerations
- Breaking changes?
- Performance impact?
- Security implications?
```

---

## Development Tips

### Useful Commands

```bash
# Clear all caches
php artisan optimize:clear

# Generate IDE helper files
php artisan ide-helper:generate
php artisan ide-helper:models
php artisan ide-helper:meta

# Database operations
php artisan migrate:fresh --seed
php artisan db:seed --class=SpecificSeeder

# Queue operations
php artisan queue:work --tries=3
php artisan queue:failed
php artisan queue:retry all

# Asset building
npm run dev        # Development with hot reload
npm run build      # Production build
npm run watch      # Watch for changes
```

### Debugging

```php
// Use Laravel's dd() and dump()
dd($variable);
dump($variable);

// Use Ray (if installed)
ray($variable);

// Log debugging info
\Log::debug('Debug message', ['data' => $variable]);

// Query debugging
DB::enableQueryLog();
// ... run queries ...
dd(DB::getQueryLog());
```

### Database Seeding

```bash
# Create new seeder
php artisan make:seeder YourSeeder

# Run specific seeder
php artisan db:seed --class=YourSeeder

# Refresh and seed
php artisan migrate:fresh --seed
```

---

## Code Review Checklist

### For Authors

- [ ] Code is self-documenting or well-commented
- [ ] No commented-out code
- [ ] No debug statements (dd, dump, console.log)
- [ ] Error handling implemented
- [ ] Input validation added
- [ ] Security considerations addressed
- [ ] Performance optimized
- [ ] Tests added/updated
- [ ] Documentation updated

### For Reviewers

- [ ] Code follows project standards
- [ ] Logic is sound and efficient
- [ ] Edge cases handled
- [ ] Security vulnerabilities checked
- [ ] Performance implications considered
- [ ] Tests are comprehensive
- [ ] Documentation is clear

---

## License

By contributing to UniPrint, you agree that your contributions will be licensed under the MIT License.

---

## Questions?

- Check [Documentation](docs/)
- Ask in [Discussions](https://github.com/your-repo/discussions)
- Contact maintainers

---

**Thank you for contributing to UniPrint! 🎉**
