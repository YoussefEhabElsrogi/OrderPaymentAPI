# 🛒 OrderPaymentAPI

**Order Management and Payment Processing System** - A comprehensive Laravel API for managing orders and processing payments with secure authentication

## ✨ Key Features

- 🔐 **Secure Authentication** using JWT
- 📦 **Order Management** - Create, update, and delete orders
- 💳 **Payment Processing** - Support for multiple payment gateways (PayPal, Stripe, Bank Transfer)
- 🏗️ **Clean Architecture** - Repository Pattern and Service Layer
- 🧪 **Comprehensive Testing** - Unit Tests and Feature Tests
- 📚 **Complete Documentation** - Clear examples for all APIs

## 🚀 Installation & Setup

### Prerequisites

- **PHP** 8.2 or higher
- **Composer** for dependency management
- **SQLite** (default) or MySQL/PostgreSQL
- **Laravel** 11.x
- **JWT** for authentication

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/YoussefEhabElsrogi/OrderPaymentAPI.git
   cd OrderPaymentAPI
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan jwt:secret
   ```

4. **Database setup**
   ```bash
   # For SQLite (default)
   touch database/database.sqlite
   
   # Or configure MySQL/PostgreSQL in .env
   php artisan migrate
   
   # Run seeders (optional)
   php artisan db:seed
   ```

5. **Run the application**
   ```bash
   php artisan serve
   ```

The API will be available at: `https://orderpaymentapi-production.up.railway.app/api`

## 📚 Used Libraries

- **Laravel 11.x** - Main framework
- **tymon/jwt-auth** - JWT authentication
- **Laravel Sanctum** - API protection
- **PHPUnit** - Testing framework
- **Faker** - Fake data generation

## 🏗️ Project Structure

```
OrderPaymentAPI/
├── app/
│   ├── Enums/              # Enumerations (OrderStatus, PaymentMethod, etc.)
│   ├── Exceptions/         # Custom exceptions
│   ├── Http/
│   │   ├── Controllers/    # Controllers (Auth, Order, Payment)
│   │   ├── Requests/       # Validation requests
│   │   └── Resources/      # API resources
│   ├── Interfaces/         # Interfaces (Repository, Service)
│   ├── Models/             # Models (User, Order, Payment)
│   ├── Repositories/       # Data access layer
│   ├── Services/           # Business logic layer
│   └── Traits/             # Shared traits
├── database/
│   ├── migrations/         # Database files
│   ├── factories/          # Fake data factories
│   └── seeders/            # Data seeders
├── tests/                  # Tests
└── routes/api.php          # API routes
```

## 📋 API Endpoints

### 🔐 Authentication

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/auth/register` | Register new user | ❌ |
| POST | `/api/auth/login` | User login | ❌ |
| GET | `/api/auth/me` | Get current user data | ✅ |
| POST | `/api/auth/logout` | User logout | ✅ |
| POST | `/api/auth/refresh` | Refresh JWT Token | ✅ |

### 📦 Order Management

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/orders` | List user's orders | ✅ |
| POST | `/api/orders` | Create new order | ✅ |
| GET | `/api/orders/{id}` | Get order details | ✅ |
| PATCH | `/api/orders/{id}` | Update order | ✅ |
| DELETE | `/api/orders/{id}` | Delete order | ✅ |

### 💳 Payment Processing

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/payments` | List all payments | ✅ |
| GET | `/api/payments/methods` | Get available payment methods | ✅ |
| POST | `/api/orders/{id}/payments` | Process payment for order | ✅ |
| GET | `/api/orders/{id}/payments` | Get payments for order | ✅ |

## 💳 Available Payment Methods

| Method | Description | Required Fields |
|--------|-------------|-----------------|
| **PayPal** | PayPal payment | `paypal_email` |
| **Stripe** | Credit card payment | `card_number`, `expiry_month`, `expiry_year`, `cvv` |
| **Bank Transfer** | Bank transfer | `bank_name`, `account_number` |

## 💡 Usage Examples

### 1️⃣ User Registration

```bash
curl -X POST https://orderpaymentapi-production.up.railway.app/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
  }
}
```

### 2️⃣ User Login

```bash
curl -X POST https://orderpaymentapi-production.up.railway.app/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### 3️⃣ Create New Order

```bash
curl -X POST https://orderpaymentapi-production.up.railway.app/api/orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{
    "notes": "My first order",
    "items": [
      {
        "product_name": "Laptop",
        "quantity": 1,
        "price": 999.99,
        "description": "High-performance laptop"
      }
    ]
  }'
```

### 4️⃣ Process PayPal Payment

```bash
curl -X POST https://orderpaymentapi-production.up.railway.app/api/orders/1/payments \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{
    "payment_method": "paypal",
    "paypal_email": "buyer@example.com"
  }'
```

### 5️⃣ Process Stripe Payment

```bash
curl -X POST https://orderpaymentapi-production.up.railway.app/api/orders/1/payments \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{
    "payment_method": "stripe",
    "card_number": "4242424242424242",
    "expiry_month": "12",
    "expiry_year": "2025",
    "cvv": "123"
  }'
```

## 📊 System States

### 📦 Order States
- **pending** - Pending (default)
- **confirmed** - Confirmed
- **shipped** - Shipped
- **delivered** - Delivered
- **cancelled** - Cancelled

### 💳 Payment States
- **pending** - Pending
- **processing** - Processing
- **completed** - Completed
- **failed** - Failed
- **refunded** - Refunded

## 📋 Business Rules

### 📦 Orders
- Orders start with "pending" status
- Only confirmed orders can accept payments
- Orders with payments cannot be deleted
- Total amount is automatically calculated from order items

### 💳 Payments
- Payments are only allowed for confirmed orders
- Each payment method has specific validation rules
- Payment processing is simulated (90% success rate for demo)
- Failed payments are logged with error details

## 📤 Response Examples

### ✅ Success Response
```json
{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "status": "pending",
    "total_amount": 999.99,
    "notes": "My first order",
    "items": [
      {
        "id": 1,
        "product_name": "Laptop",
        "quantity": 1,
        "price": 999.99,
        "description": "High-performance laptop"
      }
    ],
    "created_at": "2024-01-01T12:00:00.000000Z"
  }
}
```

### ❌ Error Response
```json
{
  "success": false,
  "message": "Invalid data",
  "errors": {
    "email": ["The email field is required"],
    "password": ["The password must be at least 8 characters"]
  }
}
```

## 🧪 Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

## 🔧 Adding New Payment Gateways

To add a new payment gateway:

1. **Create the strategy class** implementing `PaymentStrategyInterface`
2. **Register it in PaymentFactory** constructor
3. **Add it to PaymentMethod enum**
4. **Update validation rules** in ProcessPaymentRequest
5. **Add migration** if needed

## 🚀 Advanced Features

### 🔐 Security
- **JWT Authentication** - Secure authentication
- **Request Validation** - Data validation
- **Rate Limiting** - Request rate limiting
- **CORS Protection** - Cross-origin request protection

### 📊 Monitoring & Logging
- **Transaction Logging** - All transactions logged
- **Error Handling** - Comprehensive error handling
- **API Response Standardization** - Standardized response format

### 🧪 Testing
- **Unit Tests** - Unit testing
- **Feature Tests** - Feature testing
- **Database Testing** - Database testing
- **Mock Payment Gateways** - Payment gateway mocking

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## 🤝 Contributing

We welcome contributions! Please read the contribution guide before submitting a Pull Request.

## 📮 Using Postman

You can import the Postman Collection file included with the project to easily test all APIs:

1. Open Postman
2. Click "Import"
3. Select `OrderPaymentAPI.postman_collection.json` file
4. Start testing APIs

## 🔧 Environment Settings

Make sure to configure the following variables in your `.env` file:

```env
APP_NAME="OrderPaymentAPI"
APP_ENV=local
APP_DEBUG=true
APP_URL=https://orderpaymentapi-production.up.railway.app

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

JWT_SECRET=your-jwt-secret-key
JWT_TTL=60
```

## 🌐 Production Deployment

### Production Settings

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

JWT_SECRET=your-production-jwt-secret
```

### Production Commands

```bash
# Optimize performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run background jobs
php artisan queue:work

# Monitor logs
tail -f storage/logs/laravel.log
```

## 🔮 Future Updates

### Planned Features
- [ ] **Email Notifications** - Order confirmation emails
- [ ] **Discount System** - Coupons and discounts
- [ ] **Shipping Tracking** - Track shipping status
- [ ] **Admin Dashboard** - Order and payment management
- [ ] **Reports API** - Sales and statistics reports
- [ ] **Multi-Currency Support** - Multiple currency support
- [ ] **Review System** - Product and service reviews

### Technical Improvements
- [ ] **Redis Caching** - Performance optimization
- [ ] **API Rate Limiting** - Request rate limiting
- [ ] **Webhook Support** - Webhook support
- [ ] **GraphQL API** - Alternative API
- [ ] **Microservices** - Service decomposition

## 📞 Support

If you encounter any issues or have questions, please open an issue in the repository.

---

**Built with Youssef Elsrogi using Laravel**
