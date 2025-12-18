# Wallet Service

A RESTful API service for managing digital wallets, built with Laravel 12. This service provides secure wallet operations including deposits, withdrawals, and peer-to-peer transfers with support for multiple currencies.

## Features

- **Wallet Management**: Create wallets, check balances, list wallets with filtering
- **Deposits & Withdrawals**: Add or remove funds from wallets
- **Peer-to-Peer Transfers**: Transfer funds between wallets with double-entry accounting
- **Transaction History**: View transaction history with filtering and pagination
- **Idempotency Support**: Prevent duplicate transactions using idempotency keys
- **Race Condition Protection**: Pessimistic locking to handle concurrent operations
- **Multi-Currency Support**: ISO 4217 currency codes (USD, EUR, GBP, SAR, etc.)
- **Comprehensive Logging**: Event-driven architecture with detailed audit trails
- **RESTful API**: Clean, consistent JSON responses

## Tech Stack

- **Framework**: Laravel 12
- **Language**: PHP 8.2+
- **Database**: MySQL 8.0+ / PostgreSQL
- **Testing**: Pest PHP
- **Documentation**: Postman Collection included

## Architecture & Design Patterns

### Repository Pattern
Decouples business logic from data access layer using interfaces:
- `WalletRepositoryInterface` / `WalletRepository`
- `TransactionRepositoryInterface` / `TransactionRepository`

### Service Layer
Business logic encapsulated in service classes:
- `WalletService`: Wallet operations (create, deposit, withdraw)
- `TransferService`: Inter-wallet transfers
- `TransactionService`: Transaction history and statistics

### Events & Listeners (Observer Pattern)
Event-driven architecture for logging and notifications:
- `WalletCreated`, `FundsDeposited`, `FundsWithdrawn`
- `TransferCompleted`, `TransactionFailed`

### SOLID Principles
- **Dependency Inversion**: Services depend on interfaces, not concrete implementations
- **Single Responsibility**: Each class has one clear purpose
- **Open/Closed**: Easy to extend without modifying existing code

### Key Technical Features

#### Idempotency
Prevents duplicate transactions using `Idempotency-Key` header. Same key returns the original transaction instead of creating duplicates.

#### Race Condition Protection
Uses pessimistic locking (`lockForUpdate()`) to prevent lost updates during concurrent operations.

#### Double-Entry Accounting
Transfers create two transaction records atomically:
- **TRANSFER_DEBIT**: Deducts from source wallet
- **TRANSFER_CREDIT**: Adds to target wallet

#### Deadlock Prevention
Locks multiple wallets in consistent order (by ID) to prevent circular waits.

#### Minor Units
All monetary values stored as integers (cents) to avoid floating-point precision issues.

## Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL 8.0+ or PostgreSQL
- Node.js & NPM (for frontend assets, optional)

## Installation & Setup

### 1. Clone the Repository

```bash
git clone https://github.com/AsadNadeem101/wallet-service.git
cd wallet-service
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and configure your database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wallet_service
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Start the Development Server

```bash
php artisan serve
# Or use Laravel Valet/Herd for local .test domains
```

The API will be available at your configured base URL.

### 6. Test the API

```bash
# Health check
curl {base_url}/api/health
```

Expected response:
```json
{
  "status": "ok"
}
```

## Database Schema

### Wallets Table

| Column      | Type         | Description                          |
|-------------|--------------|--------------------------------------|
| id          | BIGINT       | Primary key                          |
| owner_name  | VARCHAR(255) | Wallet owner's name                  |
| currency    | VARCHAR(3)   | ISO 4217 currency code (USD, EUR)    |
| balance     | BIGINT       | Balance in minor units (cents)       |
| created_at  | TIMESTAMP    | Creation timestamp                   |
| updated_at  | TIMESTAMP    | Last update timestamp                |

**Indexes**: `currency`, composite `(owner_name, currency)`

### Transactions Table

| Column            | Type         | Description                             |
|-------------------|--------------|-----------------------------------------|
| id                | BIGINT       | Primary key                             |
| wallet_id         | BIGINT       | Foreign key to wallets                  |
| type              | ENUM         | deposit, withdrawal, transfer_debit, transfer_credit |
| amount            | BIGINT       | Transaction amount in minor units       |
| balance_after     | BIGINT       | Wallet balance after transaction        |
| related_wallet_id | BIGINT       | For transfers: the other wallet involved|
| idempotency_key   | VARCHAR(255) | Unique key for idempotency              |
| metadata          | JSON         | Additional transaction data             |
| created_at        | TIMESTAMP    | Creation timestamp                      |
| updated_at        | TIMESTAMP    | Last update timestamp                   |

**Indexes**: `wallet_id`, `type`, `created_at`, unique `(idempotency_key, wallet_id, type)`

## API Endpoints

Base URL: `{base_url}/api`

Replace `{base_url}` with your application URL (e.g., `http://localhost:8000`, `http://wallet-microservice.test`, or your production domain).

### Health Check

**GET** `/health`

Check if the service is running.

**Response:**
```json
{
  "status": "ok"
}
```

---

### Create Wallet

**POST** `/wallets`

Create a new wallet for a user.

**Request Body:**
```json
{
  "owner_name": "John Doe",
  "currency": "USD"
}
```

**Response:** `201 Created`
```json
{
  "success": true,
  "message": "Wallet created successfully.",
  "data": {
    "id": 1,
    "owner_name": "John Doe",
    "currency": "USD",
    "balance": 0,
    "balance_formatted": "$0.00",
    "created_at": "2025-12-19T10:30:00.000000Z"
  }
}
```

---

### List Wallets

**GET** `/wallets?owner=John&currency=USD`

List all wallets with optional filtering.

**Query Parameters:**
- `owner` (optional): Filter by owner name (partial match)
- `currency` (optional): Filter by currency code

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Wallets retrieved successfully.",
  "data": [
    {
      "id": 1,
      "owner_name": "John Doe",
      "currency": "USD",
      "balance": 50000,
      "balance_formatted": "$500.00",
      "created_at": "2025-12-19T10:30:00.000000Z"
    }
  ],
  "count": 1
}
```

---

### Get Wallet Details

**GET** `/wallets/{wallet_id}`

Get details of a specific wallet.

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Wallet retrieved successfully.",
  "data": {
    "id": 1,
    "owner_name": "John Doe",
    "currency": "USD",
    "balance": 50000,
    "balance_formatted": "$500.00",
    "created_at": "2025-12-19T10:30:00.000000Z"
  }
}
```

---

### Get Wallet Balance

**GET** `/wallets/{wallet_id}/balance`

Get the current balance of a wallet.

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Wallet balance retrieved successfully.",
  "data": {
    "wallet_id": 1,
    "balance": 50000,
    "balance_formatted": "$500.00",
    "currency": "USD"
  }
}
```

---

### Deposit Funds

**POST** `/wallets/{wallet_id}/deposit`

Deposit funds into a wallet.

**Headers:**
- `Idempotency-Key` (optional): Unique key to prevent duplicate deposits

**Request Body:**
```json
{
  "amount": 10000
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Deposit successful.",
  "data": {
    "id": 1,
    "wallet_id": 1,
    "type": "deposit",
    "type_label": "Deposit",
    "amount": 10000,
    "amount_formatted": "$100.00",
    "balance_after": 60000,
    "balance_after_formatted": "$600.00",
    "metadata": {
      "description": "Deposit"
    },
    "created_at": "2025-12-19T10:35:00.000000Z"
  }
}
```

**Error Response - Duplicate Idempotency Key:** `200 OK`
```json
{
  "success": true,
  "message": "Deposit successful.",
  "data": {
    "id": 1,
    "wallet_id": 1,
    "type": "deposit",
    "amount": 10000,
    "created_at": "2025-12-19T10:35:00.000000Z"
  }
}
```
Note: Returns the original transaction if idempotency key matches.

---

### Withdraw Funds

**POST** `/wallets/{wallet_id}/withdraw`

Withdraw funds from a wallet.

**Headers:**
- `Idempotency-Key` (optional): Unique key to prevent duplicate withdrawals

**Request Body:**
```json
{
  "amount": 5000
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Withdrawal successful.",
  "data": {
    "id": 2,
    "wallet_id": 1,
    "type": "withdrawal",
    "type_label": "Withdrawal",
    "amount": 5000,
    "amount_formatted": "$50.00",
    "balance_after": 55000,
    "balance_after_formatted": "$550.00",
    "metadata": {
      "description": "Withdrawal"
    },
    "created_at": "2025-12-19T10:40:00.000000Z"
  }
}
```

**Error Response - Insufficient Balance:** `400 Bad Request`
```json
{
  "success": false,
  "error": "Insufficient Balance",
  "message": "Insufficient balance in wallet. Available: $450.00, Required: $500.00"
}
```

---

### Transfer Funds

**POST** `/transfers`

Transfer funds between two wallets.

**Headers:**
- `Idempotency-Key` (optional): Unique key to prevent duplicate transfers

**Request Body:**
```json
{
  "source_wallet_id": 1,
  "target_wallet_id": 2,
  "amount": 2500
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Transfer completed successfully.",
  "data": {
    "source_transaction": {
      "id": 3,
      "wallet_id": 1,
      "type": "transfer_debit",
      "type_label": "Transfer Out",
      "amount": 2500,
      "amount_formatted": "$25.00",
      "balance_after": 52500,
      "balance_after_formatted": "$525.00",
      "related_wallet_id": 2,
      "metadata": {
        "description": "Transfer to wallet #2",
        "target_wallet_id": 2,
        "target_wallet_owner": "Jane Smith"
      },
      "created_at": "2025-12-19T10:45:00.000000Z"
    },
    "target_transaction": {
      "id": 4,
      "wallet_id": 2,
      "type": "transfer_credit",
      "type_label": "Transfer In",
      "amount": 2500,
      "amount_formatted": "$25.00",
      "balance_after": 2500,
      "balance_after_formatted": "$25.00",
      "related_wallet_id": 1,
      "metadata": {
        "description": "Transfer from wallet #1",
        "source_wallet_id": 1,
        "source_wallet_owner": "John Doe"
      },
      "created_at": "2025-12-19T10:45:00.000000Z"
    }
  }
}
```

**Error Responses:**

Currency Mismatch - `400 Bad Request`:
```json
{
  "success": false,
  "error": "Currency Mismatch",
  "message": "Cannot transfer between wallets with different currencies: USD -> EUR"
}
```

Self Transfer - `400 Bad Request`:
```json
{
  "success": false,
  "error": "Self Transfer Not Allowed",
  "message": "Cannot transfer funds to the same wallet (ID: 1)"
}
```

---

### Get Transaction History

**GET** `/wallets/{wallet_id}/transactions?type=deposit&start_date=2025-01-01&end_date=2025-12-31&per_page=20&page=1`

Get transaction history for a wallet.

**Query Parameters:**
- `type` (optional): Filter by type (deposit, withdrawal, transfer_debit, transfer_credit)
- `start_date` (optional): Filter from date (Y-m-d format)
- `end_date` (optional): Filter to date (Y-m-d format)
- `per_page` (optional): Results per page (default: 15)
- `page` (optional): Page number (default: 1)

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Transactions retrieved successfully.",
  "data": [
    {
      "id": 1,
      "wallet_id": 1,
      "type": "deposit",
      "type_label": "Deposit",
      "amount": 10000,
      "amount_formatted": "$100.00",
      "balance_after": 10000,
      "balance_after_formatted": "$100.00",
      "related_wallet_id": null,
      "metadata": {
        "description": "Deposit"
      },
      "created_at": "2025-12-19T10:35:00.000000Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 1,
    "last_page": 1
  }
}
```

---

## Error Responses

All errors follow a consistent format:

**Validation Error - `422 Unprocessable Entity`:**
```json
{
  "success": false,
  "error": "Validation Failed",
  "message": "The given data was invalid.",
  "errors": {
    "amount": ["The amount field is required."]
  }
}
```

**Resource Not Found - `404 Not Found`:**
```json
{
  "success": false,
  "error": "Resource Not Found",
  "message": "The requested resource was not found."
}
```

**Server Error - `500 Internal Server Error`:**
```json
{
  "success": false,
  "error": "Server Error",
  "message": "An unexpected error occurred. Please try again later."
}
```

Note: Internal error details (stack traces, file paths) are never exposed to clients but are logged for debugging.

---

## Testing with Postman

A comprehensive Postman collection is included: `Wallet-API.postman_collection.json`

### Import Collection

1. Open Postman
2. Click **Import**
3. Select `Wallet-API.postman_collection.json`
4. Collection includes 21 requests organized in folders

### Collection Features

- **Auto-generated Idempotency Keys**: Uses `{{$guid}}` for unique keys
- **Collection Variables**:
  - `base_url`: API base URL (default: http://localhost:8000/api)
  - `wallet_id`: Auto-saved from wallet creation
  - `wallet_id_2`: Auto-saved for transfers
- **Test Scripts**: Automatically save wallet IDs for subsequent requests
- **Example Requests**: All endpoints with valid payloads
- **Error Scenarios**: Invalid amounts, insufficient balance, currency mismatch, etc.

### Folders Included

1. **Wallets**: Create, list, show, balance
2. **Transactions**: Deposits, withdrawals
3. **Transfers**: Successful transfers, error cases
4. **Error Scenarios**: Validation errors, edge cases

---

## Project Structure

```
wallet-service/
├── app/
│   ├── Contracts/              # Repository interfaces
│   │   ├── TransactionRepositoryInterface.php
│   │   └── WalletRepositoryInterface.php
│   ├── Enums/                  # PHP 8.1+ Enums
│   │   └── TransactionType.php
│   ├── Events/                 # Domain events
│   │   ├── FundsDeposited.php
│   │   ├── FundsWithdrawn.php
│   │   ├── TransactionFailed.php
│   │   ├── TransferCompleted.php
│   │   └── WalletCreated.php
│   ├── Exceptions/             # Custom exceptions
│   │   └── Wallet/
│   │       ├── CurrencyMismatchException.php
│   │       ├── InsufficientBalanceException.php
│   │       ├── InvalidAmountException.php
│   │       └── SelfTransferException.php
│   ├── Http/
│   │   ├── Controllers/        # API controllers
│   │   │   ├── TransactionController.php
│   │   │   ├── TransferController.php
│   │   │   └── WalletController.php
│   │   ├── Requests/           # Form request validation
│   │   │   ├── DepositRequest.php
│   │   │   ├── StoreTransferRequest.php
│   │   │   ├── StoreWalletRequest.php
│   │   │   └── WithdrawRequest.php
│   │   └── Resources/          # API resources
│   │       ├── TransactionResource.php
│   │       └── WalletResource.php
│   ├── Listeners/              # Event listeners
│   │   ├── LogFundsDeposited.php
│   │   ├── LogFundsWithdrawn.php
│   │   ├── LogTransactionFailed.php
│   │   ├── LogTransferCompleted.php
│   │   └── LogWalletCreated.php
│   ├── Models/                 # Eloquent models
│   │   ├── Transaction.php
│   │   └── Wallet.php
│   ├── Repositories/           # Repository implementations
│   │   ├── TransactionRepository.php
│   │   └── WalletRepository.php
│   └── Services/               # Business logic
│       ├── TransactionService.php
│       ├── TransferService.php
│       └── WalletService.php
├── bootstrap/
│   └── app.php                 # Exception handlers
├── database/
│   └── migrations/             # Database migrations
│       ├── 2025_12_17_221456_create_wallets_table.php
│       └── 2025_12_17_221521_create_transactions_table.php
├── routes/
│   └── api.php                 # API routes
├── tests/                      # Pest PHP tests
├── Wallet-API.postman_collection.json
├── SCALING.md                  # Scaling considerations
└── README.md                   # This file
```

---

## Development

### Running Tests

```bash
php artisan test
```

### Code Quality

```bash
# Run Laravel Pint (code style fixer)
./vendor/bin/pint

# Run PHPStan (static analysis)
./vendor/bin/phpstan analyse
```

### Logging

All transactions and errors are logged in `storage/logs/laravel.log`

Event listeners log:
- Wallet creation
- Successful deposits/withdrawals/transfers
- Failed transactions with error details

---

## Security Considerations

- **No Authentication**: As per requirements, no authentication is implemented
- **SQL Injection**: Protected via Eloquent ORM and prepared statements
- **Mass Assignment**: Protected via `$fillable` attributes
- **Error Exposure**: Internal errors logged but never exposed to clients
- **Idempotency**: Prevents duplicate financial transactions
- **Race Conditions**: Pessimistic locking prevents concurrent update issues
- **Input Validation**: All inputs validated via Form Requests

---

## Currency Support

The system supports any ISO 4217 currency code (3-letter code):

- USD (US Dollar)
- EUR (Euro)
- GBP (British Pound)
- SAR (Saudi Riyal)
- JPY (Japanese Yen)
- CAD (Canadian Dollar)
- AUD (Australian Dollar)
- And 150+ more...

**Important**: All monetary values are stored as **integers in minor units** (cents, pence, etc.) to avoid floating-point precision issues.

Examples:
- $10.50 USD = 1050 cents
- €25.99 EUR = 2599 cents
- £100.00 GBP = 10000 pence
- ﷼100.00 SAR = 10000 halalas

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## Support

For issues, questions, or contributions, please open an issue on GitHub.

---

## Additional Documentation

- [SCALING.md](SCALING.md) - Scaling considerations and potential improvements
- [Postman Collection](Wallet-API.postman_collection.json) - Complete API testing suite
