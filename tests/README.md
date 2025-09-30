# Testing Documentation

## Overview
This project uses PHPUnit for feature testing. All tests are focused on testing controllers through HTTP requests, as requested.

## Test Structure

### Feature Tests
- `tests/Feature/` - Controller feature tests
  - `AuthControllerTest.php` - Tests for AuthController endpoints
  - `OrderControllerTest.php` - Tests for OrderController endpoints
  - `PaymentControllerTest.php` - Tests for PaymentController endpoints

## Running Tests

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suite
```bash
# Run only feature tests
php artisan test --testsuite=Feature

# Run only controller tests
php artisan test tests/Feature/
```

### Run Specific Test File
```bash
# Run AuthController tests
php artisan test tests/Feature/AuthControllerTest.php

# Run OrderController tests
php artisan test tests/Feature/OrderControllerTest.php

# Run PaymentController tests
php artisan test tests/Feature/PaymentControllerTest.php
```

### Run Specific Test Method
```bash
# Run specific test method
php artisan test --filter=test_method_name
```

## Test Configuration

### Database
- Uses SQLite in-memory database for testing
- Each test runs in a transaction that gets rolled back
- Uses `RefreshDatabase` trait for clean state

### HTTP Testing
- Uses Laravel's HTTP testing methods
- Tests actual API endpoints
- Uses real database with RefreshDatabase trait

## Test Coverage

### AuthController Tests
- ✅ User can register
- ✅ User can login with valid credentials
- ✅ User cannot login with invalid credentials
- ✅ Authenticated user can view profile
- ✅ Authenticated user can logout

### OrderController Tests
- ✅ Authenticated user can get their orders
- ✅ Authenticated user can get orders by status
- ✅ Authenticated user can create order
- ✅ Authenticated user can view specific order
- ✅ Authenticated user can update order
- ✅ Authenticated user can update order status
- ✅ Authenticated user can delete order without payments
- ✅ Unauthenticated user cannot access orders
- ✅ User cannot access other user orders

### PaymentController Tests
- ✅ Authenticated user can get their payments
- ✅ Authenticated user can process payment for confirmed order
- ✅ Authenticated user can view specific payment
- ✅ Authenticated user can get payments for specific order
- ✅ Authenticated user can update payment status
- ✅ Authenticated user can get available payment methods
- ✅ User cannot process payment for pending order
- ✅ Unauthenticated user cannot access payments
- ✅ User cannot access other user payments

## Best Practices

1. **Isolation**: Each test is independent and doesn't affect others
2. **HTTP Testing**: Tests actual API endpoints with real HTTP requests
3. **Database**: Uses RefreshDatabase trait for clean state between tests
4. **Authentication**: Properly tests authenticated and unauthenticated scenarios
5. **Authorization**: Tests user access to their own data vs other users' data
6. **Assertions**: Clear assertions for response structure and status codes
7. **Naming**: Descriptive test method names that explain what is being tested

## Adding New Tests

When adding new controller tests:

1. Create test file in `tests/Feature/`
2. Use `RefreshDatabase` trait for database cleanup
3. Test both success and failure scenarios
4. Test authentication and authorization
5. Use descriptive test method names
6. Add proper assertions for response structure and status codes
7. Test with real HTTP requests using Laravel's testing methods
