using HeyDav.Domain.Common.Base;
using HeyDav.Domain.ToolIntegrations.Enums;

namespace HeyDav.Domain.ToolIntegrations.Entities;

public class SyncExecutionLog : BaseEntity
{
    public Guid SyncConfigurationId { get; private set; }
    public DateTime StartedAt { get; private set; }
    public DateTime? CompletedAt { get; private set; }
    public SyncExecutionStatus Status { get; private set; }
    public bool WasSuccessful { get; private set; }
    public string? ErrorMessage { get; private set; }
    public string? ErrorDetails { get; private set; }
    
    // Execution statistics
    public int RecordsProcessed { get; private set; }
    public int RecordsCreated { get; private set; }
    public int RecordsUpdated { get; private set; }
    public int RecordsDeleted { get; private set; }
    public int RecordsSkipped { get; private set; }
    public int ConflictsDetected { get; private set; }
    public int ConflictsResolved { get; private set; }
    
    // Performance metrics
    public TimeSpan? ExecutionDuration { get; private set; }
    public double? RecordsPerSecond { get; private set; }
    public long? MemoryUsedBytes { get; private set; }
    
    // Detailed logs
    public string? ExecutionLog { get; private set; }
    public string? WarningMessages { get; private set; }
    
    // Navigation properties
    public virtual ToolSyncConfiguration SyncConfiguration { get; private set; } = null!;

    private SyncExecutionLog() { } // EF Core constructor

    public SyncExecutionLog(Guid syncConfigurationId)
    {
        SyncConfigurationId = syncConfigurationId;
        StartedAt = DateTime.UtcNow;
        Status = SyncExecutionStatus.Running;
    }

    public void AddLogEntry(string message, SyncLogLevel level = SyncLogLevel.Info)
    {
        var timestamp = DateTime.UtcNow.ToString("yyyy-MM-dd HH:mm:ss.fff");
        var logEntry = $"[{timestamp}] [{level}] {message}";
        
        if (string.IsNullOrWhiteSpace(ExecutionLog))
        {
            ExecutionLog = logEntry;
        }
        else
        {
            ExecutionLog += "\n" + logEntry;
        }
        
        UpdateTimestamp();
    }

    public void AddWarning(string warning)
    {
        if (string.IsNullOrWhiteSpace(WarningMessages))
        {
            WarningMessages = warning;
        }
        else
        {
            WarningMessages += "\n" + warning;
        }
        
        AddLogEntry(warning, SyncLogLevel.Warning);
    }

    public void UpdateStatistics(
        int recordsProcessed = 0,
        int recordsCreated = 0,
        int recordsUpdated = 0,
        int recordsDeleted = 0,
        int recordsSkipped = 0,
        int conflictsDetected = 0,
        int conflictsResolved = 0)
    {
        RecordsProcessed += recordsProcessed;
        RecordsCreated += recordsCreated;
        RecordsUpdated += recordsUpdated;
        RecordsDeleted += recordsDeleted;
        RecordsSkipped += recordsSkipped;
        ConflictsDetected += conflictsDetected;
        ConflictsResolved += conflictsResolved;
        
        UpdateTimestamp();
    }

    public void Complete(bool wasSuccessful, string? errorMessage = null, string? errorDetails = null)
    {
        CompletedAt = DateTime.UtcNow;
        WasSuccessful = wasSuccessful;
        ErrorMessage = errorMessage;
        ErrorDetails = errorDetails;
        Status = wasSuccessful ? SyncExecutionStatus.Completed : SyncExecutionStatus.Failed;
        
        // Calculate execution duration
        ExecutionDuration = CompletedAt.Value - StartedAt;
        
        // Calculate performance metrics
        if (ExecutionDuration.HasValue && ExecutionDuration.Value.TotalSeconds > 0)
        {
            RecordsPerSecond = RecordsProcessed / ExecutionDuration.Value.TotalSeconds;
        }
        
        // Add completion log entry
        if (wasSuccessful)
        {
            AddLogEntry($"Sync completed successfully. Processed: {RecordsProcessed}, Created: {RecordsCreated}, Updated: {RecordsUpdated}, Deleted: {RecordsDeleted}, Duration: {ExecutionDuration?.TotalSeconds:F2}s");
        }
        else
        {
            AddLogEntry($"Sync failed: {errorMessage}", SyncLogLevel.Error);
        }
        
        UpdateTimestamp();
    }

    public void Cancel(string reason)
    {
        CompletedAt = DateTime.UtcNow;
        WasSuccessful = false;
        ErrorMessage = $"Sync cancelled: {reason}";
        Status = SyncExecutionStatus.Cancelled;
        ExecutionDuration = CompletedAt.Value - StartedAt;
        
        AddLogEntry($"Sync cancelled: {reason}", SyncLogLevel.Warning);
        UpdateTimestamp();
    }

    public void UpdateMemoryUsage(long memoryUsedBytes)
    {
        MemoryUsedBytes = memoryUsedBytes;
        UpdateTimestamp();
    }

    public double GetProcessingRate()
    {
        return RecordsPerSecond ?? 0.0;
    }

    public bool IsRunning()
    {
        return Status == SyncExecutionStatus.Running;
    }

    public bool HasWarnings()
    {
        return !string.IsNullOrWhiteSpace(WarningMessages);
    }

    public bool HasConflicts()
    {
        return ConflictsDetected > 0;
    }

    public double GetConflictResolutionRate()
    {
        if (ConflictsDetected == 0) return 1.0;
        return (double)ConflictsResolved / ConflictsDetected;
    }
}