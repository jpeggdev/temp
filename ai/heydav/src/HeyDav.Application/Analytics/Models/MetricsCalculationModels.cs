using HeyDav.Domain.Analytics.Enums;

namespace HeyDav.Application.Analytics.Models;

public class MetricStatistics
{
    public string MetricName { get; set; } = string.Empty;
    public decimal Mean { get; set; }
    public decimal Median { get; set; }
    public decimal StandardDeviation { get; set; }
    public decimal Min { get; set; }
    public decimal Max { get; set; }
    public decimal Sum { get; set; }
    public int Count { get; set; }
    public decimal Variance { get; set; }
    public decimal P25 { get; set; }
    public decimal P75 { get; set; }
    public decimal P90 { get; set; }
    public decimal P95 { get; set; }
    public DateTime CalculatedAt { get; set; }
}

public class MovingAverageData
{
    public string MetricName { get; set; } = string.Empty;
    public int WindowSize { get; set; }
    public Dictionary<DateTime, decimal> Values { get; set; } = new();
    public DateTime CalculatedAt { get; set; }
}

public class CorrelationMatrix
{
    public List<string> MetricNames { get; set; } = new();
    public Dictionary<string, Dictionary<string, decimal>> Matrix { get; set; } = new();
    public DateTime CalculatedAt { get; set; }
}

public class RegressionAnalysis
{
    public string DependentMetric { get; set; } = string.Empty;
    public List<string> IndependentMetrics { get; set; } = new();
    public Dictionary<string, decimal> Coefficients { get; set; } = new();
    public decimal RSquared { get; set; }
    public decimal AdjustedRSquared { get; set; }
    public decimal StandardError { get; set; }
    public List<string> SignificantPredictors { get; set; } = new();
    public string ModelEquation { get; set; } = string.Empty;
    public DateTime CalculatedAt { get; set; }
}

public class TrendAnalysisResult
{
    public string MetricName { get; set; } = string.Empty;
    public TrendDirection Direction { get; set; }
    public decimal Slope { get; set; }
    public decimal Strength { get; set; }
    public decimal Confidence { get; set; }
    public List<ChangePoint> ChangePoints { get; set; } = new();
    public string Description { get; set; } = string.Empty;
    public DateTime AnalyzedAt { get; set; }
}

public class ChangePoint
{
    public DateTime Date { get; set; }
    public decimal BeforeValue { get; set; }
    public decimal AfterValue { get; set; }
    public decimal Confidence { get; set; }
    public string Reason { get; set; } = string.Empty;
}

public class ForecastResult
{
    public string MetricName { get; set; } = string.Empty;
    public string ForecastMethod { get; set; } = string.Empty;
    public Dictionary<DateTime, decimal> ForecastValues { get; set; } = new();
    public Dictionary<DateTime, decimal> ConfidenceIntervalLower { get; set; } = new();
    public Dictionary<DateTime, decimal> ConfidenceIntervalUpper { get; set; } = new();
    public decimal Accuracy { get; set; }
    public string ModelParameters { get; set; } = string.Empty;
    public DateTime GeneratedAt { get; set; }
}

public class SeasonalDecomposition
{
    public string MetricName { get; set; } = string.Empty;
    public Dictionary<DateTime, decimal> TrendComponent { get; set; } = new();
    public Dictionary<DateTime, decimal> SeasonalComponent { get; set; } = new();
    public Dictionary<DateTime, decimal> ResidualComponent { get; set; } = new();
    public SeasonalityType SeasonalityType { get; set; }
    public int SeasonalPeriod { get; set; }
    public decimal SeasonalStrength { get; set; }
    public DateTime AnalyzedAt { get; set; }
}

public enum SeasonalityType
{
    None,
    Daily,
    Weekly,
    Monthly,
    Quarterly,
    Yearly
}

public enum DataAggregation
{
    Raw,
    Hourly,
    Daily,
    Weekly,
    Monthly,
    Quarterly,
    Yearly
}