using HeyDav.Domain.Analytics.Enums;
using HeyDav.Domain.Analytics.ValueObjects;

namespace HeyDav.Application.Analytics.Models;

public class PerformanceScoreBreakdown
{
    public string UserId { get; set; } = string.Empty;
    public DateTime FromDate { get; set; }
    public DateTime ToDate { get; set; }
    public ProductivityScoreCard OverallScore { get; set; } = ProductivityScoreCard.Empty();
    public List<MetricContribution> MetricContributions { get; set; } = new();
    public List<PerformanceDriver> TopDrivers { get; set; } = new();
    public List<PerformanceDetractor> TopDetractors { get; set; } = new();
    public Dictionary<string, decimal> CategoryScores { get; set; } = new();
    public List<ScoreImprovement> ImprovementAreas { get; set; } = new();
    public DateTime CalculatedAt { get; set; }
}

public class MetricContribution
{
    public string MetricName { get; set; } = string.Empty;
    public decimal Value { get; set; }
    public decimal WeightedContribution { get; set; }
    public decimal PercentageOfTotal { get; set; }
    public TrendDirection Trend { get; set; }
    public string Impact { get; set; } = string.Empty; // "Positive", "Negative", "Neutral"
}

public class PerformanceDriver
{
    public string Name { get; set; } = string.Empty;
    public decimal Impact { get; set; }
    public string Description { get; set; } = string.Empty;
    public List<string> Evidence { get; set; } = new();
    public decimal Confidence { get; set; }
}

public class PerformanceDetractor
{
    public string Name { get; set; } = string.Empty;
    public decimal NegativeImpact { get; set; }
    public string Description { get; set; } = string.Empty;
    public List<string> Evidence { get; set; } = new();
    public List<string> SuggestedActions { get; set; } = new();
    public decimal Confidence { get; set; }
}

public class ScoreImprovement
{
    public string Area { get; set; } = string.Empty;
    public decimal CurrentScore { get; set; }
    public decimal PotentialScore { get; set; }
    public decimal PotentialImpact { get; set; }
    public List<string> ActionItems { get; set; } = new();
    public decimal DifficultyLevel { get; set; }
    public TimeSpan EstimatedTimeframe { get; set; }
}

public class GoalCompletionMetrics
{
    public string UserId { get; set; } = string.Empty;
    public DateTime FromDate { get; set; }
    public DateTime ToDate { get; set; }
    public int TotalGoals { get; set; }
    public int CompletedGoals { get; set; }
    public int InProgressGoals { get; set; }
    public int OverdueGoals { get; set; }
    public decimal CompletionRate { get; set; }
    public decimal AverageProgress { get; set; }
    public TimeSpan AverageCompletionTime { get; set; }
    public List<GoalCategoryMetrics> CategoryBreakdown { get; set; } = new();
    public List<GoalPerformanceData> GoalDetails { get; set; } = new();
    public DateTime CalculatedAt { get; set; }
}

public class GoalCategoryMetrics
{
    public string Category { get; set; } = string.Empty;
    public int TotalGoals { get; set; }
    public int CompletedGoals { get; set; }
    public decimal CompletionRate { get; set; }
    public decimal AverageProgress { get; set; }
    public TimeSpan AverageCompletionTime { get; set; }
}

public class GoalPerformanceData
{
    public Guid GoalId { get; set; }
    public string GoalName { get; set; } = string.Empty;
    public string Category { get; set; } = string.Empty;
    public decimal Progress { get; set; }
    public DateTime StartDate { get; set; }
    public DateTime? TargetDate { get; set; }
    public DateTime? CompletionDate { get; set; }
    public bool IsCompleted { get; set; }
    public bool IsOverdue { get; set; }
    public TimeSpan TimeSpent { get; set; }
    public List<MilestoneProgress> Milestones { get; set; } = new();
    public decimal VelocityScore { get; set; }
    public List<string> Blockers { get; set; } = new();
}

public class MilestoneProgress
{
    public Guid MilestoneId { get; set; }
    public string MilestoneName { get; set; } = string.Empty;
    public DateTime TargetDate { get; set; }
    public DateTime? CompletionDate { get; set; }
    public bool IsCompleted { get; set; }
    public decimal Progress { get; set; }
}

public class MilestoneTrackingReport
{
    public string UserId { get; set; } = string.Empty;
    public List<MilestoneStatus> UpcomingMilestones { get; set; } = new();
    public List<MilestoneStatus> OverdueMilestones { get; set; } = new();
    public List<MilestoneStatus> CompletedMilestones { get; set; } = new();
    public decimal OverallMilestoneCompletionRate { get; set; }
    public int TotalMilestones { get; set; }
    public DateTime GeneratedAt { get; set; }
}

public class MilestoneStatus
{
    public Guid MilestoneId { get; set; }
    public string MilestoneName { get; set; } = string.Empty;
    public Guid GoalId { get; set; }
    public string GoalName { get; set; } = string.Empty;
    public DateTime TargetDate { get; set; }
    public DateTime? CompletionDate { get; set; }
    public decimal Progress { get; set; }
    public int DaysUntilDue { get; set; }
    public bool IsAtRisk { get; set; }
    public List<string> RiskFactors { get; set; } = new();
}

public class HabitConsistencyMetrics
{
    public string UserId { get; set; } = string.Empty;
    public DateTime FromDate { get; set; }
    public DateTime ToDate { get; set; }
    public int TotalHabits { get; set; }
    public decimal OverallConsistencyScore { get; set; }
    public int TotalStreakDays { get; set; }
    public int LongestActiveStreak { get; set; }
    public decimal AverageCompletionRate { get; set; }
    public List<HabitPerformanceData> HabitDetails { get; set; } = new();
    public Dictionary<DayOfWeek, decimal> ConsistencyByDayOfWeek { get; set; } = new();
    public List<HabitCategoryMetrics> CategoryBreakdown { get; set; } = new();
    public DateTime CalculatedAt { get; set; }
}

public class HabitPerformanceData
{
    public Guid HabitId { get; set; }
    public string HabitName { get; set; } = string.Empty;
    public string Category { get; set; } = string.Empty;
    public int CurrentStreak { get; set; }
    public int LongestStreak { get; set; }
    public decimal CompletionRate { get; set; }
    public int TotalCompletions { get; set; }
    public int TotalOpportunities { get; set; }
    public List<DateTime> CompletionDates { get; set; } = new();
    public List<DateTime> MissedDates { get; set; } = new();
    public decimal ConsistencyScore { get; set; }
    public TrendDirection RecentTrend { get; set; }
    public List<string> Insights { get; set; } = new();
}

public class HabitCategoryMetrics
{
    public string Category { get; set; } = string.Empty;
    public int HabitCount { get; set; }
    public decimal AverageConsistencyScore { get; set; }
    public decimal AverageCompletionRate { get; set; }
    public int TotalStreakDays { get; set; }
    public int LongestStreak { get; set; }
}

public class HabitStreakAnalysis
{
    public string UserId { get; set; } = string.Empty;
    public List<StreakData> ActiveStreaks { get; set; } = new();
    public List<StreakData> BrokenStreaks { get; set; } = new();
    public List<StreakData> RecordStreaks { get; set; } = new();
    public int TotalActiveStreakDays { get; set; }
    public decimal AverageStreakLength { get; set; }
    public List<StreakPattern> StreakPatterns { get; set; } = new();
    public List<string> StreakInsights { get; set; } = new();
    public DateTime AnalyzedAt { get; set; }
}

public class StreakData
{
    public Guid HabitId { get; set; }
    public string HabitName { get; set; } = string.Empty;
    public int Length { get; set; }
    public DateTime StartDate { get; set; }
    public DateTime? EndDate { get; set; }
    public bool IsActive { get; set; }
    public List<DateTime> CompletionDates { get; set; } = new();
}

public class StreakPattern
{
    public string PatternName { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public List<DayOfWeek> StrongDays { get; set; } = new();
    public List<DayOfWeek> WeakDays { get; set; } = new();
    public decimal Confidence { get; set; }
}

public class EnergyProductivityCorrelation
{
    public string UserId { get; set; } = string.Empty;
    public DateTime FromDate { get; set; }
    public DateTime ToDate { get; set; }
    public decimal CorrelationCoefficient { get; set; }
    public string CorrelationStrength { get; set; } = string.Empty; // "Strong", "Moderate", "Weak"
    public Dictionary<int, decimal> EnergyLevelProductivity { get; set; } = new(); // Energy Level -> Avg Productivity
    public Dictionary<TimeSpan, EnergyProductivityPoint> HourlyCorrelation { get; set; } = new();
    public List<string> Insights { get; set; } = new();
    public decimal OptimalEnergyThreshold { get; set; }
    public List<TimeSpan> HighEnergyHighProductivityPeriods { get; set; } = new();
    public DateTime AnalyzedAt { get; set; }
}

public class EnergyProductivityPoint
{
    public TimeSpan Hour { get; set; }
    public decimal AverageEnergyLevel { get; set; }
    public decimal AverageProductivityScore { get; set; }
    public int DataPoints { get; set; }
    public decimal Correlation { get; set; }
}

public class MoodProductivityCorrelation
{
    public string UserId { get; set; } = string.Empty;
    public DateTime FromDate { get; set; }
    public DateTime ToDate { get; set; }
    public decimal CorrelationCoefficient { get; set; }
    public string CorrelationStrength { get; set; } = string.Empty;
    public Dictionary<int, decimal> MoodProductivityMapping { get; set; } = new(); // Mood -> Avg Productivity
    public Dictionary<DateTime, MoodProductivityPoint> DailyCorrelation { get; set; } = new();
    public List<string> Insights { get; set; } = new();
    public decimal OptimalMoodThreshold { get; set; }
    public List<string> MoodBoosters { get; set; } = new();
    public DateTime AnalyzedAt { get; set; }
}

public class MoodProductivityPoint
{
    public DateTime Date { get; set; }
    public decimal AverageMood { get; set; }
    public decimal ProductivityScore { get; set; }
    public int SessionCount { get; set; }
    public List<string> Activities { get; set; } = new();
}

public class WellbeingMetrics
{
    public string UserId { get; set; } = string.Empty;
    public DateTime FromDate { get; set; }
    public DateTime ToDate { get; set; }
    public decimal OverallWellbeingScore { get; set; }
    public decimal AverageEnergyLevel { get; set; }
    public decimal AverageMoodScore { get; set; }
    public decimal StressLevel { get; set; }
    public decimal WorkLifeBalanceScore { get; set; }
    public decimal SleepQualityScore { get; set; }
    public int BurnoutRiskLevel { get; set; } // 1-10 scale
    public List<WellbeingTrend> Trends { get; set; } = new();
    public List<string> WellbeingInsights { get; set; } = new();
    public List<string> RecommendedActions { get; set; } = new();
    public DateTime CalculatedAt { get; set; }
}

public class WellbeingTrend
{
    public string Metric { get; set; } = string.Empty;
    public TrendDirection Direction { get; set; }
    public decimal Change { get; set; }
    public string TimeFrame { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
}

public class PerformanceComparison
{
    public string UserId { get; set; } = string.Empty;
    public DateTime Period1Start { get; set; }
    public DateTime Period1End { get; set; }
    public DateTime Period2Start { get; set; }
    public DateTime Period2End { get; set; }
    public List<MetricComparison> MetricComparisons { get; set; } = new();
    public decimal OverallImprovement { get; set; }
    public List<string> TopImprovements { get; set; } = new();
    public List<string> TopRegressions { get; set; } = new();
    public List<string> ComparisonInsights { get; set; } = new();
    public DateTime GeneratedAt { get; set; }
}

public class MetricComparison
{
    public string MetricName { get; set; } = string.Empty;
    public decimal Period1Value { get; set; }
    public decimal Period2Value { get; set; }
    public decimal Change { get; set; }
    public decimal PercentageChange { get; set; }
    public string ChangeDirection { get; set; } = string.Empty; // "Improved", "Declined", "Stable"
    public string Significance { get; set; } = string.Empty; // "Significant", "Minor", "Negligible"
}

public class PersonalBenchmarkAnalysis
{
    public string UserId { get; set; } = string.Empty;
    public Dictionary<string, PersonalBenchmark> Benchmarks { get; set; } = new();
    public List<BenchmarkAchievement> RecentAchievements { get; set; } = new();
    public List<BenchmarkGoal> BenchmarkGoals { get; set; } = new();
    public decimal OverallBenchmarkScore { get; set; }
    public DateTime AnalyzedAt { get; set; }
}

public class PersonalBenchmark
{
    public string MetricName { get; set; } = string.Empty;
    public decimal CurrentValue { get; set; }
    public decimal PersonalBest { get; set; }
    public decimal PersonalAverage { get; set; }
    public decimal PersonalWorst { get; set; }
    public DateTime PersonalBestDate { get; set; }
    public decimal PercentileRank { get; set; } // vs own historical data
    public TrendDirection RecentTrend { get; set; }
}

public class BenchmarkAchievement
{
    public string MetricName { get; set; } = string.Empty;
    public string AchievementType { get; set; } = string.Empty; // "PersonalBest", "Streak", "Milestone"
    public decimal Value { get; set; }
    public DateTime AchievedDate { get; set; }
    public string Description { get; set; } = string.Empty;
}

public class BenchmarkGoal
{
    public string MetricName { get; set; } = string.Empty;
    public decimal TargetValue { get; set; }
    public decimal CurrentValue { get; set; }
    public decimal Progress { get; set; }
    public DateTime TargetDate { get; set; }
    public bool IsAchievable { get; set; }
    public List<string> SuggestedActions { get; set; } = new();
}

public class PerformanceRanking
{
    public string UserId { get; set; } = string.Empty;
    public List<MetricRanking> MetricRankings { get; set; } = new();
    public decimal OverallPerformancePercentile { get; set; }
    public List<string> TopPerformanceAreas { get; set; } = new();
    public List<string> ImprovementAreas { get; set; } = new();
    public string ComparisonGroup { get; set; } = string.Empty; // "Personal", "Peer", "Industry"
    public DateTime GeneratedAt { get; set; }
}

public class MetricRanking
{
    public string MetricName { get; set; } = string.Empty;
    public decimal Value { get; set; }
    public decimal Percentile { get; set; }
    public int Rank { get; set; }
    public int TotalParticipants { get; set; }
    public string PerformanceLevel { get; set; } = string.Empty; // "Excellent", "Good", "Average", "Below Average"
}

public class EfficiencyMetrics
{
    public string UserId { get; set; } = string.Empty;
    public DateTime FromDate { get; set; }
    public DateTime ToDate { get; set; }
    public decimal OverallEfficiencyScore { get; set; }
    public decimal TimeUtilizationRate { get; set; }
    public decimal TaskCompletionEfficiency { get; set; }
    public decimal ResourceAllocationEfficiency { get; set; }
    public decimal ContextSwitchingPenalty { get; set; }
    public decimal MultitaskingEfficiency { get; set; }
    public List<EfficiencyFactor> EfficiencyFactors { get; set; } = new();
    public List<string> EfficiencyInsights { get; set; } = new();
    public List<string> OptimizationOpportunities { get; set; } = new();
    public DateTime CalculatedAt { get; set; }
}

public class EfficiencyFactor
{
    public string Name { get; set; } = string.Empty;
    public decimal Score { get; set; }
    public decimal Impact { get; set; }
    public string Description { get; set; } = string.Empty;
    public List<string> ImprovementSuggestions { get; set; } = new();
}

public class QualityMetrics
{
    public string UserId { get; set; } = string.Empty;
    public DateTime FromDate { get; set; }
    public DateTime ToDate { get; set; }
    public decimal OverallQualityScore { get; set; }
    public decimal WorkQualityRating { get; set; }
    public decimal ReworkRate { get; set; }
    public decimal ErrorRate { get; set; }
    public decimal CompletionAccuracy { get; set; }
    public decimal StakeholderSatisfaction { get; set; }
    public List<QualityIndicator> QualityIndicators { get; set; } = new();
    public List<string> QualityInsights { get; set; } = new();
    public DateTime CalculatedAt { get; set; }
}

public class QualityIndicator
{
    public string Name { get; set; } = string.Empty;
    public decimal Score { get; set; }
    public string Unit { get; set; } = string.Empty;
    public TrendDirection Trend { get; set; }
    public decimal Target { get; set; }
    public string Status { get; set; } = string.Empty; // "Meeting", "Exceeding", "Below"
}

public class TimeAllocationEfficiency
{
    public string UserId { get; set; } = string.Empty;
    public DateTime FromDate { get; set; }
    public DateTime ToDate { get; set; }
    public decimal EfficiencyScore { get; set; }
    public Dictionary<string, TimeAllocationData> CategoryAllocation { get; set; } = new();
    public Dictionary<string, TimeAllocationData> ProjectAllocation { get; set; } = new();
    public List<AllocationInsight> Insights { get; set; } = new();
    public List<AllocationRecommendation> Recommendations { get; set; } = new();
    public DateTime AnalyzedAt { get; set; }
}

public class TimeAllocationData
{
    public string Name { get; set; } = string.Empty;
    public TimeSpan AllocatedTime { get; set; }
    public decimal Percentage { get; set; }
    public decimal ValueScore { get; set; } // 1-10 scale
    public decimal EfficiencyRating { get; set; }
    public bool IsOptimal { get; set; }
    public decimal SuggestedPercentage { get; set; }
}

public class AllocationInsight
{
    public string Category { get; set; } = string.Empty;
    public string Insight { get; set; } = string.Empty;
    public string Type { get; set; } = string.Empty; // "Opportunity", "Warning", "Achievement"
    public decimal Impact { get; set; }
}

public class AllocationRecommendation
{
    public string Title { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public decimal PotentialImpact { get; set; }
    public decimal ImplementationDifficulty { get; set; }
    public List<string> ActionSteps { get; set; } = new();
}

public class MetricTrendAnalysis
{
    public string UserId { get; set; } = string.Empty;
    public List<MetricTrend> Trends { get; set; } = new();
    public Dictionary<string, SeasonalPattern> SeasonalPatterns { get; set; } = new();
    public List<TrendInsight> Insights { get; set; } = new();
    public DateTime AnalyzedAt { get; set; }
}

public class MetricTrend
{
    public string MetricName { get; set; } = string.Empty;
    public TrendDirection Direction { get; set; }
    public decimal Slope { get; set; }
    public decimal Acceleration { get; set; }
    public decimal Confidence { get; set; }
    public List<TrendPoint> DataPoints { get; set; } = new();
    public string TrendDescription { get; set; } = string.Empty;
}

public class TrendPoint
{
    public DateTime Date { get; set; }
    public decimal Value { get; set; }
    public decimal MovingAverage { get; set; }
}

public class TrendInsight
{
    public string MetricName { get; set; } = string.Empty;
    public string Insight { get; set; } = string.Empty;
    public InsightType Type { get; set; }
    public decimal Confidence { get; set; }
    public List<string> SupportingEvidence { get; set; } = new();
}

public class PerformanceVelocity
{
    public string UserId { get; set; } = string.Empty;
    public decimal CurrentVelocity { get; set; }
    public decimal AverageVelocity { get; set; }
    public TrendDirection VelocityTrend { get; set; }
    public Dictionary<string, decimal> MetricVelocities { get; set; } = new();
    public List<VelocityFactor> AcceleratingFactors { get; set; } = new();
    public List<VelocityFactor> DeceleratingFactors { get; set; } = new();
    public decimal PredictedVelocity { get; set; }
    public DateTime AnalyzedAt { get; set; }
}

public class VelocityFactor
{
    public string Name { get; set; } = string.Empty;
    public decimal Impact { get; set; }
    public string Description { get; set; } = string.Empty;
    public List<string> Evidence { get; set; } = new();
}

public class SeasonalPerformanceAnalysis
{
    public string UserId { get; set; } = string.Empty;
    public string MetricName { get; set; } = string.Empty;
    public Dictionary<string, decimal> MonthlyAverages { get; set; } = new();
    public Dictionary<string, decimal> QuarterlyAverages { get; set; } = new();
    public Dictionary<DayOfWeek, decimal> WeeklyPatterns { get; set; } = new();
    public Dictionary<int, decimal> HourlyPatterns { get; set; } = new();
    public List<SeasonalInsight> Insights { get; set; } = new();
    public decimal SeasonalityStrength { get; set; }
    public string DominantPattern { get; set; } = string.Empty;
    public DateTime AnalyzedAt { get; set; }
}

public class SeasonalInsight
{
    public string Pattern { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public decimal Strength { get; set; }
    public List<string> PeakPeriods { get; set; } = new();
    public List<string> LowPeriods { get; set; } = new();
    public List<string> Recommendations { get; set; } = new();
}

public class RealTimePerformanceSnapshot
{
    public string UserId { get; set; } = string.Empty;
    public DateTime SnapshotTime { get; set; }
    public decimal CurrentProductivityScore { get; set; }
    public int CurrentEnergyLevel { get; set; }
    public string CurrentActivity { get; set; } = string.Empty;
    public TimeSpan ActiveSessionDuration { get; set; }
    public int TodayTasksCompleted { get; set; }
    public int TodayTasksRemaining { get; set; }
    public decimal TodayProgressScore { get; set; }
    public List<string> ActiveGoals { get; set; } = new();
    public List<string> TodayInsights { get; set; } = new();
    public Dictionary<string, decimal> RealTimeMetrics { get; set; } = new();
}

public class DailyPerformanceSummary
{
    public string UserId { get; set; } = string.Empty;
    public DateTime Date { get; set; }
    public decimal OverallScore { get; set; }
    public int TasksCompleted { get; set; }
    public int TasksPlanned { get; set; }
    public TimeSpan TotalWorkTime { get; set; }
    public TimeSpan ProductiveTime { get; set; }
    public int FocusSessions { get; set; }
    public int EnergyLevelAverage { get; set; }
    public int MoodAverage { get; set; }
    public List<string> TopActivities { get; set; } = new();
    public List<string> Achievements { get; set; } = new();
    public List<string> Insights { get; set; } = new();
    public Dictionary<string, decimal> DailyMetrics { get; set; } = new();
}

public class WeeklyPerformanceSummary
{
    public string UserId { get; set; } = string.Empty;
    public DateTime WeekStart { get; set; }
    public DateTime WeekEnd { get; set; }
    public decimal WeeklyScore { get; set; }
    public List<DailyPerformanceSummary> DailySummaries { get; set; } = new();
    public WeeklyTrends Trends { get; set; } = new();
    public List<string> WeeklyAchievements { get; set; } = new();
    public List<string> WeeklyInsights { get; set; } = new();
    public List<string> NextWeekRecommendations { get; set; } = new();
    public Dictionary<string, decimal> WeeklyMetrics { get; set; } = new();
}

public class WeeklyTrends
{
    public TrendDirection ProductivityTrend { get; set; }
    public TrendDirection EnergyTrend { get; set; }
    public TrendDirection MoodTrend { get; set; }
    public TrendDirection TaskCompletionTrend { get; set; }
    public string BestDay { get; set; } = string.Empty;
    public string MostChallengingDay { get; set; } = string.Empty;
}

// Additional support models for custom metrics and reporting
public class CustomMetricDefinition
{
    public string Name { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public string Formula { get; set; } = string.Empty;
    public List<string> RequiredMetrics { get; set; } = new();
    public Dictionary<string, object> Parameters { get; set; } = new();
    public string Unit { get; set; } = string.Empty;
    public MetricCategory Category { get; set; }
}

public class CustomMetricResult
{
    public string MetricName { get; set; } = string.Empty;
    public decimal Value { get; set; }
    public string Unit { get; set; } = string.Empty;
    public DateTime CalculatedAt { get; set; }
    public Dictionary<string, object> CalculationDetails { get; set; } = new();
}

public class MetricUpdate
{
    public string MetricName { get; set; } = string.Empty;
    public decimal Value { get; set; }
    public DateTime Timestamp { get; set; }
    public Dictionary<string, object> Metadata { get; set; } = new();
}


public class PerformanceReport
{
    public Guid Id { get; set; }
    public string UserId { get; set; } = string.Empty;
    public string Title { get; set; } = string.Empty;
    public ReportType Type { get; set; }
    public DateTime FromDate { get; set; }
    public DateTime ToDate { get; set; }
    public Dictionary<string, decimal> Metrics { get; set; } = new();
    public List<PerformanceInsight> Insights { get; set; } = new();
    public List<PerformanceTrend> Trends { get; set; } = new();
    public Dictionary<string, object> ChartData { get; set; } = new();
    public DateTime GeneratedAt { get; set; }
}

public class PerformanceInsight
{
    public string Title { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public InsightType Type { get; set; }
    public InsightPriority Priority { get; set; }
    public decimal Impact { get; set; }
    public List<string> Recommendations { get; set; } = new();
}

public class PerformanceTrend
{
    public string MetricName { get; set; } = string.Empty;
    public TrendDirection Direction { get; set; }
    public decimal ChangePercent { get; set; }
    public string Description { get; set; } = string.Empty;
    public bool IsSignificant { get; set; }
}

public class ProductivityPattern
{
    public Guid Id { get; set; }
    public string UserId { get; set; } = string.Empty;
    public string Name { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public PatternType Type { get; set; }
    public decimal Strength { get; set; }
    public decimal Confidence { get; set; }
    public Dictionary<string, object> Parameters { get; set; } = new();
    public DateTime FirstDetected { get; set; }
    public DateTime LastUpdated { get; set; }
}

public enum PatternType
{
    Temporal,
    Behavioral,
    Environmental,
    Performance,
    Energy,
    Focus
}

public class DeliveryMethod
{
    public string Type { get; set; } = string.Empty; // Email, Dashboard, Notification
    public Dictionary<string, object> Configuration { get; set; } = new();
    public bool IsEnabled { get; set; }
}

public class RecurringReportConfig
{
    public Guid Id { get; set; }
    public string UserId { get; set; } = string.Empty;
    public string ReportName { get; set; } = string.Empty;
    public ReportType ReportType { get; set; }
    public RecurrencePattern Recurrence { get; set; } = new();
    public List<DeliveryMethod> DeliveryMethods { get; set; } = new();
    public Dictionary<string, object> ReportSettings { get; set; } = new();
    public bool IsActive { get; set; }
    public DateTime NextScheduledGeneration { get; set; }
    public DateTime CreatedAt { get; set; }
}

public class RecurrencePattern
{
    public RecurrenceType Type { get; set; }
    public int Interval { get; set; }
    public DayOfWeek? DayOfWeek { get; set; }
    public int? DayOfMonth { get; set; }
    public TimeSpan? TimeOfDay { get; set; }
}

public enum RecurrenceType
{
    Daily,
    Weekly,
    Monthly,
    Quarterly,
    Yearly
}