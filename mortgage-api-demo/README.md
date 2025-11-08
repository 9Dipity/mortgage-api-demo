# Mortgage Application API - Laravel Demo

A demonstration Laravel backend API for mortgage application processing, showcasing event-driven architecture, clean code practices, and database optimization.

## 🎯 Project Overview

This project simulates a mortgage application platform backend, similar to what would power an AI-driven mortgage app. It demonstrates:

- RESTful API design for mortgage applications
- Event-driven architecture for application workflow
- Multi-tenant support for different lenders
- Permission-based access control
- Database optimization and query performance
- Clean, testable, maintainable code

## 🏗️ Architecture Highlights

### Event-Driven Design
- `MortgageApplicationSubmitted` - Triggers credit check and document verification
- `CreditCheckCompleted` - Initiates automated decision process
- `ApplicationApproved/Rejected` - Notifies applicant and creates audit trail

### Multi-Tenant Support
- Lender isolation at database level
- Permission-based API access per lender
- Configurable approval criteria per tenant

### Database Optimization
- Indexed foreign keys and search columns
- Optimized queries for application filtering
- Database migrations with rollback support

## 🚀 Key Features

### 1. Mortgage Application Management
- Submit new mortgage applications
- Track application status
- Document upload handling
- Applicant financial profile management

### 2. Automated Credit Assessment
- Credit score evaluation
- Debt-to-income ratio calculation
- Affordability analysis
- Risk scoring

### 3. Lender Management (Multi-Tenant)
- Multiple lender support
- Custom approval criteria
- Lender-specific reporting

### 4. Audit Trail & Compliance
- Full application history
- Status change tracking
- Regulatory compliance logging

## 📋 API Endpoints

```
POST   /api/applications              - Submit new mortgage application
GET    /api/applications              - List applications (filtered, paginated)
GET    /api/applications/{id}         - Get application details
PATCH  /api/applications/{id}/status  - Update application status
POST   /api/applications/{id}/documents - Upload documents

GET    /api/lenders                   - List available lenders
GET    /api/lenders/{id}/criteria     - Get lender approval criteria

POST   /api/credit-check              - Initiate credit check
GET    /api/credit-check/{id}         - Get credit check results
```

## 🗄️ Database Schema

### Key Tables
- `mortgage_applications` - Core application data
- `applicants` - Personal and financial information
- `credit_checks` - Credit assessment results
- `lenders` - Multi-tenant lender configuration
- `application_documents` - Document tracking
- `application_events` - Audit trail

### Performance Optimizations
- Composite indexes on `(lender_id, status, created_at)`
- Full-text search on applicant names
- Soft deletes for data retention
- Optimized foreign key relationships

## 🔧 Technical Implementation

### Technologies Used
- **Laravel 10+** - Modern PHP framework
- **MySQL 8+** - Relational database with optimization
- **Event-Driven Architecture** - Decoupled business logic
- **Repository Pattern** - Clean separation of concerns
- **Service Layer** - Business logic encapsulation
- **API Resources** - Consistent response formatting
- **Form Requests** - Validation layer

### Code Quality
- PSR-12 coding standards
- Type hints and return types
- Comprehensive docblocks
- SOLID principles
- DRY (Don't Repeat Yourself)
- Single Responsibility Principle

### Testing Strategy
- Feature tests for API endpoints
- Unit tests for business logic
- Database transactions for test isolation
- Factory patterns for test data

## 📊 Business Logic Examples

### Affordability Calculation
```php
// Calculates maximum affordable mortgage based on income and expenses
$maxAffordable = ($annualIncome * 4.5) - $existingDebt;
$monthlyPayment = ($loanAmount * $interestRate) / 12;
$affordabilityRatio = ($monthlyPayment / $monthlyIncome) * 100;
```

### Risk Scoring
```php
// Multi-factor risk assessment
- Credit score weight: 40%
- Debt-to-income ratio: 30%
- Employment stability: 20%
- Deposit percentage: 10%
```

## 🎓 What This Demonstrates

### Laravel Skills
✅ Eloquent ORM with relationships  
✅ Database migrations and seeders  
✅ Event-driven architecture  
✅ API resource controllers  
✅ Form request validation  
✅ Service layer pattern  
✅ Repository pattern  
✅ Queue jobs (async processing)  
✅ Database query optimization  
✅ Multi-tenancy support  

### General Backend Skills
✅ RESTful API design  
✅ Clean architecture principles  
✅ SOLID principles  
✅ Design patterns  
✅ Database normalization  
✅ Performance optimization  
✅ Security best practices  
✅ Documentation  

### Domain Knowledge
✅ Understanding of mortgage application workflow  
✅ Financial calculations (affordability, DTI)  
✅ Compliance and audit requirements  
✅ Multi-stakeholder systems  

## 🚀 Installation

```bash
# Clone repository
git clone https://github.com/yourusername/mortgage-api-demo.git
cd mortgage-api-demo

# Install dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate
php artisan db:seed

# Run tests
php artisan test

# Start server
php artisan serve
```

## 📝 Environment Configuration

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mortgage_api
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
CACHE_DRIVER=redis
```

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test suite
php artisan test --testsuite=Feature
```

## 📈 Performance Considerations

- **Query Optimization**: Eager loading to prevent N+1 queries
- **Caching**: Redis for frequently accessed data
- **Indexing**: Strategic indexes on high-traffic columns
- **Pagination**: Cursor-based pagination for large datasets
- **Queue Jobs**: Async processing for heavy operations

## 🔐 Security Features

- API authentication (Sanctum)
- Request validation
- SQL injection prevention (Eloquent ORM)
- CSRF protection
- Rate limiting
- Input sanitization

## 📚 Learning Resources Used

- Laravel Documentation (laravel.com)
- UK Mortgage Industry Standards
- Clean Architecture by Robert Martin
- Laravel Best Practices

## 🤝 Contributing

This is a demonstration project created for portfolio purposes. Feedback and suggestions are welcome!

## 📄 License

This project is open-source and available under the MIT License.

## 👤 Author

**Arturs Irbitis**
- Email: Arturs.irbitis@gmail.com
- LinkedIn: [arturs-irbitis-91645959](https://linkedin.com/in/arturs-irbitis-91645959)

---

**Note**: This is a demonstration project showcasing Laravel backend development skills. It simulates a mortgage application API but is not intended for production use.
