using HeyDav.Application.Analytics.Models;
using HeyDav.Domain.Analytics.Enums;
using HeyDav.Domain.Analytics.ValueObjects;

namespace HeyDav.Application.Analytics.Services;

public interface IInsightGenerationSystem
{
    // Core Insight Generation
    Task<List<ProductivityInsight>> GenerateAllInsightsAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GenerateInsightsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GenerateInsightsByTypeAsync(string userId, InsightType type, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GenerateActionableInsightsAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GenerateHighPriorityInsightsAsync(string userId, CancellationToken cancellationToken = default);
    
    // Specialized Insight Generators
    Task<List<ProductivityInsight>> GeneratePatternInsightsAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GenerateAnomalyInsightsAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GenerateTrendInsightsAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GenerateOpportunityInsightsAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GeneratePerformanceInsightsAsync(string userId, CancellationToken cancellationToken = default);
    
    // Predictive Insights
    Task<List<ProductivityInsight>> GeneratePredictiveInsightsAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GenerateRiskInsightsAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GenerateForecastInsightsAsync(string userId, int forecastDays = 7, CancellationToken cancellationToken = default);
    
    // Contextual Insights
    Task<List<ProductivityInsight>> GenerateTimeBasedInsightsAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GenerateEnergyBasedInsightsAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GenerateGoalBasedInsightsAsync(string userId, List<Guid> goalIds, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GenerateHabitBasedInsightsAsync(string userId, List<Guid> habitIds, CancellationToken cancellationToken = default);
    
    // Comparative Insights
    Task<List<ProductivityInsight>> GenerateComparisonInsightsAsync(string userId, DateTime period1Start, DateTime period1End, DateTime period2Start, DateTime period2End, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GenerateBenchmarkInsightsAsync(string userId, List<BenchmarkComparison> benchmarks, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GeneratePeerInsightsAsync(string userId, CancellationToken cancellationToken = default);
    
    // Recommendations
    Task<List<string>> GenerateRecommendationsAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<ActionableRecommendation>> GenerateActionableRecommendationsAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<ImprovementSuggestion>> GenerateImprovementSuggestionsAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<OptimizationOpportunity>> GenerateOptimizationOpportunitiesAsync(string userId, CancellationToken cancellationToken = default);
    
    // Real-time Insights
    Task<List<ProductivityInsight>> GenerateRealTimeInsightsAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GenerateDailyInsightsAsync(string userId, DateTime date, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GenerateWeeklyInsightsAsync(string userId, DateTime weekStart, CancellationToken cancellationToken = default);
    
    // Insight Management
    Task SaveInsightAsync(string userId, ProductivityInsight insight, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GetInsightHistoryAsync(string userId, int count = 50, CancellationToken cancellationToken = default);
    Task MarkInsightAsReadAsync(string userId, Guid insightId, CancellationToken cancellationToken = default);
    Task MarkInsightAsActedUponAsync(string userId, Guid insightId, string action, CancellationToken cancellationToken = default);
    Task UpdateInsightFeedbackAsync(string userId, Guid insightId, InsightFeedback feedback, CancellationToken cancellationToken = default);
}

public interface IPatternDetectionEngine
{
    // Behavioral Pattern Detection
    Task<List<BehavioralPattern>> DetectWorkPatterns(string userId, CancellationToken cancellationToken = default);
    Task<List<BehavioralPattern>> DetectEnergyPatterns(string userId, CancellationToken cancellationToken = default);
    Task<List<BehavioralPattern>> DetectProductivityPatterns(string userId, CancellationToken cancellationToken = default);
    Task<List<BehavioralPattern>> DetectFocusPatterns(string userId, CancellationToken cancellationToken = default);
    
    // Temporal Pattern Detection
    Task<List<TemporalPattern>> DetectDailyPatterns(string userId, CancellationToken cancellationToken = default);
    Task<List<TemporalPattern>> DetectWeeklyPatterns(string userId, CancellationToken cancellationToken = default);
    Task<List<TemporalPattern>> DetectSeasonalPatterns(string userId, CancellationToken cancellationToken = default);
    
    // Correlation Pattern Detection
    Task<List<CorrelationPattern>> DetectMetricCorrelations(string userId, List<string> metricNames, CancellationToken cancellationToken = default);
    Task<List<CorrelationPattern>> DetectContextualCorrelations(string userId, CancellationToken cancellationToken = default);
    Task<List<CorrelationPattern>> DetectCausalPatterns(string userId, CancellationToken cancellationToken = default);
    
    // Anomaly Detection
    Task<List<AnomalyDetection>> DetectAnomalies(string userId, List<string> metricNames, CancellationToken cancellationToken = default);
    Task<List<AnomalyDetection>> DetectPerformanceAnomalies(string userId, CancellationToken cancellationToken = default);
    Task<List<AnomalyDetection>> DetectBehavioralAnomalies(string userId, CancellationToken cancellationToken = default);
}

public interface IAnomalyDetectionEngine
{
    // Statistical Anomaly Detection
    Task<List<AnomalyDetection>> DetectStatisticalAnomalies(string userId, string metricName, CancellationToken cancellationToken = default);
    Task<List<AnomalyDetection>> DetectOutliers(string userId, List<string> metricNames, CancellationToken cancellationToken = default);
    Task<List<AnomalyDetection>> DetectTrendBreaks(string userId, string metricName, CancellationToken cancellationToken = default);
    
    // Machine Learning Anomaly Detection
    Task<List<AnomalyDetection>> DetectAnomaliesUsingML(string userId, List<string> metricNames, CancellationToken cancellationToken = default);
    Task<List<AnomalyDetection>> DetectContextualAnomalies(string userId, CancellationToken cancellationToken = default);
    Task<List<AnomalyDetection>> DetectMultivariateAnomalies(string userId, List<string> metricNames, CancellationToken cancellationToken = default);
    
    // Time Series Anomaly Detection
    Task<List<AnomalyDetection>> DetectTimeSeriesAnomalies(string userId, string metricName, CancellationToken cancellationToken = default);
    Task<List<AnomalyDetection>> DetectSeasonalAnomalies(string userId, string metricName, CancellationToken cancellationToken = default);
    Task<List<AnomalyDetection>> DetectChangePoints(string userId, string metricName, CancellationToken cancellationToken = default);
}

public interface IRecommendationEngine
{
    // Core Recommendations
    Task<List<ActionableRecommendation>> GeneratePersonalizedRecommendations(string userId, CancellationToken cancellationToken = default);
    Task<List<ActionableRecommendation>> GeneratePerformanceRecommendations(string userId, CancellationToken cancellationToken = default);
    Task<List<ActionableRecommendation>> GenerateEfficiencyRecommendations(string userId, CancellationToken cancellationToken = default);
    Task<List<ActionableRecommendation>> GenerateWellbeingRecommendations(string userId, CancellationToken cancellationToken = default);
    
    // Goal and Habit Recommendations
    Task<List<ActionableRecommendation>> GenerateGoalRecommendations(string userId, List<Guid> goalIds, CancellationToken cancellationToken = default);
    Task<List<ActionableRecommendation>> GenerateHabitRecommendations(string userId, List<Guid> habitIds, CancellationToken cancellationToken = default);
    Task<List<ActionableRecommendation>> GenerateSchedulingRecommendations(string userId, CancellationToken cancellationToken = default);
    
    // Time Management Recommendations
    Task<List<ActionableRecommendation>> GenerateTimeManagementRecommendations(string userId, CancellationToken cancellationToken = default);
    Task<List<ActionableRecommendation>> GenerateFocusRecommendations(string userId, CancellationToken cancellationToken = default);
    Task<List<ActionableRecommendation>> GenerateEnergyOptimizationRecommendations(string userId, CancellationToken cancellationToken = default);
    
    // Learning and Adaptation
    Task UpdateRecommendationModelAsync(string userId, Guid recommendationId, RecommendationFeedback feedback, CancellationToken cancellationToken = default);
    Task<RecommendationEffectiveness> AnalyzeRecommendationEffectiveness(string userId, CancellationToken cancellationToken = default);
    Task PersonalizeRecommendationEngine(string userId, CancellationToken cancellationToken = default);
}

public interface IPredictiveInsightEngine
{
    // Performance Predictions
    Task<List<ProductivityInsight>> PredictPerformanceDecline(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> PredictBurnoutRisk(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> PredictGoalAchievementRisk(string userId, List<Guid> goalIds, CancellationToken cancellationToken = default);
    
    // Behavioral Predictions
    Task<List<ProductivityInsight>> PredictHabitBreakRisk(string userId, List<Guid> habitIds, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> PredictProductivityDips(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> PredictOptimalTiming(string userId, CancellationToken cancellationToken = default);
    
    // Opportunity Predictions
    Task<List<ProductivityInsight>> PredictImprovementOpportunities(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> PredictSkillDevelopmentOpportunities(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> PredictEfficiencyGains(string userId, CancellationToken cancellationToken = default);
}

public interface IContextualInsightEngine
{
    // Environmental Context
    Task<List<ProductivityInsight>> GenerateEnvironmentalInsights(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GenerateLocationBasedInsights(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GenerateToolUsageInsights(string userId, CancellationToken cancellationToken = default);
    
    // Social Context
    Task<List<ProductivityInsight>> GenerateCollaborationInsights(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GenerateTeamProductivityInsights(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GenerateMeetingEffectivenessInsights(string userId, CancellationToken cancellationToken = default);
    
    // Personal Context
    Task<List<ProductivityInsight>> GenerateLifestyleInsights(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GenerateHealthImpactInsights(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> GenerateStressImpactInsights(string userId, CancellationToken cancellationToken = default);
}

public interface IInsightPersonalizationEngine
{
    // Personalization
    Task<PersonalizationProfile> BuildPersonalizationProfile(string userId, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> PersonalizeInsights(string userId, List<ProductivityInsight> insights, CancellationToken cancellationToken = default);
    Task<List<ActionableRecommendation>> PersonalizeRecommendations(string userId, List<ActionableRecommendation> recommendations, CancellationToken cancellationToken = default);
    
    // Learning and Adaptation
    Task UpdatePersonalizationModel(string userId, InsightInteraction interaction, CancellationToken cancellationToken = default);
    Task<PersonalizationEffectiveness> MeasurePersonalizationEffectiveness(string userId, CancellationToken cancellationToken = default);
    Task OptimizePersonalizationModel(string userId, CancellationToken cancellationToken = default);
    
    // Preference Management
    Task<InsightPreferences> GetInsightPreferences(string userId, CancellationToken cancellationToken = default);
    Task UpdateInsightPreferences(string userId, InsightPreferences preferences, CancellationToken cancellationToken = default);
    Task<List<InsightCategory>> GetPreferredInsightCategories(string userId, CancellationToken cancellationToken = default);
}