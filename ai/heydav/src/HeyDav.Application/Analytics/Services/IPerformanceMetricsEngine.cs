using HeyDav.Application.Analytics.Models;
using HeyDav.Domain.Analytics.Enums;
using HeyDav.Domain.Analytics.ValueObjects;

namespace HeyDav.Application.Analytics.Services;

public interface IPerformanceMetricsEngine
{
    // Comprehensive Scoring
    Task<ProductivityScoreCard> CalculateProductivityScoreAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<PerformanceScoreBreakdown> GetDetailedScoreBreakdownAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<List<PerformanceMetric>> CalculateAllMetricsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<Dictionary<string, decimal>> GetKPIDashboardAsync(string userId, CancellationToken cancellationToken = default);
    
    // Goal Achievement Tracking
    Task<GoalCompletionMetrics> CalculateGoalMetricsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<List<GoalPerformanceData>> GetGoalPerformanceAsync(string userId, List<Guid> goalIds, CancellationToken cancellationToken = default);
    Task<MilestoneTrackingReport> GetMilestoneProgressAsync(string userId, CancellationToken cancellationToken = default);
    
    // Habit Consistency Tracking
    Task<HabitConsistencyMetrics> CalculateHabitMetricsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<List<HabitPerformanceData>> GetHabitPerformanceAsync(string userId, List<Guid> habitIds, CancellationToken cancellationToken = default);
    Task<HabitStreakAnalysis> AnalyzeHabitStreaksAsync(string userId, CancellationToken cancellationToken = default);
    
    // Energy and Mood Correlation
    Task<EnergyProductivityCorrelation> AnalyzeEnergyCorrelationAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<MoodProductivityCorrelation> AnalyzeMoodCorrelationAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<WellbeingMetrics> CalculateWellbeingMetricsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    
    // Comparative Analysis
    Task<PerformanceComparison> ComparePerformancePeriodsAsync(string userId, DateTime period1Start, DateTime period1End, DateTime period2Start, DateTime period2End, CancellationToken cancellationToken = default);
    Task<PersonalBenchmarkAnalysis> GetPersonalBenchmarksAsync(string userId, CancellationToken cancellationToken = default);
    Task<PerformanceRanking> GetPerformanceRankingAsync(string userId, List<string> metricNames, CancellationToken cancellationToken = default);
    
    // Efficiency and Quality Metrics
    Task<EfficiencyMetrics> CalculateEfficiencyMetricsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<QualityMetrics> CalculateQualityMetricsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<TimeAllocationEfficiency> AnalyzeTimeAllocationAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    
    // Trend Analysis
    Task<MetricTrendAnalysis> AnalyzeMetricTrendsAsync(string userId, List<string> metricNames, int dayCount = 90, CancellationToken cancellationToken = default);
    Task<PerformanceVelocity> CalculatePerformanceVelocityAsync(string userId, CancellationToken cancellationToken = default);
    Task<SeasonalPerformanceAnalysis> AnalyzeSeasonalPerformanceAsync(string userId, string metricName, CancellationToken cancellationToken = default);
    
    // Real-time Metrics
    Task<RealTimePerformanceSnapshot> GetRealTimeSnapshotAsync(string userId, CancellationToken cancellationToken = default);
    Task<DailyPerformanceSummary> GetDailyPerformanceSummaryAsync(string userId, DateTime date, CancellationToken cancellationToken = default);
    Task<WeeklyPerformanceSummary> GetWeeklyPerformanceSummaryAsync(string userId, DateTime weekStart, CancellationToken cancellationToken = default);
    
    // Custom Metrics
    Task<CustomMetricResult> CalculateCustomMetricAsync(string userId, CustomMetricDefinition definition, CancellationToken cancellationToken = default);
    Task<List<CustomMetricResult>> CalculateMultipleCustomMetricsAsync(string userId, List<CustomMetricDefinition> definitions, CancellationToken cancellationToken = default);
    Task SaveCustomMetricDefinitionAsync(string userId, CustomMetricDefinition definition, CancellationToken cancellationToken = default);
    
    // Metric Updates and Recalculation
    Task RecalculateAllMetricsAsync(string userId, CancellationToken cancellationToken = default);
    Task UpdateMetricAsync(string userId, string metricName, decimal value, DateTime timestamp, CancellationToken cancellationToken = default);
    Task BulkUpdateMetricsAsync(string userId, List<MetricUpdate> updates, CancellationToken cancellationToken = default);
}

public interface IBenchmarkingSystem
{
    // Industry Benchmarks
    Task<List<BenchmarkData>> GetIndustryBenchmarksAsync(string industry, List<string> metricNames, CancellationToken cancellationToken = default);
    Task<List<BenchmarkData>> GetRoleBenchmarksAsync(string role, List<string> metricNames, CancellationToken cancellationToken = default);
    Task<BenchmarkComparison> CompareToIndustryAsync(string userId, string industry, List<string> metricNames, CancellationToken cancellationToken = default);
    Task<BenchmarkComparison> CompareToRoleAsync(string userId, string role, List<string> metricNames, CancellationToken cancellationToken = default);
    
    // Peer Comparisons
    Task<PeerBenchmarkData> GetPeerBenchmarksAsync(string userId, List<string> metricNames, CancellationToken cancellationToken = default);
    Task<BenchmarkComparison> CompareToPeersAsync(string userId, List<string> metricNames, CancellationToken cancellationToken = default);
    Task<TeamBenchmarkData> GetTeamBenchmarksAsync(string teamId, List<string> metricNames, CancellationToken cancellationToken = default);
    
    // Anonymous Benchmarking
    Task<AnonymousBenchmarkData> GetAnonymousBenchmarksAsync(List<string> metricNames, Dictionary<string, object> filters, CancellationToken cancellationToken = default);
    Task ContributeAnonymousDataAsync(string userId, List<string> metricNames, Dictionary<string, object> metadata, CancellationToken cancellationToken = default);
    
    // Goal-based Benchmarking
    Task<GoalBenchmarkData> GetGoalBenchmarksAsync(string goalType, List<string> metricNames, CancellationToken cancellationToken = default);
    Task<BenchmarkComparison> CompareGoalProgressAsync(string userId, Guid goalId, CancellationToken cancellationToken = default);
    
    // Benchmarking Analytics
    Task<BenchmarkTrend> AnalyzeBenchmarkTrendsAsync(string benchmarkType, string metricName, int monthCount = 12, CancellationToken cancellationToken = default);
    Task<List<BenchmarkInsight>> GenerateBenchmarkInsightsAsync(string userId, List<BenchmarkComparison> comparisons, CancellationToken cancellationToken = default);
}

public interface IMetricCalculationEngine
{
    // Core Calculation Methods
    Task<decimal> CalculateMetricAsync(string userId, string metricName, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<Dictionary<DateTime, decimal>> CalculateTimeSeriesAsync(string userId, string metricName, DateTime fromDate, DateTime toDate, DataAggregation aggregation, CancellationToken cancellationToken = default);
    Task<Dictionary<string, decimal>> CalculateMultipleMetricsAsync(string userId, List<string> metricNames, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    
    // Statistical Operations
    Task<MetricStatistics> CalculateStatisticsAsync(string userId, string metricName, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<decimal> CalculatePercentileAsync(string userId, string metricName, decimal percentile, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<MovingAverageData> CalculateMovingAverageAsync(string userId, string metricName, int windowSize, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    
    // Correlation Analysis
    Task<decimal> CalculateCorrelationAsync(string userId, string metric1, string metric2, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<CorrelationMatrix> CalculateCorrelationMatrixAsync(string userId, List<string> metricNames, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    
    // Regression Analysis
    Task<RegressionAnalysis> PerformLinearRegressionAsync(string userId, string dependentMetric, List<string> independentMetrics, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<TrendAnalysisResult> AnalyzeTrendAsync(string userId, string metricName, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    
    // Forecasting
    Task<ForecastResult> ForecastMetricAsync(string userId, string metricName, int forecastDays, CancellationToken cancellationToken = default);
    Task<SeasonalDecomposition> DecomposeSeasonalityAsync(string userId, string metricName, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
}

public interface IPerformanceReportGenerator
{
    // Standard Reports
    Task<PerformanceReport> GenerateComprehensiveReportAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<ExecutiveSummaryReport> GenerateExecutiveSummaryAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<DetailedMetricsReport> GenerateDetailedMetricsReportAsync(string userId, DateTime fromDate, DateTime toDate, List<string> metricNames, CancellationToken cancellationToken = default);
    
    // Specialized Reports
    Task<GoalProgressReport> GenerateGoalProgressReportAsync(string userId, List<Guid> goalIds, CancellationToken cancellationToken = default);
    Task<HabitTrackingReport> GenerateHabitTrackingReportAsync(string userId, List<Guid> habitIds, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<ProductivityTrendReport> GenerateProductivityTrendReportAsync(string userId, int monthCount = 6, CancellationToken cancellationToken = default);
    
    // Comparative Reports
    Task<BenchmarkingReport> GenerateBenchmarkingReportAsync(string userId, List<BenchmarkComparison> comparisons, CancellationToken cancellationToken = default);
    Task<TeamPerformanceReport> GenerateTeamPerformanceReportAsync(string teamId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    
    // Custom Reports
    Task<CustomReport> GenerateCustomReportAsync(string userId, CustomReportDefinition definition, CancellationToken cancellationToken = default);
    Task<List<ReportTemplate>> GetAvailableReportTemplatesAsync(string userId, CancellationToken cancellationToken = default);
    Task SaveReportTemplateAsync(string userId, ReportTemplate template, CancellationToken cancellationToken = default);
    
    // Export and Delivery
    Task<byte[]> ExportReportAsync(PerformanceReport report, DataExportFormat format, CancellationToken cancellationToken = default);
    Task DeliverReportAsync(string userId, PerformanceReport report, DeliveryMethod method, CancellationToken cancellationToken = default);
    Task ScheduleRecurringReportAsync(string userId, RecurringReportConfig config, CancellationToken cancellationToken = default);
}