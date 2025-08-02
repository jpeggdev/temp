using HeyDav.Domain.Analytics.Enums;
using HeyDav.Domain.Analytics.Entities;

namespace HeyDav.Application.Analytics.Models;

public class TimeEntryUpdateRequest
{
    public DateTime? StartTime { get; set; }
    public DateTime? EndTime { get; set; }
    public string? Activity { get; set; }
    public string? Project { get; set; }
    public string? Category { get; set; }
    public string? Description { get; set; }
    public List<string>? Tags { get; set; }
    public bool? IsBillable { get; set; }
    public decimal? HourlyRate { get; set; }
}

public class ActivityDetectionResult
{
    public DateTime StartTime { get; set; }
    public DateTime EndTime { get; set; }
    public string Activity { get; set; } = string.Empty;
    public string? Application { get; set; }
    public string? WindowTitle { get; set; }
    public string? Category { get; set; }
    public decimal Confidence { get; set; }
    public TimeTrackingSource Source { get; set; }
    public Dictionary<string, object> Metadata { get; set; } = new();
}

public class ActivityData
{
    public DateTime Timestamp { get; set; }
    public string ActivityType { get; set; } = string.Empty;
    public string? ApplicationName { get; set; }
    public string? WindowTitle { get; set; }
    public string? Url { get; set; }
    public string? FilePath { get; set; }
    public Dictionary<string, object> AdditionalData { get; set; } = new();
}

public class AutomaticTrackingSettings
{
    public bool TrackApplications { get; set; } = true;
    public bool TrackWebsites { get; set; } = true;
    public bool TrackFileSystem { get; set; } = false;
    public bool TrackCalendarEvents { get; set; } = true;
    public TimeSpan MinimumTrackingDuration { get; set; } = TimeSpan.FromMinutes(1);
    public TimeSpan IdleTimeThreshold { get; set; } = TimeSpan.FromMinutes(5);
    public List<string> IgnoredApplications { get; set; } = new();
    public List<string> IgnoredWebsites { get; set; } = new();
    public List<CategoryMapping> CategoryMappings { get; set; } = new();
    public bool AutoCategorize { get; set; } = true;
    public bool MergeShortActivities { get; set; } = true;
}

public class CategoryMapping
{
    public string Pattern { get; set; } = string.Empty; // Regex pattern
    public string Category { get; set; } = string.Empty;
    public int Priority { get; set; } = 0;
}

public class TimeAllocationReport
{
    public string UserId { get; set; } = string.Empty;
    public DateTime FromDate { get; set; }
    public DateTime ToDate { get; set; }
    public TimeSpan TotalTrackedTime { get; set; }
    public Dictionary<string, TimeSpan> TimeByCategory { get; set; } = new();
    public Dictionary<string, TimeSpan> TimeByProject { get; set; } = new();
    public Dictionary<DayOfWeek, TimeSpan> TimeByDayOfWeek { get; set; } = new();
    public Dictionary<int, TimeSpan> TimeByHour { get; set; } = new(); // Hour of day -> Time
    public List<DailyTimeBreakdown> DailyBreakdowns { get; set; } = new();
    public decimal ProductiveTimePercentage { get; set; }
    public DateTime GeneratedAt { get; set; }
}

public class DailyTimeBreakdown
{
    public DateTime Date { get; set; }
    public TimeSpan TotalTime { get; set; }
    public TimeSpan ProductiveTime { get; set; }
    public Dictionary<string, TimeSpan> CategoryTime { get; set; } = new();
    public int NumberOfSessions { get; set; }
    public TimeSpan AverageSessionDuration { get; set; }
}

public class ProjectTimeReport
{
    public string UserId { get; set; } = string.Empty;
    public DateTime FromDate { get; set; }
    public DateTime ToDate { get; set; }
    public List<ProjectTimeData> Projects { get; set; } = new();
    public TimeSpan TotalProjectTime { get; set; }
    public string MostTimeConsuming { get; set; } = string.Empty;
    public decimal AverageHoursPerProject { get; set; }
    public DateTime GeneratedAt { get; set; }
}

public class ProjectTimeData
{
    public string Project { get; set; } = string.Empty;
    public TimeSpan TotalTime { get; set; }
    public decimal Percentage { get; set; }
    public int SessionCount { get; set; }
    public TimeSpan AverageSessionDuration { get; set; }
    public List<string> TopActivities { get; set; } = new();
    public Dictionary<DateTime, TimeSpan> DailyTime { get; set; } = new();
}

public class CategoryTimeReport
{
    public string UserId { get; set; } = string.Empty;
    public DateTime FromDate { get; set; }
    public DateTime ToDate { get; set; }
    public List<CategoryTimeData> Categories { get; set; } = new();
    public TimeSpan TotalCategorizedTime { get; set; }
    public string MostTimeConsuming { get; set; } = string.Empty;
    public decimal ProductivityScore { get; set; }
    public DateTime GeneratedAt { get; set; }
}

public class CategoryTimeData
{
    public string Category { get; set; } = string.Empty;
    public TimeSpan TotalTime { get; set; }
    public decimal Percentage { get; set; }
    public int SessionCount { get; set; }
    public TimeSpan AverageSessionDuration { get; set; }
    public List<string> TopActivities { get; set; } = new();
    public decimal ProductivityRating { get; set; } // 1-10 scale
    public List<TimeSpan> PeakHours { get; set; } = new();
}

public class ProductivityTimeReport
{
    public string UserId { get; set; } = string.Empty;
    public DateTime FromDate { get; set; }
    public DateTime ToDate { get; set; }
    public TimeSpan TotalWorkTime { get; set; }
    public TimeSpan ProductiveTime { get; set; }
    public TimeSpan NeutralTime { get; set; }
    public TimeSpan DistractingTime { get; set; }
    public decimal ProductivityScore { get; set; }
    public Dictionary<TimeSpan, decimal> HourlyProductivityScores { get; set; } = new();
    public List<ProductivityPeak> ProductivityPeaks { get; set; } = new();
    public List<ProductivityDip> ProductivityDips { get; set; } = new();
    public List<string> Insights { get; set; } = new();
    public DateTime GeneratedAt { get; set; }
}

public class ProductivityPeak
{
    public TimeSpan StartTime { get; set; }
    public TimeSpan EndTime { get; set; }
    public decimal AverageScore { get; set; }
    public string PrimaryActivity { get; set; } = string.Empty;
    public int Frequency { get; set; } // How often this peak occurs
}

public class ProductivityDip
{
    public TimeSpan StartTime { get; set; }
    public TimeSpan EndTime { get; set; }
    public decimal AverageScore { get; set; }
    public string PrimaryActivity { get; set; } = string.Empty;
    public List<string> PossibleCauses { get; set; } = new();
}

public class BillableTimeReport
{
    public string UserId { get; set; } = string.Empty;
    public DateTime FromDate { get; set; }
    public DateTime ToDate { get; set; }
    public TimeSpan TotalBillableTime { get; set; }
    public TimeSpan TotalNonBillableTime { get; set; }
    public decimal TotalBillableAmount { get; set; }
    public decimal AverageHourlyRate { get; set; }
    public List<BillableProjectData> Projects { get; set; } = new();
    public Dictionary<DateTime, decimal> DailyEarnings { get; set; } = new();
    public DateTime GeneratedAt { get; set; }
}

public class BillableProjectData
{
    public string Project { get; set; } = string.Empty;
    public TimeSpan BillableTime { get; set; }
    public decimal HourlyRate { get; set; }
    public decimal TotalAmount { get; set; }
    public int SessionCount { get; set; }
    public List<BillableTimeEntry> TimeEntries { get; set; } = new();
}

public class BillableTimeEntry
{
    public Guid Id { get; set; }
    public DateTime StartTime { get; set; }
    public DateTime EndTime { get; set; }
    public string Activity { get; set; } = string.Empty;
    public TimeSpan Duration { get; set; }
    public decimal HourlyRate { get; set; }
    public decimal Amount { get; set; }
}

public class TimeEstimationInsight
{
    public string UserId { get; set; } = string.Empty;
    public string Activity { get; set; } = string.Empty;
    public TimeSpan EstimatedTime { get; set; }
    public TimeSpan AverageActualTime { get; set; }
    public decimal AccuracyScore { get; set; } // 0-100
    public TrendDirection Trend { get; set; }
    public List<string> ImprovementTips { get; set; } = new();
    public List<HistoricalTimeData> History { get; set; } = new();
    public DateTime GeneratedAt { get; set; }
}

public class HistoricalTimeData
{
    public DateTime Date { get; set; }
    public TimeSpan EstimatedTime { get; set; }
    public TimeSpan ActualTime { get; set; }
    public decimal Accuracy { get; set; }
}

public class TimeImprovementSuggestion
{
    public string Title { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public string Category { get; set; } = string.Empty;
    public decimal PotentialTimeSavings { get; set; } // Hours per week
    public decimal ImplementationDifficulty { get; set; } // 1-10 scale
    public List<string> ActionSteps { get; set; } = new();
    public TimeSpan ExpectedResults { get; set; }
    public Dictionary<string, object> SupportingData { get; set; } = new();
}

public class FocusSession
{
    public Guid Id { get; set; }
    public string UserId { get; set; } = string.Empty;
    public DateTime StartTime { get; set; }
    public DateTime? EndTime { get; set; }
    public TimeSpan PlannedDuration { get; set; }
    public TimeSpan? ActualDuration => EndTime?.Subtract(StartTime);
    public string Activity { get; set; } = string.Empty;
    public string? Goal { get; set; }
    public int? FocusScore { get; set; } // 1-10 scale
    public int InterruptionCount { get; set; }
    public List<Interruption> Interruptions { get; set; } = new();
    public bool CompletedSuccessfully { get; set; }
    public string? Notes { get; set; }
    public Dictionary<string, object> Metadata { get; set; } = new();
}

public class Interruption
{
    public DateTime Timestamp { get; set; }
    public string Type { get; set; } = string.Empty; // "Internal", "External", "System"
    public string Source { get; set; } = string.Empty;
    public TimeSpan Duration { get; set; }
    public string? Description { get; set; }
}

public class FocusSessionRequest
{
    public string Activity { get; set; } = string.Empty;
    public TimeSpan PlannedDuration { get; set; }
    public string? Goal { get; set; }
    public bool EnableBreakReminders { get; set; } = true;
    public TimeSpan BreakInterval { get; set; } = TimeSpan.FromMinutes(25); // Pomodoro default
    public List<string> Tags { get; set; } = new();
}

public class FocusSessionUpdate
{
    public int? FocusScore { get; set; }
    public string? Notes { get; set; }
    public List<Interruption>? Interruptions { get; set; }
}

public class FocusSessionCompletion
{
    public int FocusScore { get; set; }
    public bool CompletedSuccessfully { get; set; }
    public string? Notes { get; set; }
    public List<Interruption> Interruptions { get; set; } = new();
}

public class FocusMetrics
{
    public string UserId { get; set; } = string.Empty;
    public DateTime FromDate { get; set; }
    public DateTime ToDate { get; set; }
    public int TotalFocusSessions { get; set; }
    public int CompletedSessions { get; set; }
    public decimal CompletionRate { get; set; }
    public TimeSpan TotalFocusTime { get; set; }
    public TimeSpan AverageSessionDuration { get; set; }
    public decimal AverageFocusScore { get; set; }
    public int TotalInterruptions { get; set; }
    public decimal AverageInterruptionsPerSession { get; set; }
    public Dictionary<string, int> InterruptionSources { get; set; } = new();
    public List<TimeSpan> BestFocusHours { get; set; } = new();
    public Dictionary<string, FocusActivityMetrics> ActivityMetrics { get; set; } = new();
}

public class FocusActivityMetrics
{
    public string Activity { get; set; } = string.Empty;
    public int SessionCount { get; set; }
    public TimeSpan TotalTime { get; set; }
    public decimal AverageFocusScore { get; set; }
    public decimal CompletionRate { get; set; }
    public decimal AverageInterruptions { get; set; }
}

public class FocusInsight
{
    public string Title { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public InsightType Type { get; set; }
    public decimal Confidence { get; set; }
    public List<string> Recommendations { get; set; } = new();
    public Dictionary<string, object> Data { get; set; } = new();
}

public class TaskTimeAnalysis
{
    public Guid TaskId { get; set; }
    public string TaskName { get; set; } = string.Empty;
    public TimeSpan TotalTimeSpent { get; set; }
    public TimeSpan EstimatedTime { get; set; }
    public decimal VariancePercentage { get; set; }
    public int SessionCount { get; set; }
    public TimeSpan AverageSessionDuration { get; set; }
    public List<TimeEntry> TimeEntries { get; set; } = new();
    public DateTime FirstSession { get; set; }
    public DateTime? LastSession { get; set; }
    public bool IsCompleted { get; set; }
    public List<string> Insights { get; set; } = new();
}

public class GoalTimeAnalysis
{
    public Guid GoalId { get; set; }
    public string GoalName { get; set; } = string.Empty;
    public TimeSpan TotalTimeSpent { get; set; }
    public TimeSpan EstimatedTime { get; set; }
    public decimal ProgressPercentage { get; set; }
    public int TaskCount { get; set; }
    public int CompletedTasks { get; set; }
    public List<TaskTimeAnalysis> TaskAnalyses { get; set; } = new();
    public Dictionary<string, TimeSpan> TimeByCategory { get; set; } = new();
    public List<TimeEntry> AllTimeEntries { get; set; } = new();
    public DateTime FirstSession { get; set; }
    public DateTime? LastSession { get; set; }
    public decimal VelocityScore { get; set; } // Time spent vs progress made
    public List<string> Insights { get; set; } = new();
}

public class TimeEstimate
{
    public string Activity { get; set; } = string.Empty;
    public TimeSpan EstimatedTime { get; set; }
    public decimal Confidence { get; set; } // 0-100
    public TimeSpan MinEstimate { get; set; }
    public TimeSpan MaxEstimate { get; set; }
    public List<string> Assumptions { get; set; } = new();
    public List<string> RiskFactors { get; set; } = new();
    public Dictionary<string, object> ModelData { get; set; } = new();
    public DateTime GeneratedAt { get; set; }
}

public class TimeEstimationAccuracy
{
    public string UserId { get; set; } = string.Empty;
    public decimal OverallAccuracy { get; set; } // 0-100
    public decimal AverageVariance { get; set; } // Percentage
    public TrendDirection AccuracyTrend { get; set; }
    public Dictionary<string, decimal> AccuracyByActivity { get; set; } = new();
    public Dictionary<string, decimal> AccuracyByCategory { get; set; } = new();
    public List<EstimationPattern> Patterns { get; set; } = new();
    public DateTime AnalyzedAt { get; set; }
}

public class EstimationPattern
{
    public string Name { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public decimal Frequency { get; set; } // 0-100
    public string RecommendedAction { get; set; } = string.Empty;
}