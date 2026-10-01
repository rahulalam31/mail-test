# Domain Intelligence Checker

A Laravel-based domain intelligence and email infrastructure checker that provides:

- DNS record inspection
- SPF / DMARC / DKIM detection
- DNSBL / RBL blacklist checking
- Google Workspace detection
- Microsoft 365 detection
- Bulk CSV/TXT processing
- Background queue processing
- Concurrent bulk processing through Redis/Horizon
- Live progress tracking
- Result filtering
- CSV export
- Per-domain status and error reporting

---

## 1. Requirements

### Server requirements

- PHP 8.4+
- Laravel 12.x
- MySQL 8+
- Redis 6+
- Composer
- Node.js 18+
- npm
- PHP extensions:
  - `pdo`
  - `pdo_mysql`
  - `mbstring`
  - `openssl`
  - `fileinfo`
  - `json`
  - `filter`
  - `sockets` if required by the environment
  - DNS-related PHP functionality available through `dns_get_record()`

### Recommended development environment

```text
PHP       8.4+
Laravel   12.x
MySQL     8.x
Redis     6.x+
Node      18+
```

---

# 2. Installation

Clone the project:

```bash
git clone https://github.com/rahulalam31/mail-test>
cd mail-test
```

Install PHP dependencies:

```bash
composer install
```

Install frontend dependencies:

```bash
npm install
```

Create the environment file:

```bash
cp .env.example .env
```

Generate the Laravel application key:

```bash
php artisan key:generate
```

---

# 3. Database Configuration

Use Default SQlite db for fast deploy. or you can configure Mysql below.
Configure MySQL in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=domain_checker
DB_USERNAME=root
DB_PASSWORD=
```

Create the database and run migrations:

```bash
php artisan migrate
```

The application uses two primary tables:

```text
bulk_checks
domain_checks
```

### `bulk_checks`

Stores the overall state of a bulk operation:

- Total domains
- Queued domains
- Currently checking domains
- Completed domains
- Failed domains
- Uploaded filename

### `domain_checks`

Stores the individual result for every domain:

- Original input
- Normalized domain
- Processing status
- DNS records
- MX records
- SPF
- DMARC
- DKIM
- Nameservers
- Blacklist results
- Mail provider
- Detection evidence
- Errors
- Processing timestamps

---

# 4. Redis Configuration

Bulk processing uses Redis queues.

Configure `.env`:

```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_QUEUE=domain-checks
```

The application uses a dedicated queue:

```text
domain-checks
```

This prevents domain-checking jobs from competing unnecessarily with unrelated application jobs.

---

# 5. DNS Configuration

The DNS configuration is stored in:

```text
config/dns.php
```

Example:

```php
return [
    'timeout' => (int) env('DNS_TIMEOUT', 3),

    'retries' => (int) env('DNS_RETRIES', 2),

    'blacklists' => [
        'zen.spamhaus.org',
        'bl.spamcop.net',
        'b.barracudacentral.org',
        'dnsbl.sorbs.net',
    ],

    'dkim_selectors' => [
        'default',
        'selector1',
        'selector2',
        'google',
        'k1',
        'dkim',
        'mail',
        's1',
        's2',
    ],
];
```

Environment configuration:

```env
DNS_TIMEOUT=3
DNS_RETRIES=2
```

> **Important:** The current implementation uses PHP's native `dns_get_record()` API. The retry configuration is implemented at the application level, but the native PHP DNS API does not provide a reliable per-query timeout parameter. Therefore `DNS_TIMEOUT` should currently be considered a configuration placeholder rather than a guaranteed hard timeout. A production implementation should use a DNS client/resolver with explicit timeout support.

---

# 7. Run the Application

Start Laravel:

```bash
php artisan serve
```

The application will normally be available at:

```text
http://127.0.0.1:8000
```

The domain checker is available at:

```text
/domain-checker
```

---

# 8. Queue Processing

Bulk checks are processed asynchronously.

For local development, Horizon is used to manage concurrent workers.

Install Horizon:

```bash
composer require laravel/horizon
```

Install its configuration:

```bash
php artisan horizon:install
```

Run:

```bash
php artisan horizon
```

Horizon processes jobs from:

```text
domain-checks
```

The application can therefore process multiple domain checks concurrently instead of processing an entire bulk file sequentially.

---

# 9. Queue Architecture

The processing flow is:

```text
                    ┌──────────────────────┐
                    │    Livewire UI       │
                    └──────────┬───────────┘
                               │
                               ▼
                    ┌──────────────────────┐
                    │ BulkDomainImporter   │
                    └──────────┬───────────┘
                               │
                               ▼
                    ┌──────────────────────┐
                    │    domain_checks     │
                    │      database        │
                    └──────────┬───────────┘
                               │
                               ▼
                    ┌──────────────────────┐
                    │ Redis Queue          │
                    │ domain-checks        │
                    └──────────┬───────────┘
                               │
                         ┌─────┴─────┐
                         │           │
                         ▼           ▼
                    ┌─────────┐ ┌─────────┐
                    │ Worker  │ │ Worker  │
                    │    1    │ │    2    │
                    └────┬────┘ └────┬────┘
                         │           │
                         └─────┬─────┘
                               ▼
                    ┌──────────────────────┐
                    │ DomainCheckService   │
                    └──────────┬───────────┘
                               │
             ┌─────────────────┼──────────────────┐
             │                 │                  │
             ▼                 ▼                  ▼
       ┌───────────┐     ┌────────────┐    ┌──────────────┐
       │ DNS       │     │ DNSBL/RBL  │    │ Mail Provider│
       │ Checker   │     │ Checker    │    │ Detector     │
       └───────────┘     └────────────┘    └──────────────┘
```

---

# 10. Application Architecture

The application follows a service-oriented architecture.

```text
app/
├── Jobs/
│   └── ProcessDomainCheck.php
│
├── Livewire/
│   └── DomainChecker.php
│
├── Models/
│   ├── BulkCheck.php
│   └── DomainCheck.php
│
├── Services/
│   ├── DomainCheckService.php
│   ├── BulkDomainImporter.php
│   │
│   ├── Domain/
│   │   └── DomainNormalizer.php
│   │
│   ├── Dns/
│   │   ├── DnsResolver.php
│   │   ├── DnsChecker.php
│   │   └── DkimChecker.php
│   │
│   ├── Blacklist/
│   │   └── DnsBlacklistChecker.php
│   │
│   └── MailProvider/
│       └── MailProviderDetector.php
│
└── Http/
    └── Controllers/
        └── DomainCheckExportController.php
```

### Main responsibilities

#### `DomainNormalizer`

Responsible for:

- Domain normalization
- Email-to-domain extraction
- URL normalization
- Lowercasing
- Basic domain validation

Examples:

```text
Example.COM
        ↓
example.com

user@example.com
        ↓
example.com

https://example.com/path
        ↓
example.com
```

---

## `DomainCheckService`

Acts as the main application service.

It coordinates:

```text
DNS
DNSBL
DKIM
Mail Provider Detection
```

and produces a single normalized result structure consumed by both:

- Livewire
- Queue jobs

This keeps the Livewire component from containing DNS/business logic.

---

## `DnsResolver`

Provides the low-level DNS lookup abstraction.

Instead of calling:

```php
dns_get_record()
```

throughout the application, DNS access is centralized in one service.

This makes it easier to replace the DNS implementation later with:

- Cloudflare DNS
- Google Public DNS
- another recursive resolver
- a dedicated DNS client
- an internal DNS service

without rewriting the application layer.

---

# 11. DNS Checks

The application checks:

```text
A
AAAA
MX
TXT
CNAME
NS
PTR
SPF
DMARC
DKIM
```

### A / AAAA

Used to identify the domain's IPv4 and IPv6 addresses.

These IP addresses are also used as inputs for DNSBL checks.

---

## MX

MX records are normalized into:

```json
[
    {
        "host": "aspmx.l.google.com",
        "priority": 10
    }
]
```

The normalized representation makes it easier for the provider detector and frontend to consume.

---

## SPF

SPF is detected from TXT records.

The application searches for records beginning with:

```text
v=spf1
```

The result contains:

```json
{
    "present": true,
    "value": "v=spf1 ..."
}
```

---

## DMARC

DMARC is queried using:

```text
_dmarc.example.com
```

The application searches TXT records for:

```text
v=DMARC1
```

The result contains:

```json
{
    "present": true,
    "value": "v=DMARC1; ..."
}
```

---

## DKIM

DKIM differs from SPF and DMARC because there is no standardized DNS mechanism for discovering an organization's active DKIM selector.

Therefore, the application supports:

### Explicit selector

Example:

```text
selector1
```

The application checks:

```text
selector1._domainkey.example.com
```

### Common selector discovery

When no selector is provided, the application checks a configurable list of common selectors:

```text
default
selector1
selector2
google
k1
dkim
mail
s1
s2
```

This is heuristic rather than guaranteed discovery.

A domain may have DKIM configured using a selector that is not included in this list.

---

# 12. DNSBL / RBL Checking

The application checks domain IP addresses against configurable DNSBL providers.

Configured sources include:

```text
zen.spamhaus.org
bl.spamcop.net
b.barracudacentral.org
dnsbl.sorbs.net
```

For IPv4, the DNSBL query is generated by reversing the IP:

```text
1.2.3.4
```

becomes:

```text
4.3.2.1.dnsbl.example
```

For IPv6, the hexadecimal nibbles are reversed according to DNSBL lookup conventions.

---

## DNSBL result states

The application distinguishes:

### Clean

The IP was successfully checked and no configured blacklist returned a listing.

### Listed

At least one configured DNSBL returned a positive result.

### Unknown

The application could not reliably determine the status because one or more DNSBL lookups failed.

This distinction is important because:

```text
DNS lookup failure ≠ Clean
```

A failed DNSBL query should not incorrectly report an IP as clean.

---

# 13. Google Workspace / Microsoft 365 Detection

Mail provider detection is primarily based on MX records.

The application normalizes MX hostnames and checks them against known infrastructure patterns.

---

## Google Workspace

Known Google Workspace MX hosts include:

```text
aspmx.l.google.com
alt1.aspmx.l.google.com
alt2.aspmx.l.google.com
alt3.aspmx.l.google.com
alt4.aspmx.l.google.com
```

If a matching MX record is found:

```text
provider = google
status = detected
```

---

## Microsoft 365

Microsoft 365 commonly uses MX hosts ending with:

```text
.mail.protection.outlook.com
```

For example:

```text
example-com.mail.protection.outlook.com
```

If a matching MX record is found:

```text
provider = microsoft
status = detected
```

---

## Other

If MX records exist but don't match the known Google Workspace or Microsoft 365 patterns:

```text
provider = other
```

---

## Not detected

If there are no MX records:

```text
provider = not_detected
```

This means the application could not determine a mail provider from MX records. It does not necessarily prove that the domain has no email infrastructure.

---

# 14. Bulk Processing

Bulk input supports:

```text
CSV
TXT
```

Example TXT:

```text
google.com
microsoft.com
example.com
cloudflare.com
```

CSV files can contain domain/email values.

Each input is normalized before creating a `DomainCheck` record.

Invalid values are skipped.

---

# 15. Duplicate Handling

Domains are normalized before duplicate detection.

For example:

```text
Google.com
google.com
user@google.com
https://google.com
```

all normalize to:

```text
google.com
```

Only one domain check is created within the same bulk operation.

A database-level unique constraint is also used on:

```text
bulk_check_id + domain
```

This provides an additional protection against duplicate records.

---

# 16. Bulk Job Lifecycle

Every domain starts as:

```text
queued
```

When a worker picks it up:

```text
checking
```

After successful processing:

```text
completed
```

After all configured retry attempts fail:

```text
failed
```

The UI exposes these states directly.

Example:

```text
Queued      347
Checking      5
Completed   640
Failed        8
```

---

# 17. Queue Retries

Domain processing jobs use multiple attempts.

Current configuration:

```php
public int $tries = 3;

public array $backoff = [
    5,
    15,
    30,
];
```

The approximate retry sequence is:

```text
Attempt 1
    ↓
Failure
    ↓
5 seconds
    ↓
Attempt 2
    ↓
Failure
    ↓
15 seconds
    ↓
Attempt 3
    ↓
Failure
    ↓
Failed
```

If a retry succeeds, the domain is marked:

```text
completed
```

---

# 18. Live Progress

The Livewire interface periodically refreshes the bulk check.

The current implementation uses:

```blade
wire:poll.2s="refreshBulk"
```

This allows the UI to display progress without requiring a full page refresh.

The progress calculation is:

```text
completed + failed
------------------ × 100
      total
```

For example:

```text
Total      = 1000
Completed  = 700
Failed     = 20

Processed = 720

Progress = 72%
```

---

# 19. CSV Export

Completed and failed bulk results can be exported as CSV.

The export includes:

```text
Domain
Input
Status
Blacklist Status
Blacklists
Mail Provider
MX Records
SPF
DMARC
DKIM
Nameservers
Error
```

The export uses a streamed response so that large result sets don't have to be loaded entirely into memory.

---

# 20. Livewire Implementation

The frontend is implemented using:

```text
Laravel Livewire 3
Tailwind CSS
Blade
```

The main component is:

```text
App\Livewire\DomainChecker
```

The component handles:

- Single checks
- File uploads
- Bulk operation state
- Filtering
- Search
- Result selection
- Details modal
- Progress polling

Business logic remains in service classes instead of being embedded inside the Livewire component.

---

# 21. Running the Complete Application Locally

Start Redis:

```bash
redis-server
```

Start Laravel:

```bash
php artisan serve
```

Start frontend development:

```bash
npm run dev
```

Start Horizon:

```bash
php artisan horizon
```

The normal local workflow is therefore:

```text
Terminal 1
──────────
redis-server

Terminal 2
──────────
php artisan serve

Terminal 3
──────────
npm run dev

Terminal 4
──────────
php artisan horizon
```

Then open:

```text
http://127.0.0.1:8000/domain-checker
```

---

# 22. Testing

Run the test suite:

```bash
php artisan test
```

Specific tests can be run using:

```bash
php artisan test tests/Unit/DomainNormalizerTest.php
```

and:

```bash
php artisan test tests/Unit/MailProviderDetectorTest.php
```

The tests cover important normalization and provider-detection behavior.

---

# 23. Environment Configuration

Example `.env`:

```env
APP_NAME="Domain Intelligence Checker"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=domain_checker
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_QUEUE=domain-checks

DNS_TIMEOUT=3
DNS_RETRIES=2
```

For production:

```env
APP_ENV=production
APP_DEBUG=false
```

---

# 24. Assumptions

## Domain normalization

The application assumes inputs are primarily:

- Domains
- Email addresses
- URLs containing a domain

Email addresses are reduced to their domain:

```text
user@example.com
→ example.com
```

---

## DKIM

DKIM selectors cannot reliably be discovered automatically from DNS.

The implementation therefore:

1. Allows the user to provide a selector.
2. Checks a configurable list of common selectors when no selector is supplied.

This provides useful coverage without pretending that arbitrary DKIM selectors can be discovered from DNS alone.

---

## Mail provider detection

Provider detection is based primarily on MX records.

It is therefore an infrastructure classification rather than an authoritative statement about the organization's complete email stack.

For example, a domain could use:

```text
Primary provider → Microsoft 365
Secondary routing → third-party gateway
```

The application reports based on the MX evidence it sees.

---

## DNSBL

DNSBL availability depends on the configured third-party DNSBL services.

A DNSBL timeout or resolver failure is treated as:

```text
unknown
```

rather than:

```text
clean
```

This avoids false-clean results.

---

# 25. Current Limitations

### 1. DNS timeout

PHP's native:

```php
dns_get_record()
```

does not expose a reliable per-query timeout parameter.

The application implements retries but does not currently guarantee that every DNS lookup is terminated after exactly the configured timeout.

For production, this should be replaced with a DNS client/resolver supporting:

- Explicit timeout
- Retry policy
- Resolver selection
- DNS response codes
- Query cancellation
- Optional DNS-over-TLS/HTTPS

---

### 2. DKIM discovery

Automatic DKIM selector discovery is inherently limited.

The application uses:

- User-supplied selectors
- Configurable common selectors

It cannot guarantee detection of an arbitrary selector.

---

### 3. DNSBL provider policies

Some DNSBL services have usage restrictions, query limits, or commercial licensing requirements.

Production deployment should review the terms and recommended query methods for every configured DNSBL provider.

The blacklist list is therefore configurable rather than hardcoded into the application architecture.

---

### 4. PTR lookups

PTR resolution currently relies on the system resolver.

A production implementation should move PTR queries into the same controlled DNS client used for other DNS operations so that timeout, retry and resolver behavior is consistent.

---

### 5. Livewire polling

The current UI uses:

```blade
wire:poll.2s
```

This is simple and reliable for an assignment/demo environment.

For a high-volume production application, polling every two seconds from many browser sessions could generate unnecessary application traffic.

A better production implementation would use:

```text
Laravel Reverb
    +
WebSockets
    +
Livewire events
```

to push job completion updates to the browser.

---

# 26. Production Improvements

The following improvements would be recommended before production deployment.

## DNS infrastructure

Replace native PHP DNS lookups with a proper asynchronous/timeout-aware DNS client.

Potential capabilities:

```text
Explicit timeouts
Retry policies
Resolver pools
DNS response-code handling
Circuit breakers
Caching
IPv4/IPv6 support
```

---

## DNS caching

DNS results could be cached using Redis.

For example:

```text
dns:example.com:A
dns:example.com:AAAA
dns:example.com:MX
dns:example.com:TXT
```

with appropriate TTLs.

This would significantly reduce repeated DNS queries.

---

## Queue scaling

Use Horizon with controlled worker limits.

Example:

```text
Small deployment
2–5 workers

Medium deployment
5–20 workers

Large deployment
Auto-scaled workers
```

The actual limit should be based on:

- DNS provider limits
- DNSBL policies
- CPU
- network bandwidth
- Redis capacity
- database throughput

Simply increasing concurrency indefinitely would increase the likelihood of rate limiting.

---

## Rate limiting

A production system should implement per-provider rate limits.

For example:

```text
DNS queries
     ↓
Rate limiter
     ↓
Resolver

DNSBL queries
     ↓
Rate limiter
     ↓
DNSBL
```

This is especially important for third-party DNSBL services.

---

## Database optimization

For large datasets:

- Index `bulk_check_id`
- Index `status`
- Index `domain`
- Index `mail_provider`
- Index `blacklist_status`
- Consider partitioning or archival for historical checks
- Avoid unnecessarily storing very large raw DNS responses

---

## Bulk upload limits

Production should impose limits such as:

```text
Maximum file size
Maximum domains per upload
Maximum concurrent bulk jobs
Maximum requests per user
```

Large files should preferably be streamed rather than loaded completely into PHP memory.

---

## Authentication and authorization

Production should add:

```text
Authentication
Authorization
Per-user bulk ownership
Per-user rate limits
Audit logging
```

A user should only be able to access/export their own bulk checks.

---

## Observability

Production should include:

```text
Laravel Telescope
Laravel Horizon
Centralized application logs
Queue failure monitoring
DNS failure metrics
Blacklist failure metrics
Response-time metrics
```

Useful metrics include:

```text
Average DNS lookup time
DNS failure rate
DNSBL failure rate
Jobs/minute
Queue wait time
Average domain processing time
Failed jobs
```

---

## WebSocket-based progress

Replace:

```blade
wire:poll.2s
```

with:

```text
Laravel Reverb
```

and broadcast events such as:

```text
DomainCheckStarted
DomainCheckCompleted
DomainCheckFailed
BulkCheckProgressUpdated
```

This would make progress updates event-driven instead of polling-based.

---

# 27. Security Considerations

The application accepts user-controlled domain input, therefore DNS queries should never be treated as arbitrary shell commands.

The implementation uses PHP's DNS API rather than constructing shell commands such as:

```bash
dig <user-input>
```

This reduces command-injection risk.

Input is normalized and validated before being processed.

For production, additional protections should include:

- Authentication
- Authorization
- Rate limiting
- Upload validation
- Maximum bulk size
- Queue limits
- Abuse monitoring
- DNS query restrictions
- SSRF review if HTTP-based verification is introduced later

---

# 28. Design Decisions

### Why Livewire?

The application is primarily a server-driven operational interface.

Livewire provides:

- Reactive filtering
- File uploads
- Progress updates
- Modal state
- Search
- Minimal JavaScript
- Good Laravel integration

without requiring a separate SPA/API architecture.

---

### Why Redis?

Redis provides:

- Fast queue operations
- Reliable Laravel queue integration
- Easy horizontal worker scaling
- Compatibility with Laravel Horizon

---

### Why Horizon?

Horizon provides:

- Worker management
- Queue monitoring
- Concurrency configuration
- Retry visibility
- Failed job visibility
- Operational metrics

It is preferable to manually starting multiple queue workers for production.

---

### Why service classes?

DNS, blacklist and provider detection are isolated into separate services.

This keeps the system modular:

```text
DomainCheckService
       │
       ├── DnsChecker
       │
       ├── DkimChecker
       │
       ├── DnsBlacklistChecker
       │
       └── MailProviderDetector
```

Each service can be tested and replaced independently.

---

# 29. End-to-End Flow

### Single domain

```text
User enters domain/email
        ↓
DomainNormalizer
        ↓
DomainCheckService
        ↓
 ┌──────┼────────┬──────────────┐
 ↓      ↓        ↓              ↓
DNS   DNSBL    DKIM      Mail Provider
 ↓      ↓        ↓              ↓
 └──────┴────────┴──────────────┘
                ↓
          Result returned
                ↓
           Livewire UI
```

---

### Bulk domain check

```text
CSV/TXT upload
       ↓
BulkDomainImporter
       ↓
Normalize + validate
       ↓
Remove duplicates
       ↓
Create BulkCheck
       ↓
Create DomainCheck records
       ↓
Dispatch queue jobs
       ↓
Redis
       ↓
Laravel Horizon
       ↓
Concurrent ProcessDomainCheck jobs
       ↓
DomainCheckService
       ↓
Store results
       ↓
Update bulk counters
       ↓
Livewire refresh
       ↓
User sees progress
```

---

# 30. Summary

This project uses Laravel 12, PHP 8.4, Livewire 3, MySQL, Redis and Laravel Horizon to provide a domain intelligence checker with both synchronous single-domain checks and asynchronous bulk processing.

The architecture intentionally separates:

```text
UI
↓
Application Services
↓
DNS / DNSBL / DKIM / Provider Detection
↓
Database
```

while Redis/Horizon handles background processing and concurrency.

The implementation favors correctness around uncertain DNS results: a DNS failure is not automatically considered a clean result, and provider detection is based on explicit MX evidence.

For production, the most important next improvements would be a proper timeout-aware DNS client, stronger DNS caching/rate limiting, WebSocket-based progress updates, authentication/authorization, and operational monitoring.
