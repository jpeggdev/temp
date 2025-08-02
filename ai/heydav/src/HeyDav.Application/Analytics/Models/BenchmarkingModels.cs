using HeyDav.Domain.Analytics.Enums;

namespace HeyDav.Application.Analytics.Models;

public class BenchmarkData
{
    public string MetricName { get; set; } = string.Empty;
    public string BenchmarkType { get; set; } = string.Empty;
    public decimal Value { get; set; }
    public string Unit { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public DateTime LastUpdated { get; set; }
    public Dictionary<string, object> Metadata { get; set; } = new();
}

public class BenchmarkComparison
{
    public string UserId { get; set; } = string.Empty;
    public string MetricName { get; set; } = string.Empty;
    public decimal UserValue { get; set; }
    public decimal BenchmarkValue { get; set; }
    public decimal Percentile { get; set; }
    public string ComparisonType { get; set; } = string.Empty;
    public string Performance { get; set; } = string.Empty; // Above/Below/At benchmark
    public decimal Deviation { get; set; }
    public string Recommendations { get; set; } = string.Empty;
    public DateTime ComparedAt { get; set; }
}

public class PeerBenchmarkData
{
    public string MetricName { get; set; } = string.Empty;
    public decimal Mean { get; set; }
    public decimal Median { get; set; }
    public decimal P25 { get; set; }
    public decimal P75 { get; set; }
    public decimal P90 { get; set; }
    public int SampleSize { get; set; }
    public DateTime CalculatedAt { get; set; }
}

public class TeamBenchmarkData
{
    public string TeamId { get; set; } = string.Empty;
    public string MetricName { get; set; } = string.Empty;
    public decimal TeamAverage { get; set; }
    public decimal TeamMedian { get; set; }
    public List<TeamMemberBenchmark> Members { get; set; } = new();
    public DateTime CalculatedAt { get; set; }
}

public class TeamMemberBenchmark
{
    public string UserId { get; set; } = string.Empty;
    public string UserName { get; set; } = string.Empty;
    public decimal Value { get; set; }
    public decimal TeamRank { get; set; }
}

public class AnonymousBenchmarkData
{
    public Dictionary<string, StatisticalSummary> Metrics { get; set; } = new();
    public Dictionary<string, object> FilterCriteria { get; set; } = new();
    public int SampleSize { get; set; }
    public DateTime CalculatedAt { get; set; }
}

public class StatisticalSummary
{
    public decimal Mean { get; set; }
    public decimal Median { get; set; }
    public decimal StandardDeviation { get; set; }
    public decimal Min { get; set; }
    public decimal Max { get; set; }
    public decimal P25 { get; set; }
    public decimal P75 { get; set; }
    public decimal P90 { get; set; }
    public decimal P95 { get; set; }
}

public class GoalBenchmarkData
{
    public string GoalType { get; set; } = string.Empty;
    public Dictionary<string, decimal> TypicalCompletionTimes { get; set; } = new();
    public Dictionary<string, decimal> SuccessRates { get; set; } = new();
    public Dictionary<string, List<string>> CommonMilestones { get; set; } = new();
    public DateTime LastUpdated { get; set; }
}

public class BenchmarkTrend
{
    public string BenchmarkType { get; set; } = string.Empty;
    public string MetricName { get; set; } = string.Empty;
    public List<TrendDataPoint> TrendData { get; set; } = new();
    public TrendDirection Direction { get; set; }
    public decimal TrendStrength { get; set; }
    public DateTime AnalyzedAt { get; set; }
}

public class TrendDataPoint
{
    public DateTime Date { get; set; }
    public decimal Value { get; set; }
}

public class BenchmarkInsight
{
    public string Title { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public string MetricName { get; set; } = string.Empty;
    public InsightType Type { get; set; }
    public InsightPriority Priority { get; set; }
    public List<string> ActionableSteps { get; set; } = new();
    public decimal PotentialImprovement { get; set; }
    public DateTime GeneratedAt { get; set; }
}