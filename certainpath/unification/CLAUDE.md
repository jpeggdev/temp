# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Unification is a multi-tenant SaaS marketing campaign management platform built with Symfony 6.4. It handles prospect data collection, address verification, campaign management, and batch processing for service industry companies (HVAC, plumbing, etc.).

## Development Commands

### Testing
```bash
# Run all tests (takes ~30 minutes)
./vendor/bin/phpunit

# Clear temp files before testing
rm -rf var/tmp/*

# Run specific test
./vendor/bin/phpunit tests/Services/AddressServiceTest.php
```

### Code Quality
```bash
# Fix code style issues
vendor/bin/php-cs-fixer fix

# Run static analysis
vendor/bin/phpstan analyse

# Run both quality checks
composer checks
```

### Database Operations
```bash
# Setup local databases (DROPS existing data)
./provision_databases

# Run migrations
bin/console doctrine:migrations:migrate

# Populate test data
bin/console unification:populate-data
```

### Frontend Development
```bash
# Build Tailwind CSS
bin/console tailwind:build

# Install frontend dependencies
npm install
```

### Campaign Management
```bash
# Create new campaign
bin/console unification:campaign:init <name> <startDate> <endDate> <mailingFrequencyWeeks> <companyId> <mailPackageId> [<description>] [<phoneNumber>]

# Process next campaign iteration
bin/console unification:campaign:process-next-iteration [<iterationStartDate>]
```

### Data Migration
```bash
# Bulk migration from Stochastic Roster
bin/console unification:data:bulk-migration --limit=2
```

## Architecture Overview

### Core Domain Entities
- **Company**: Multi-tenant root entity with identifier-based isolation
- **Campaign**: Marketing campaigns with iterations and batch processing
- **Prospect**: Potential customers with address verification and trade classification
- **Address**: Dual system (regular/restricted) with third-party verification
- **Batch**: Processing units for campaign iterations with status tracking

### Key Services
- **MigrationService**: Handles bulk data imports from various sources (DBF, CSV, Excel)
- **AddressService**: Address verification using strategy pattern (USPS, SmartyStreets, Bypass)
- **CampaignService**: Campaign lifecycle management with event sourcing
- **CompanyDigestingAndProcessingService**: Multi-tenant data processing

### Data Integration
- **External Systems**: ServiceTitan, FieldEdge, Successware, MailManager
- **File Processing**: Supports DBF, CSV, Excel formats with standardization
- **Address Verification**: Multiple providers with fallback strategies
- **Cloud Storage**: AWS S3 and OneDrive integration

### Repository Pattern
- **AbstractRepository**: Base class with common CRUD operations
- **Query Builders**: Dedicated classes for complex queries
- **Unmanaged Repositories**: External system integration (separate namespace)

### Entity Traits
- **TimestampableEntity**: Automatic created/updated timestamps
- **StatusEntity**: Active/inactive status management
- **ExternalIdEntity**: External system integration identifiers
- **PostProcessEntity**: Batch processing status flags

## Development Practices

### Code Organization
- Services are `readonly` classes with constructor injection
- Use strategy pattern for pluggable components (AddressVerification)
- Factory pattern for parsers and importers
- Event sourcing for campaign state management

### Multi-tenancy
- All data operations are scoped by Company entity
- Use CompanyRepository for tenant-aware queries
- Avoid cross-tenant data access

### Address Processing
- All addresses go through verification pipeline
- Support for business/residential classification
- Restricted addresses are tracked separately
- Geographic filtering for campaign targeting

### File Processing
- Large files processed in batches
- Support for various formats (DBF legacy files common)
- Temporary file management in var/tmp/
- AWS S3 integration for file storage

## Environment Setup

### Prerequisites
- PHP 8.2+ with extensions: bcmath, ctype, dbase, fileinfo, iconv, json, xmlreader, zip, pdo
- csvkit: `brew install csvkit` (Mac)
- dbase extension: `pecl install dbase`

### Configuration Files
- Copy `.env.test.local` and `.env.local` from SharePoint
- VPN connection required for testing
- Database configuration in `.env.local`

## Deployment

### Production Deployment
```bash
# Deploy to production
BRANCH=<branch-name> php vendor/bin/dep deploy nexus.mycertainpath.com

# Local deployment testing
BRANCH=<branch-name> php vendor/bin/dep deploy localhost
```

### Docker Development
```bash
# Build and start containers
docker-compose build
docker-compose up -d

# Inside container setup
bin/console doctrine:database:create
bin/console doctrine:migrations:migrate
bin/console unification:populate-data
bin/console tailwind:build
```

## Important Notes

- Test suite can take 30+ minutes to complete
- Large data migrations should use batch processing
- Address verification has rate limits - use bypass service for testing
- Campaign processing involves complex state transitions
- File imports support legacy DBF format common in service industry
- All prospect data is company-scoped for multi-tenancy