using HeyDav.Domain.Analytics.Entities;
using HeyDav.Domain.Analytics.Enums;
using HeyDav.Domain.Analytics.ValueObjects;
using HeyDav.Application.Analytics.Models;

namespace HeyDav.Application.Analytics.Services;

public interface IProductivityAnalyticsEngine
{
    // Pattern Analysis
    Task<UserProductivityProfile> AnalyzeUserPatternsAsync(string userId, CancellationToken cancellationToken = default);
    Task<UserProductivityProfile> AnalyzeUserPatternsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<List<ProductivityPattern>> GetProductivityPatternsAsync(string userId, int dayCount = 30, CancellationToken cancellationToken = default);
    Task<Dictionary<TimeSpan, decimal>> GetEnergyPatternsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<Dictionary<DayOfWeek, decimal>> GetWeeklyPatternsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    
    // Predictive Analytics
    Task<ProductivityForecast> PredictProductivityAsync(string userId, DateTime targetDate, CancellationToken cancellationToken = default);
    Task<TaskCompletionPrediction> PredictTaskCompletionAsync(string userId, Guid taskId, CancellationToken cancellationToken = default);
    Task<GoalAchievementPrediction> PredictGoalAchievementAsync(string userId, Guid goalId, CancellationToken cancellationToken = default);
    Task<EnergyLevelPrediction> PredictEnergyLevelsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<OptimalSchedulingSuggestion> GetOptimalSchedulingSuggestionAsync(string userId, List<Guid> taskIds, CancellationToken cancellationToken = default);
    
    // Insights and Recommendations
    Task<List<ProductivityInsight>> GenerateInsightsAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GenerateInsightsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<List<string>> GenerateRecommendationsAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityBottleneck>> IdentifyBottlenecksAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityOpportunity>> IdentifyImprovementOpportunitiesAsync(string userId, CancellationToken cancellationToken = default);
    
    // Performance Metrics
    Task<ProductivityScoreCard> CalculateProductivityScoreAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<Dictionary<string, decimal>> CalculateKPIsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<List<PerformanceMetric>> GetPerformanceMetricsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    
    // Trend Analysis
    Task<List<ProductivityTrend>> AnalyzeTrendsAsync(string userId, List<string> metricNames, CancellationToken cancellationToken = default);
    Task<SeasonalAnalysis> AnalyzeSeasonalPatternsAsync(string userId, string metricName, CancellationToken cancellationToken = default);
    Task<List<ProductivityTrend>> ComparePeriodTrendsAsync(string userId, DateTime period1Start, DateTime period1End, DateTime period2Start, DateTime period2End, CancellationToken cancellationToken = default);
    
    // Context Analysis
    Task<ContextualProductivityAnalysis> AnalyzeContextualProductivityAsync(string userId, CancellationToken cancellationToken = default);
    Task<Dictionary<string, decimal>> GetProductivityByContextAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    
    // Data Updates
    Task UpdateAnalyticsModelAsync(string userId, ProductivityDataPoint dataPoint, CancellationToken cancellationToken = default);
    Task RefreshUserAnalyticsAsync(string userId, CancellationToken cancellationToken = default);
}

public interface IProductivityPatternAnalyzer
{
    Task<UserProductivityProfile> AnalyzeUserPatternsAsync(string userId, CancellationToken cancellationToken = default);
    Task<UserProductivityProfile> AnalyzeUserPatternsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<List<ProductivityPattern>> GetHistoricalPatternsAsync(string userId, int dayCount = 30, CancellationToken cancellationToken = default);
    Task UpdatePatternsAsync(string userId, ProductivityDataPoint dataPoint, CancellationToken cancellationToken = default);
    Task<Dictionary<TimeSpan, decimal>> GetOptimalWorkingHoursAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<ContextPattern>> GetContextPatternsAsync(string userId, CancellationToken cancellationToken = default);
}

public interface IEnergyLevelPredictor
{
    Task<Dictionary<TimeSpan, int>> PredictEnergyLevelsAsync(string userId, DateTime date, DateTime endDate, CancellationToken cancellationToken = default);
    Task<int> PredictEnergyLevelAsync(string userId, DateTime dateTime, CancellationToken cancellationToken = default);
    Task UpdateEnergyModelAsync(string userId, DateTime dateTime, int actualEnergyLevel, CancellationToken cancellationToken = default);
    Task<EnergyOptimizationSuggestion> GetEnergyOptimizationSuggestionAsync(string userId, CancellationToken cancellationToken = default);
}

public interface IWorkflowAnalytics
{
    Task<UserProductivityProfile> GetUserPatternsAsync(string? userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GetWorkflowInsightsAsync(Guid? templateId = null, string? userId = null, CancellationToken cancellationToken = default);
    Task<ProductivityScoreCard> CalculateProductivityScoreAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
}