using HeyDav.Domain.Common.Base;
using HeyDav.Domain.Analytics.Enums;

namespace HeyDav.Domain.Analytics.ValueObjects;

public class SessionMetrics : ValueObject
{
    public int TasksCompleted { get; }
    public TimeSpan FocusTime { get; }
    public int InterruptionCount { get; }
    public int ContextSwitches { get; }
    public TimeSpan DeepWorkTime { get; }
    public Dictionary<string, decimal> CustomMetrics { get; }

    private SessionMetrics(
        int tasksCompleted,
        TimeSpan focusTime,
        int interruptionCount,
        int contextSwitches,
        TimeSpan deepWorkTime,
        Dictionary<string, decimal>? customMetrics = null)
    {
        TasksCompleted = tasksCompleted;
        FocusTime = focusTime;
        InterruptionCount = interruptionCount;
        ContextSwitches = contextSwitches;
        DeepWorkTime = deepWorkTime;
        CustomMetrics = customMetrics ?? new Dictionary<string, decimal>();
    }

    public static SessionMetrics Empty()
    {
        return new SessionMetrics(0, TimeSpan.Zero, 0, 0, TimeSpan.Zero);
    }

    public static SessionMetrics Create(
        int tasksCompleted,
        TimeSpan focusTime,
        int interruptionCount,
        int contextSwitches,
        TimeSpan deepWorkTime,
        Dictionary<string, decimal>? customMetrics = null)
    {
        return new SessionMetrics(tasksCompleted, focusTime, interruptionCount, contextSwitches, deepWorkTime, customMetrics);
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return TasksCompleted;
        yield return FocusTime;
        yield return InterruptionCount;
        yield return ContextSwitches;
        yield return DeepWorkTime;
        foreach (var metric in CustomMetrics.OrderBy(x => x.Key))
        {
            yield return metric;
        }
    }
}

public class ProductivityScoreCard : ValueObject
{
    public decimal OverallScore { get; }
    public decimal TaskCompletionScore { get; }
    public decimal TimeManagementScore { get; }
    public decimal FocusScore { get; }
    public decimal EnergyScore { get; }
    public decimal GoalProgressScore { get; }
    public decimal HabitConsistencyScore { get; }
    public decimal WellbeingScore { get; }
    public DateTime CalculatedAt { get; }

    private ProductivityScoreCard(
        decimal overallScore,
        decimal taskCompletionScore,
        decimal timeManagementScore,
        decimal focusScore,
        decimal energyScore,
        decimal goalProgressScore,
        decimal habitConsistencyScore,
        decimal wellbeingScore)
    {
        OverallScore = overallScore;
        TaskCompletionScore = taskCompletionScore;
        TimeManagementScore = timeManagementScore;
        FocusScore = focusScore;
        EnergyScore = energyScore;
        GoalProgressScore = goalProgressScore;
        HabitConsistencyScore = habitConsistencyScore;
        WellbeingScore = wellbeingScore;
        CalculatedAt = DateTime.UtcNow;
    }

    public static ProductivityScoreCard Empty()
    {
        return new ProductivityScoreCard(0, 0, 0, 0, 0, 0, 0, 0);
    }

    public static ProductivityScoreCard Create(
        decimal taskCompletionScore,
        decimal timeManagementScore,
        decimal focusScore,
        decimal energyScore,
        decimal goalProgressScore,
        decimal habitConsistencyScore,
        decimal wellbeingScore)
    {
        // Validate scores are within range
        var scores = new[] { taskCompletionScore, timeManagementScore, focusScore, energyScore, 
                           goalProgressScore, habitConsistencyScore, wellbeingScore };
        
        if (scores.Any(s => s < 0 || s > 100))
            throw new ArgumentOutOfRangeException("All scores must be between 0 and 100");

        // Calculate weighted overall score
        var overallScore = (taskCompletionScore * 0.2m + timeManagementScore * 0.15m + 
                           focusScore * 0.15m + energyScore * 0.1m + goalProgressScore * 0.2m + 
                           habitConsistencyScore * 0.1m + wellbeingScore * 0.1m);

        return new ProductivityScoreCard(overallScore, taskCompletionScore, timeManagementScore,
                                       focusScore, energyScore, goalProgressScore, habitConsistencyScore, wellbeingScore);
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return OverallScore;
        yield return TaskCompletionScore;
        yield return TimeManagementScore;
        yield return FocusScore;
        yield return EnergyScore;
        yield return GoalProgressScore;
        yield return HabitConsistencyScore;
        yield return WellbeingScore;
        yield return CalculatedAt.Date;
    }
}

public class ProductivityInsight : ValueObject
{
    public Guid Id { get; }
    public string Title { get; }
    public string Description { get; }
    public InsightType Type { get; }
    public InsightPriority Priority { get; }
    public bool IsActionable { get; }
    public string? RecommendedAction { get; }
    public Dictionary<string, object> Data { get; }
    public DateTime GeneratedAt { get; }
    public decimal ConfidenceScore { get; } // 0-100
    public string? Source { get; }

    public ProductivityInsight(
        string title,
        string description,
        InsightType type,
        InsightPriority priority,
        bool isActionable = false,
        string? recommendedAction = null,
        Dictionary<string, object>? data = null,
        decimal confidenceScore = 80,
        string? source = null)
    {
        if (string.IsNullOrWhiteSpace(title))
            throw new ArgumentException("Title cannot be empty", nameof(title));
        
        if (string.IsNullOrWhiteSpace(description))
            throw new ArgumentException("Description cannot be empty", nameof(description));
        
        if (confidenceScore < 0 || confidenceScore > 100)
            throw new ArgumentOutOfRangeException(nameof(confidenceScore), "Confidence score must be between 0 and 100");

        Id = Guid.NewGuid();
        Title = title;
        Description = description;
        Type = type;
        Priority = priority;
        IsActionable = isActionable;
        RecommendedAction = recommendedAction;
        Data = data ?? new Dictionary<string, object>();
        GeneratedAt = DateTime.UtcNow;
        ConfidenceScore = confidenceScore;
        Source = source;
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return Id;
        yield return Title;
        yield return Description;
        yield return Type;
        yield return Priority;
        yield return IsActionable;
        yield return RecommendedAction ?? "";
        yield return GeneratedAt;
        yield return ConfidenceScore;
        yield return Source ?? "";
        foreach (var item in Data.OrderBy(x => x.Key))
        {
            yield return item;
        }
    }
}

public class PerformanceMetric : ValueObject
{
    public string Name { get; }
    public decimal Value { get; }
    public string Unit { get; }
    public MetricCategory Category { get; }
    public DateTime MeasuredAt { get; }
    public decimal? Target { get; }
    public decimal? Benchmark { get; }
    public TrendDirection Trend { get; }
    public string? Description { get; }

    public PerformanceMetric(
        string name,
        decimal value,
        string unit,
        MetricCategory category,
        decimal? target = null,
        decimal? benchmark = null,
        TrendDirection trend = TrendDirection.Stable,
        string? description = null)
    {
        if (string.IsNullOrWhiteSpace(name))
            throw new ArgumentException("Name cannot be empty", nameof(name));
        
        if (string.IsNullOrWhiteSpace(unit))
            throw new ArgumentException("Unit cannot be empty", nameof(unit));

        Name = name;
        Value = value;
        Unit = unit;
        Category = category;
        MeasuredAt = DateTime.UtcNow;
        Target = target;
        Benchmark = benchmark;
        Trend = trend;
        Description = description;
    }

    public decimal? GetPerformanceVsTarget()
    {
        if (!Target.HasValue) return null;
        return Target.Value == 0 ? null : (Value / Target.Value) * 100;
    }

    public decimal? GetPerformanceVsBenchmark()
    {
        if (!Benchmark.HasValue) return null;
        return Benchmark.Value == 0 ? null : (Value / Benchmark.Value) * 100;
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return Name;
        yield return Value;
        yield return Unit;
        yield return Category;
        yield return MeasuredAt.Date;
        yield return Target ?? 0;
        yield return Benchmark ?? 0;
        yield return Trend;
        yield return Description ?? "";
    }
}

public class ProductivityTrend : ValueObject
{
    public string MetricName { get; }
    public TrendDirection Direction { get; }
    public decimal Magnitude { get; } // Percentage change
    public TimeSpan Period { get; }
    public decimal Confidence { get; } // 0-100
    public DateTime AnalyzedAt { get; }
    public List<decimal> DataPoints { get; }

    public ProductivityTrend(
        string metricName,
        TrendDirection direction,
        decimal magnitude,
        TimeSpan period,
        decimal confidence,
        List<decimal>? dataPoints = null)
    {
        if (string.IsNullOrWhiteSpace(metricName))
            throw new ArgumentException("Metric name cannot be empty", nameof(metricName));
        
        if (confidence < 0 || confidence > 100)
            throw new ArgumentOutOfRangeException(nameof(confidence), "Confidence must be between 0 and 100");

        MetricName = metricName;
        Direction = direction;
        Magnitude = magnitude;
        Period = period;
        Confidence = confidence;
        AnalyzedAt = DateTime.UtcNow;
        DataPoints = dataPoints ?? new List<decimal>();
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return MetricName;
        yield return Direction;
        yield return Magnitude;
        yield return Period;
        yield return Confidence;
        yield return AnalyzedAt.Date;
        foreach (var point in DataPoints)
        {
            yield return point;
        }
    }
}

public class BenchmarkData : ValueObject
{
    public BenchmarkType Type { get; }
    public string Name { get; }
    public string MetricName { get; }
    public decimal Value { get; }
    public string Unit { get; }
    public decimal Percentile { get; }
    public int SampleSize { get; }
    public DateTime LastUpdated { get; }
    public string? Source { get; }

    public BenchmarkData(
        BenchmarkType type,
        string name,
        string metricName,
        decimal value,
        string unit,
        decimal percentile,
        int sampleSize,
        string? source = null)
    {
        if (string.IsNullOrWhiteSpace(name))
            throw new ArgumentException("Name cannot be empty", nameof(name));
        
        if (string.IsNullOrWhiteSpace(metricName))
            throw new ArgumentException("Metric name cannot be empty", nameof(metricName));
        
        if (percentile < 0 || percentile > 100)
            throw new ArgumentOutOfRangeException(nameof(percentile), "Percentile must be between 0 and 100");

        Type = type;
        Name = name;
        MetricName = metricName;
        Value = value;
        Unit = unit;
        Percentile = percentile;
        SampleSize = sampleSize;
        LastUpdated = DateTime.UtcNow;
        Source = source;
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return Type;
        yield return Name;
        yield return MetricName;
        yield return Value;
        yield return Unit;
        yield return Percentile;
        yield return SampleSize;
        yield return LastUpdated.Date;
        yield return Source ?? "";
    }
}

public class VisualizationConfig : ValueObject
{
    public VisualizationType Type { get; }
    public string Title { get; }
    public Dictionary<string, object> Configuration { get; }
    public List<string> DataSeries { get; }
    public string? XAxis { get; }
    public string? YAxis { get; }
    public bool ShowLegend { get; }
    public bool ShowGrid { get; }

    public VisualizationConfig(
        VisualizationType type,
        string title,
        Dictionary<string, object>? configuration = null,
        List<string>? dataSeries = null,
        string? xAxis = null,
        string? yAxis = null,
        bool showLegend = true,
        bool showGrid = true)
    {
        if (string.IsNullOrWhiteSpace(title))
            throw new ArgumentException("Title cannot be empty", nameof(title));

        Type = type;
        Title = title;
        Configuration = configuration ?? new Dictionary<string, object>();
        DataSeries = dataSeries ?? new List<string>();
        XAxis = xAxis;
        YAxis = yAxis;
        ShowLegend = showLegend;
        ShowGrid = showGrid;
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return Type;
        yield return Title;
        yield return XAxis ?? "";
        yield return YAxis ?? "";
        yield return ShowLegend;
        yield return ShowGrid;
        foreach (var config in Configuration.OrderBy(x => x.Key))
        {
            yield return config;
        }
        foreach (var series in DataSeries.OrderBy(x => x))
        {
            yield return series;
        }
    }
}