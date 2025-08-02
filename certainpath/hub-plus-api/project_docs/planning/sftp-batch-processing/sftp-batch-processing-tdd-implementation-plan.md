# TDD Implementation Plan: SFTP Batch Processing

## Test-Driven Development Strategy

This document outlines the comprehensive Test-Driven Development approach for implementing the SFTP Batch Processing enhancement, leveraging the existing test files in `tests/Files/usps/sftp/`.

## TDD Philosophy & Approach

### Core TDD Principles
1. **Red → Green → Refactor**: Write failing test first, implement minimal code to pass, then refactor
2. **Test First**: Never write production code without a failing test
3. **Small Steps**: Incremental development with frequent test runs
4. **Comprehensive Coverage**: Every line of production code tested

### Unit Test Conventions (CRITICAL)
**Assertion Method Usage:**
- ✅ **CORRECT**: Use `self::assertSame()`, `self::assertEquals()`, `self::assertTrue()` for stateless operations
- ❌ **INCORRECT**: `$this->assertSame()`, `$this->assertEquals()`, `$this->assertTrue()`

**Exception Handling:**
- ✅ **CORRECT**: Use `$this->expectException()` for stateful exception testing
- ❌ **INCORRECT**: `self::expectException()`

**Example:**
```php
// Correct usage
self::assertSame('expected', $actual);
self::assertEquals(42, $result);
self::assertTrue($condition);

// Exception testing - use $this for stateful operations
$this->expectException(\InvalidArgumentException::class);
$service->methodThatThrows();
```

### Test File Assets
- **Location**: `tests/Files/usps/sftp/`
- **Count**: 38 USPS postage files
- **Format**: Real SFTP file structure with authentic data
- **Coverage**: Various scenarios, dates, record counts
- **Purpose**: Perfect foundation for realistic TDD testing

## Implementation Phases

### Phase 1: Test Infrastructure Setup

#### 1.1 Base Test Classes
**Duration**: 1-2 days  
**TDD Cycle**: Create test infrastructure

```php
// tests/Service/SftpBatchPostageProcessorServiceTest.php
abstract class SftpBatchProcessorTestCase extends AbstractKernelTestCase
{
    protected SftpBatchPostageProcessorService $processor;
    protected string $testFilesPath;
    protected PostageProcessedFileRepository $processedFileRepository;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->testFilesPath = __DIR__ . '/../Files/usps/sftp';
        // Setup test dependencies
    }
    
    protected function getTestFile(string $filename): string
    {
        return $this->testFilesPath . '/' . $filename;
    }
    
    protected function createTestDirectory(array $files): string
    {
        // Create temporary directory with specific files
    }
    
    protected function assertFileProcessed(string $filename, string $expectedStatus = 'SUCCESS'): void
    {
        // Verify file marked as processed in database
    }
}
```

**Tests to Write First**:
```php
public function testCanInstantiateService(): void
{
    self::assertInstanceOf(SftpBatchPostageProcessorService::class, $this->processor);
}

public function testTestFilesExist(): void
{
    self::assertDirectoryExists($this->testFilesPath);
    $files = glob($this->testFilesPath . '/*.txt');
    self::assertGreaterThan(30, count($files)); // Verify test files present
}
```

#### 1.2 Entity Test Foundation
```php
// tests/Entity/PostageProcessedFileTest.php
class PostageProcessedFileTest extends AbstractKernelTestCase
{
    public function testEntityCreation(): void
    {
        $file = new PostageProcessedFile();
        self::assertNull($file->getId()); // Before persistence
        self::assertNotNull($file->getUuid()); // UUID trait
    }
    
    public function testSettersAndGetters(): void
    {
        // Test all entity properties
    }
    
    public function testTimestampableTraits(): void
    {
        // Test created/updated timestamps
    }
}
```

### Phase 2: Core Entity TDD (PostageProcessedFile)

#### 2.1 Red Phase: Entity Tests
**Duration**: 1 day

**Write failing tests first**:
```php
// tests/Entity/PostageProcessedFileTest.php
public function testRequiredFields(): void
{
    $this->expectException(\TypeError::class);
    new PostageProcessedFile(); // Should fail - required fields missing
}

public function testFileHashValidation(): void
{
    $file = new PostageProcessedFile();
    $file->setFileHash('invalid-hash');
    
    // Should validate MD5 format (32 characters)
    $this->expectException(\InvalidArgumentException::class);
    $file->validate();
}

public function testStatusEnum(): void
{
    $file = new PostageProcessedFile();
    
    $this->expectException(\InvalidArgumentException::class);
    $file->setStatus('INVALID_STATUS'); // Should only accept SUCCESS, FAILED, PARTIAL
}
```

#### 2.2 Green Phase: Implement Entity
Create minimal `PostageProcessedFile` entity to pass tests:
```php
// src/Entity/PostageProcessedFile.php
class PostageProcessedFile
{
    // Minimal implementation to pass tests
    private const VALID_STATUSES = ['SUCCESS', 'FAILED', 'PARTIAL'];
    
    public function setStatus(string $status): self
    {
        if (!in_array($status, self::VALID_STATUSES)) {
            throw new \InvalidArgumentException("Invalid status: $status");
        }
        $this->status = $status;
        return $this;
    }
}
```

#### 2.3 Refactor Phase: Optimize Entity
- Add proper validation
- Enhance documentation  
- Optimize property types

### Phase 3: Repository TDD

#### 3.1 Red Phase: Repository Tests
```php
// tests/Repository/PostageProcessedFileRepositoryTest.php
class PostageProcessedFileRepositoryTest extends AbstractKernelTestCase
{
    public function testFindByFilenameAndHash(): void
    {
        $repo = $this->getRepository(PostageProcessedFileRepository::class);
        
        // Should return null for non-existent file
        $result = $repo->findByFilenameAndHash('nonexistent.txt', 'fake-hash');
        $this->assertNull($result);
    }
    
    public function testIsFileProcessed(): void
    {
        $repo = $this->getRepository(PostageProcessedFileRepository::class);
        
        // Should return false for unprocessed file
        $this->assertFalse($repo->isFileProcessed('test.txt', 'hash123'));
    }
    
    public function testGetProcessingStatistics(): void
    {
        $repo = $this->getRepository(PostageProcessedFileRepository::class);
        
        $stats = $repo->getProcessingStatistics();
        $this->assertArrayHasKey('total_files', $stats);
        $this->assertArrayHasKey('success_count', $stats);
        $this->assertArrayHasKey('failed_count', $stats);
    }
}
```

#### 3.2 Green & Refactor: Implement Repository
```php
// src/Repository/PostageProcessedFileRepository.php
class PostageProcessedFileRepository extends ServiceEntityRepository
{
    public function findByFilenameAndHash(string $filename, string $hash): ?PostageProcessedFile
    {
        return $this->findOneBy(['filename' => $filename, 'fileHash' => $hash]);
    }
    
    public function isFileProcessed(string $filename, string $hash): bool
    {
        return $this->findByFilenameAndHash($filename, $hash) !== null;
    }
    
    public function getProcessingStatistics(): array
    {
        // Implementation to pass tests
    }
}
```

### Phase 4: Core Service TDD (SftpBatchPostageProcessorService)

#### 4.1 Red Phase: Service Foundation Tests
```php
// tests/Service/SftpBatchPostageProcessorServiceTest.php
public function testProcessEmptyDirectory(): void
{
    $emptyDir = $this->createTempDirectory([]);
    
    $summary = $this->processor->processDirectory($emptyDir);
    
    self::assertEquals(0, $summary->totalFiles);
    self::assertEquals(0, $summary->processedFiles);
    self::assertTrue($summary->isSuccessful());
}

public function testProcessDirectoryWithNonExistentPath(): void
{
    $this->expectException(\InvalidArgumentException::class);
    $this->processor->processDirectory('/nonexistent/path');
}

public function testProcessSingleValidFile(): void
{
    $testFile = $this->getTestFile('20250523200000_46003456_EPS_CM_HR_IVZS.txt');
    
    $result = $this->processor->processFile($testFile);
    
    self::assertTrue($result);
    $this->assertFileProcessed('20250523200000_46003456_EPS_CM_HR_IVZS.txt');
}
```

#### 4.2 Green Phase: Basic Implementation
```php
// src/Service/SftpBatchPostageProcessorService.php
readonly class SftpBatchPostageProcessorService
{
    public function processDirectory(string $directoryPath): ProcessingSummary
    {
        if (!is_dir($directoryPath)) {
            throw new \InvalidArgumentException("Directory does not exist: $directoryPath");
        }
        
        $files = glob($directoryPath . '/*.txt');
        
        if (empty($files)) {
            return new ProcessingSummary(0, 0, 0, 0, 0);
        }
        
        // Minimal implementation to pass initial tests
    }
}
```

#### 4.3 Iterative TDD for Each Feature

**File Processing Logic**:
```php
// Red: Write failing test
public function testProcessFileCallsUploadService(): void
{
    $mockUploadService = $this->createMock(UploadPostageExpenseService::class);
    $mockUploadService->expects($this->once())
                     ->method('handleWithDirectFilePath')
                     ->with($this->equalTo('/path/to/test.txt'));
    
    $processor = new SftpBatchPostageProcessorService($mockUploadService, ...);
    $processor->processFile('/path/to/test.txt');
}

// Green: Implement to pass
public function processFile(string $filePath, bool $skipIfProcessed = true): bool
{
    // Calculate hash, check if processed, call upload service
    $this->uploadService->handleWithDirectFilePath($filePath);
    return true;
}
```

**Duplicate Prevention**:
```php
// Red: Write failing test  
public function testSkipAlreadyProcessedFile(): void
{
    $testFile = $this->getTestFile('20250523200000_46003456_EPS_CM_HR_IVZS.txt');
    
    // Process file first time
    $this->processor->processFile($testFile);
    
    // Second time should skip
    $mockUploadService = $this->createMock(UploadPostageExpenseService::class);
    $mockUploadService->expects($this->never())->method('handleWithDirectFilePath');
    
    $result = $this->processor->processFile($testFile, true);
    $this->assertFalse($result); // Should return false for skipped
}

// Green: Implement deduplication logic
// Refactor: Optimize hash calculation and storage
```

### Phase 5: Integration Testing with Real SFTP Files

#### 5.1 End-to-End Processing Tests
```php
public function testProcessAllSftpTestFiles(): void
{
    $testDir = $this->testFilesPath;
    
    $summary = $this->processor->processDirectory($testDir);
    
    // Verify all 38 files processed
    self::assertEquals(38, $summary->totalFiles);
    self::assertEquals(38, $summary->processedFiles);
    self::assertEquals(0, $summary->failedFiles);
    self::assertTrue($summary->isSuccessful());
    
    // Verify records created in database
    $batchPostageRepo = $this->getRepository(BatchPostageRepository::class);
    $totalRecords = $batchPostageRepo->count([]);
    self::assertGreaterThan(500, $totalRecords); // Expected record count from files
}
```

#### 5.2 Column Mapping Integration Tests
```php
public function testSftpColumnMappingWorksCorrectly(): void
{
    // Test that "Number of Pieces" maps to quantity_sent
    // Test that "Transaction Amount" maps to cost
    // Test that "Job ID" maps to reference
    
    $testFile = $this->getTestFile('20250523200000_46003456_EPS_CM_HR_IVZS.txt');
    $this->processor->processFile($testFile);
    
    // Verify specific records match expected data from file
    $batchPostage = $this->batchPostageRepository->findOneByReference('00522180');
    self::assertNotNull($batchPostage);
    self::assertEquals(2030, $batchPostage->getQuantitySent());
    self::assertEquals('529.89', $batchPostage->getCost());
}
```

### Phase 6: Enhanced BatchPostageRecordMap TDD

#### 6.1 Red Phase: Column Mapping Tests
```php
// tests/ValueObject/BatchPostageRecordMapTest.php
public function testSftpColumnMapping(): void
{
    $map = new BatchPostageRecordMap();
    
    $testHeaders = ['Job ID', 'Number of Pieces', 'Transaction Amount'];
    
    self::assertTrue($map->canMapColumn('Job ID', 'reference'));
    self::assertTrue($map->canMapColumn('Number of Pieces', 'quantity_sent'));
    self::assertTrue($map->canMapColumn('Transaction Amount', 'cost'));
}

public function testBackwardCompatibility(): void
{
    $map = new BatchPostageRecordMap();
    
    // Old headers should still work
    self::assertTrue($map->canMapColumn('Pieces', 'quantity_sent'));
    self::assertTrue($map->canMapColumn('Amount', 'cost'));
    self::assertTrue($map->canMapColumn('jobid', 'reference'));
}
```

#### 6.2 Green & Refactor: Enhance Mapping
Update column mappings to pass tests while maintaining backward compatibility.

### Phase 7: Console Command TDD

#### 7.1 Command Interface Tests
```php
// tests/Command/ProcessSftpPostageFilesCommandTest.php
public function testCommandExecution(): void
{
    $command = $this->application->find('hub:postage:process-sftp-directory');
    $commandTester = new CommandTester($command);
    
    $commandTester->execute([
        'directory' => $this->testFilesPath
    ]);
    
    self::assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
    self::assertStringContainsString('Successfully processed', $commandTester->getDisplay());
}
```

### Phase 8: Error Handling & Edge Cases TDD

#### 8.1 Error Scenarios
```php
public function testProcessMalformedFile(): void
{
    $malformedFile = $this->createTempFile('invalid,content,format');
    
    $result = $this->processor->processFile($malformedFile, false);
    
    self::assertFalse($result);
    $this->assertFileMarkedAsFailed($malformedFile);
}

public function testProcessFileWithMissingColumns(): void
{
    $incompleteFile = $this->createTempFileWithHeaders(['Job ID', 'Amount']); // Missing "Number of Pieces"
    
    $this->expectException(FieldsAreMissing::class);
    $this->processor->processFile($incompleteFile);
}

public function testDatabaseTransactionRollback(): void
{
    // Test that failed processing rolls back database changes
}
```

## Test Execution Strategy

### Continuous Testing
- **Pre-commit Hook**: Run all tests before commits
- **CI/CD Pipeline**: Automated testing on push
- **Coverage Requirements**: Minimum 95% coverage for new code

### Test Categories
1. **Unit Tests**: Individual methods and classes
2. **Integration Tests**: Service interactions
3. **Functional Tests**: End-to-end workflows
4. **Performance Tests**: Processing speed benchmarks

### Test Data Management
- **Real SFTP Files**: Use actual test files for realistic scenarios
- **Synthetic Data**: Generate edge cases and error conditions
- **Database Fixtures**: Consistent test data setup
- **Cleanup**: Automatic test data cleanup between runs

## Success Metrics

### Test Coverage Goals
- **Line Coverage**: 100% for new service classes
- **Branch Coverage**: 95% for all conditional logic
- **Method Coverage**: 100% for public methods

### Quality Gates
- All tests must pass before merging
- PHPStan level 8 compliance
- No code duplication above 5%
- Performance tests must meet benchmarks

### TDD Benefits Tracking
- **Defect Rate**: Track bugs found in production vs. development
- **Development Speed**: Time to implement features
- **Code Quality**: Maintainability metrics
- **Refactoring Safety**: Confident code changes with test coverage

## Implementation Timeline

| Phase | Duration | Deliverables |
|-------|----------|--------------|
| 1: Test Infrastructure | 1-2 days | Base test classes, test utilities |
| 2: Entity TDD | 1 day | PostageProcessedFile entity + tests |
| 3: Repository TDD | 1 day | Repository methods + tests |
| 4: Core Service TDD | 2-3 days | SftpBatchPostageProcessorService + tests |
| 5: Integration Testing | 1-2 days | End-to-end tests with real files |
| 6: Column Mapping TDD | 1 day | Enhanced BatchPostageRecordMap |
| 7: Console Command TDD | 1 day | CLI interface + tests |
| 8: Error Handling TDD | 1-2 days | Edge cases and error scenarios |

**Total Estimated Duration**: 8-12 days

## Handoff to Development

### Developer Onboarding
1. **TDD Training**: Ensure developer understands TDD methodology
2. **Test File Orientation**: Walk through existing SFTP test files
3. **Architecture Review**: Discuss design decisions and constraints
4. **Tool Setup**: Configure testing environment and CI/CD

### Development Process
1. **Start with Tests**: Always write failing tests first
2. **Incremental Development**: Small commits with test + implementation
3. **Regular Integration**: Frequent pushes to catch integration issues
4. **Code Review**: Focus on test quality and coverage

### Quality Assurance
- **Test Review**: QA reviews test scenarios for completeness
- **Manual Testing**: Supplement automated tests with manual verification  
- **Performance Validation**: Verify processing speed requirements
- **User Acceptance**: Stakeholder validation of functionality

This comprehensive TDD approach ensures robust, well-tested code that meets all requirements while providing excellent regression protection for future enhancements.