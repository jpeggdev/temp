namespace HeyDav.Domain.Analytics.Enums;

public enum SessionType
{
    Work,
    Learning,
    Planning,
    Meeting,
    Break,
    Personal,
    Exercise,
    Other
}

public enum TimeTrackingSource
{
    Manual,
    Automatic,
    ApplicationTracking,
    WebsiteTracking,
    FileSystemTracking,
    CalendarIntegration,
    TaskIntegration
}

public enum ReportType
{
    Daily,
    Weekly,
    Monthly,
    Quarterly,
    Yearly,
    Custom,
    ProjectSummary,
    GoalProgress,
    HabitTracking
}

public enum ReportStatus
{
    Generating,
    Generated,
    Delivered,
    Archived,
    Error
}

public enum InsightType
{
    Pattern,
    Anomaly,
    Opportunity,
    Warning,
    Achievement,
    Recommendation,
    Trend,
    Benchmark,
    Prediction
}

public enum InsightPriority
{
    Low,
    Medium,
    High,
    Urgent
}

public enum TrendDirection
{
    Increasing,
    Decreasing,
    Stable,
    Volatile,
    Seasonal
}

public enum BenchmarkType
{
    Personal,
    Industry,
    Role,
    Team,
    Organization,
    Global
}

public enum VisualizationType
{
    LineChart,
    BarChart,
    PieChart,
    Heatmap,
    Scatter,
    Histogram,
    Treemap,
    Gauge,
    Timeline,
    Calendar
}

public enum DataAggregation
{
    Sum,
    Average,
    Count,
    Maximum,
    Minimum,
    Median,
    Percentile,
    StandardDeviation
}

public enum MetricCategory
{
    Productivity,
    Time,
    Quality,
    Efficiency,
    Wellbeing,
    Goals,
    Habits,
    Performance,
    Engagement
}

public enum PredictionType
{
    TaskCompletion,
    GoalAchievement,
    HabitConsistency,
    EnergyLevel,
    ProductivityScore,
    TimeAllocation,
    OptimalScheduling,
    BurnoutRisk
}

public enum AnalysisTimeframe
{
    Hour,
    Day,
    Week,
    Month,
    Quarter,
    Year,
    AllTime
}

public enum ComparisonType
{
    PreviousPeriod,
    SamePeriodLastYear,
    PersonalBest,
    PersonalAverage,
    Benchmark,
    Goal,
    Peer
}

public enum AlertSeverity
{
    Info,
    Low,
    Medium,
    High,
    Critical
}

public enum DataExportFormat
{
    Json,
    Csv,
    Excel,
    Pdf,
    Image,
    Html
}

public enum PrivacyLevel
{
    Public,
    Team,
    Organization,
    Private,
    Anonymous
}