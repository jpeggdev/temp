# CLAUDE.md

This file provides **project-specific** guidance for the Hub Plus API repository.

## 🔴 CRITICAL INSTRUCTIONS 🔴

This project uses the Agentic Persona Mapping (APM) framework. The APM system is installed in the `.apm/` directory within this project.

**IMPORTANT**: There are TWO CLAUDE.md files in this project:
1. **This file** (in the project root) - General project instructions and overview
2. **`.apm/CLAUDE.md`** - APM-specific instructions and persona activation commands

### To activate the APM framework:
- Use the `/ap` or `/ap_orchestrator` command to launch the AP Orchestrator
- The APM-specific CLAUDE.md file in `.apm/` contains detailed instructions for all persona commands
- All APM infrastructure, personas, and configuration are contained within the `.apm/` directory

### Key APM Commands:
- `/ap` or `/ap_orchestrator` - Activates the AP Orchestrator (central coordinator)
- `/dev` - Activates the Developer persona
- `/architect` - Activates the Architect persona
- `/qa` - Activates the QA persona
- `/pm` - Activates the Project Manager persona
- See `.apm/CLAUDE.md` for the complete list of personas and commands

**NOTE**: When any APM command is used, you must follow the specific behavioral rules defined in `.apm/CLAUDE.md`. The APM framework has its own session management, voice notifications, and operational procedures that override default behavior.
Global instructions: @~/.claude/CLAUDE.md | Project docs: @README.md

## 🏗️ MODULAR ARCHITECTURE APPROACH

**CRITICAL**: All new features MUST follow this modular architecture pattern for organized, scalable code structure.

### Feature Module Structure

When implementing new features, organize all related files within a module structure:

**Pattern 1 - Simple Feature Module:**
```
src/Module/{FeatureName}/
├── Command/ (optional - prefer global commands)
├── Controller/
├── DTO/
├── Entity/ (optional - prefer global entities)
├── Exception/
├── Repository/ (optional - prefer global repositories)
├── Voter/
├── Service/
└── ValueObject/
```

**Pattern 2 - Hierarchical Module (preferred for related features):**
```
src/Module/{ModuleName}/Feature/{FeatureName}/
├── Command/ (optional - prefer global commands)
├── Controller/
├── DTO/
├── Exception/
├── Repository/ (optional - prefer global repositories)
├── Service/
├── Voter/
└── ValueObject/
```

### Examples from Existing Codebase

```
src/Module/Stochastic/Feature/PostageUploads/
├── Repository/BatchPostageRepository.php
├── Service/UploadPostageExpenseService.php
└── ValueObject/BatchPostageRecordMap.php

src/Module/Stochastic/Feature/PostageUploadsSftp/
├── Command/ProcessSftpPostageFilesCommand.php
├── Repository/PostageProcessedFileRepository.php
├── Service/SftpBatchPostageProcessorService.php
└── ValueObject/ProcessingSummary.php
```

### Implementation Guidelines

1. **Feature Identification**: When starting work on a new feature, AI must prompt user to:
   - **Name the feature** (e.g., "PostageUploadsSftp", "UserAuthentication", "EventRegistration")
   - **Choose structure pattern**:
     - `App\Module\{FeatureName}` for standalone features
     - `App\Module\{ModuleName}\Feature\{FeatureName}` for features that belong to a logical grouping

2. **Architectural Consistency**: Within each feature module, maintain consistent folder structure:
   - **Command/** - Symfony console commands
   - **Controller/** - HTTP controllers and API endpoints
   - **DTO/** - Data Transfer Objects for API contracts
   - **Entity/** - Only if feature-specific; prefer global entities
   - **Exception/** - Custom exceptions for the feature
   - **Repository/** - Database repositories and queries
   - **Service/** - Business logic and orchestration services
   - **ValueObject/** - Immutable value objects and data containers

3. **Namespace Convention**: Follow PSR-4 autoloading with proper namespaces:
   ```php
   // Simple pattern
   namespace App\Module\UserAuthentication\Service;

   // Hierarchical pattern
   namespace App\Module\Stochastic\Feature\PostageUploadsSftp\Service;
   ```

4. **Cross-Module Dependencies**: Modules can depend on:
   - **Global entities** in `src/Entity/`
   - **Shared services** from other modules
   - **Global value objects** in `src/ValueObject/`

5. **Testing Structure**: Mirror the module structure in tests:
   ```
   tests/Module/{ModuleName}/Feature/{FeatureName}/
   ├── Command/ (optional - prefer global commands)
   ├── Repository/ (optional - prefer global repositories)
   ├── Voter/
   ├── Service/
   └── Integration/
   ```

### Benefits of This Approach

- **Domain Isolation**: Related functionality grouped together
- **Scalability**: Easy to add new features without cluttering global directories
- **Team Collaboration**: Clear ownership boundaries for different features
- **Code Discovery**: Intuitive navigation to find feature-related code
- **Refactoring Safety**: Changes within a feature module have limited blast radius

**When in doubt about feature organization, ALWAYS ask the user**:
*"What should we name this feature, and should it follow the pattern `App\Module\{FeatureName}` or `App\Module\{ModuleName}\Feature\{FeatureName}`?"*

## Essential Commands

### Development Setup

```bash
# Initial setup (after cloning)
composer install
docker-compose build
docker-compose up -d
bin/console doctrine:database:create
bin/console doctrine:migrations:migrate
bin/console doctrine:fixtures:load --append

# Initialize required data
bin/console hub:trades:initialize
bin/console hub:software:initialize
bin/console hub:campaign-products:initialize
```

### Code Quality Commands

```bash
# Run static analysis (MUST pass before committing)
vendor/bin/phpstan analyse

# Check code style violations
vendor/bin/php-cs-fixer fix --dry-run --diff

# Fix code style automatically
vendor/bin/php-cs-fixer fix

# Run both checks (composer script)
composer checks
```

### AUTOMATED CODE QUALITY ENFORCEMENT
**CRITICAL**: Before any git commit operation, Claude MUST automatically:
1. Run `vendor/bin/phpstan analyse` - must pass with no errors
2. Run `vendor/bin/php-cs-fixer fix --dry-run --diff` - check for style issues
3. If style issues found, run `vendor/bin/php-cs-fixer fix` to auto-fix
4. Re-run PHPStan after fixes to ensure no new issues
5. Only proceed with commit if all checks pass

**Exception**: If user explicitly requests to skip checks with `--skip-checks` flag, warn but proceed.

### PROJECT DOCUMENTATION ORGANIZATION
**CRITICAL**: All project documentation MUST follow this mandatory structure:

```
project_docs/
├── requirements/{feature-name}/
├── architecture/{feature-name}/
├── planning/{feature-name}/
├── specifications/{feature-name}/
├── reports/{feature-name}/
├── rules/{feature-name}/
├── artifacts/{feature-name}/
├── releases/{feature-name}/
├── session_notes/{feature-name}/
└── prompt-histories/{feature-name}/
```

### PROMPT HISTORY DOCUMENTATION
**CRITICAL**: All AI collaboration sessions MUST be documented for team knowledge sharing and improved AI collaboration effectiveness.

**Structure Pattern**:
```
project_docs/prompt-histories/{feature-name}/
└── YYYY.MM.DD.prompts.md
```

**Required Content for Each Prompt History File**:

1. **Session Context**:
   - Project name and feature
   - Date and participants
   - Session outcome summary

2. **Literal Prompt Sequence**:
   - **EXACT user prompts in chronological order**
   - Numbered sequence showing thought process progression
   - All prompts including brief ones, questions, and commands
   - Shows the user's actual decision-making flow and iterative refinement

3. **Key Prompts and Interactions**:
   - Selected effective prompts with detailed analysis
   - Context explaining why each prompt was used
   - AI responses and outcomes
   - Impact of each interaction on the session

4. **Effective Patterns Identified**:
   - Successful collaboration approaches
   - Technical patterns that worked well
   - Process improvements discovered

5. **Reusable Prompts**:
   - Templates for similar future features
   - Generic patterns others can adapt
   - Architecture and process decision prompts

**Examples**:
- `project_docs/prompt-histories/sftp-batch-processing/2025.07.21.prompts.md`
- `project_docs/prompt-histories/user-authentication/2025.07.15.prompts.md`
- `project_docs/prompt-histories/event-registration/2025.07.10.prompts.md`

**Benefits**:
- **Team Learning**: Share effective AI collaboration techniques
- **Consistency**: Reuse successful prompt patterns
- **Efficiency**: Avoid rediscovering effective approaches
- **Onboarding**: Help new team members collaborate effectively with AI
- **Process Improvement**: Identify and codify best practices

**AI Responsibility**: At the end of significant feature work, AI should offer to create prompt history documentation:
*"Would you like me to create a prompt history document for this session to help your team learn from our effective collaboration patterns?"*

### UNIT TEST CONVENTIONS

**CRITICAL**: When making assertions in PHPUnit tests, use the static method notation for stateless operations:
- CORRECT: self::assertSame('test','test');
- UNDESIRED: $this->assertSame('test','test');
For stateful assertions such as checking Exceptions, keep using instance method invocation:
- CORRECT: $this->expectException(Foo::class);
- INCORRECT: self::expectException(Foo::class);

**NEVER place documentation files directly in the main folders** (`requirements/`, `architecture/`, etc.). **ALWAYS create feature-specific subfolders** within each main category.

**Examples**:
- ✅ CORRECT: `requirements/sftp-batch-processing/enhancement-requirements.md`
- ❌ INCORRECT: `requirements/enhancement-requirements.md`
- ✅ CORRECT: `architecture/user-authentication/auth-system-design.md`
- ❌ INCORRECT: `architecture/auth-system-design.md`

**This rule applies to ALL APM agents** (Analyst, PM, Architect, Developer, QA, etc.) and ensures organized, scalable project documentation.

### TDD STRATEGY FOR DATA OPERATIONS
**CRITICAL DIRECTIVE**: When test-driving features with database operations, follow this mandatory approach:

1. **Test-Drive Repository First**: Start by test-driving the repository class with all necessary persistence and retrieval operations
2. **Entity Emerges from Repository Tests**: Let entity design naturally evolve from repository requirements rather than designing entity first
3. **Use AbstractKernelTestCase**: ALL data operation tests MUST extend `AbstractKernelTestCase` which automatically resets database schema from entity graph at each test run
4. **Real Database Operations**: Test against actual database operations - DO NOT mock repositories or database interactions
5. **End-to-End Confidence**: Repository tests provide full-stack validation from entity through database persistence
6. **Service Integration**: Test-drive service classes using real repository instances, not mocks
7. **Migration Generation**: Only after all tests pass, run `bin/console doctrine:migrations:diff` to generate migration from entity changes
8. **Migration Cleanup**: Clean up generated migration file by removing statements unrelated to the specific feature being built

**Benefits of this approach**:
- Tests real persistence behavior vs. mocked interactions
- Repository requirements naturally shape optimal entity structure
- Database constraints and relationships are validated in tests
- Full confidence in data operations before production deployment
- Clean, feature-specific migrations generated from working code

**Dependency Injection in Tests**:
When test-driving services that depend on other services or repositories, use AbstractKernelTestCase's helper methods to get real instances:

```php
// Repository tests: Use getRepository() for Doctrine repositories
class PostageProcessedFileRepositoryTest extends AbstractKernelTestCase
{
    private PostageProcessedFileRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        /** @var PostageProcessedFileRepository $repo */
        $repo = $this->getRepository(PostageProcessedFileRepository::class);
        $this->repository = $repo;
    }
}

// Service tests: Use getService() for services, getRepository() for repositories
class SftpBatchPostageProcessorServiceTest extends AbstractKernelTestCase
{
    private SftpBatchPostageProcessorService $processor;
    private PostageProcessedFileRepository $processedFileRepository;

    public function setUp(): void
    {
        parent::setUp();

        // Get repository using inherited method
        /** @var PostageProcessedFileRepository $repo */
        $repo = $this->getRepository(PostageProcessedFileRepository::class);
        $this->processedFileRepository = $repo;

        // Get services using getService() - no mocking!
        $uploadService = $this->getService(UploadPostageExpenseService::class);

        // Use annotation for proper type hinting
         /** @var SftpBatchPostageProcessorService $processorService */
        $processorService = $this->getService(SftpBatchPostageProcessorService::class);
        $this->processor = $processorService;
    }
}

// Integration tests: Use getService() for complete service resolution
class SftpBatchProcessingIntegrationTest extends AbstractKernelTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Service fully resolved by container with all dependencies
        $this->processor = $this->getService(SftpBatchPostageProcessorService::class);
    }
}

// Value Object tests: No dependencies needed, instantiate directly
class BatchPostageRecordMapTest extends AbstractKernelTestCase
{
    public function testSftpColumnMapping(): void
    {
        $map = new BatchPostageRecordMap(); // Direct instantiation
    }
}
```

**Key Patterns**:
- **getRepository(RepositoryClass::class)**: For Doctrine repositories from EntityManager
- **getService(ServiceClass::class)**: For services configured in services.yaml with full dependency injection
- **Direct instantiation**: For Value Objects and simple classes with no dependencies
- **Never mock**: Always use real services and repositories to test actual behavior with real database operations

**Example workflow**:
```php
// 1. Test-drive repository operations first
class PostageProcessedFileRepositoryTest extends AbstractKernelTestCase
{
    public function testFindByFilenameAndHash(): void {
        // Test drives entity structure and database behavior
    }
}

// 2. Entity structure emerges to satisfy repository tests
// 3. Service tests use real repository, not mocks
// 4. Generate migration only after all tests pass
```
### MISCELLANEOUS CODING RULES
**CRITICAL DIRECTIVE**: Keep the following rules when coding:

1. When building a Controller never use the Annotation import, use Attribute instead:
  - Wrong: use Symfony\Component\Routing\Annotation\Route;
  - Correct: use Symfony\Component\Routing\Attribute\Route;
2. When using uniqid() always set the second parameter for more entropy:
  - Wrong: uniqid()
  - Correct: uniqid('', true)
  - Wrong: uniqid('some-string')
  - Correct: uniqid('some-string', true)

### COMMAND VALIDATION AND PERMISSIONS
**Available Commands**: 25+ commands available in `~/.claude/commands/`
- Key commands: `fix-issue`, `create-feature`, `code-review`, `write-tests`, `refactor-code`
- All commands require specific permissions - if a command fails due to permissions, check settings.local.json
- Commands that require GitHub CLI: `fix-issue`, `code-review`, `create-feature`
- Commands that require Docker: `e2e-setup`, `ci-setup`

**Required Permissions for Hub Plus API**:
- `vendor/bin/phpstan analyse`, `vendor/bin/php-cs-fixer fix`, `vendor/bin/phpunit`
- `bin/console` (all Symfony console commands)
- `docker exec`, `docker run`, `docker network` (for container operations)
- `composer checks`, `composer install`

### Testing

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/Service/YourServiceTest.php

# Run tests with coverage
vendor/bin/phpunit --coverage-html coverage
```

### Common Console Commands

```bash
# Company roster sync
bin/console hub:company:ingest --source=account_application  # CARA sync
bin/console hub:company:ingest --source=stochastic_roster   # Stochastic sync

# Database migrations
bin/console doctrine:migrations:diff    # Generate migration from entity changes
bin/console doctrine:migrations:migrate # Run pending migrations
bin/console doctrine:migrations:status  # Check migration status

# Craft CMS migration
bin/console hub:migrate_craft_database -v|vv|vvv [--skip-categories] [--skip-tags] # Migrate Craft CMS data

# Clear cache
bin/console cache:clear
```

### Deployment

```bash
# Deploy to production
vendor/bin/dep deploy

# Deploy specific branch
vendor/bin/dep deploy branch={branch_name}
```

## Architecture Overview

### Technology Stack

- **Framework**: Symfony 6.4 LTS
- **PHP Version**: 8.3+
- **Database**: PostgreSQL (main) + multiple external connections
- **Authentication**: Auth0 JWT
- **Messaging**: Symfony Messenger with database transport
- **Testing**: PHPUnit with Symfony test bundle

### Database Connections

#### **REQUIRED** (for core functionality):
- `default`: Main application database with all entities - **MUST HAVE**
- `messenger`: Async message queue storage - **MUST HAVE**

#### **OPTIONAL** (for specific features):
- `generic_ingest`: Data import staging area - Only needed for data import commands
- `certainpath`: Legacy CARA system (read-only) - Only for CARA integration (`hub:company:ingest --source=account_application`)
- `craft_cms`: CMS migration source (read-only) - Only for CMS migration (`hub:migrate_craft_database`)
- `unification`: Marketing campaign system - Only for campaign features

#### **Setup Priority**:
1. Start with `default` and `messenger` databases only
2. Add others only when their specific features are needed
3. Most development work can be done with just the core databases

### Domain Architecture

#### Multi-Tenancy Model

- **User** → can have multiple **Employee** records → each linked to one **Company**
- Permissions cascade: BusinessRole → BusinessRolePermission → Permission
- Employee-specific permission overrides via EmployeePermission

#### Core Business Entities

1. **Company**: Central entity representing client businesses
    - Integrates with Salesforce, Intacct, and field service software
    - Has employees, trades, and campaign associations

2. **Event System**: Training and educational events
    - Event → EventSession → EventEnrollment
    - Support for instructors, venues, discounts, and vouchers
    - Full-text search using PostgreSQL tsvector

3. **Resource System**: Educational content management
    - Resources with content blocks, categories, and tags
    - Publishing workflow with draft/published states
    - View tracking and favorites

4. **Campaign Management**: Marketing campaigns
    - EmailCampaign tied to events
    - CampaignProduct for direct mail services
    - Integration with CARA and Unification APIs

### Module Structure

The codebase uses a hybrid structure:

- Standard Symfony structure for most features
- Module-based organization for complex domains:
    - `src/Module/EmailManagement/`
    - `src/Module/EventRegistration/`
    - `src/Module/CraftMigration/`

Each module contains its own features with dedicated controllers, services, and repositories.

### API Design Patterns

- **Single-action controllers**: Each controller handles one specific action
- **DTO pattern**: Request/Response DTOs for API contracts
- **Service layer**: Business logic separated from controllers
- **Repository pattern**: Database queries isolated in repositories
- **Voters**: Symfony security voters for complex authorization

### External Integrations

1. **Auth0**: JWT authentication and user management
2. **Salesforce**: CRM synchronization via REST API
3. **CARA API**: Legacy system integration
4. **Unification API**: Marketing campaign management
5. **AWS S3**: File storage
6. **Mailchimp Transactional**: Email delivery
7. **Authorize.net**: Payment processing

### Key Conventions

- All entities use UUID traits for external references
- Soft deletes implemented via deletedAt timestamps
- Audit logging for sensitive operations
- PostgreSQL-specific features (tsvector, custom types)
- Timestampable trait for created/updated tracking

### Testing Strategy

- Unit tests for services and utilities
- Integration tests using test database
- Fixtures for consistent test data
- Mock external API calls
- Use `AbstractKernelTestCase` for integration tests
- Use `AbstractWebTestCase` for API endpoint tests

## ERROR HANDLING AND RECOVERY

### Common Error Scenarios and Responses

**1. PHPStan Failures**:
- Run `vendor/bin/phpstan analyse --error-format=json` for detailed output
- Common fixes: Add proper type hints, fix undefined variables, resolve class imports
- If persistent: Check `phpstan.neon` configuration

**2. PHP-CS-Fixer Issues**:
- Always run `vendor/bin/php-cs-fixer fix` to auto-resolve style issues
- If fixes break code: Investigate manually and adjust `.php-cs-fixer.dist.php` rules

**3. Test Failures**:
- Run specific failing test: `vendor/bin/phpunit tests/Path/To/FailingTest.php`
- Check test database state and fixtures
- Verify test environment configuration

**4. Database Connection Issues**:
- Verify Docker containers are running: `docker-compose ps`
- Check database exists: `bin/console doctrine:database:create`
- Run migrations: `bin/console doctrine:migrations:migrate`

**5. Missing Dependencies**:
- Run `composer install` for PHP dependencies
- Check Docker containers: `docker-compose up -d`
- Verify required external services (Auth0, Salesforce credentials)

### Recovery Procedures
- **Always** provide specific commands to resolve issues
- **Never** leave user with vague "something went wrong" messages
- **Offer alternatives** when primary solution fails


## 🚀 AGENTIC PERSONA MAPPING (APM)

---

### 🔴 CRITICAL COMMAND 🔴

**When the user types ANY of these as their first message:**
- `ap`
- `ap_orchestrator`
- `agents`
- `apm`

**→ IMMEDIATELY execute the `/ap_orchestrator` command**

This launches the full AP Orchestrator initialization sequence, including:
- Loading all APM infrastructure from `.apm/` directory
- Initializing all agent personas
- Setting up session management
- Presenting orchestrator capabilities

---

### APM Framework Structure

The Agentic Persona Mapping system provides:
- **AP Orchestrator**: Central coordination and delegation
- **Specialized Agents**: Analyst, PM, Architect, Developer, QA, and more
- **Session Management**: Intelligent context preservation and handoffs
- **Collaborative Workflow**: Seamless transitions between personas

All APM components are located in the `.apm/` directory.

---

**Remember**: `ap` = Full AP Orchestrator activation, not just a simple response!

