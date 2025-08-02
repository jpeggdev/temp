using HeyDav.Domain.Common.Base;
using HeyDav.Domain.ToolIntegrations.Enums;
using HeyDav.Domain.ToolIntegrations.ValueObjects;

namespace HeyDav.Domain.ToolIntegrations.Entities;

public class ToolSyncConfiguration : BaseEntity
{
    public Guid ToolConnectionId { get; private set; }
    public string Name { get; private set; } = string.Empty;
    public string Description { get; private set; } = string.Empty;
    public SyncDirection Direction { get; private set; }
    public SyncFrequency Frequency { get; private set; }
    public bool IsEnabled { get; private set; } = true;
    public string EntityType { get; private set; } = string.Empty; // TodoItem, Goal, etc.
    public string RemoteEntityType { get; private set; } = string.Empty; // Issue, Task, etc.
    
    // Sync settings
    public SyncSettings Settings { get; private set; } = new();
    public FieldMappingConfiguration FieldMappings { get; private set; } = new();
    public ConflictResolutionStrategy ConflictResolution { get; private set; }
    
    // Scheduling
    public DateTime? NextSyncAt { get; private set; }
    public DateTime? LastSyncAt { get; private set; }
    public DateTime? LastSuccessfulSyncAt { get; private set; }
    public string? LastSyncError { get; private set; }
    public int ConsecutiveFailures { get; private set; }
    
    // Statistics
    public int TotalSyncAttempts { get; private set; }
    public int SuccessfulSyncs { get; private set; }
    public int RecordsCreated { get; private set; }
    public int RecordsUpdated { get; private set; }
    public int RecordsDeleted { get; private set; }
    public int ConflictsResolved { get; private set; }
    
    // Navigation properties
    public virtual ToolConnection ToolConnection { get; private set; } = null!;
    public virtual ICollection<SyncExecutionLog> ExecutionLogs { get; private set; } = new List<SyncExecutionLog>();

    private ToolSyncConfiguration() { } // EF Core constructor

    public ToolSyncConfiguration(
        Guid toolConnectionId,
        string name,
        string description,
        SyncDirection direction,
        SyncFrequency frequency,
        string entityType,
        string remoteEntityType,
        ConflictResolutionStrategy conflictResolution = ConflictResolutionStrategy.LocalWins)
    {
        ToolConnectionId = toolConnectionId;
        Name = name ?? throw new ArgumentNullException(nameof(name));
        Description = description ?? throw new ArgumentNullException(nameof(description));
        Direction = direction;
        Frequency = frequency;
        EntityType = entityType ?? throw new ArgumentNullException(nameof(entityType));
        RemoteEntityType = remoteEntityType ?? throw new ArgumentNullException(nameof(remoteEntityType));
        ConflictResolution = conflictResolution;
        
        ScheduleNextSync();
    }

    public void UpdateSettings(SyncSettings settings)
    {
        Settings = settings ?? throw new ArgumentNullException(nameof(settings));
        UpdateTimestamp();
    }

    public void UpdateFieldMappings(FieldMappingConfiguration fieldMappings)
    {
        FieldMappings = fieldMappings ?? throw new ArgumentNullException(nameof(fieldMappings));
        UpdateTimestamp();
    }

    public void UpdateFrequency(SyncFrequency frequency)
    {
        Frequency = frequency;
        ScheduleNextSync();
        UpdateTimestamp();
    }

    public void UpdateConflictResolution(ConflictResolutionStrategy strategy)
    {
        ConflictResolution = strategy;
        UpdateTimestamp();
    }

    public void Enable()
    {
        IsEnabled = true;
        ScheduleNextSync();
        UpdateTimestamp();
    }

    public void Disable()
    {
        IsEnabled = false;
        NextSyncAt = null;
        UpdateTimestamp();
    }

    public void RecordSyncAttempt(bool wasSuccessful, string? errorMessage = null, 
        int recordsCreated = 0, int recordsUpdated = 0, int recordsDeleted = 0, int conflictsResolved = 0)
    {
        TotalSyncAttempts++;
        LastSyncAt = DateTime.UtcNow;
        
        if (wasSuccessful)
        {
            SuccessfulSyncs++;
            LastSuccessfulSyncAt = DateTime.UtcNow;
            ConsecutiveFailures = 0;
            LastSyncError = null;
            
            RecordsCreated += recordsCreated;
            RecordsUpdated += recordsUpdated;
            RecordsDeleted += recordsDeleted;
            ConflictsResolved += conflictsResolved;
        }
        else
        {
            ConsecutiveFailures++;
            LastSyncError = errorMessage;
        }
        
        ScheduleNextSync();
        UpdateTimestamp();
    }

    public void ScheduleNextSync()
    {
        if (!IsEnabled)
        {
            NextSyncAt = null;
            return;
        }

        var baseTime = DateTime.UtcNow;
        
        // Add exponential backoff for consecutive failures
        if (ConsecutiveFailures > 0)
        {
            var backoffMinutes = Math.Min(Math.Pow(2, ConsecutiveFailures), 60); // Max 1 hour backoff
            baseTime = baseTime.AddMinutes(backoffMinutes);
        }

        NextSyncAt = Frequency switch
        {
            SyncFrequency.Realtime => baseTime.AddMinutes(1), // Minimum 1 minute for realtime
            SyncFrequency.EveryMinute => baseTime.AddMinutes(1),
            SyncFrequency.Every5Minutes => baseTime.AddMinutes(5),
            SyncFrequency.Every15Minutes => baseTime.AddMinutes(15),
            SyncFrequency.Every30Minutes => baseTime.AddMinutes(30),
            SyncFrequency.Hourly => baseTime.AddHours(1),
            SyncFrequency.Every6Hours => baseTime.AddHours(6),
            SyncFrequency.Daily => baseTime.AddDays(1),
            SyncFrequency.Weekly => baseTime.AddDays(7),
            SyncFrequency.Manual => null,
            _ => baseTime.AddHours(1)
        };
    }

    public bool IsReadyForSync()
    {
        return IsEnabled && 
               NextSyncAt.HasValue && 
               NextSyncAt.Value <= DateTime.UtcNow &&
               ConsecutiveFailures < 5; // Stop trying after 5 consecutive failures
    }

    public double GetSuccessRate()
    {
        if (TotalSyncAttempts == 0) return 1.0;
        return (double)SuccessfulSyncs / TotalSyncAttempts;
    }

    public bool IsHealthy()
    {
        return IsEnabled && 
               ConsecutiveFailures < 3 && 
               GetSuccessRate() > 0.8 &&
               (LastSuccessfulSyncAt == null || LastSuccessfulSyncAt.Value > DateTime.UtcNow.AddDays(-7));
    }

    public TimeSpan? GetTimeSinceLastSync()
    {
        return LastSyncAt.HasValue ? DateTime.UtcNow - LastSyncAt.Value : null;
    }

    public TimeSpan? GetTimeUntilNextSync()
    {
        return NextSyncAt.HasValue ? NextSyncAt.Value - DateTime.UtcNow : null;
    }
}