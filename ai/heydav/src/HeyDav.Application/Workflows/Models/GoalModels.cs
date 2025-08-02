using HeyDav.Domain.Goals.Entities;
using HeyDav.Application.Analytics.Models;

namespace HeyDav.Application.Workflows.Models;

// Request Models
public class ActionPlanRequest
{
    public ActionPlanTimeframe Timeframe { get; set; } = ActionPlanTimeframe.OneMonth;
    public TimeSpan AvailableTimePerWeek { get; set; } = TimeSpan.FromHours(10);
    public ActionPlanPriority PriorityLevel { get; set; } = ActionPlanPriority.Medium;
    public SkillLevel SkillLevel { get; set; } = SkillLevel.Intermediate;
    public List<string> AvailableResources { get; set; } = new();
    public List<string> Constraints { get; set; } = new();
    public bool IncludeBreakdownTasks { get; set; } = true;
    public bool IncludeDeadlines { get; set; } = true;
}

// Response Models
public class GoalProgressReport
{
    public Guid GoalId { get; set; }
    public string GoalTitle { get; set; } = string.Empty;
    public decimal CurrentProgress { get; set; } // 0-100
    public decimal ProgressSinceLastWeek { get; set; }
    public decimal ProgressSinceLastMonth { get; set; }
    public int CompletedMilestones { get; set; }
    public int TotalMilestones { get; set; }
    public int RelatedTasksCompleted { get; set; }
    public int TotalRelatedTasks { get; set; }
    public DateTime? EstimatedCompletionDate { get; set; }
    public decimal ProgressVelocity { get; set; } // Progress per day
    public string StatusSummary { get; set; } = string.Empty;
    public List<string> Recommendations { get; set; } = new();
    public List<Milestone> NextMilestones { get; set; } = new();
    public List<string> BlockersAndRisks { get; set; } = new();
    public DateTime GeneratedAt { get; set; } = DateTime.UtcNow;
}

public class GoalTrackingInsights
{
    public string UserId { get; set; } = string.Empty;
    public string TimeRange { get; set; } = string.Empty;
    public int TotalGoals { get; set; }
    public int ActiveGoals { get; set; }
    public int GoalsAchieved { get; set; }
    public decimal AverageProgressRate { get; set; }
    public Dictionary<GoalType, int> GoalsByCategory { get; set; } = new();
    public List<GoalPerformanceInfo> TopPerformingGoals { get; set; } = new();
    public List<Goal> GoalsNeedingAttention { get; set; } = new();
    public List<ProductivityInsight> ProductivityInsights { get; set; } = new();
    public List<string> Recommendations { get; set; } = new();
}

public class GoalOptimizationSuggestions
{
    public Guid GoalId { get; set; }
    public string GoalTitle { get; set; } = string.Empty;
    public decimal OptimizationScore { get; set; } // 0-100, higher is better optimized
    public List<OptimizationSuggestion> Suggestions { get; set; } = new();
    public DateTime AnalyzedAt { get; set; } = DateTime.UtcNow;
}

public class CourseCorrection
{
    public Guid GoalId { get; set; }
    public string GoalTitle { get; set; } = string.Empty;
    public GoalStatusAnalysis CurrentStatus { get; set; } = new();
    public List<GoalIssue> IdentifiedIssues { get; set; } = new();
    public List<CorrectionAction> RecommendedActions { get; set; } = new();
    public GoalOutcomePrediction PredictedOutcome { get; set; } = new();
    public DateTime AnalyzedAt { get; set; } = DateTime.UtcNow;
}

// Supporting Models
public class ActionItem
{
    public string Title { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public ActionItemType Type { get; set; }
    public ActionItemImpact Impact { get; set; }
    public ActionItemEffort Effort { get; set; }
    public int EstimatedDays { get; set; }
    public int Priority { get; set; } // Calculated priority score
    public DateTime SuggestedStartDate { get; set; }
    public DateTime SuggestedDueDate { get; set; }
    public List<string> Prerequisites { get; set; } = new();
    public List<string> Resources { get; set; } = new();
    public List<string> SuccessCriteria { get; set; } = new();
    public string? Notes { get; set; }
}

public class ActionPlanContext
{
    public Goal Goal { get; set; } = null!;
    public ActionPlanTimeframe Timeframe { get; set; }
    public TimeSpan AvailableTimePerWeek { get; set; }
    public ActionPlanPriority PriorityLevel { get; set; }
    public SkillLevel SkillLevel { get; set; }
    public List<string> Resources { get; set; } = new();
    public List<string> Constraints { get; set; } = new();
}

public class ActionPlanTemplate
{
    public GoalType GoalType { get; set; }
    public string Name { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public List<ActionItemTemplate> ActionItemTemplates { get; set; } = new();
    public List<string> CommonMilestones { get; set; } = new();
    public TimeSpan EstimatedDuration { get; set; }
}

public class ActionItemTemplate
{
    public string Title { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public ActionItemType Type { get; set; }
    public int Order { get; set; }
    public int EstimatedDays { get; set; }
    public List<string> Prerequisites { get; set; } = new();
    public bool IsOptional { get; set; }
}

public class GoalPerformanceInfo
{
    public Guid GoalId { get; set; }
    public string GoalTitle { get; set; } = string.Empty;
    public decimal CurrentProgress { get; set; }
    public decimal ProgressRate { get; set; }
    public int CompletedMilestones { get; set; }
    public int TotalMilestones { get; set; }
    public GoalPerformanceStatus Status { get; set; }
}

public class MilestoneRecommendation
{
    public string Title { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public int SuggestedOrder { get; set; }
    public TimeSpan EstimatedDuration { get; set; }
    public decimal TargetProgress { get; set; } // 0-100, what % of goal this milestone represents
    public List<string> SuccessCriteria { get; set; } = new();
    public MilestoneType Type { get; set; }
    public MilestonePriority Priority { get; set; }
}

public class OptimizationSuggestion
{
    public OptimizationCategory Category { get; set; }
    public string Title { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public OptimizationImpact Impact { get; set; }
    public OptimizationEffort ImplementationEffort { get; set; }
    public string ExpectedBenefit { get; set; } = string.Empty;
    public List<string> ActionSteps { get; set; } = new();
    public int Priority { get; set; } // Calculated priority score
}

public class GoalStatusAnalysis
{
    public decimal ProgressRate { get; set; }
    public bool IsOnTrack { get; set; }
    public int? DaysUntilDeadline { get; set; }
    public decimal CompletionProbability { get; set; } // 0-100
    public GoalHealthStatus HealthStatus { get; set; }
    public string StatusDescription { get; set; } = string.Empty;
}

public class GoalIssue
{
    public GoalIssueType Type { get; set; }
    public IssueSeverity Severity { get; set; }
    public string Description { get; set; } = string.Empty;
    public DateTime IdentifiedDate { get; set; } = DateTime.UtcNow;
    public List<string> PossibleCauses { get; set; } = new();
    public List<string> RecommendedSolutions { get; set; } = new();
}

public class CorrectionAction
{
    public string Description { get; set; } = string.Empty;
    public CorrectionActionType Type { get; set; }
    public int ExpectedImpact { get; set; } // 1-10 scale
    public int EffortRequired { get; set; } // 1-10 scale
    public TimeSpan EstimatedTime { get; set; }
    public List<string> Steps { get; set; } = new();
    public string? RationalE { get; set; }
}

public class GoalOutcomePrediction
{
    public Guid GoalId { get; set; }
    public decimal SuccessProbability { get; set; } // 0-100
    public DateTime? PredictedCompletionDate { get; set; }
    public GoalOutcome MostLikelyOutcome { get; set; }
    public List<PredictionFactor> PositiveFactors { get; set; } = new();
    public List<PredictionFactor> NegativeFactors { get; set; } = new();
    public string Summary { get; set; } = string.Empty;
    public decimal Confidence { get; set; } // 0-100, confidence in the prediction
}

public class PredictionFactor
{
    public string Description { get; set; } = string.Empty;
    public decimal Impact { get; set; } // -10 to +10
    public decimal Confidence { get; set; } // 0-100
    public FactorCategory Category { get; set; }
}

public class ProgressDataPoint
{
    public DateTime Date { get; set; }
    public decimal Value { get; set; } // Progress percentage
    public string? Notes { get; set; }
    public ProgressDataSource Source { get; set; }
}

public class GoalPerformanceMetrics
{
    public Guid GoalId { get; set; }
    public decimal AverageProgressRate { get; set; }
    public int TotalDaysActive { get; set; }
    public int DaysWithProgress { get; set; }
    public decimal ConsistencyScore { get; set; } // 0-100
    public TimeSpan AverageTimeToMilestone { get; set; }
    public decimal MilestoneCompletionRate { get; set; }
    public List<PerformanceAlert> Alerts { get; set; } = new();
}

public class PerformanceAlert
{
    public AlertType Type { get; set; }
    public AlertSeverity Severity { get; set; }
    public string Message { get; set; } = string.Empty;
    public DateTime TriggeredAt { get; set; }
    public bool IsResolved { get; set; }
}

public class GoalTrend
{
    public Guid GoalId { get; set; }
    public string GoalTitle { get; set; } = string.Empty;
    public TrendDirection Direction { get; set; }
    public decimal ChangeRate { get; set; }
    public string Description { get; set; } = string.Empty;
    public List<TrendDataPoint> DataPoints { get; set; } = new();
    public TrendSignificance Significance { get; set; }
}

public class TrendDataPoint
{
    public DateTime Date { get; set; }
    public decimal Value { get; set; }
    public string Metric { get; set; } = string.Empty;
}

public class GoalCompletionPrediction
{
    public Guid GoalId { get; set; }
    public DateTime? PredictedCompletionDate { get; set; }
    public decimal Confidence { get; set; }
    public CompletionScenario OptimisticScenario { get; set; } = new();
    public CompletionScenario RealisticScenario { get; set; } = new();
    public CompletionScenario PessimisticScenario { get; set; } = new();
    public List<string> KeyAssumptions { get; set; } = new();
}

public class CompletionScenario
{
    public string Name { get; set; } = string.Empty;
    public DateTime CompletionDate { get; set; }
    public decimal Probability { get; set; }
    public string Description { get; set; } = string.Empty;
    public List<string> RequiredConditions { get; set; } = new();
}

// Enums
public enum ActionPlanTimeframe
{
    OneWeek,
    TwoWeeks,
    OneMonth,
    ThreeMonths,
    SixMonths
}

public enum ActionPlanPriority
{
    Low,
    Medium,
    High,
    Critical
}

public enum SkillLevel
{
    Beginner,
    Intermediate,
    Advanced,
    Expert
}

public enum ActionItemType
{
    Research,
    Planning,
    Execution,
    Review,
    Learning,
    Communication,
    Testing,
    Documentation
}

public enum ActionItemImpact
{
    Low,
    Medium,
    High
}

public enum ActionItemEffort
{
    Low,
    Medium,
    High
}

public enum GoalPerformanceStatus
{
    Excellent,
    Good,
    Fair,
    Poor,
    Critical
}

public enum MilestoneType
{
    Planning,
    Checkpoint,
    Deliverable,
    Review,
    Final
}

public enum MilestonePriority
{
    Low,
    Medium,
    High,
    Critical
}

public enum OptimizationCategory
{
    Timeline,
    Structure,
    Focus,
    Resources,
    Tracking,
    Motivation
}

public enum OptimizationImpact
{
    Low,
    Medium,
    High
}

public enum OptimizationEffort
{
    Low,
    Medium,
    High
}

public enum GoalHealthStatus
{
    Healthy,
    AtRisk,
    Critical,
    Failing
}

public enum GoalIssueType
{
    StalledProgress,
    OverdueMilestones,
    InsufficientTime,
    LackOfResources,
    UnrealisticTimeline,
    LowMotivation,
    ExternalBlockers
}

public enum IssueSeverity
{
    Low,
    Medium,
    High,
    Critical
}

public enum CorrectionActionType
{
    ScheduleAdjustment,
    ResourceReallocation,
    ScopeModification,
    ProcessImprovement,
    MotivationBoost,
    SkillDevelopment
}

public enum GoalOutcome
{
    FullyAchieved,
    PartiallyAchieved,
    Abandoned,
    Extended,
    Modified
}

public enum FactorCategory
{
    Progress,
    Time,
    Resources,
    Motivation,
    External,
    Skills
}

public enum ProgressDataSource
{
    Manual,
    Automatic,
    Integration,
    Estimated
}

public enum AlertType
{
    StagnantProgress,
    OverdueMilestone,
    DeadlineApproaching,
    ResourceConstraint,
    PerformanceDrop
}

public enum AlertSeverity
{
    Info,
    Warning,
    Error,
    Critical
}

public enum TrendSignificance
{
    None,
    Minor,
    Moderate,
    Major,
    Critical
}