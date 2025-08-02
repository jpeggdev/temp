using HeyDav.Domain.Analytics.Enums;
using HeyDav.Domain.Analytics.ValueObjects;
using HeyDav.Domain.Workflows.Enums;

namespace HeyDav.Application.Analytics.Models;

public class UserProductivityProfile
{
    public string UserId { get; set; } = string.Empty;
    public DateTime AnalyzedAt { get; set; }
    public DateTime FromDate { get; set; }
    public DateTime ToDate { get; set; }
    
    // Peak Performance Patterns
    public List<TimeSpan> PeakHours { get; set; } = new();
    public List<DayOfWeek> MostProductiveDays { get; set; } = new();
    public Dictionary<TimeSpan, int> EnergyPatterns { get; set; } = new();
    public Dictionary<TimeSpan, decimal> ProductivityPatterns { get; set; } = new();
    
    // Work Patterns
    public TimeSpan AverageWorkSessionDuration { get; set; }
    public int AverageTasksPerDay { get; set; }
    public decimal AverageTaskCompletionRate { get; set; }
    public int ContextSwitchingFrequency { get; set; }
    public decimal FocusTimePercentage { get; set; }
    
    // Behavioral Patterns
    public List<TaskCompletionPattern> TaskCompletionPatterns { get; set; } = new();
    public List<ContextPattern> ContextPatterns { get; set; } = new();
    public Dictionary<string, decimal> CategoryProductivity { get; set; } = new();
    
    // Performance Indicators
    public decimal OverallProductivityScore { get; set; }
    public decimal ConsistencyScore { get; set; }
    public decimal EfficiencyScore { get; set; }
    public decimal MeetingImpactScore { get; set; }
    
    // Preferences and Tendencies
    public WorkingStyleProfile WorkingStyle { get; set; } = new();
    public List<string> PreferredWorkTypes { get; set; } = new();
    public List<string> ProductivityDrivers { get; set; } = new();
    public List<string> ProductivityBarriers { get; set; } = new();
}

public class TaskCompletionPattern
{
    public DayOfWeek DayOfWeek { get; set; }
    public decimal CompletionRate { get; set; }
    public TimeSpan AverageCompletionTime { get; set; }
    public int TaskCount { get; set; }
    public List<string> CommonTaskTypes { get; set; } = new();
}

public class ContextPattern
{
    public string Context { get; set; } = string.Empty;
    public decimal ProductivityScore { get; set; }
    public TimeSpan AverageSessionDuration { get; set; }
    public int Frequency { get; set; }
    public List<TimeSpan> OptimalTimes { get; set; } = new();
}

public class WorkingStyleProfile
{
    public bool IsMorningPerson { get; set; }
    public bool PrefersLongSessions { get; set; }
    public bool RespondsWellToDeadlines { get; set; }
    public int OptimalBreakFrequency { get; set; } // minutes between breaks
    public TimeSpan OptimalSessionLength { get; set; }
    public bool WorksBetterWithMusic { get; set; }
    public bool NeedsQuietEnvironment { get; set; }
}

public class ProductivityForecast
{
    public string UserId { get; set; } = string.Empty;
    public DateTime TargetDate { get; set; }
    public decimal PredictedProductivityScore { get; set; }
    public decimal Confidence { get; set; }
    public List<HourlyProductivityForecast> HourlyForecasts { get; set; } = new();
    public List<string> InfluencingFactors { get; set; } = new();
    public List<string> Recommendations { get; set; } = new();
    public DateTime GeneratedAt { get; set; }
}

public class HourlyProductivityForecast
{
    public TimeSpan Hour { get; set; }
    public decimal PredictedScore { get; set; }
    public int PredictedEnergyLevel { get; set; }
    public decimal Confidence { get; set; }
    public string? RecommendedActivity { get; set; }
}

public class TaskCompletionPrediction
{
    public Guid TaskId { get; set; }
    public string TaskName { get; set; } = string.Empty;
    public decimal CompletionProbability { get; set; }
    public DateTime PredictedCompletionDate { get; set; }
    public TimeSpan EstimatedTimeRequired { get; set; }
    public decimal Confidence { get; set; }
    public List<string> RiskFactors { get; set; } = new();
    public List<string> SuccessFactors { get; set; } = new();
    public DateTime GeneratedAt { get; set; }
}

public class GoalAchievementPrediction
{
    public Guid GoalId { get; set; }
    public string GoalName { get; set; } = string.Empty;
    public decimal AchievementProbability { get; set; }
    public DateTime PredictedAchievementDate { get; set; }
    public decimal CurrentProgress { get; set; }
    public decimal RequiredWeeklyProgress { get; set; }
    public decimal Confidence { get; set; }
    public List<string> CriticalActions { get; set; } = new();
    public List<string> RiskFactors { get; set; } = new();
    public DateTime GeneratedAt { get; set; }
}

public class EnergyLevelPrediction
{
    public string UserId { get; set; } = string.Empty;
    public DateTime FromDate { get; set; }
    public DateTime ToDate { get; set; }
    public Dictionary<DateTime, DailyEnergyPrediction> DailyPredictions { get; set; } = new();
    public decimal AverageConfidence { get; set; }
    public DateTime GeneratedAt { get; set; }
}

public class DailyEnergyPrediction
{
    public DateTime Date { get; set; }
    public Dictionary<TimeSpan, int> HourlyEnergyLevels { get; set; } = new();
    public int PeakEnergyHour { get; set; }
    public int LowestEnergyHour { get; set; }
    public decimal AverageEnergyLevel { get; set; }
    public List<string> InfluencingFactors { get; set; } = new();
}

public class OptimalSchedulingSuggestion
{
    public string UserId { get; set; } = string.Empty;
    public List<ScheduledTaskSuggestion> TaskSuggestions { get; set; } = new();
    public decimal OptimizationScore { get; set; }
    public string OptimizationStrategy { get; set; } = string.Empty;
    public List<string> Reasoning { get; set; } = new();
    public Dictionary<string, object> Constraints { get; set; } = new();
    public DateTime GeneratedAt { get; set; }
}

public class ScheduledTaskSuggestion
{
    public Guid TaskId { get; set; }
    public string TaskName { get; set; } = string.Empty;
    public DateTime SuggestedStartTime { get; set; }
    public DateTime SuggestedEndTime { get; set; }
    public decimal PredictedProductivityScore { get; set; }
    public int PredictedEnergyLevel { get; set; }
    public string Reasoning { get; set; } = string.Empty;
    public decimal Confidence { get; set; }
}

public class ProductivityBottleneck
{
    public string Name { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public string Category { get; set; } = string.Empty;
    public decimal Impact { get; set; } // 0-100
    public decimal Frequency { get; set; } // 0-100
    public List<string> Symptoms { get; set; } = new();
    public List<string> SuggestedSolutions { get; set; } = new();
    public decimal ResolutionDifficulty { get; set; } // 0-100
    public Dictionary<string, object> Data { get; set; } = new();
}

public class ProductivityOpportunity
{
    public string Name { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public string Category { get; set; } = string.Empty;
    public decimal PotentialImpact { get; set; } // 0-100
    public decimal ImplementationDifficulty { get; set; } // 0-100
    public decimal ExpectedROI { get; set; }
    public List<string> RequiredActions { get; set; } = new();
    public List<string> Benefits { get; set; } = new();
    public TimeSpan EstimatedTimeToImplement { get; set; }
    public Dictionary<string, object> Data { get; set; } = new();
}

public class SeasonalAnalysis
{
    public string UserId { get; set; } = string.Empty;
    public string MetricName { get; set; } = string.Empty;
    public Dictionary<int, decimal> MonthlyAverages { get; set; } = new(); // Month -> Average
    public Dictionary<DayOfWeek, decimal> WeeklyAverages { get; set; } = new();
    public Dictionary<int, decimal> HourlyAverages { get; set; } = new(); // Hour -> Average
    public List<SeasonalPattern> DetectedPatterns { get; set; } = new();
    public decimal SeasonalityStrength { get; set; } // 0-100
    public DateTime AnalyzedAt { get; set; }
}

public class SeasonalPattern
{
    public string Name { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public decimal Strength { get; set; } // 0-100
    public string Pattern { get; set; } = string.Empty; // "weekly", "monthly", "yearly"
    public List<object> PeakPeriods { get; set; } = new();
    public List<object> LowPeriods { get; set; } = new();
}

public class ContextualProductivityAnalysis
{
    public string UserId { get; set; } = string.Empty;
    public Dictionary<string, ContextProductivityData> ContextData { get; set; } = new();
    public List<string> TopProductiveContexts { get; set; } = new();
    public List<string> LeastProductiveContexts { get; set; } = new();
    public List<ContextualInsight> Insights { get; set; } = new();
    public DateTime AnalyzedAt { get; set; }
}

public class ContextProductivityData
{
    public string Context { get; set; } = string.Empty;
    public decimal AverageProductivityScore { get; set; }
    public TimeSpan TotalTimeSpent { get; set; }
    public int SessionCount { get; set; }
    public TimeSpan AverageSessionDuration { get; set; }
    public Dictionary<TimeSpan, decimal> BestTimes { get; set; } = new();
    public List<string> CommonActivities { get; set; } = new();
}

public class ContextualInsight
{
    public string Context { get; set; } = string.Empty;
    public string Insight { get; set; } = string.Empty;
    public string Recommendation { get; set; } = string.Empty;
    public decimal Confidence { get; set; }
    public Dictionary<string, object> SupportingData { get; set; } = new();
}

public class ProductivityDataPoint
{
    public DateTime Timestamp { get; set; }
    public ProductivityMetric Metric { get; set; }
    public decimal Value { get; set; }
    public string? Context { get; set; }
    public Dictionary<string, object> Metadata { get; set; } = new();
}

public class EnergyOptimizationSuggestion
{
    public string UserId { get; set; } = string.Empty;
    public List<EnergyTip> Tips { get; set; } = new();
    public Dictionary<TimeSpan, string> OptimalActivities { get; set; } = new();
    public List<string> EnergyDrains { get; set; } = new();
    public List<string> EnergyBoosters { get; set; } = new();
    public DateTime GeneratedAt { get; set; }
}

public class EnergyTip
{
    public string Title { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public string Category { get; set; } = string.Empty;
    public decimal PotentialImpact { get; set; }
    public decimal ImplementationDifficulty { get; set; }
    public TimeSpan TimeToSeeResults { get; set; }
}