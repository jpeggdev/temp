using HeyDav.Domain.Analytics.Enums;
using HeyDav.Domain.Analytics.ValueObjects;

namespace HeyDav.Application.Analytics.Models;

public class ActionableRecommendation
{
    public Guid Id { get; set; }
    public string Title { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public string Category { get; set; } = string.Empty;
    public InsightPriority Priority { get; set; }
    public decimal PotentialImpact { get; set; } // 0-100
    public decimal ImplementationDifficulty { get; set; } // 0-100
    public decimal ConfidenceScore { get; set; } // 0-100
    public List<string> ActionSteps { get; set; } = new();
    public List<string> Benefits { get; set; } = new();
    public List<string> Risks { get; set; } = new();
    public TimeSpan EstimatedTimeToImplement { get; set; }
    public TimeSpan ExpectedResultsTimeframe { get; set; }
    public Dictionary<string, object> SupportingData { get; set; } = new();
    public DateTime GeneratedAt { get; set; }
    public DateTime? AcceptedAt { get; set; }
    public DateTime? CompletedAt { get; set; }
    public string? UserFeedback { get; set; }
    public decimal? ActualImpact { get; set; }
}

public class ImprovementSuggestion
{
    public Guid Id { get; set; }
    public string Area { get; set; } = string.Empty;
    public string Current { get; set; } = string.Empty;
    public string Suggested { get; set; } = string.Empty;
    public string Reasoning { get; set; } = string.Empty;
    public decimal ImpactScore { get; set; } // 0-100
    public decimal Feasibility { get; set; } // 0-100
    public List<string> Prerequisites { get; set; } = new();
    public List<string> Steps { get; set; } = new();
    public List<string> Metrics { get; set; } = new();
    public Dictionary<string, decimal> ExpectedMetricImpacts { get; set; } = new();
    public DateTime GeneratedAt { get; set; }
}

public class OptimizationOpportunity
{
    public Guid Id { get; set; }
    public string Name { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public string Type { get; set; } = string.Empty; // "Time", "Energy", "Focus", "Quality"
    public decimal PotentialGain { get; set; }
    public string GainMetric { get; set; } = string.Empty; // "hours/week", "% improvement"
    public decimal EffortRequired { get; set; } // 0-100
    public decimal ROI { get; set; } // Return on Investment
    public List<string> RequiredChanges { get; set; } = new();
    public List<string> Constraints { get; set; } = new();
    public OpportunityFeasibility Feasibility { get; set; } = new();
    public Dictionary<string, object> AnalysisData { get; set; } = new();
    public DateTime IdentifiedAt { get; set; }
}

public class OpportunityFeasibility
{
    public decimal Technical { get; set; } // 0-100
    public decimal Financial { get; set; } // 0-100
    public decimal Time { get; set; } // 0-100
    public decimal Personal { get; set; } // 0-100
    public decimal Overall => (Technical + Financial + Time + Personal) / 4;
}

public class BehavioralPattern
{
    public Guid Id { get; set; }
    public string Name { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public string Category { get; set; } = string.Empty;
    public decimal Strength { get; set; } // 0-100, how consistent the pattern is
    public decimal Confidence { get; set; } // 0-100, confidence in the pattern
    public List<PatternOccurrence> Occurrences { get; set; } = new();
    public Dictionary<string, object> PatternData { get; set; } = new();
    public List<string> Triggers { get; set; } = new();
    public List<string> Outcomes { get; set; } = new();
    public PatternTrend Trend { get; set; } = new();
    public DateTime FirstDetected { get; set; }
    public DateTime LastUpdated { get; set; }
}

public class PatternOccurrence
{
    public DateTime Timestamp { get; set; }
    public string Context { get; set; } = string.Empty;
    public Dictionary<string, decimal> Metrics { get; set; } = new();
    public decimal Strength { get; set; }
}

public class PatternTrend
{
    public TrendDirection Direction { get; set; }
    public decimal ChangeRate { get; set; }
    public string Description { get; set; } = string.Empty;
}

public class TemporalPattern
{
    public Guid Id { get; set; }
    public string Name { get; set; } = string.Empty;
    public string TimeScale { get; set; } = string.Empty; // "Hourly", "Daily", "Weekly", "Monthly"
    public Dictionary<string, decimal> TimePoints { get; set; } = new(); // Time -> Value
    public decimal Regularity { get; set; } // 0-100
    public decimal Predictability { get; set; } // 0-100
    public List<TemporalPeak> Peaks { get; set; } = new();
    public List<TemporalPeak> Valleys { get; set; } = new();
    public string Description { get; set; } = string.Empty;
    public DateTime DetectedAt { get; set; }
}

public class TemporalPeak
{
    public string TimePoint { get; set; } = string.Empty;
    public decimal Value { get; set; }
    public decimal Significance { get; set; }
    public string Type { get; set; } = string.Empty; // "Peak" or "Valley"
}

public class CorrelationPattern
{
    public Guid Id { get; set; }
    public string Metric1 { get; set; } = string.Empty;
    public string Metric2 { get; set; } = string.Empty;
    public decimal CorrelationCoefficient { get; set; }
    public string CorrelationType { get; set; } = string.Empty; // "Positive", "Negative", "Non-linear"
    public decimal Strength { get; set; } // 0-100
    public decimal Significance { get; set; } // Statistical significance
    public List<CorrelationPoint> DataPoints { get; set; } = new();
    public string Description { get; set; } = string.Empty;
    public List<string> PossibleCauses { get; set; } = new();
    public DateTime DetectedAt { get; set; }
}

public class CorrelationPoint
{
    public DateTime Timestamp { get; set; }
    public decimal Metric1Value { get; set; }
    public decimal Metric2Value { get; set; }
    public string? Context { get; set; }
}

public class AnomalyDetection
{
    public Guid Id { get; set; }
    public string MetricName { get; set; } = string.Empty;
    public DateTime Timestamp { get; set; }
    public decimal ActualValue { get; set; }
    public decimal ExpectedValue { get; set; }
    public decimal Deviation { get; set; }
    public decimal Severity { get; set; } // 0-100
    public string AnomalyType { get; set; } = string.Empty; // "Outlier", "Trend Break", "Contextual"
    public string DetectionMethod { get; set; } = string.Empty;
    public decimal Confidence { get; set; } // 0-100
    public List<string> PossibleCauses { get; set; } = new();
    public List<string> RecommendedActions { get; set; } = new();
    public Dictionary<string, object> ContextData { get; set; } = new();
    public bool IsResolved { get; set; }
    public DateTime DetectedAt { get; set; }
}

public class InsightFeedback
{
    public Guid InsightId { get; set; }
    public string UserId { get; set; } = string.Empty;
    public bool IsHelpful { get; set; }
    public bool IsActionable { get; set; }
    public bool IsAccurate { get; set; }
    public int Relevance { get; set; } // 1-5 scale
    public int Clarity { get; set; } // 1-5 scale
    public string? Comments { get; set; }
    public List<string> Tags { get; set; } = new();
    public DateTime ProvidedAt { get; set; }
}

public class RecommendationFeedback
{
    public Guid RecommendationId { get; set; }
    public string UserId { get; set; } = string.Empty;
    public bool WasImplemented { get; set; }
    public int Effectiveness { get; set; } // 1-5 scale
    public int Feasibility { get; set; } // 1-5 scale
    public decimal? ActualImpact { get; set; }
    public TimeSpan? TimeToImplement { get; set; }
    public List<string> Challenges { get; set; } = new();
    public List<string> UnexpectedBenefits { get; set; } = new();
    public string? Comments { get; set; }
    public DateTime ProvidedAt { get; set; }
}

public class RecommendationEffectiveness
{
    public string UserId { get; set; } = string.Empty;
    public int TotalRecommendations { get; set; }
    public int ImplementedRecommendations { get; set; }
    public decimal ImplementationRate { get; set; }
    public decimal AverageEffectiveness { get; set; }
    public decimal AverageImpact { get; set; }
    public Dictionary<string, RecommendationCategoryStats> CategoryStats { get; set; } = new();
    public List<TopRecommendation> TopRecommendations { get; set; } = new();
    public List<string> ImprovementAreas { get; set; } = new();
    public DateTime AnalyzedAt { get; set; }
}

public class RecommendationCategoryStats
{
    public string Category { get; set; } = string.Empty;
    public int Total { get; set; }
    public int Implemented { get; set; }
    public decimal ImplementationRate { get; set; }
    public decimal AverageEffectiveness { get; set; }
    public decimal AverageImpact { get; set; }
}

public class TopRecommendation
{
    public Guid Id { get; set; }
    public string Title { get; set; } = string.Empty;
    public string Category { get; set; } = string.Empty;
    public decimal Effectiveness { get; set; }
    public decimal Impact { get; set; }
    public DateTime ImplementedAt { get; set; }
}

public class PersonalizationProfile
{
    public string UserId { get; set; } = string.Empty;
    public Dictionary<string, decimal> InsightTypePreferences { get; set; } = new();
    public Dictionary<string, decimal> CategoryInterests { get; set; } = new();
    public List<string> PreferredInsightFormats { get; set; } = new();
    public List<string> PreferredActionTypes { get; set; } = new();
    public int DetailLevel { get; set; } // 1-5, 1=concise, 5=detailed
    public int FrequencyPreference { get; set; } // How often to receive insights
    public List<string> DislikedCategories { get; set; } = new();
    public decimal ImplementationCapacity { get; set; } // How many recommendations user typically implements
    public Dictionary<string, decimal> TopicExpertise { get; set; } = new();
    public PersonalizationMetrics Metrics { get; set; } = new();
    public DateTime LastUpdated { get; set; }
}

public class PersonalizationMetrics
{
    public decimal EngagementRate { get; set; }
    public decimal ImplementationRate { get; set; }
    public decimal SatisfactionScore { get; set; }
    public int TotalInteractions { get; set; }
    public DateTime LastInteraction { get; set; }
}

public class InsightInteraction
{
    public Guid InsightId { get; set; }
    public string UserId { get; set; } = string.Empty;
    public string InteractionType { get; set; } = string.Empty; // "View", "Save", "Implement", "Dismiss", "Share"
    public TimeSpan TimeSpent { get; set; }
    public Dictionary<string, object> InteractionData { get; set; } = new();
    public DateTime Timestamp { get; set; }
}

public class PersonalizationEffectiveness
{
    public string UserId { get; set; } = string.Empty;
    public decimal OverallEffectiveness { get; set; }
    public decimal InsightRelevance { get; set; }
    public decimal RecommendationAccuracy { get; set; }
    public decimal UserSatisfaction { get; set; }
    public PersonalizationImprovements Improvements { get; set; } = new();
    public List<PersonalizationInsight> Insights { get; set; } = new();
    public DateTime MeasuredAt { get; set; }
}

public class PersonalizationImprovements
{
    public List<string> StrengthAreas { get; set; } = new();
    public List<string> ImprovementAreas { get; set; } = new();
    public List<string> SuggestedActions { get; set; } = new();
}

public class PersonalizationInsight
{
    public string Area { get; set; } = string.Empty;
    public string Insight { get; set; } = string.Empty;
    public decimal Impact { get; set; }
    public List<string> SuggestedAdjustments { get; set; } = new();
}

public class InsightPreferences
{
    public string UserId { get; set; } = string.Empty;
    public List<InsightType> PreferredTypes { get; set; } = new();
    public List<InsightPriority> PreferredPriorities { get; set; } = new();
    public List<string> PreferredCategories { get; set; } = new();
    public int MaxInsightsPerDay { get; set; } = 5;
    public bool ReceivePredictiveInsights { get; set; } = true;
    public bool ReceiveRealTimeInsights { get; set; } = true;
    public bool ReceiveWeeklyDigest { get; set; } = true;
    public List<string> NotificationChannels { get; set; } = new();
    public Dictionary<string, bool> TopicSubscriptions { get; set; } = new();
    public DateTime LastUpdated { get; set; }
}

public class InsightCategory
{
    public string Name { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public string Icon { get; set; } = string.Empty;
    public decimal UserInterest { get; set; } // 0-100
    public int InsightCount { get; set; }
    public DateTime LastInsight { get; set; }
    public bool IsSubscribed { get; set; }
    public List<string> RelatedCategories { get; set; } = new();
}

public class InsightDeliveryContext
{
    public string UserId { get; set; } = string.Empty;
    public DateTime DeliveryTime { get; set; }
    public string Channel { get; set; } = string.Empty; // "InApp", "Email", "Push"
    public string UserContext { get; set; } = string.Empty; // "Working", "Planning", "Reviewing"
    public Dictionary<string, object> ContextData { get; set; } = new();
    public List<string> RecentActivities { get; set; } = new();
}

public class InsightTemplate
{
    public string Name { get; set; } = string.Empty;
    public string TitleTemplate { get; set; } = string.Empty;
    public string DescriptionTemplate { get; set; } = string.Empty;
    public InsightType Type { get; set; }
    public List<string> RequiredDataPoints { get; set; } = new();
    public Dictionary<string, object> TemplateParameters { get; set; } = new();
    public List<string> VariationTemplates { get; set; } = new();
}

public class InsightGenerationContext
{
    public string UserId { get; set; } = string.Empty;
    public DateTime RequestTime { get; set; }
    public List<string> RequestedTypes { get; set; } = new();
    public Dictionary<string, object> UserState { get; set; } = new();
    public List<ProductivityInsight> RecentInsights { get; set; } = new();
    public PersonalizationProfile PersonalizationProfile { get; set; } = new();
    public Dictionary<string, decimal> AvailableMetrics { get; set; } = new();
}

public class InsightQualityMetrics
{
    public Guid InsightId { get; set; }
    public decimal Relevance { get; set; } // 0-100
    public decimal Accuracy { get; set; } // 0-100
    public decimal Novelty { get; set; } // 0-100
    public decimal Actionability { get; set; } // 0-100
    public decimal Clarity { get; set; } // 0-100
    public decimal OverallQuality => (Relevance + Accuracy + Novelty + Actionability + Clarity) / 5;
    public List<string> QualityFactors { get; set; } = new();
    public DateTime EvaluatedAt { get; set; }
}

public class InsightImpactMeasurement
{
    public Guid InsightId { get; set; }
    public string UserId { get; set; } = string.Empty;
    public Dictionary<string, decimal> BaselineMetrics { get; set; } = new();
    public Dictionary<string, decimal> PostInsightMetrics { get; set; } = new();
    public Dictionary<string, decimal> MetricChanges { get; set; } = new();
    public decimal OverallImpact { get; set; }
    public bool PositiveImpact { get; set; }
    public List<string> ObservedChanges { get; set; } = new();
    public TimeSpan MeasurementPeriod { get; set; }
    public DateTime MeasuredAt { get; set; }
}

public class InsightA_BTestResult
{
    public string TestName { get; set; } = string.Empty;
    public string VariantA { get; set; } = string.Empty;
    public string VariantB { get; set; } = string.Empty;
    public int ParticipantsA { get; set; }
    public int ParticipantsB { get; set; }
    public Dictionary<string, decimal> MetricsA { get; set; } = new();
    public Dictionary<string, decimal> MetricsB { get; set; } = new(); 
    public decimal StatisticalSignificance { get; set; }
    public string WinningVariant { get; set; } = string.Empty;
    public List<string> KeyFindings { get; set; } = new();
    public DateTime TestCompletedAt { get; set; }
}

public class InsightExperiment
{
    public string Name { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public string Hypothesis { get; set; } = string.Empty;
    public List<string> Variants { get; set; } = new();
    public List<string> SuccessMetrics { get; set; } = new();
    public DateTime StartDate { get; set; }
    public DateTime? EndDate { get; set; }
    public Dictionary<string, object> Results { get; set; } = new();
    public bool IsCompleted { get; set; }
}

public class InsightEngagementMetrics
{
    public string UserId { get; set; } = string.Empty;
    public int TotalInsightsReceived { get; set; }
    public int InsightsViewed { get; set; }
    public int InsightsActedUpon { get; set; }
    public int InsightsShared { get; set; }
    public int InsightsSaved { get; set; }
    public decimal ViewRate => TotalInsightsReceived > 0 ? (decimal)InsightsViewed / TotalInsightsReceived : 0;
    public decimal ActionRate => InsightsViewed > 0 ? (decimal)InsightsActedUpon / InsightsViewed : 0;
    public decimal ShareRate => InsightsViewed > 0 ? (decimal)InsightsShared / InsightsViewed : 0;
    public decimal SaveRate => InsightsViewed > 0 ? (decimal)InsightsSaved / InsightsViewed : 0;
    public TimeSpan AverageEngagementTime { get; set; }
    public List<string> TopEngagedCategories { get; set; } = new();
    public DateTime LastEngagement { get; set; }
}

public class InsightContentStrategy
{
    public string UserId { get; set; } = string.Empty;
    public Dictionary<string, decimal> ContentMix { get; set; } = new(); // Type -> Percentage
    public int OptimalFrequency { get; set; }
    public List<string> PreferredDeliveryTimes { get; set; } = new();
    public List<string> HighPerformingTemplates { get; set; } = new();
    public List<string> ContentThemes { get; set; } = new();
    public Dictionary<string, string> PersonalizationTokens { get; set; } = new();
    public DateTime LastOptimized { get; set; }
}

public class ProductivityInsight
{
    public Guid Id { get; set; }
    public string Title { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public InsightType Type { get; set; }
    public InsightPriority Priority { get; set; }
    public bool IsActionable { get; set; }
    public string? RecommendedAction { get; set; }
    public decimal ConfidenceScore { get; set; }
    public string Source { get; set; } = string.Empty;
    public List<string> SupportingData { get; set; } = new();
    public List<string> RelatedMetrics { get; set; } = new();
    public DateTime GeneratedAt { get; set; }
    public DateTime? ExpiresAt { get; set; }
    public bool IsViewed { get; set; }
    public bool IsActedUpon { get; set; }
    public DateTime? ViewedAt { get; set; }
    public DateTime? ActedUponAt { get; set; }

    public ProductivityInsight() { }

    public ProductivityInsight(string title, string description, InsightType type, InsightPriority priority, 
        bool isActionable = false, string? recommendedAction = null, decimal confidenceScore = 0, string source = "")
    {
        Id = Guid.NewGuid();
        Title = title;
        Description = description;
        Type = type;
        Priority = priority;
        IsActionable = isActionable;
        RecommendedAction = recommendedAction;
        ConfidenceScore = confidenceScore;
        Source = source;
        GeneratedAt = DateTime.UtcNow;
    }
}