using HeyDav.Application.Analytics.Models;
using HeyDav.Domain.Analytics.Entities;
using HeyDav.Domain.Analytics.Enums;
using HeyDav.Domain.Analytics.Interfaces;
using HeyDav.Domain.Analytics.ValueObjects;
using Microsoft.Extensions.Logging;

namespace HeyDav.Application.Analytics.Services;

public class InsightGenerationSystem : IInsightGenerationSystem
{
    private readonly ILogger<InsightGenerationSystem> _logger;
    private readonly IProductivitySessionRepository _sessionRepository;
    private readonly ITimeEntryRepository _timeEntryRepository;
    private readonly IAnalyticsDataRepository _dataRepository;
    private readonly IPatternDetectionEngine _patternEngine;
    private readonly IAnomalyDetectionEngine _anomalyEngine;
    private readonly IRecommendationEngine _recommendationEngine;
    private readonly IPredictiveInsightEngine _predictiveEngine;
    private readonly IContextualInsightEngine _contextualEngine;
    private readonly IInsightPersonalizationEngine _personalizationEngine;

    public InsightGenerationSystem(
        ILogger<InsightGenerationSystem> logger,
        IProductivitySessionRepository sessionRepository,
        ITimeEntryRepository timeEntryRepository,
        IAnalyticsDataRepository dataRepository,
        IPatternDetectionEngine patternEngine,
        IAnomalyDetectionEngine anomalyEngine,
        IRecommendationEngine recommendationEngine,
        IPredictiveInsightEngine predictiveEngine,
        IContextualInsightEngine contextualEngine,
        IInsightPersonalizationEngine personalizationEngine)
    {
        _logger = logger;
        _sessionRepository = sessionRepository;
        _timeEntryRepository = timeEntryRepository;
        _dataRepository = dataRepository;
        _patternEngine = patternEngine;
        _anomalyEngine = anomalyEngine;
        _recommendationEngine = recommendationEngine;
        _predictiveEngine = predictiveEngine;
        _contextualEngine = contextualEngine;
        _personalizationEngine = personalizationEngine;
    }

    public async Task<List<ProductivityInsight>> GenerateAllInsightsAsync(string userId, CancellationToken cancellationToken = default)
    {
        _logger.LogInformation("Generating all insights for user {UserId}", userId);

        var insights = new List<ProductivityInsight>();

        // Generate different types of insights
        var patternInsights = await GeneratePatternInsightsAsync(userId, cancellationToken);
        var anomalyInsights = await GenerateAnomalyInsightsAsync(userId, cancellationToken);
        var trendInsights = await GenerateTrendInsightsAsync(userId, cancellationToken);
        var opportunityInsights = await GenerateOpportunityInsightsAsync(userId, cancellationToken);
        var performanceInsights = await GeneratePerformanceInsightsAsync(userId, cancellationToken);
        var predictiveInsights = await GeneratePredictiveInsightsAsync(userId, cancellationToken);

        insights.AddRange(patternInsights);
        insights.AddRange(anomalyInsights);
        insights.AddRange(trendInsights);
        insights.AddRange(opportunityInsights);
        insights.AddRange(performanceInsights);
        insights.AddRange(predictiveInsights);

        // Personalize insights
        var personalizedInsights = await _personalizationEngine.PersonalizeInsights(userId, insights, cancellationToken);

        // Sort by priority and relevance
        var sortedInsights = personalizedInsights
            .OrderByDescending(i => i.Priority)
            .ThenByDescending(i => i.ConfidenceScore)
            .Take(20) // Limit to top 20 insights
            .ToList();

        _logger.LogInformation("Generated {Count} insights for user {UserId}", sortedInsights.Count, userId);
        return sortedInsights;
    }

    public async Task<List<ProductivityInsight>> GenerateInsightsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        _logger.LogInformation("Generating insights for user {UserId} from {FromDate} to {ToDate}", userId, fromDate, toDate);

        var insights = new List<ProductivityInsight>();
        var sessions = await _sessionRepository.GetByUserIdAndDateRangeAsync(userId, fromDate, toDate, cancellationToken);
        var timeEntries = await _timeEntryRepository.GetByUserIdAndDateRangeAsync(userId, fromDate, toDate, cancellationToken);

        // Generate productivity pattern insights
        insights.AddRange(await GenerateProductivityPatternInsights(userId, sessions, timeEntries));

        // Generate time allocation insights
        insights.AddRange(await GenerateTimeAllocationInsights(userId, timeEntries));

        // Generate energy correlation insights
        insights.AddRange(await GenerateEnergyCorrelationInsights(userId, sessions));

        // Generate focus insights
        insights.AddRange(await GenerateFocusInsights(userId, sessions));

        // Generate efficiency insights
        insights.AddRange(await GenerateEfficiencyInsights(userId, sessions, timeEntries));

        return await _personalizationEngine.PersonalizeInsights(userId, insights, cancellationToken);
    }

    public async Task<List<ProductivityInsight>> GenerateInsightsByTypeAsync(string userId, InsightType type, CancellationToken cancellationToken = default)
    {
        return type switch
        {
            InsightType.Pattern => await GeneratePatternInsightsAsync(userId, cancellationToken),
            InsightType.Anomaly => await GenerateAnomalyInsightsAsync(userId, cancellationToken),
            InsightType.Trend => await GenerateTrendInsightsAsync(userId, cancellationToken),
            InsightType.Opportunity => await GenerateOpportunityInsightsAsync(userId, cancellationToken),
            InsightType.Recommendation => await GenerateRecommendationInsightsAsync(userId, cancellationToken),
            InsightType.Prediction => await GeneratePredictiveInsightsAsync(userId, cancellationToken),
            _ => new List<ProductivityInsight>()
        };
    }

    public async Task<List<ProductivityInsight>> GenerateActionableInsightsAsync(string userId, CancellationToken cancellationToken = default)
    {
        var allInsights = await GenerateAllInsightsAsync(userId, cancellationToken);
        return allInsights.Where(i => i.IsActionable).ToList();
    }

    public async Task<List<ProductivityInsight>> GenerateHighPriorityInsightsAsync(string userId, CancellationToken cancellationToken = default)
    {
        var allInsights = await GenerateAllInsightsAsync(userId, cancellationToken);
        return allInsights.Where(i => i.Priority == InsightPriority.High || i.Priority == InsightPriority.Urgent).ToList();
    }

    public async Task<List<ProductivityInsight>> GeneratePatternInsightsAsync(string userId, CancellationToken cancellationToken = default)
    {
        var insights = new List<ProductivityInsight>();
        
        // Detect behavioral patterns
        var workPatterns = await _patternEngine.DetectWorkPatterns(userId, cancellationToken);
        var energyPatterns = await _patternEngine.DetectEnergyPatterns(userId, cancellationToken);
        var productivityPatterns = await _patternEngine.DetectProductivityPatterns(userId, cancellationToken);

        // Convert patterns to insights
        foreach (var pattern in workPatterns.Where(p => p.Strength >= 70))
        {
            insights.Add(new ProductivityInsight(
                $"Work Pattern: {pattern.Name}",
                pattern.Description,
                InsightType.Pattern,
                GetPriorityFromStrength(pattern.Strength),
                isActionable: true,
                recommendedAction: GeneratePatternRecommendation(pattern),
                confidenceScore: pattern.Confidence,
                source: "Pattern Detection Engine"
            ));
        }

        foreach (var pattern in energyPatterns.Where(p => p.Strength >= 70))
        {
            insights.Add(new ProductivityInsight(
                $"Energy Pattern: {pattern.Name}",
                pattern.Description,
                InsightType.Pattern,
                GetPriorityFromStrength(pattern.Strength),
                isActionable: true,
                recommendedAction: GenerateEnergyPatternRecommendation(pattern),
                confidenceScore: pattern.Confidence,
                source: "Energy Pattern Analysis"
            ));
        }

        return insights;
    }

    public async Task<List<ProductivityInsight>> GenerateAnomalyInsightsAsync(string userId, CancellationToken cancellationToken = default)
    {
        var insights = new List<ProductivityInsight>();
        var metricNames = new[] { "ProductivityScore", "EnergyLevel", "FocusTime", "TaskCompletionRate" };
        
        var anomalies = await _anomalyEngine.DetectAnomaliesUsingML(userId, metricNames.ToList(), cancellationToken);
        
        foreach (var anomaly in anomalies.Where(a => a.Severity >= 70))
        {
            var priority = anomaly.Severity >= 90 ? InsightPriority.Urgent :
                          anomaly.Severity >= 80 ? InsightPriority.High : InsightPriority.Medium;

            insights.Add(new ProductivityInsight(
                $"Anomaly Detected: {anomaly.MetricName}",
                $"Unusual {anomaly.MetricName.ToLower()} detected on {anomaly.Timestamp:MMM dd}. " +
                $"Expected: {anomaly.ExpectedValue:F1}, Actual: {anomaly.ActualValue:F1}",
                InsightType.Anomaly,
                priority,
                isActionable: anomaly.RecommendedActions.Any(),
                recommendedAction: string.Join("; ", anomaly.RecommendedActions),
                confidenceScore: anomaly.Confidence,
                source: "Anomaly Detection Engine"
            ));
        }

        return insights;
    }

    public async Task<List<ProductivityInsight>> GenerateTrendInsightsAsync(string userId, CancellationToken cancellationToken = default)
    {
        var insights = new List<ProductivityInsight>();
        var metricNames = new[] { "ProductivityScore", "TaskCompletionRate", "EnergyLevel", "FocusTime" };

        foreach (var metricName in metricNames)
        {
            var dailyValues = await _dataRepository.GetDailyMetricAsync(userId, metricName, 
                DateTime.UtcNow.AddDays(-30), DateTime.UtcNow, cancellationToken);

            if (dailyValues.Count >= 7) // Need at least a week of data
            {
                var trend = AnalyzeTrend(dailyValues);
                
                if (Math.Abs(trend.Slope) > 0.1m) // Significant trend
                {
                    var direction = trend.Slope > 0 ? "improving" : "declining";
                    var priority = Math.Abs(trend.Slope) > 0.3m ? InsightPriority.High : InsightPriority.Medium;

                    insights.Add(new ProductivityInsight(
                        $"{metricName} Trend: {direction.ToUpper()}",
                        $"Your {metricName.ToLower()} has been {direction} over the past 30 days. " +
                        $"Rate of change: {trend.Slope:F2} per day.",
                        InsightType.Trend,
                        priority,
                        isActionable: true,
                        recommendedAction: GenerateTrendRecommendation(metricName, trend.Slope),
                        confidenceScore: trend.Confidence,
                        source: "Trend Analysis Engine"
                    ));
                }
            }
        }

        return insights;
    }

    public async Task<List<ProductivityInsight>> GenerateOpportunityInsightsAsync(string userId, CancellationToken cancellationToken = default)
    {
        var insights = new List<ProductivityInsight>();
        var sessions = await _sessionRepository.GetByUserIdAndDateRangeAsync(userId, 
            DateTime.UtcNow.AddDays(-30), DateTime.UtcNow, cancellationToken);

        // Analyze context switching opportunities
        var contextSwitches = CalculateContextSwitches(sessions);
        if (contextSwitches > 8) // High context switching
        {
            insights.Add(new ProductivityInsight(
                "Context Switching Opportunity",
                $"You're switching contexts {contextSwitches} times per day on average. " +
                "Reducing this could improve your focus and efficiency.",
                InsightType.Opportunity,
                InsightPriority.Medium,
                isActionable: true,
                recommendedAction: "Try batching similar tasks together and using time blocks for focused work",
                confidenceScore: 85m,
                source: "Context Analysis Engine"
            ));
        }

        // Analyze energy utilization opportunities
        var energyWaste = CalculateEnergyWasteOpportunity(sessions);
        if (energyWaste.PotentialGain > 15)
        {
            insights.Add(new ProductivityInsight(
                "Energy Optimization Opportunity",
                $"You could potentially improve productivity by {energyWaste.PotentialGain:F0}% " +
                "by better aligning high-energy periods with important tasks.",
                InsightType.Opportunity,
                InsightPriority.High,
                isActionable: true,
                recommendedAction: energyWaste.Recommendation,
                confidenceScore: energyWaste.Confidence,
                source: "Energy Optimization Engine"
            ));
        }

        return insights;
    }

    public async Task<List<ProductivityInsight>> GeneratePerformanceInsightsAsync(string userId, CancellationToken cancellationToken = default)
    {
        var insights = new List<ProductivityInsight>();
        var currentScore = await _dataRepository.GetMetricAverageAsync(userId, "ProductivityScore", 
            DateTime.UtcNow.AddDays(-7), DateTime.UtcNow, cancellationToken);
        var previousScore = await _dataRepository.GetMetricAverageAsync(userId, "ProductivityScore", 
            DateTime.UtcNow.AddDays(-14), DateTime.UtcNow.AddDays(-7), cancellationToken);

        if (currentScore > 0 && previousScore > 0)
        {
            var change = ((currentScore - previousScore) / previousScore) * 100;
            
            if (Math.Abs(change) > 5) // Significant change
            {
                var changeType = change > 0 ? "improvement" : "decline";
                var priority = Math.Abs(change) > 15 ? InsightPriority.High : InsightPriority.Medium;

                insights.Add(new ProductivityInsight(
                    $"Performance {changeType.ToUpper()}",
                    $"Your productivity score has {(change > 0 ? "increased" : "decreased")} by {Math.Abs(change):F1}% " +
                    "compared to last week.",
                    InsightType.Achievement,
                    priority,
                    isActionable: change < 0,
                    recommendedAction: change < 0 ? "Review recent changes and identify factors affecting your productivity" : null,
                    confidenceScore: 90m,
                    source: "Performance Analysis Engine"
                ));
            }
        }

        return insights;
    }

    public async Task<List<ProductivityInsight>> GeneratePredictiveInsightsAsync(string userId, CancellationToken cancellationToken = default)
    {
        var insights = new List<ProductivityInsight>();

        // Predict burnout risk
        var burnoutInsights = await _predictiveEngine.PredictBurnoutRisk(userId, cancellationToken);
        insights.AddRange(burnoutInsights);

        // Predict performance decline
        var performanceInsights = await _predictiveEngine.PredictPerformanceDecline(userId, cancellationToken);
        insights.AddRange(performanceInsights);

        // Predict productivity dips
        var productivityInsights = await _predictiveEngine.PredictProductivityDips(userId, cancellationToken);
        insights.AddRange(productivityInsights);

        return insights;
    }

    public async Task<List<ProductivityInsight>> GenerateRiskInsightsAsync(string userId, CancellationToken cancellationToken = default)
    {
        var insights = new List<ProductivityInsight>();
        var sessions = await _sessionRepository.GetByUserIdAndDateRangeAsync(userId, 
            DateTime.UtcNow.AddDays(-14), DateTime.UtcNow, cancellationToken);

        // Analyze burnout risk factors
        var avgSessionLength = sessions.Where(s => s.Duration.HasValue)
                                     .Average(s => s.Duration!.Value.TotalHours);
        var avgInterruptions = sessions.Average(s => s.InterruptionCount);
        var energyTrend = CalculateEnergyTrend(sessions);

        if (avgSessionLength > 6 || avgInterruptions > 10 || energyTrend < -0.1m)
        {
            var riskLevel = CalculateBurnoutRisk(avgSessionLength, avgInterruptions, energyTrend);
            
            insights.Add(new ProductivityInsight(
                "Burnout Risk Warning",
                $"Risk level: {riskLevel}. Long work sessions ({avgSessionLength:F1}h avg), " +
                $"high interruptions ({avgInterruptions:F0} avg), or declining energy detected.",
                InsightType.Warning,
                InsightPriority.High,
                isActionable: true,
                recommendedAction: "Consider taking breaks, reducing session length, or addressing interruption sources",
                confidenceScore: 80m,
                source: "Risk Assessment Engine"
            ));
        }

        return insights;
    }

    public async Task<List<ProductivityInsight>> GenerateForecastInsightsAsync(string userId, int forecastDays = 7, CancellationToken cancellationToken = default)
    {
        var insights = new List<ProductivityInsight>();
        
        // Get historical productivity data
        var historicalData = await _dataRepository.GetDailyMetricAsync(userId, "ProductivityScore", 
            DateTime.UtcNow.AddDays(-30), DateTime.UtcNow, cancellationToken);

        if (historicalData.Count >= 14) // Need sufficient data for forecasting
        {
            var forecast = GenerateProductivityForecast(historicalData, forecastDays);
            
            insights.Add(new ProductivityInsight(
                "Productivity Forecast",
                $"Based on recent patterns, your productivity is expected to {forecast.Direction.ToString().ToLower()} " +
                $"by {Math.Abs(forecast.Change):F1}% over the next {forecastDays} days.",
                InsightType.Prediction,
                InsightPriority.Medium,
                isActionable: forecast.Change < -5,
                recommendedAction: forecast.Change < -5 ? "Consider adjusting your schedule or taking proactive measures" : null,
                confidenceScore: forecast.Confidence,
                source: "Forecasting Engine"
            ));
        }

        return insights;
    }

    // Time-based insights
    public async Task<List<ProductivityInsight>> GenerateTimeBasedInsightsAsync(string userId, CancellationToken cancellationToken = default)
    {
        var insights = new List<ProductivityInsight>();
        var sessions = await _sessionRepository.GetByUserIdAndDateRangeAsync(userId, 
            DateTime.UtcNow.AddDays(-30), DateTime.UtcNow, cancellationToken);

        // Analyze peak hours
        var hourlyProductivity = sessions
            .GroupBy(s => s.StartTime.Hour)
            .ToDictionary(g => g.Key, g => g.Average(s => s.GetProductivityScore()));

        if (hourlyProductivity.Any())
        {
            var peakHour = hourlyProductivity.OrderByDescending(kvp => kvp.Value).First();
            var lowHour = hourlyProductivity.OrderBy(kvp => kvp.Value).First();

            if (peakHour.Value - lowHour.Value > 2) // Significant difference
            {
                insights.Add(new ProductivityInsight(
                    "Peak Performance Hours",
                    $"Your productivity peaks at {peakHour.Key}:00 (score: {peakHour.Value:F1}) " +
                    $"and is lowest at {lowHour.Key}:00 (score: {lowHour.Value:F1}).",
                    InsightType.Pattern,
                    InsightPriority.Medium,
                    isActionable: true,
                    recommendedAction: $"Schedule your most important work around {peakHour.Key}:00 for optimal results",
                    confidenceScore: 85m,
                    source: "Time Analysis Engine"
                ));
            }
        }

        return insights;
    }

    public async Task<List<ProductivityInsight>> GenerateEnergyBasedInsightsAsync(string userId, CancellationToken cancellationToken = default)
    {
        var insights = new List<ProductivityInsight>();
        var sessions = await _sessionRepository.GetByUserIdAndDateRangeAsync(userId, 
            DateTime.UtcNow.AddDays(-30), DateTime.UtcNow, cancellationToken);

        // Analyze energy-productivity correlation
        var energyProductivityCorrelation = CalculateEnergyProductivityCorrelation(sessions);
        
        if (Math.Abs(energyProductivityCorrelation) > 0.5m)
        {
            var correlationType = energyProductivityCorrelation > 0 ? "strong positive" : "strong negative";
            
            insights.Add(new ProductivityInsight(
                "Energy-Productivity Correlation",
                $"There's a {correlationType} correlation ({energyProductivityCorrelation:F2}) " +
                "between your energy levels and productivity.",
                InsightType.Pattern,
                InsightPriority.Medium,
                isActionable: true,
                recommendedAction: energyProductivityCorrelation > 0 ? 
                    "Focus on maintaining high energy levels for better productivity" :
                    "Consider if high energy might be leading to rushed or unfocused work",
                confidenceScore: Math.Abs(energyProductivityCorrelation) * 100,
                source: "Energy Correlation Engine"
            ));
        }

        return insights;
    }

    // Helper methods for insight generation
    private async Task<List<ProductivityInsight>> GenerateProductivityPatternInsights(string userId, List<ProductivitySession> sessions, List<TimeEntry> timeEntries)
    {
        var insights = new List<ProductivityInsight>();

        // Analyze daily patterns
        var dailyAverages = sessions.GroupBy(s => s.StartTime.DayOfWeek)
                                  .ToDictionary(g => g.Key, g => g.Average(s => s.GetProductivityScore()));

        if (dailyAverages.Any())
        {
            var bestDay = dailyAverages.OrderByDescending(kvp => kvp.Value).First();
            var worstDay = dailyAverages.OrderBy(kvp => kvp.Value).First();

            if (bestDay.Value - worstDay.Value > 1.5m)
            {
                insights.Add(new ProductivityInsight(
                    "Daily Productivity Pattern",
                    $"You're most productive on {bestDay.Key}s (score: {bestDay.Value:F1}) " +
                    $"and least productive on {worstDay.Key}s (score: {worstDay.Value:F1}).",
                    InsightType.Pattern,
                    InsightPriority.Medium,
                    isActionable: true,
                    recommendedAction: $"Consider scheduling important work on {bestDay.Key}s and lighter tasks on {worstDay.Key}s",
                    confidenceScore: 80m
                ));
            }
        }

        return insights;
    }

    private async Task<List<ProductivityInsight>> GenerateTimeAllocationInsights(string userId, List<TimeEntry> timeEntries)
    {
        var insights = new List<ProductivityInsight>();
        var completedEntries = timeEntries.Where(e => e.Duration.HasValue).ToList();

        if (completedEntries.Any())
        {
            var categoryTotals = completedEntries
                .GroupBy(e => e.Category ?? "Uncategorized")
                .ToDictionary(g => g.Key, g => g.Sum(e => e.Duration!.Value.TotalHours));

            var totalHours = categoryTotals.Values.Sum();
            var largestCategory = categoryTotals.OrderByDescending(kvp => kvp.Value).First();

            if (largestCategory.Value / totalHours > 0.6) // More than 60% in one category
            {
                insights.Add(new ProductivityInsight(
                    "Time Allocation Imbalance",
                    $"You're spending {largestCategory.Value / totalHours * 100:F0}% of your time on {largestCategory.Key}. " +
                    "Consider if this allocation aligns with your priorities.",
                    InsightType.Opportunity,
                    InsightPriority.Medium,
                    isActionable: true,
                    recommendedAction: "Review your time allocation and adjust if needed to better balance your activities",
                    confidenceScore: 75m
                ));
            }
        }

        return insights;
    }

    private async Task<List<ProductivityInsight>> GenerateEnergyCorrelationInsights(string userId, List<ProductivitySession> sessions)
    {
        var insights = new List<ProductivityInsight>();
        
        var energyProductivityData = sessions
            .Where(s => s.EnergyLevelStart > 0)
            .Select(s => new { Energy = s.EnergyLevelStart, Productivity = s.GetProductivityScore() })
            .ToList();

        if (energyProductivityData.Count >= 10)
        {
            var correlation = CalculateCorrelation(
                energyProductivityData.Select(d => (double)d.Energy).ToList(),
                energyProductivityData.Select(d => (double)d.Productivity).ToList()
            );

            if (Math.Abs(correlation) > 0.4)
            {
                var direction = correlation > 0 ? "increases" : "decreases";
                insights.Add(new ProductivityInsight(
                    "Energy-Productivity Relationship",
                    $"Your productivity {direction} when your energy level is higher (correlation: {correlation:F2}). " +
                    "This suggests energy management is important for your performance.",
                    InsightType.Pattern,
                    InsightPriority.Medium,
                    isActionable: true,
                    recommendedAction: correlation > 0 ? 
                        "Focus on maintaining high energy levels through proper rest, nutrition, and breaks" :
                        "Consider if high energy periods might be leading to rushed or less focused work",
                    confidenceScore: (decimal)(Math.Abs(correlation) * 100)
                ));
            }
        }

        return insights;
    }

    private async Task<List<ProductivityInsight>> GenerateFocusInsights(string userId, List<ProductivitySession> sessions)
    {
        var insights = new List<ProductivityInsight>();
        var focusSessions = sessions.Where(s => s.FocusScore.HasValue).ToList();

        if (focusSessions.Any())
        {
            var avgFocusScore = focusSessions.Average(s => s.FocusScore!.Value);
            var avgInterruptions = sessions.Average(s => s.InterruptionCount);

            if (avgFocusScore < 6) // Below average focus
            {
                insights.Add(new ProductivityInsight(
                    "Focus Improvement Opportunity",
                    $"Your average focus score is {avgFocusScore:F1}/10 with {avgInterruptions:F1} interruptions per session. " +
                    "Improving focus could significantly boost your productivity.",
                    InsightType.Opportunity,
                    InsightPriority.High,
                    isActionable: true,
                    recommendedAction: "Try using focus techniques like Pomodoro, minimizing distractions, and creating a dedicated workspace",
                    confidenceScore: 85m
                ));
            }
        }

        return insights;
    }

    private async Task<List<ProductivityInsight>> GenerateEfficiencyInsights(string userId, List<ProductivitySession> sessions, List<TimeEntry> timeEntries)
    {
        var insights = new List<ProductivityInsight>();
        
        // Calculate context switching frequency
        var contextSwitches = CalculateContextSwitches(sessions);
        
        if (contextSwitches > 10)
        {
            insights.Add(new ProductivityInsight(
                "High Context Switching",
                $"You're switching contexts {contextSwitches:F1} times per day, which may reduce your efficiency. " +
                "Batching similar tasks could help maintain focus.",
                InsightType.Opportunity,
                InsightPriority.Medium,
                isActionable: true,
                recommendedAction: "Group similar activities together and use time blocking to reduce context switching",
                confidenceScore: 80m
            ));
        }

        return insights;
    }

    private async Task<List<ProductivityInsight>> GenerateRecommendationInsightsAsync(string userId, CancellationToken cancellationToken = default)
    {
        var recommendations = await _recommendationEngine.GeneratePersonalizedRecommendations(userId, cancellationToken);
        
        return recommendations.Take(5).Select(r => new ProductivityInsight(
            r.Title,
            r.Description,
            InsightType.Recommendation,
            r.Priority,
            isActionable: true,
            recommendedAction: string.Join("; ", r.ActionSteps.Take(2)),
            confidenceScore: r.ConfidenceScore,
            source: "Recommendation Engine"
        )).ToList();
    }

    // Calculation helper methods
    private decimal CalculateContextSwitches(List<ProductivitySession> sessions)
    {
        if (sessions.Count <= 1) return 0;

        var dailyGroups = sessions.GroupBy(s => s.StartTime.Date);
        var totalSwitches = 0;

        foreach (var dayGroup in dailyGroups)
        {
            var daySessions = dayGroup.OrderBy(s => s.StartTime).ToList();
            for (int i = 1; i < daySessions.Count; i++)
            {
                if (daySessions[i].Context != daySessions[i - 1].Context)
                    totalSwitches++;
            }
        }

        return dailyGroups.Count() > 0 ? (decimal)totalSwitches / dailyGroups.Count() : 0;
    }

    private (decimal PotentialGain, string Recommendation, decimal Confidence) CalculateEnergyWasteOpportunity(List<ProductivitySession> sessions)
    {
        var energyProductivityPairs = sessions
            .Where(s => s.EnergyLevelStart > 0)
            .Select(s => new { Energy = s.EnergyLevelStart, Productivity = s.GetProductivityScore() })
            .ToList();

        if (energyProductivityPairs.Count < 10)
            return (0, "", 0);

        // Find sessions with high energy but low productivity
        var wastedEnergySessions = energyProductivityPairs
            .Where(p => p.Energy >= 8 && p.Productivity <= 6)
            .ToList();

        var potentialGain = wastedEnergySessions.Count * 2.5m; // Rough estimate
        var recommendation = "Schedule your most important tasks during high-energy periods (energy level 8+)";
        var confidence = Math.Min(90, wastedEnergySessions.Count * 10);

        return (potentialGain, recommendation, confidence);
    }

    private TrendAnalysis AnalyzeTrend(Dictionary<DateTime, decimal> values)
    {
        if (values.Count < 3) return new TrendAnalysis { Slope = 0, Confidence = 0 };

        var orderedValues = values.OrderBy(kvp => kvp.Key).ToList();
        var n = orderedValues.Count;
        
        // Simple linear regression
        var sumX = Enumerable.Range(0, n).Sum();
        var sumY = (double)orderedValues.Sum(kvp => kvp.Value);
        var sumXY = orderedValues.Select((kvp, i) => i * (double)kvp.Value).Sum();
        var sumX2 = Enumerable.Range(0, n).Sum(x => x * x);

        var slope = (n * sumXY - sumX * sumY) / (n * sumX2 - sumX * sumX);
        var confidence = Math.Min(95, Math.Abs(slope) * 100 + 60);

        return new TrendAnalysis 
        { 
            Slope = (decimal)slope, 
            Confidence = (decimal)confidence 
        };
    }

    private ProductivityForecastResult GenerateProductivityForecast(Dictionary<DateTime, decimal> historicalData, int forecastDays)
    {
        var trend = AnalyzeTrend(historicalData);
        var currentValue = historicalData.Values.Last();
        var forecastValue = currentValue + (trend.Slope * forecastDays);
        var change = ((forecastValue - currentValue) / currentValue) * 100;
        
        return new ProductivityForecastResult
        {
            Direction = change > 0 ? TrendDirection.Increasing : change < 0 ? TrendDirection.Decreasing : TrendDirection.Stable,
            Change = change,
            Confidence = Math.Min(85, trend.Confidence)
        };
    }

    private decimal CalculateEnergyTrend(List<ProductivitySession> sessions)
    {
        var dailyEnergyAverages = sessions
            .GroupBy(s => s.StartTime.Date)
            .ToDictionary(g => g.Key, g => (decimal)g.Average(s => s.EnergyLevelStart));

        return AnalyzeTrend(dailyEnergyAverages).Slope;
    }

    private string CalculateBurnoutRisk(double avgSessionLength, double avgInterruptions, decimal energyTrend)
    {
        var riskScore = 0;
        
        if (avgSessionLength > 6) riskScore += 2;
        if (avgSessionLength > 8) riskScore += 2;
        if (avgInterruptions > 10) riskScore += 2;
        if (avgInterruptions > 15) riskScore += 1;
        if (energyTrend < -0.1m) riskScore += 2;
        if (energyTrend < -0.2m) riskScore += 2;

        return riskScore switch
        {
            <= 2 => "Low",
            <= 4 => "Moderate",
            <= 6 => "High",
            _ => "Critical"
        };
    }

    private decimal CalculateEnergyProductivityCorrelation(List<ProductivitySession> sessions)
    {
        var energyProductivityPairs = sessions
            .Where(s => s.EnergyLevelStart > 0)
            .Select(s => new { Energy = (double)s.EnergyLevelStart, Productivity = (double)s.GetProductivityScore() })
            .ToList();

        if (energyProductivityPairs.Count < 5) return 0;

        return (decimal)CalculateCorrelation(
            energyProductivityPairs.Select(p => p.Energy).ToList(),
            energyProductivityPairs.Select(p => p.Productivity).ToList()
        );
    }

    private double CalculateCorrelation(List<double> x, List<double> y)
    {
        if (x.Count != y.Count || x.Count < 2) return 0;

        var meanX = x.Average();
        var meanY = y.Average();

        var numerator = x.Zip(y, (xi, yi) => (xi - meanX) * (yi - meanY)).Sum();
        var denominator = Math.Sqrt(x.Sum(xi => Math.Pow(xi - meanX, 2)) * y.Sum(yi => Math.Pow(yi - meanY, 2)));

        return denominator == 0 ? 0 : numerator / denominator;
    }

    private InsightPriority GetPriorityFromStrength(decimal strength)
    {
        return strength switch
        {
            >= 90 => InsightPriority.Urgent,
            >= 80 => InsightPriority.High,
            >= 70 => InsightPriority.Medium,
            _ => InsightPriority.Low
        };
    }

    private string GeneratePatternRecommendation(BehavioralPattern pattern)
    {
        return pattern.Category.ToLower() switch
        {
            "work" => $"Leverage this {pattern.Name.ToLower()} by scheduling similar activities during optimal times",
            "energy" => $"Plan your most demanding tasks to align with this {pattern.Name.ToLower()}",
            "focus" => $"Use this {pattern.Name.ToLower()} to maximize your deep work sessions",
            _ => $"Consider how to optimize around this {pattern.Name.ToLower()}"
        };
    }

    private string GenerateEnergyPatternRecommendation(BehavioralPattern pattern)
    {
        return $"Schedule your most important work during the times when this {pattern.Name.ToLower()} occurs for maximum efficiency";
    }

    private string GenerateTrendRecommendation(string metricName, decimal slope)
    {
        var direction = slope > 0 ? "positive" : "negative";
        return metricName.ToLower() switch
        {
            "productivityscore" => slope > 0 ? "Continue current practices that are driving this improvement" : "Identify and address factors causing this decline",
            "energylevel" => slope > 0 ? "Maintain the habits supporting your increasing energy" : "Focus on rest, nutrition, and stress management",
            "focustime" => slope > 0 ? "Build on your improving focus habits" : "Eliminate distractions and improve your work environment",
            _ => $"Monitor this {direction} trend and take appropriate action"
        };
    }

    // Placeholder implementations for remaining interface methods
    public async Task<List<ProductivityInsight>> GenerateGoalBasedInsightsAsync(string userId, List<Guid> goalIds, CancellationToken cancellationToken = default)
    {
        return new List<ProductivityInsight>();
    }

    public async Task<List<ProductivityInsight>> GenerateHabitBasedInsightsAsync(string userId, List<Guid> habitIds, CancellationToken cancellationToken = default)
    {
        return new List<ProductivityInsight>();
    }

    public async Task<List<ProductivityInsight>> GenerateComparisonInsightsAsync(string userId, DateTime period1Start, DateTime period1End, DateTime period2Start, DateTime period2End, CancellationToken cancellationToken = default)
    {
        return new List<ProductivityInsight>();
    }

    public async Task<List<ProductivityInsight>> GenerateBenchmarkInsightsAsync(string userId, List<BenchmarkComparison> benchmarks, CancellationToken cancellationToken = default)
    {
        return new List<ProductivityInsight>();
    }

    public async Task<List<ProductivityInsight>> GeneratePeerInsightsAsync(string userId, CancellationToken cancellationToken = default)
    {
        return new List<ProductivityInsight>();
    }

    public async Task<List<string>> GenerateRecommendationsAsync(string userId, CancellationToken cancellationToken = default)
    {
        var actionableRecommendations = await _recommendationEngine.GeneratePersonalizedRecommendations(userId, cancellationToken);
        return actionableRecommendations.Select(r => r.Description).ToList();
    }

    public async Task<List<ActionableRecommendation>> GenerateActionableRecommendationsAsync(string userId, CancellationToken cancellationToken = default)
    {
        return await _recommendationEngine.GeneratePersonalizedRecommendations(userId, cancellationToken);
    }

    public async Task<List<ImprovementSuggestion>> GenerateImprovementSuggestionsAsync(string userId, CancellationToken cancellationToken = default)
    {
        return new List<ImprovementSuggestion>(); // Placeholder
    }

    public async Task<List<OptimizationOpportunity>> GenerateOptimizationOpportunitiesAsync(string userId, CancellationToken cancellationToken = default)
    {
        return new List<OptimizationOpportunity>(); // Placeholder
    }

    public async Task<List<ProductivityInsight>> GenerateRealTimeInsightsAsync(string userId, CancellationToken cancellationToken = default)
    {
        // Generate insights based on current activity and context
        var insights = new List<ProductivityInsight>();
        
        var activeSession = await _sessionRepository.GetCurrentActiveSessionAsync(userId, cancellationToken);
        if (activeSession != null)
        {
            var duration = activeSession.GetCurrentDuration();
            
            if (duration > TimeSpan.FromHours(2))
            {
                insights.Add(new ProductivityInsight(
                    "Long Session Alert",
                    $"You've been working for {duration.TotalHours:F1} hours. Consider taking a break to maintain focus.",
                    InsightType.Warning,
                    InsightPriority.Medium,
                    isActionable: true,
                    recommendedAction: "Take a 10-15 minute break to recharge",
                    confidenceScore: 90m,
                    source: "Real-time Monitor"
                ));
            }
        }

        return insights;
    }

    public async Task<List<ProductivityInsight>> GenerateDailyInsightsAsync(string userId, DateTime date, CancellationToken cancellationToken = default)
    {
        return await GenerateInsightsAsync(userId, date.Date, date.Date.AddDays(1), cancellationToken);
    }

    public async Task<List<ProductivityInsight>> GenerateWeeklyInsightsAsync(string userId, DateTime weekStart, CancellationToken cancellationToken = default)
    {
        return await GenerateInsightsAsync(userId, weekStart, weekStart.AddDays(7), cancellationToken);
    }

    public async Task SaveInsightAsync(string userId, ProductivityInsight insight, CancellationToken cancellationToken = default)
    {
        _logger.LogInformation("Saving insight {InsightId} for user {UserId}", insight.Id, userId);
        // Implementation would save to database
    }

    public async Task<List<ProductivityInsight>> GetInsightHistoryAsync(string userId, int count = 50, CancellationToken cancellationToken = default)
    {
        // Implementation would retrieve from database
        return new List<ProductivityInsight>();
    }

    public async Task MarkInsightAsReadAsync(string userId, Guid insightId, CancellationToken cancellationToken = default)
    {
        _logger.LogInformation("Marking insight {InsightId} as read for user {UserId}", insightId, userId);
        // Implementation would update database
    }

    public async Task MarkInsightAsActedUponAsync(string userId, Guid insightId, string action, CancellationToken cancellationToken = default)
    {
        _logger.LogInformation("Marking insight {InsightId} as acted upon for user {UserId} with action: {Action}", insightId, userId, action);
        // Implementation would update database and potentially improve recommendation engine
    }

    public async Task UpdateInsightFeedbackAsync(string userId, Guid insightId, InsightFeedback feedback, CancellationToken cancellationToken = default)
    {
        _logger.LogInformation("Updating insight feedback for insight {InsightId} from user {UserId}", insightId, userId);
        // Implementation would save feedback and use it to improve future insights
    }

    // Helper classes for internal calculations
    private class TrendAnalysis
    {
        public decimal Slope { get; set; }
        public decimal Confidence { get; set; }
    }

    private class ProductivityForecastResult
    {
        public TrendDirection Direction { get; set; }
        public decimal Change { get; set; }
        public decimal Confidence { get; set; }
    }
}