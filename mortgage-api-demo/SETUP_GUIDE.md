# Setup Guide - Mortgage API Demo

This guide will help you get the Mortgage API Demo up and running on your local machine.

## Prerequisites

- PHP 8.1 or higher
- Composer
- MySQL 8.0 or higher
- Git

## Installation Steps

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/mortgage-api-demo.git
cd mortgage-api-demo
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Environment Configuration

```bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Database

Edit your `.env` file and set your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mortgage_api
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Create Database

```bash
# Create the database (if using MySQL command line)
mysql -u your_username -p
CREATE DATABASE mortgage_api;
EXIT;
```

### 6. Run Migrations

```bash
# Run database migrations
php artisan migrate

# (Optional) Seed with sample data
php artisan db:seed
```

### 7. Start Development Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

## Testing the API

### Using cURL

**Create an Applicant:**
```bash
curl -X POST http://localhost:8000/api/v1/applicants \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "phone": "07700900000",
    "date_of_birth": "1990-01-01",
    "employment_status": "employed",
    "annual_income": 60000,
    "monthly_expenses": 1500,
    "existing_debt": 5000,
    "credit_score": 750,
    "address_line_1": "123 Test Street",
    "city": "London",
    "postcode": "SW1A 1AA"
  }'
```

**Create a Mortgage Application:**
```bash
curl -X POST http://localhost:8000/api/v1/applications \
  -H "Content-Type: application/json" \
  -d '{
    "lender_id": 1,
    "applicant_id": 1,
    "property_value": 300000,
    "loan_amount": 270000,
    "deposit_amount": 30000,
    "loan_term_years": 25,
    "interest_rate": 4.5,
    "property_address": "456 Property Lane, London",
    "property_type": "semi_detached",
    "purchase_type": "purchase"
  }'
```

**List Applications:**
```bash
curl http://localhost:8000/api/v1/applications
```

**Filter by Status:**
```bash
curl "http://localhost:8000/api/v1/applications?status=submitted"
```

**Get Application Details:**
```bash
curl http://localhost:8000/api/v1/applications/1
```

**Update Application Status:**
```bash
curl -X PATCH http://localhost:8000/api/v1/applications/1/status \
  -H "Content-Type: application/json" \
  -d '{
    "status": "approved",
    "notes": "Application approved"
  }'
```

### Using Postman

Import the following collection into Postman:

1. **Base URL**: `http://localhost:8000`
2. **Endpoints**:
   - `POST /api/v1/applications` - Create application
   - `GET /api/v1/applications` - List applications
   - `GET /api/v1/applications/{id}` - Get application
   - `PATCH /api/v1/applications/{id}/status` - Update status
   - `GET /api/v1/applications/{id}/evaluate` - Evaluate application

## Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test --filter MortgageApplicationApiTest

# Run with coverage (requires Xdebug)
php artisan test --coverage
```

## Database Schema

The project includes the following main tables:

- `lenders` - Mortgage lender configuration
- `applicants` - Applicant personal and financial data
- `mortgage_applications` - Core application data
- `application_events` - Audit trail
- `credit_checks` - Credit assessment results
- `application_documents` - Document tracking

## Key Features to Explore

### 1. Event-Driven Architecture

The system uses Laravel events and listeners:
- When an application is submitted, it automatically triggers a credit check
- Status changes are logged to the audit trail
- Events can be queued for async processing

### 2. Calculated Fields

Applications automatically calculate:
- Loan-to-Value (LTV) ratio
- Monthly payment amount
- Affordability ratio
- Risk score

### 3. Multi-Tenant Support

Each lender has:
- Custom approval criteria
- Configurable interest rates
- Isolated application data

### 4. Query Optimization

- Eager loading prevents N+1 queries
- Strategic indexes on frequently queried columns
- Repository pattern for clean data access

## Project Structure

```
mortgage-api-demo/
├── app/
│   ├── Events/           # Event classes
│   ├── Listeners/        # Event listeners
│   ├── Models/           # Eloquent models
│   ├── Services/         # Business logic
│   ├── Repositories/     # Data access layer
│   └── Http/
│       └── Controllers/  # API controllers
├── database/
│   ├── migrations/       # Database schema
│   └── seeders/          # Sample data
├── routes/
│   └── api.php          # API routes
└── tests/
    └── Feature/         # API tests
```

## Troubleshooting

### Database Connection Issues

If you get "Access denied" errors:
1. Check your `.env` database credentials
2. Ensure MySQL is running
3. Verify the database exists

### Migration Errors

If migrations fail:
```bash
# Reset and re-run migrations
php artisan migrate:fresh
```

### Composer Issues

If you get dependency errors:
```bash
# Update dependencies
composer update

# Clear composer cache
composer clear-cache
```

## Next Steps

1. **Add Authentication**: Implement Laravel Sanctum for API authentication
2. **Add Caching**: Use Redis for frequently accessed data
3. **Queue Jobs**: Move heavy operations to background jobs
4. **Add Logging**: Implement comprehensive logging
5. **API Documentation**: Generate Swagger/OpenAPI docs

## Support

For issues or questions:
- Email: Arturs.irbitis@gmail.com
- LinkedIn: [arturs-irbitis-91645959](https://linkedin.com/in/arturs-irbitis-91645959)

## License

This project is open-source under the MIT License.
