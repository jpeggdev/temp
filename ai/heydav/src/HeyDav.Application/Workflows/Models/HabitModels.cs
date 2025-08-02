using HeyDav.Domain.Workflows.Enums;
using HeyDav.Domain.Workflows.ValueObjects;
using HeyDav.Domain.Analytics.Enums;
using HeyDav.Application.Analytics.Models;

namespace HeyDav.Application.Workflows.Models;

// Request Models
public class CreateHabitRequest
{
    public string Name { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public HabitType Type { get; set; }
    public HabitFrequency Frequency { get; set; }
    public DateTime StartDate { get; set; } = DateTime.Today;
    public TimeSpan? TargetDuration { get; set; }
    public int? TargetCount { get; set; }
    public string? TargetUnit { get; set; }
    public HabitPriority Priority { get; set; } = HabitPriority.Medium;
    public string? ReminderSettings { get; set; }
    public List<string> Tags { get; set; } = new();
}

public class UpdateHabitRequest
{
    public string Name { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public HabitFrequency Frequency { get; set; }
    public TimeSpan? TargetDuration { get; set; }
    public int? TargetCount { get; set; }
    public string? TargetUnit { get; set; }
    public HabitPriority Priority { get; set; }
    public string? ReminderSettings { get; set; }
}

public class LogHabitEntryRequest
{
    public Guid HabitId { get; set; }
    public DateTime Date { get; set; } = DateTime.Today;
    public bool IsCompleted { get; set; }
    public TimeSpan? ActualDuration { get; set; }
    public int? ActualCount { get; set; }
    public string? Notes { get; set; }
    public decimal? Mood { get; set; } // 1-10 scale
    public decimal? Energy { get; set; } // 1-10 scale
}

// Response Models
public class HabitDashboard
{
    public string UserId { get; set; } = string.Empty;
    public int TotalActiveHabits { get; set; }
    public int TodayCompletedHabits { get; set; }
    public decimal WeeklyProgress { get; set; } // 0-100
    public decimal MonthlyProgress { get; set; } // 0-100
    public decimal OverallProgress { get; set; } // 0-100
    public List<HabitStreakInfo> CurrentStreaks { get; set; } = new();
    public List<TodayHabitStatus> TodaysHabits { get; set; } = new();
    public List<Achievement> RecentAchievements { get; set; } = new();
    public string? MotivationalMessage { get; set; }
    public DateTime LastUpdated { get; set; } = DateTime.UtcNow;
}

public class HabitAnalyticsReport
{
    public string UserId { get; set; } = string.Empty;
    public string ReportPeriod { get; set; } = string.Empty;
    public int TotalHabits { get; set; }
    public int ActiveHabits { get; set; }
    public int CompletedDays { get; set; }
    public int TotalPossibleDays { get; set; }
    public decimal OverallCompletionRate { get; set; }
    public List<HabitInsights> HabitInsights { get; set; } = new();
    public List<Achievement> Achievements { get; set; } = new();
    public List<HabitTrend> Trends { get; set; } = new();
    public List<string> Recommendations { get; set; } = new();
    public DateTime GeneratedAt { get; set; } = DateTime.UtcNow;
}

public class HabitRecommendation
{
    public string Category { get; set; } = string.Empty;
    public string Title { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public HabitFrequency SuggestedFrequency { get; set; }
    public TimeSpan EstimatedTime { get; set; }
    public HabitPriority Difficulty { get; set; }
    public string[] Benefits { get; set; } = Array.Empty<string>();
    public string[] StartingTips { get; set; } = Array.Empty<string>();
    public decimal RecommendationScore { get; set; }
}

// Supporting Models
public class HabitStreakInfo
{
    public Guid HabitId { get; set; }
    public string HabitName { get; set; } = string.Empty;
    public int CurrentStreak { get; set; }
    public int LongestStreak { get; set; }
    public DateTime? LastCompletedDate { get; set; }
}

public class TodayHabitStatus
{
    public Guid HabitId { get; set; }
    public string HabitName { get; set; } = string.Empty;
    public bool IsCompleted { get; set; }
    public TimeSpan? TargetDuration { get; set; }
    public int? TargetCount { get; set; }
    public TimeSpan? ActualDuration { get; set; }
    public int? ActualCount { get; set; }
    public int CurrentStreak { get; set; }
    public decimal Progress => CalculateProgress();

    private decimal CalculateProgress()
    {
        if (IsCompleted) return 100;

        if (TargetDuration.HasValue && ActualDuration.HasValue)
        {
            return Math.Min(100, (decimal)(ActualDuration.Value.TotalMinutes / TargetDuration.Value.TotalMinutes) * 100);
        }

        if (TargetCount.HasValue && ActualCount.HasValue)
        {
            return Math.Min(100, (decimal)ActualCount.Value / TargetCount.Value * 100);
        }

        return 0;
    }
}

public class HabitTrend
{
    public Guid HabitId { get; set; }
    public string HabitName { get; set; } = string.Empty;
    public TrendDirection Direction { get; set; }
    public decimal ChangePercentage { get; set; }
    public string Description { get; set; } = string.Empty;
    public List<HabitTrendDataPoint> DataPoints { get; set; } = new();
}

public class HabitTrendDataPoint
{
    public DateTime Date { get; set; }
    public decimal Value { get; set; }
    public string Metric { get; set; } = string.Empty; // "completion_rate", "streak", "duration", etc.
}

public class HabitCorrelationReport
{
    public string UserId { get; set; } = string.Empty;
    public List<HabitCorrelation> Correlations { get; set; } = new();
    public List<HabitImpactAnalysis> ImpactAnalyses { get; set; } = new();
    public string Summary { get; set; } = string.Empty;
}

public class HabitCorrelation
{
    public Guid Habit1Id { get; set; }
    public string Habit1Name { get; set; } = string.Empty;
    public Guid Habit2Id { get; set; }
    public string Habit2Name { get; set; } = string.Empty;
    public decimal CorrelationCoefficient { get; set; } // -1 to 1
    public CorrelationType Type { get; set; }
    public string Description { get; set; } = string.Empty;
}

public class HabitImpactAnalysis
{
    public Guid HabitId { get; set; }
    public string HabitName { get; set; } = string.Empty;
    public decimal MoodImpact { get; set; } // How this habit affects mood
    public decimal EnergyImpact { get; set; } // How this habit affects energy
    public decimal ProductivityImpact { get; set; } // How this habit affects productivity
    public string Summary { get; set; } = string.Empty;
}

public class HabitPrediction
{
    public Guid HabitId { get; set; }
    public string HabitName { get; set; } = string.Empty;
    public decimal SuccessProbability { get; set; } // 0-100
    public PredictionTimeframe Timeframe { get; set; }
    public List<string> SuccessFactors { get; set; } = new();
    public List<string> RiskFactors { get; set; } = new();
    public List<string> Recommendations { get; set; } = new();
}

public class Achievement
{
    public Guid Id { get; set; }
    public string Title { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public AchievementType Type { get; set; }
    public int Points { get; set; }
    public DateTime EarnedDate { get; set; }
    public Guid? RelatedHabitId { get; set; }
    public string? RelatedHabitName { get; set; }
    public string Icon { get; set; } = string.Empty;
    public AchievementRarity Rarity { get; set; }
}

public class Badge
{
    public Guid Id { get; set; }
    public string Name { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public string Icon { get; set; } = string.Empty;
    public BadgeCategory Category { get; set; }
    public List<BadgeRequirement> Requirements { get; set; } = new();
    public bool IsEarned { get; set; }
    public DateTime? EarnedDate { get; set; }
    public int Points { get; set; }
}

public class BadgeRequirement
{
    public string Description { get; set; } = string.Empty;
    public bool IsMet { get; set; }
    public decimal Progress { get; set; } // 0-100
    public string? ProgressDescription { get; set; }
}

public class UserGamificationProfile
{
    public string UserId { get; set; } = string.Empty;
    public int TotalPoints { get; set; }
    public int Level { get; set; }
    public decimal ExperienceToNextLevel { get; set; }
    public List<Achievement> RecentAchievements { get; set; } = new();
    public List<Badge> EarnedBadges { get; set; } = new();
    public HabitGamificationStats Stats { get; set; } = new();
    public List<string> UnlockedFeatures { get; set; } = new();
}

public class HabitGamificationStats
{
    public int TotalHabitsCreated { get; set; }
    public int TotalDaysTracked { get; set; }
    public int LongestOverallStreak { get; set; }
    public int PerfectWeeks { get; set; } // Weeks where all habits were completed
    public int PerfectMonths { get; set; }
    public TimeSpan TotalTimeInvested { get; set; }
    public Dictionary<string, int> CategoryStats { get; set; } = new();
}

public class MotivationalMessage
{
    public string Message { get; set; } = string.Empty;
    public MotivationTrigger Trigger { get; set; }
    public MessageType Type { get; set; }
    public DateTime RelevantDate { get; set; }
    public Guid? RelatedHabitId { get; set; }
    public string? ActionPrompt { get; set; }
}

// Enums
public enum TrendDirection
{
    Improving,
    Declining,
    Stable,
    Volatile
}

public enum CorrelationType
{
    Positive,    // When one habit goes up, the other goes up
    Negative,    // When one habit goes up, the other goes down
    NoCorrelation
}

public enum PredictionTimeframe
{
    NextWeek,
    NextMonth,
    NextQuarter
}

public enum AchievementType
{
    Streak,
    Consistency,
    Milestone,
    Improvement,
    Dedication,
    Challenge,
    Social
}

public enum AchievementRarity
{
    Common,
    Uncommon,
    Rare,
    Epic,
    Legendary
}

public enum BadgeCategory
{
    Consistency,
    Streaks,
    Variety,
    Dedication,
    Improvement,
    Milestones,
    Social,
    Special
}

public enum MotivationTrigger
{
    StreakBroken,
    MilestoneReached,
    LowMotivation,
    ConsistencyImproving,
    NewHabitStarted,
    DailyReminder,
    WeeklyReview
}

public enum MessageType
{
    Encouragement,
    Celebration,
    Gentle_Push,
    Educational,
    Challenge,
    Reminder
}

public class UserHabitPatterns
{
    public string UserId { get; set; } = string.Empty;
    public List<HabitPattern> Patterns { get; set; } = new();
    public Dictionary<DayOfWeek, decimal> CompletionRateByDay { get; set; } = new();
    public Dictionary<string, decimal> CompletionRateByCategory { get; set; } = new();
    public List<HabitInsight> Insights { get; set; } = new();
    public DateTime AnalyzedAt { get; set; }
}

public class HabitPattern
{
    public string HabitName { get; set; } = string.Empty;
    public string PatternType { get; set; } = string.Empty;
    public decimal Strength { get; set; }
    public string Description { get; set; } = string.Empty;
    public List<string> TriggerEvents { get; set; } = new();
}

public class HabitInsight
{
    public string HabitName { get; set; } = string.Empty;
    public string Insight { get; set; } = string.Empty;
    public InsightType Type { get; set; }
    public decimal Confidence { get; set; }
    public List<string> Recommendations { get; set; } = new();
}

// Fixing duplicate class definitions
public class HabitInsights
{
    public Guid HabitId { get; set; }
    public string HabitName { get; set; } = string.Empty;
    public decimal CompletionRate { get; set; }
    public int CurrentStreak { get; set; }
    public int LongestStreak { get; set; }
    public TrendDirection Trend { get; set; }
    public List<string> Insights { get; set; } = new();
    public List<string> Recommendations { get; set; } = new();
}