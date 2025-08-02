using HeyDav.Domain.TodoManagement.Entities;
using HeyDav.Domain.Goals.Entities;
using HeyDav.Domain.Workflows.Enums;
using HeyDav.Domain.Workflows.ValueObjects;
using HeyDav.Application.Analytics.Models;

namespace HeyDav.Application.Workflows.Models;

// Request Models
public class OptimizeScheduleRequest
{
    public string UserId { get; set; } = string.Empty;
    public DateTime StartDate { get; set; }
    public DateTime EndDate { get; set; }
    public List<SchedulingItem> Tasks { get; set; } = new();
    public List<Goal> Goals { get; set; } = new();
    public SchedulingPreferences Preferences { get; set; } = SchedulingPreferences.Default();
    public bool IncludeBreaks { get; set; } = true;
    public bool AllowOvertime { get; set; } = false;
}

public class TimeSlotRecommendationRequest
{
    public string UserId { get; set; } = string.Empty;
    public TimeSpan Duration { get; set; }
    public DateTime? PreferredDate { get; set; }
    public TimeSpan? PreferredTime { get; set; }
    public TimeSpan? EarliestTime { get; set; }
    public TimeSpan? LatestTime { get; set; }
    public WorkflowStepType TaskType { get; set; }
    public int RequiredEnergyLevel { get; set; } = 5; // 1-10 scale
    public bool AllowWeekends { get; set; } = false;
}

public class SchedulingConflictRequest
{
    public Guid ConflictId { get; set; }
    public string UserId { get; set; } = string.Empty;
    public List<SchedulingItem> ConflictingItems { get; set; } = new();
    public DateTime ConflictDate { get; set; }
    public TimeSpan ConflictStartTime { get; set; }
    public TimeSpan ConflictEndTime { get; set; }
}

public class FocusTimeRequest
{
    public string UserId { get; set; } = string.Empty;
    public DateTime Date { get; set; }
    public TimeSpan? MinimumDuration { get; set; }
    public TimeSpan? MaximumDuration { get; set; }
    public List<WorkflowStepType> TaskTypes { get; set; } = new();
    public bool IncludeBreaks { get; set; } = true;
}

// Response Models
public class ScheduleOptimizationResult
{
    public OptimizedSchedule OptimizedSchedule { get; set; } = new();
    public List<ProductivityInsight> Insights { get; set; } = new();
    public decimal ConfidenceScore { get; set; } // 0-100
    public List<OptimizedSchedule> AlternativeOptions { get; set; } = new();
    public List<string> Warnings { get; set; } = new();
    public DateTime GeneratedAt { get; set; } = DateTime.UtcNow;
}

public class TimeSlotRecommendation
{
    public AvailableTimeSlot? RecommendedSlot { get; set; }
    public List<AvailableTimeSlot> AlternativeSlots { get; set; } = new();
    public string Reasoning { get; set; } = string.Empty;
    public decimal ConfidenceScore { get; set; }
    public List<string> Considerations { get; set; } = new();
}

public class ConflictResolution
{
    public Guid ConflictId { get; set; }
    public List<ConflictResolutionOption> Options { get; set; } = new();
    public ConflictResolutionOption? RecommendedOption { get; set; }
    public bool AutoResolutionPossible { get; set; }
    public string? AutoResolutionReason { get; set; }
}

public class FocusTimeRecommendation
{
    public DateTime Date { get; set; }
    public List<FocusTimeSlot> RecommendedSlots { get; set; } = new();
    public double TotalFocusTime { get; set; } // in minutes
    public List<TimeSpan> OptimalBreakIntervals { get; set; } = new();
    public List<string> EnvironmentRecommendations { get; set; } = new();
    public string? ProductivityTips { get; set; }
}

// Core Models
public class SchedulingItem
{
    public Guid Id { get; set; }
    public string Title { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public TimeSpan Duration { get; set; }
    public int Priority { get; set; } // 1-10
    public DateTime? PreferredDate { get; set; }
    public TimeSpan? PreferredTime { get; set; }
    public int RequiredEnergyLevel { get; set; } = 5; // 1-10
    public bool IsFlexible { get; set; } = true;
    public List<Guid> Dependencies { get; set; } = new();
    public WorkflowStepType Type { get; set; }
    public string? Context { get; set; } // Work, Personal, etc.
}

public class OptimizedSchedule
{
    public List<ScheduledTask> ScheduledTasks { get; set; } = new();
    public List<ScheduledBreak> ScheduledBreaks { get; set; } = new();
    public List<SchedulingConflict> Conflicts { get; set; } = new();
    public decimal OptimizationScore { get; set; } // 0-100
    public TimeSpan TotalScheduledTime { get; set; }
    public TimeSpan TotalFreeTime { get; set; }
    public Dictionary<string, object> Metadata { get; set; } = new();
}

public class ScheduledTask
{
    public Guid TaskId { get; set; }
    public string Title { get; set; } = string.Empty;
    public DateTime StartTime { get; set; }
    public TimeSpan Duration { get; set; }
    public int Priority { get; set; }
    public int PredictedEnergyLevel { get; set; }
    public decimal SchedulingScore { get; set; }
    public string? Notes { get; set; }
    public bool IsFixed { get; set; } // Cannot be moved
}

public class ScheduledBreak
{
    public DateTime StartTime { get; set; }
    public TimeSpan Duration { get; set; }
    public BreakType Type { get; set; }
    public string? Activity { get; set; }
}

public class SchedulingConflict
{
    public Guid Id { get; set; }
    public string Description { get; set; } = string.Empty;
    public List<Guid> ConflictingTaskIds { get; set; } = new();
    public ConflictSeverity Severity { get; set; }
    public DateTime ConflictTime { get; set; }
    public List<string> PossibleResolutions { get; set; } = new();
}

public class ConflictResolutionOption
{
    public ConflictResolutionType Type { get; set; }
    public string Description { get; set; } = string.Empty;
    public decimal Impact { get; set; } // 0-1, where 0 is no impact and 1 is high impact
    public decimal Confidence { get; set; } // 0-1
    public Dictionary<string, object> Parameters { get; set; } = new();
}

public class AvailableTimeSlot
{
    public DateTime Date { get; set; }
    public TimeSpan StartTime { get; set; }
    public TimeSpan Duration { get; set; }
    public int PredictedEnergyLevel { get; set; }
    public decimal Score { get; set; }
    public List<string> Considerations { get; set; } = new();
}

public class FocusTimeSlot
{
    public TimeSpan StartTime { get; set; }
    public TimeSpan Duration { get; set; }
    public int EnergyLevel { get; set; } // 1-10
    public int DistractionLevel { get; set; } // 1-10, where 1 is very low distractions
    public string[] RecommendedTaskTypes { get; set; } = Array.Empty<string>();
}

public class CalendarCommitment
{
    public string Id { get; set; } = string.Empty;
    public string Title { get; set; } = string.Empty;
    public DateTime StartTime { get; set; }
    public DateTime EndTime { get; set; }
    public bool IsFlexible { get; set; }
    public string? Location { get; set; }
    public List<string> Attendees { get; set; } = new();
}

public class SchedulingContext
{
    public string UserId { get; set; } = string.Empty;
    public DateTime StartDate { get; set; }
    public DateTime EndDate { get; set; }
    public List<SchedulingItem> Tasks { get; set; } = new();
    public List<Goal> Goals { get; set; } = new();
    public SchedulingPreferences Preferences { get; set; } = SchedulingPreferences.Default();
    public UserProductivityPatterns Patterns { get; set; } = new();
    public Dictionary<TimeSpan, int> EnergyPredictions { get; set; } = new();
    public List<CalendarCommitment> ExistingCommitments { get; set; } = new();
}

public class ProductivityDataPoint
{
    public DateTime Timestamp { get; set; }
    public ProductivityMetric Metric { get; set; }
    public decimal Value { get; set; }
    public string? Context { get; set; }
    public Dictionary<string, object> AdditionalData { get; set; } = new();
}

// Supporting Models
public class TaskCompletionPattern
{
    public DayOfWeek DayOfWeek { get; set; }
    public decimal CompletionRate { get; set; }
    public TimeSpan AverageCompletionTime { get; set; }
    public int TaskCount { get; set; }
}

// Enums
public enum BreakType
{
    Short,      // 5-15 minutes
    Medium,     // 15-30 minutes  
    Long,       // 30+ minutes
    Lunch,
    Exercise,
    Walk
}

public enum ConflictSeverity
{
    Low,        // Can be easily resolved
    Medium,     // Requires some adjustment
    High,       // Significant impact
    Critical    // Major disruption
}

public enum ConflictResolutionType
{
    Reschedule,
    Split,
    Adjust,
    Delegate,
    Cancel,
    Combine
}

// Extensions for UserProductivityPatterns
public static class UserProductivityPatternsExtensions
{
    public static UserProductivityPatterns Default(string userId)
    {
        return new UserProductivityPatterns
        {
            UserId = userId,
            ExperienceLevel = WorkflowDifficulty.Beginner,
            PreferredCategories = new List<WorkflowCategory> { WorkflowCategory.DailyPlanning },
            PreferredSessionDuration = TimeSpan.FromMinutes(45),
            MostProductiveDays = new List<DayOfWeek> { DayOfWeek.Tuesday, DayOfWeek.Wednesday, DayOfWeek.Thursday },
            MostProductiveHours = new List<TimeSpan> { new TimeSpan(9, 0, 0), new TimeSpan(14, 0, 0) },
            AverageCompletionRate = 75m,
            SchedulingPreferences = SchedulingPreferences.Default()
        };
    }
}

// Additional Supporting Classes
public class UserProductivityPatterns
{
    public string? UserId { get; set; }
    public WorkflowDifficulty ExperienceLevel { get; set; }
    public List<WorkflowCategory> PreferredCategories { get; set; } = new();
    public TimeSpan PreferredSessionDuration { get; set; }
    public List<DayOfWeek> MostProductiveDays { get; set; } = new();
    public List<TimeSpan> MostProductiveHours { get; set; } = new();
    public List<TimeSpan> PeakHours { get; set; } = new();
    public Dictionary<TimeSpan, int> EnergyPatterns { get; set; } = new();
    public List<TaskCompletionPattern> TaskCompletionPatterns { get; set; } = new();
    public decimal AverageCompletionRate { get; set; }
    public int ContextSwitchingFrequency { get; set; }
    public decimal MeetingImpactScore { get; set; }
    public SchedulingPreferences SchedulingPreferences { get; set; } = SchedulingPreferences.Default();
}