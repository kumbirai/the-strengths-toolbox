# Testing Guide

## Overview
This guide covers all testing procedures, commands, and best practices for The Strengths Toolbox.

## Test Structure

### Feature Tests
Located in `tests/Feature/`:
- `HomepageTest.php` - Homepage functionality
- `ContactFormTest.php` - Contact form testing
- `EbookFormTest.php` - eBook signup form
- `BlogTest.php` - Blog functionality
- `PageTest.php` - Static and dynamic pages
- `HealthCheckTest.php` - Health check endpoints

### Unit Tests
Located in `tests/Unit/`:
- `Services/FormServiceTest.php` - Form service unit tests

## Running Tests

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suite
```bash
# Feature tests only
php artisan test --testsuite=Feature

# Unit tests only
php artisan test --testsuite=Unit
```

### Run Specific Test File
```bash
php artisan test tests/Feature/ContactFormTest.php
```

### Run Specific Test Method
```bash
php artisan test --filter test_contact_form_submission_succeeds
```

### Run with Coverage
```bash
php artisan test --coverage
```

## Test Commands

### Form Testing
```bash
# Test all forms
php artisan test:forms --url=http://localhost:8000

# Test with custom URL
php artisan test:forms --url=https://staging.example.com
```

### Route Testing
```bash
# Test all routes
php artisan test:routes
```

### Performance Benchmarking
```bash
# Benchmark performance
php artisan benchmark:performance

# Custom URL and iterations
php artisan benchmark:performance --url=https://your-domain.com --iterations=20
```

### Security Audit
```bash
# Run security audit
php artisan security:audit
```

## Test Coverage

### Current Coverage
- Feature Tests: Homepage, Forms, Blog, Pages, Health Checks
- Unit Tests: Form Service
- Integration Tests: Form submissions, Email notifications

### Coverage Targets
- Critical paths: 80%+
- Service layer: 70%+
- Controllers: 60%+
- Overall: 70%+

## Writing New Tests

### Feature Test Example
```php
public function test_feature_works(): void
{
    $response = $this->get('/route');
    
    $response->assertStatus(200);
    $response->assertViewIs('view.name');
    $response->assertSee('Expected Content');
}
```

### Form Test Example
```php
public function test_form_submission(): void
{
    $response = $this->postJson('/form', [
        'field' => 'value',
    ]);
    
    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
}
```

## Test Data

### Factories Available
- `BlogPostFactory`
- `CategoryFactory`
- `TagFactory`
- `PageFactory`
- `FormFactory`
- `TestimonialFactory`

### Using Factories
```php
// Create a blog post
$post = BlogPost::factory()->create();

// Create with specific attributes
$post = BlogPost::factory()->create([
    'title' => 'Custom Title',
    'is_published' => true,
]);

// Create unpublished post
$post = BlogPost::factory()->unpublished()->create();
```

## CI/CD Integration

### GitHub Actions
Tests run automatically on:
- Push to main/develop branches
- Pull requests

### Manual CI Run
```bash
# Run full CI suite locally
composer test
```

## Best Practices

### 1. Test Isolation
- Each test should be independent
- Use `RefreshDatabase` trait
- Clean up after tests

### 2. Test Naming
- Use descriptive names
- Follow pattern: `test_feature_behavior`
- Group related tests

### 3. Assertions
- Use specific assertions
- Test both success and failure cases
- Verify data persistence

### 4. Mocking
- Mock external services
- Mock email sending
- Mock API calls

## Troubleshooting

### Tests Failing
1. Check database connection
2. Verify test data setup
3. Check for environment issues
4. Review error messages

### Slow Tests
1. Use in-memory database
2. Minimize database operations
3. Use factories efficiently
4. Cache expensive operations

---

**Last Updated:** 2025-01-27
