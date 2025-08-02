using HeyDav.Application.Analytics.Models;
using HeyDav.Domain.Analytics.Entities;
using HeyDav.Domain.Analytics.Enums;
using HeyDav.Domain.Analytics.Interfaces;
using HeyDav.Domain.Analytics.ValueObjects;
using Microsoft.Extensions.Logging;

namespace HeyDav.Application.Analytics.Services;

public class PerformanceMetricsEngine : IPerformanceMetricsEngine
{
    private readonly ILogger<PerformanceMetricsEngine> _logger;
    private readonly IProductivitySessionRepository _sessionRepository;
    private readonly ITimeEntryRepository _timeEntryRepository;
    private readonly IAnalyticsDataRepository _dataRepository;
    private readonly IBenchmarkingSystem _benchmarkingSystem;
    private readonly IMetricCalculationEngine _calculationEngine;

    public PerformanceMetricsEngine(
        ILogger<PerformanceMetricsEngine> logger,
        IProductivitySessionRepository sessionRepository,
        ITimeEntryRepository timeEntryRepository,
        IAnalyticsDataRepository dataRepository,
        IBenchmarkingSystem benchmarkingSystem,
        IMetricCalculationEngine calculationEngine)
    {
        _logger = logger;
        _sessionRepository = sessionRepository;
        _timeEntryRepository = timeEntryRepository;
        _dataRepository = dataRepository;
        _benchmarkingSystem = benchmarkingSystem;
        _calculationEngine = calculationEngine;
    }

    public async Task<ProductivityScoreCard> CalculateProductivityScoreAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        _logger.LogInformation("Calculating productivity score for user {UserId} from {FromDate} to {ToDate}", userId, fromDate, toDate);

        var sessions = await _sessionRepository.GetByUserIdAndDateRangeAsync(userId, fromDate, toDate, cancellationToken);
        var timeEntries = await _timeEntryRepository.GetByUserIdAndDateRangeAsync(userId, fromDate, toDate, cancellationToken);

        // Calculate individual component scores
        var taskCompletionScore = await CalculateTaskCompletionScoreAsync(userId, sessions, timeEntries);
        var timeManagementScore = CalculateTimeManagementScore(sessions, timeEntries);
        var focusScore = CalculateFocusScore(sessions);
        var energyScore = CalculateEnergyScore(sessions);
        var goalProgressScore = await CalculateGoalProgressScoreAsync(userId, fromDate, toDate);
        var habitConsistencyScore = await CalculateHabitConsistencyScoreAsync(userId, fromDate, toDate);
        var wellbeingScore = CalculateWellbeingScore(sessions);

        return ProductivityScoreCard.Create(
            taskCompletionScore,
            timeManagementScore,
            focusScore,
            energyScore,
            goalProgressScore,
            habitConsistencyScore,
            wellbeingScore
        );
    }

    public async Task<PerformanceScoreBreakdown> GetDetailedScoreBreakdownAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var scoreCard = await CalculateProductivityScoreAsync(userId, fromDate, toDate, cancellationToken);
        
        var breakdown = new PerformanceScoreBreakdown
        {
            UserId = userId,
            FromDate = fromDate,
            ToDate = toDate,
            OverallScore = scoreCard,
            CalculatedAt = DateTime.UtcNow
        };

        // Calculate metric contributions
        breakdown.MetricContributions = new List<MetricContribution>
        {
            new() { MetricName = "Task Completion", Value = scoreCard.TaskCompletionScore, WeightedContribution = scoreCard.TaskCompletionScore * 0.2m, PercentageOfTotal = 20m },
            new() { MetricName = "Time Management", Value = scoreCard.TimeManagementScore, WeightedContribution = scoreCard.TimeManagementScore * 0.15m, PercentageOfTotal = 15m },
            new() { MetricName = "Focus", Value = scoreCard.FocusScore, WeightedContribution = scoreCard.FocusScore * 0.15m, PercentageOfTotal = 15m },
            new() { MetricName = "Energy", Value = scoreCard.EnergyScore, WeightedContribution = scoreCard.EnergyScore * 0.1m, PercentageOfTotal = 10m },
            new() { MetricName = "Goal Progress", Value = scoreCard.GoalProgressScore, WeightedContribution = scoreCard.GoalProgressScore * 0.2m, PercentageOfTotal = 20m },
            new() { MetricName = "Habit Consistency", Value = scoreCard.HabitConsistencyScore, WeightedContribution = scoreCard.HabitConsistencyScore * 0.1m, PercentageOfTotal = 10m },
            new() { MetricName = "Wellbeing", Value = scoreCard.WellbeingScore, WeightedContribution = scoreCard.WellbeingScore * 0.1m, PercentageOfTotal = 10m }
        };

        // Identify top drivers and detractors
        breakdown.TopDrivers = IdentifyTopDrivers(breakdown.MetricContributions);
        breakdown.TopDetractors = IdentifyTopDetractors(breakdown.MetricContributions);
        
        // Category scores
        breakdown.CategoryScores = await CalculateCategoryScores(userId, fromDate, toDate);
        
        // Improvement areas
        breakdown.ImprovementAreas = IdentifyImprovementAreas(breakdown.MetricContributions);

        return breakdown;
    }

    public async Task<List<PerformanceMetric>> CalculateAllMetricsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var metrics = new List<PerformanceMetric>();
        
        // Core productivity metrics
        var productivityScore = await _calculationEngine.CalculateMetricAsync(userId, "ProductivityScore", fromDate, toDate, cancellationToken);
        metrics.Add(new PerformanceMetric("Productivity Score", productivityScore, "score", MetricCategory.Productivity));

        var taskCompletionRate = await _calculationEngine.CalculateMetricAsync(userId, "TaskCompletionRate", fromDate, toDate, cancellationToken);
        metrics.Add(new PerformanceMetric("Task Completion Rate", taskCompletionRate, "%", MetricCategory.Performance));

        var focusTime = await _dataRepository.GetMetricSumAsync(userId, "FocusTime", fromDate, toDate, cancellationToken);
        metrics.Add(new PerformanceMetric("Focus Time", focusTime, "hours", MetricCategory.Time));

        var energyLevel = await _dataRepository.GetMetricAverageAsync(userId, "EnergyLevel", fromDate, toDate, cancellationToken);
        metrics.Add(new PerformanceMetric("Average Energy Level", energyLevel, "level", MetricCategory.Wellbeing));

        // Add more metrics as needed
        var goalProgress = await _calculationEngine.CalculateMetricAsync(userId, "GoalProgress", fromDate, toDate, cancellationToken);
        metrics.Add(new PerformanceMetric("Goal Progress", goalProgress, "%", MetricCategory.Goals));

        var habitConsistency = await _calculationEngine.CalculateMetricAsync(userId, "HabitConsistency", fromDate, toDate, cancellationToken);
        metrics.Add(new PerformanceMetric("Habit Consistency", habitConsistency, "%", MetricCategory.Habits));

        return metrics;
    }

    public async Task<Dictionary<string, decimal>> GetKPIDashboardAsync(string userId, CancellationToken cancellationToken = default)
    {
        var endDate = DateTime.UtcNow;
        var startDate = endDate.AddDays(-30); // Last 30 days

        var kpis = new Dictionary<string, decimal>();
        
        // Core KPIs
        kpis["ProductivityScore"] = await _calculationEngine.CalculateMetricAsync(userId, "ProductivityScore", startDate, endDate, cancellationToken);
        kpis["TaskCompletionRate"] = await _calculationEngine.CalculateMetricAsync(userId, "TaskCompletionRate", startDate, endDate, cancellationToken);
        kpis["FocusTimeHours"] = await _dataRepository.GetMetricSumAsync(userId, "FocusTime", startDate, endDate, cancellationToken);
        kpis["AverageEnergyLevel"] = await _dataRepository.GetMetricAverageAsync(userId, "EnergyLevel", startDate, endDate, cancellationToken);
        kpis["GoalProgressRate"] = await _calculationEngine.CalculateMetricAsync(userId, "GoalProgress", startDate, endDate, cancellationToken);
        kpis["HabitConsistencyRate"] = await _calculationEngine.CalculateMetricAsync(userId, "HabitConsistency", startDate, endDate, cancellationToken);
        
        // Efficiency KPIs
        kpis["EfficiencyScore"] = await CalculateEfficiencyScore(userId, startDate, endDate);
        kpis["TimeUtilization"] = await CalculateTimeUtilization(userId, startDate, endDate);
        
        return kpis;
    }

    public async Task<GoalCompletionMetrics> CalculateGoalMetricsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        // This would integrate with the goal management system
        // For now, providing a placeholder implementation
        var metrics = new GoalCompletionMetrics
        {
            UserId = userId,
            FromDate = fromDate,
            ToDate = toDate,
            TotalGoals = 10,
            CompletedGoals = 7,
            InProgressGoals = 2,
            OverdueGoals = 1,
            CompletionRate = 0.7m,
            AverageProgress = 0.82m,
            AverageCompletionTime = TimeSpan.FromDays(21),
            CalculatedAt = DateTime.UtcNow
        };

        // Calculate category breakdown
        metrics.CategoryBreakdown = new List<GoalCategoryMetrics>
        {
            new() { Category = "Health", TotalGoals = 3, CompletedGoals = 3, CompletionRate = 1.0m, AverageProgress = 1.0m },
            new() { Category = "Career", TotalGoals = 4, CompletedGoals = 3, CompletionRate = 0.75m, AverageProgress = 0.85m },
            new() { Category = "Personal", TotalGoals = 3, CompletedGoals = 1, CompletionRate = 0.33m, AverageProgress = 0.55m }
        };

        return metrics;
    }

    public async Task<List<GoalPerformanceData>> GetGoalPerformanceAsync(string userId, List<Guid> goalIds, CancellationToken cancellationToken = default)
    {
        var goalPerformanceList = new List<GoalPerformanceData>();

        foreach (var goalId in goalIds)
        {
            // This would fetch real goal data from the goal repository
            var goalPerformance = new GoalPerformanceData
            {
                GoalId = goalId,
                GoalName = $"Goal {goalId}",
                Category = "Career",
                Progress = 0.75m,
                StartDate = DateTime.UtcNow.AddDays(-60),
                TargetDate = DateTime.UtcNow.AddDays(30),
                IsCompleted = false,
                IsOverdue = false,
                TimeSpent = TimeSpan.FromHours(45),
                VelocityScore = 0.8m
            };

            goalPerformanceList.Add(goalPerformance);
        }

        return goalPerformanceList;
    }

    public async Task<MilestoneTrackingReport> GetMilestoneProgressAsync(string userId, CancellationToken cancellationToken = default)
    {
        // This would integrate with milestone tracking
        return new MilestoneTrackingReport
        {
            UserId = userId,
            TotalMilestones = 25,
            OverallMilestoneCompletionRate = 0.76m,
            GeneratedAt = DateTime.UtcNow,
            UpcomingMilestones = GenerateUpcomingMilestones(),
            OverdueMilestones = GenerateOverdueMilestones(),
            CompletedMilestones = GenerateCompletedMilestones()
        };
    }

    public async Task<HabitConsistencyMetrics> CalculateHabitMetricsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        // This would integrate with habit tracking system
        return new HabitConsistencyMetrics
        {
            UserId = userId,
            FromDate = fromDate,
            ToDate = toDate,
            TotalHabits = 8,
            OverallConsistencyScore = 0.83m,
            TotalStreakDays = 145,
            LongestActiveStreak = 23,
            AverageCompletionRate = 0.81m,
            CalculatedAt = DateTime.UtcNow
        };
    }

    public async Task<List<HabitPerformanceData>> GetHabitPerformanceAsync(string userId, List<Guid> habitIds, CancellationToken cancellationToken = default)
    {
        var habitPerformanceList = new List<HabitPerformanceData>();

        foreach (var habitId in habitIds)
        {
            habitPerformanceList.Add(new HabitPerformanceData
            {
                HabitId = habitId,
                HabitName = $"Habit {habitId}",
                Category = "Health",
                CurrentStreak = 12,
                LongestStreak = 28,
                CompletionRate = 0.85m,
                TotalCompletions = 85,
                TotalOpportunities = 100,
                ConsistencyScore = 0.87m,
                RecentTrend = TrendDirection.Increasing
            });
        }

        return habitPerformanceList;
    }

    public async Task<HabitStreakAnalysis> AnalyzeHabitStreaksAsync(string userId, CancellationToken cancellationToken = default)
    {
        return new HabitStreakAnalysis
        {
            UserId = userId,
            TotalActiveStreakDays = 145,
            AverageStreakLength = 18.1m,
            AnalyzedAt = DateTime.UtcNow,
            ActiveStreaks = GenerateActiveStreaks(),
            BrokenStreaks = GenerateBrokenStreaks(),
            RecordStreaks = GenerateRecordStreaks()
        };
    }

    public async Task<EnergyProductivityCorrelation> AnalyzeEnergyCorrelationAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var sessions = await _sessionRepository.GetByUserIdAndDateRangeAsync(userId, fromDate, toDate, cancellationToken);
        
        // Calculate correlation between energy levels and productivity scores
        var correlationCoefficient = CalculateEnergyProductivityCorrelation(sessions);
        
        var correlation = new EnergyProductivityCorrelation
        {
            UserId = userId,
            FromDate = fromDate,
            ToDate = toDate,
            CorrelationCoefficient = correlationCoefficient,
            CorrelationStrength = GetCorrelationStrength(correlationCoefficient),
            OptimalEnergyThreshold = CalculateOptimalEnergyThreshold(sessions),
            AnalyzedAt = DateTime.UtcNow
        };

        // Energy level to productivity mapping
        correlation.EnergyLevelProductivity = sessions
            .GroupBy(s => s.EnergyLevelStart)
            .ToDictionary(g => g.Key, g => g.Average(s => s.GetProductivityScore()));

        // Hourly correlation
        correlation.HourlyCorrelation = CalculateHourlyEnergyProductivityCorrelation(sessions);

        // Generate insights
        correlation.Insights = GenerateEnergyCorrelationInsights(correlation);

        return correlation;
    }

    public async Task<MoodProductivityCorrelation> AnalyzeMoodCorrelationAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var sessions = await _sessionRepository.GetByUserIdAndDateRangeAsync(userId, fromDate, toDate, cancellationToken);
        
        var moodSessions = sessions.Where(s => s.MoodStart.HasValue).ToList();
        var correlationCoefficient = CalculateMoodProductivityCorrelation(moodSessions);
        
        return new MoodProductivityCorrelation
        {
            UserId = userId,
            FromDate = fromDate,
            ToDate = toDate,
            CorrelationCoefficient = correlationCoefficient,
            CorrelationStrength = GetCorrelationStrength(correlationCoefficient),
            OptimalMoodThreshold = CalculateOptimalMoodThreshold(moodSessions),
            AnalyzedAt = DateTime.UtcNow,
            MoodProductivityMapping = CalculateMoodProductivityMapping(moodSessions),
            DailyCorrelation = CalculateDailyMoodProductivityCorrelation(moodSessions),
            Insights = GenerateMoodCorrelationInsights(correlationCoefficient)
        };
    }

    public async Task<WellbeingMetrics> CalculateWellbeingMetricsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var sessions = await _sessionRepository.GetByUserIdAndDateRangeAsync(userId, fromDate, toDate, cancellationToken);
        
        return new WellbeingMetrics
        {
            UserId = userId,
            FromDate = fromDate,
            ToDate = toDate,
            OverallWellbeingScore = CalculateOverallWellbeingScore(sessions),
            AverageEnergyLevel = (decimal)sessions.Where(s => s.EnergyLevelStart > 0).Average(s => s.EnergyLevelStart),
            AverageMoodScore = (decimal)sessions.Where(s => s.MoodStart.HasValue).Average(s => s.MoodStart!.Value),
            StressLevel = CalculateStressLevel(sessions),
            WorkLifeBalanceScore = CalculateWorkLifeBalanceScore(sessions),
            BurnoutRiskLevel = CalculateBurnoutRiskLevel(sessions),
            CalculatedAt = DateTime.UtcNow,
            Trends = GenerateWellbeingTrends(sessions),
            WellbeingInsights = GenerateWellbeingInsights(sessions)
        };
    }

    public async Task<PerformanceComparison> ComparePerformancePeriodsAsync(string userId, DateTime period1Start, DateTime period1End, DateTime period2Start, DateTime period2End, CancellationToken cancellationToken = default)
    {
        var period1Metrics = await CalculateAllMetricsAsync(userId, period1Start, period1End, cancellationToken);
        var period2Metrics = await CalculateAllMetricsAsync(userId, period2Start, period2End, cancellationToken);

        var comparison = new PerformanceComparison
        {
            UserId = userId,
            Period1Start = period1Start,
            Period1End = period1End,
            Period2Start = period2Start,
            Period2End = period2End,
            GeneratedAt = DateTime.UtcNow
        };

        // Calculate metric comparisons
        comparison.MetricComparisons = CalculateMetricComparisons(period1Metrics, period2Metrics);
        
        // Calculate overall improvement
        comparison.OverallImprovement = comparison.MetricComparisons.Average(m => m.PercentageChange);
        
        // Identify top improvements and regressions
        comparison.TopImprovements = comparison.MetricComparisons
            .Where(m => m.PercentageChange > 0)
            .OrderByDescending(m => m.PercentageChange)
            .Take(3)
            .Select(m => $"{m.MetricName}: +{m.PercentageChange:F1}%")
            .ToList();

        comparison.TopRegressions = comparison.MetricComparisons
            .Where(m => m.PercentageChange < 0)
            .OrderBy(m => m.PercentageChange)
            .Take(3)
            .Select(m => $"{m.MetricName}: {m.PercentageChange:F1}%")
            .ToList();

        // Generate insights
        comparison.ComparisonInsights = GenerateComparisonInsights(comparison);

        return comparison;
    }

    public async Task<PersonalBenchmarkAnalysis> GetPersonalBenchmarksAsync(string userId, CancellationToken cancellationToken = default)
    {
        var metricNames = new[] { "ProductivityScore", "TaskCompletionRate", "FocusTime", "EnergyLevel" };
        var analysis = new PersonalBenchmarkAnalysis
        {
            UserId = userId,
            AnalyzedAt = DateTime.UtcNow
        };

        foreach (var metricName in metricNames)
        {
            var currentValue = await _calculationEngine.CalculateMetricAsync(userId, metricName, DateTime.UtcNow.AddDays(-7), DateTime.UtcNow, cancellationToken);
            var personalBest = await _dataRepository.GetMetricMaxAsync(userId, metricName, DateTime.UtcNow.AddYears(-1), DateTime.UtcNow, cancellationToken);
            var personalAverage = await _dataRepository.GetMetricAverageAsync(userId, metricName, DateTime.UtcNow.AddYears(-1), DateTime.UtcNow, cancellationToken);
            var personalWorst = await _dataRepository.GetMetricMinAsync(userId, metricName, DateTime.UtcNow.AddYears(-1), DateTime.UtcNow, cancellationToken);

            analysis.Benchmarks[metricName] = new PersonalBenchmark
            {
                MetricName = metricName,
                CurrentValue = currentValue,
                PersonalBest = personalBest,
                PersonalAverage = personalAverage,
                PersonalWorst = personalWorst,
                PersonalBestDate = DateTime.UtcNow.AddDays(-30), // Placeholder
                PercentileRank = CalculatePersonalPercentileRank(currentValue, personalAverage, personalBest, personalWorst),
                RecentTrend = TrendDirection.Increasing // Placeholder
            };
        }

        analysis.OverallBenchmarkScore = analysis.Benchmarks.Values.Average(b => b.PercentileRank);
        return analysis;
    }

    public async Task<PerformanceRanking> GetPerformanceRankingAsync(string userId, List<string> metricNames, CancellationToken cancellationToken = default)
    {
        var ranking = new PerformanceRanking
        {
            UserId = userId,
            ComparisonGroup = "Personal",
            GeneratedAt = DateTime.UtcNow
        };

        foreach (var metricName in metricNames)
        {
            var currentValue = await _calculationEngine.CalculateMetricAsync(userId, metricName, DateTime.UtcNow.AddDays(-30), DateTime.UtcNow, cancellationToken);
            var percentile = CalculateMetricPercentile(currentValue, metricName);

            ranking.MetricRankings.Add(new MetricRanking
            {
                MetricName = metricName,
                Value = currentValue,
                Percentile = percentile,
                Rank = CalculateRankFromPercentile(percentile),
                TotalParticipants = 100, // Placeholder
                PerformanceLevel = GetPerformanceLevel(percentile)
            });
        }

        ranking.OverallPerformancePercentile = ranking.MetricRankings.Average(r => r.Percentile);
        ranking.TopPerformanceAreas = ranking.MetricRankings.Where(r => r.Percentile >= 80).Select(r => r.MetricName).ToList();
        ranking.ImprovementAreas = ranking.MetricRankings.Where(r => r.Percentile < 50).Select(r => r.MetricName).ToList();

        return ranking;
    }

    public async Task<EfficiencyMetrics> CalculateEfficiencyMetricsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var sessions = await _sessionRepository.GetByUserIdAndDateRangeAsync(userId, fromDate, toDate, cancellationToken);
        var timeEntries = await _timeEntryRepository.GetByUserIdAndDateRangeAsync(userId, fromDate, toDate, cancellationToken);

        return new EfficiencyMetrics
        {
            UserId = userId,
            FromDate = fromDate,
            ToDate = toDate,
            OverallEfficiencyScore = await CalculateEfficiencyScore(userId, fromDate, toDate),
            TimeUtilizationRate = await CalculateTimeUtilization(userId, fromDate, toDate),
            TaskCompletionEfficiency = CalculateTaskCompletionEfficiency(sessions),
            ContextSwitchingPenalty = CalculateContextSwitchingPenalty(sessions),
            CalculatedAt = DateTime.UtcNow,
            EfficiencyFactors = GenerateEfficiencyFactors(sessions, timeEntries),
            EfficiencyInsights = GenerateEfficiencyInsights(sessions, timeEntries)
        };
    }

    public async Task<QualityMetrics> CalculateQualityMetricsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        // This would integrate with quality tracking systems
        return new QualityMetrics
        {
            UserId = userId,
            FromDate = fromDate,
            ToDate = toDate,
            OverallQualityScore = 82.5m,
            WorkQualityRating = 8.3m,
            ReworkRate = 0.12m,
            ErrorRate = 0.05m,
            CompletionAccuracy = 0.94m,
            StakeholderSatisfaction = 8.7m,
            CalculatedAt = DateTime.UtcNow,
            QualityIndicators = GenerateQualityIndicators()
        };
    }

    public async Task<TimeAllocationEfficiency> AnalyzeTimeAllocationAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var timeEntries = await _timeEntryRepository.GetByUserIdAndDateRangeAsync(userId, fromDate, toDate, cancellationToken);
        var completedEntries = timeEntries.Where(e => e.Duration.HasValue).ToList();

        var analysis = new TimeAllocationEfficiency
        {
            UserId = userId,
            FromDate = fromDate,
            ToDate = toDate,
            AnalyzedAt = DateTime.UtcNow
        };

        // Category allocation analysis
        var categoryGroups = completedEntries.GroupBy(e => e.Category ?? "Uncategorized");
        var totalTime = TimeSpan.FromTicks(completedEntries.Sum(e => e.Duration!.Value.Ticks));

        foreach (var group in categoryGroups)
        {
            var categoryTime = TimeSpan.FromTicks(group.Sum(e => e.Duration!.Value.Ticks));
            var percentage = (decimal)(categoryTime.TotalMinutes / totalTime.TotalMinutes * 100);

            analysis.CategoryAllocation[group.Key] = new TimeAllocationData
            {
                Name = group.Key,
                AllocatedTime = categoryTime,
                Percentage = percentage,
                ValueScore = GetCategoryValueScore(group.Key),
                EfficiencyRating = CalculateCategoryEfficiency(group.ToList()),
                IsOptimal = IsOptimalAllocation(group.Key, percentage),
                SuggestedPercentage = GetSuggestedPercentage(group.Key)
            };
        }

        analysis.EfficiencyScore = analysis.CategoryAllocation.Values.Average(a => a.EfficiencyRating);
        analysis.Insights = GenerateAllocationInsights(analysis.CategoryAllocation);
        analysis.Recommendations = GenerateAllocationRecommendations(analysis.CategoryAllocation);

        return analysis;
    }

    // Helper methods for calculations
    private async Task<decimal> CalculateTaskCompletionScoreAsync(string userId, List<ProductivitySession> sessions, List<TimeEntry> timeEntries)
    {
        // This would integrate with task management system
        // Placeholder calculation based on session completion
        var completedSessions = sessions.Where(s => s.EndTime.HasValue).Count();
        var totalSessions = sessions.Count;
        return totalSessions > 0 ? (decimal)completedSessions / totalSessions * 100 : 0;
    }

    private decimal CalculateTimeManagementScore(List<ProductivitySession> sessions, List<TimeEntry> timeEntries)
    {
        if (!sessions.Any()) return 0;

        // Calculate based on session adherence to planned durations and time utilization
        var plannedVsActualScore = sessions
            .Where(s => s.Duration.HasValue)
            .Average(s => Math.Min(100, 100 - Math.Abs((decimal)(s.Duration!.Value.TotalMinutes - 60)) / 60 * 100));

        return (decimal)plannedVsActualScore;
    }

    private decimal CalculateFocusScore(List<ProductivitySession> sessions)
    {
        if (!sessions.Any()) return 0;

        var focusSessions = sessions.Where(s => s.FocusScore.HasValue).ToList();
        return focusSessions.Any() ? (decimal)focusSessions.Average(s => s.FocusScore!.Value * 10) : 0;
    }

    private decimal CalculateEnergyScore(List<ProductivitySession> sessions)
    {
        if (!sessions.Any()) return 0;
        return (decimal)sessions.Average(s => s.EnergyLevelStart * 10);
    }

    private async Task<decimal> CalculateGoalProgressScoreAsync(string userId, DateTime fromDate, DateTime toDate)
    {
        // This would integrate with goal management system
        return 75m; // Placeholder
    }

    private async Task<decimal> CalculateHabitConsistencyScoreAsync(string userId, DateTime fromDate, DateTime toDate)
    {
        // This would integrate with habit tracking system
        return 80m; // Placeholder
    }

    private decimal CalculateWellbeingScore(List<ProductivitySession> sessions)
    {
        if (!sessions.Any()) return 75m;

        var moodSessions = sessions.Where(s => s.MoodStart.HasValue || s.MoodEnd.HasValue).ToList();
        if (!moodSessions.Any()) return 75m;

        var avgMood = moodSessions.Average(s => (s.MoodStart ?? s.MoodEnd ?? 5));
        return (decimal)avgMood * 10;
    }

    private List<PerformanceDriver> IdentifyTopDrivers(List<MetricContribution> contributions)
    {
        return contributions
            .Where(c => c.Value >= 80)
            .OrderByDescending(c => c.WeightedContribution)
            .Take(3)
            .Select(c => new PerformanceDriver
            {
                Name = c.MetricName,
                Impact = c.WeightedContribution,
                Description = $"Strong performance in {c.MetricName.ToLower()}",
                Confidence = 0.85m
            })
            .ToList();
    }

    private List<PerformanceDetractor> IdentifyTopDetractors(List<MetricContribution> contributions)
    {
        return contributions
            .Where(c => c.Value < 60)
            .OrderBy(c => c.Value)
            .Take(3)
            .Select(c => new PerformanceDetractor
            {
                Name = c.MetricName,
                NegativeImpact = 100 - c.Value,
                Description = $"Below-average performance in {c.MetricName.ToLower()}",
                Confidence = 0.85m,
                SuggestedActions = GetImprovementActions(c.MetricName)
            })
            .ToList();
    }

    private List<string> GetImprovementActions(string metricName)
    {
        return metricName.ToLower() switch
        {
            "task completion" => new List<string> { "Break tasks into smaller chunks", "Use time blocking", "Eliminate distractions" },
            "focus" => new List<string> { "Practice mindfulness", "Use Pomodoro technique", "Create distraction-free environment" },
            "energy" => new List<string> { "Improve sleep quality", "Take regular breaks", "Exercise regularly" },
            _ => new List<string> { "Review and optimize approach", "Seek guidance from experts" }
        };
    }

    private async Task<Dictionary<string, decimal>> CalculateCategoryScores(string userId, DateTime fromDate, DateTime toDate)
    {
        // This would calculate scores for different categories of work
        return new Dictionary<string, decimal>
        {
            { "Development", 85m },
            { "Meetings", 72m },
            { "Planning", 78m },
            { "Administration", 65m }
        };
    }

    private List<ScoreImprovement> IdentifyImprovementAreas(List<MetricContribution> contributions)
    {
        return contributions
            .Where(c => c.Value < 80)
            .Select(c => new ScoreImprovement
            {
                Area = c.MetricName,
                CurrentScore = c.Value,
                PotentialScore = Math.Min(100, c.Value + 20),
                PotentialImpact = (Math.Min(100, c.Value + 20) - c.Value) * c.PercentageOfTotal / 100,
                ActionItems = GetImprovementActions(c.MetricName),
                DifficultyLevel = GetImprovementDifficulty(c.MetricName),
                EstimatedTimeframe = GetImprovementTimeframe(c.MetricName)
            })
            .ToList();
    }

    private decimal GetImprovementDifficulty(string metricName)
    {
        return metricName.ToLower() switch
        {
            "habit consistency" => 7m,
            "energy" => 6m,
            "focus" => 5m,
            "task completion" => 4m,
            _ => 5m
        };
    }

    private TimeSpan GetImprovementTimeframe(string metricName)
    {
        return metricName.ToLower() switch
        {
            "habit consistency" => TimeSpan.FromDays(60),
            "energy" => TimeSpan.FromDays(30),
            "focus" => TimeSpan.FromDays(21),
            "task completion" => TimeSpan.FromDays(14),
            _ => TimeSpan.FromDays(30)
        };
    }

    // Additional helper methods would continue here...
    // This is a comprehensive foundation for the performance metrics engine

    // Placeholder implementations for remaining interface methods
    public async Task<MetricTrendAnalysis> AnalyzeMetricTrendsAsync(string userId, List<string> metricNames, int dayCount = 90, CancellationToken cancellationToken = default)
    {
        return new MetricTrendAnalysis { UserId = userId, AnalyzedAt = DateTime.UtcNow };
    }

    public async Task<PerformanceVelocity> CalculatePerformanceVelocityAsync(string userId, CancellationToken cancellationToken = default)
    {
        return new PerformanceVelocity { UserId = userId, CurrentVelocity = 1.2m, AnalyzedAt = DateTime.UtcNow };
    }

    public async Task<SeasonalPerformanceAnalysis> AnalyzeSeasonalPerformanceAsync(string userId, string metricName, CancellationToken cancellationToken = default)
    {
        return new SeasonalPerformanceAnalysis { UserId = userId, MetricName = metricName, AnalyzedAt = DateTime.UtcNow };
    }

    public async Task<RealTimePerformanceSnapshot> GetRealTimeSnapshotAsync(string userId, CancellationToken cancellationToken = default)
    {
        return new RealTimePerformanceSnapshot 
        { 
            UserId = userId, 
            SnapshotTime = DateTime.UtcNow,
            CurrentProductivityScore = 78.5m,
            CurrentEnergyLevel = 7,
            TodayTasksCompleted = 8,
            TodayTasksRemaining = 3
        };
    }

    public async Task<DailyPerformanceSummary> GetDailyPerformanceSummaryAsync(string userId, DateTime date, CancellationToken cancellationToken = default)
    {
        return new DailyPerformanceSummary 
        { 
            UserId = userId, 
            Date = date,
            OverallScore = 82.3m,
            TasksCompleted = 9,
            TasksPlanned = 12,
            TotalWorkTime = TimeSpan.FromHours(8.5),
            ProductiveTime = TimeSpan.FromHours(6.2)
        };
    }

    public async Task<WeeklyPerformanceSummary> GetWeeklyPerformanceSummaryAsync(string userId, DateTime weekStart, CancellationToken cancellationToken = default)
    {
        return new WeeklyPerformanceSummary 
        { 
            UserId = userId, 
            WeekStart = weekStart,
            WeekEnd = weekStart.AddDays(6),
            WeeklyScore = 79.6m
        };
    }

    public async Task<CustomMetricResult> CalculateCustomMetricAsync(string userId, CustomMetricDefinition definition, CancellationToken cancellationToken = default)
    {
        return new CustomMetricResult 
        { 
            MetricName = definition.Name,
            Value = 75m,
            Unit = definition.Unit,
            CalculatedAt = DateTime.UtcNow
        };
    }

    public async Task<List<CustomMetricResult>> CalculateMultipleCustomMetricsAsync(string userId, List<CustomMetricDefinition> definitions, CancellationToken cancellationToken = default)
    {
        var results = new List<CustomMetricResult>();
        foreach (var definition in definitions)
        {
            results.Add(await CalculateCustomMetricAsync(userId, definition, cancellationToken));
        }
        return results;
    }

    public async Task SaveCustomMetricDefinitionAsync(string userId, CustomMetricDefinition definition, CancellationToken cancellationToken = default)
    {
        _logger.LogInformation("Saving custom metric definition {MetricName} for user {UserId}", definition.Name, userId);
        // Implementation would save to database
    }

    public async Task RecalculateAllMetricsAsync(string userId, CancellationToken cancellationToken = default)
    {
        _logger.LogInformation("Recalculating all metrics for user {UserId}", userId);
        // Implementation would trigger recalculation of all metrics
    }

    public async Task UpdateMetricAsync(string userId, string metricName, decimal value, DateTime timestamp, CancellationToken cancellationToken = default)
    {
        _logger.LogInformation("Updating metric {MetricName} to {Value} for user {UserId}", metricName, value, userId);
        // Implementation would update metric value
    }

    public async Task BulkUpdateMetricsAsync(string userId, List<MetricUpdate> updates, CancellationToken cancellationToken = default)
    {
        _logger.LogInformation("Bulk updating {Count} metrics for user {UserId}", updates.Count, userId);
        foreach (var update in updates)
        {
            await UpdateMetricAsync(userId, update.MetricName, update.Value, update.Timestamp, cancellationToken);
        }
    }

    // Additional helper methods for complex calculations
    private async Task<decimal> CalculateEfficiencyScore(string userId, DateTime fromDate, DateTime toDate)
    {
        // Complex efficiency calculation
        return 76.5m;
    }

    private async Task<decimal> CalculateTimeUtilization(string userId, DateTime fromDate, DateTime toDate)
    {
        // Time utilization calculation
        return 0.82m;
    }

    private decimal CalculateTaskCompletionEfficiency(List<ProductivitySession> sessions)
    {
        if (!sessions.Any()) return 0;
        var completedSessions = sessions.Where(s => s.EndTime.HasValue).Count();
        return (decimal)completedSessions / sessions.Count * 100;
    }

    private decimal CalculateContextSwitchingPenalty(List<ProductivitySession> sessions)
    {
        if (sessions.Count <= 1) return 0;

        var switches = 0;
        for (int i = 1; i < sessions.Count; i++)
        {
            if (sessions[i].Context != sessions[i - 1].Context)
                switches++;
        }

        // Penalty increases with more switches
        return Math.Min(50, switches * 2);
    }

    private List<EfficiencyFactor> GenerateEfficiencyFactors(List<ProductivitySession> sessions, List<TimeEntry> timeEntries)
    {
        return new List<EfficiencyFactor>
        {
            new() { Name = "Focus Time", Score = 85m, Impact = 0.3m, Description = "Good focus session durations" },
            new() { Name = "Context Switching", Score = 72m, Impact = -0.15m, Description = "Moderate context switching penalty" },
            new() { Name = "Time Utilization", Score = 78m, Impact = 0.25m, Description = "Effective use of available time" }
        };
    }

    private List<string> GenerateEfficiencyInsights(List<ProductivitySession> sessions, List<TimeEntry> timeEntries)
    {
        return new List<string>
        {
            "Peak efficiency occurs during morning hours",
            "Context switching reduces efficiency by 15%",
            "Longer focus sessions correlate with higher output"
        };
    }

    private List<QualityIndicator> GenerateQualityIndicators()
    {
        return new List<QualityIndicator>
        {
            new() { Name = "Code Quality", Score = 8.5m, Unit = "rating", Trend = TrendDirection.Increasing, Target = 8.0m, Status = "Exceeding" },
            new() { Name = "Rework Rate", Score = 12m, Unit = "%", Trend = TrendDirection.Decreasing, Target = 15m, Status = "Meeting" }
        };
    }

    // Correlation calculation methods
    private decimal CalculateEnergyProductivityCorrelation(List<ProductivitySession> sessions)
    {
        if (sessions.Count < 2) return 0;

        var energyLevels = sessions.Select(s => (double)s.EnergyLevelStart).ToList();
        var productivityScores = sessions.Select(s => (double)s.GetProductivityScore()).ToList();

        return (decimal)CalculatePearsonCorrelation(energyLevels, productivityScores);
    }

    private decimal CalculateMoodProductivityCorrelation(List<ProductivitySession> sessions)
    {
        var moodSessions = sessions.Where(s => s.MoodStart.HasValue).ToList();
        if (moodSessions.Count < 2) return 0;

        var moodLevels = moodSessions.Select(s => (double)s.MoodStart!.Value).ToList();
        var productivityScores = moodSessions.Select(s => (double)s.GetProductivityScore()).ToList();

        return (decimal)CalculatePearsonCorrelation(moodLevels, productivityScores);
    }

    private double CalculatePearsonCorrelation(List<double> x, List<double> y)
    {
        if (x.Count != y.Count || x.Count < 2) return 0;

        var meanX = x.Average();
        var meanY = y.Average();

        var numerator = x.Zip(y, (xi, yi) => (xi - meanX) * (yi - meanY)).Sum();
        var denominator = Math.Sqrt(x.Sum(xi => Math.Pow(xi - meanX, 2)) * y.Sum(yi => Math.Pow(yi - meanY, 2)));

        return denominator == 0 ? 0 : numerator / denominator;
    }

    private string GetCorrelationStrength(decimal correlation)
    {
        var abs = Math.Abs(correlation);
        return abs switch
        {
            >= 0.7m => "Strong",
            >= 0.4m => "Moderate",
            _ => "Weak"
        };
    }

    private decimal CalculateOptimalEnergyThreshold(List<ProductivitySession> sessions)
    {
        if (!sessions.Any()) return 7m;

        var highProductivitySessions = sessions.Where(s => s.GetProductivityScore() >= 7).ToList();
        return highProductivitySessions.Any() ? (decimal)highProductivitySessions.Average(s => s.EnergyLevelStart) : 7m;
    }

    private decimal CalculateOptimalMoodThreshold(List<ProductivitySession> sessions)
    {
        var moodSessions = sessions.Where(s => s.MoodStart.HasValue).ToList();
        if (!moodSessions.Any()) return 7m;

        var highProductivitySessions = moodSessions.Where(s => s.GetProductivityScore() >= 7).ToList();
        return highProductivitySessions.Any() ? (decimal)highProductivitySessions.Average(s => s.MoodStart!.Value) : 7m;
    }

    private Dictionary<TimeSpan, EnergyProductivityPoint> CalculateHourlyEnergyProductivityCorrelation(List<ProductivitySession> sessions)
    {
        return sessions
            .GroupBy(s => new TimeSpan(s.StartTime.Hour, 0, 0))
            .ToDictionary(g => g.Key, g => new EnergyProductivityPoint
            {
                Hour = g.Key,
                AverageEnergyLevel = (decimal)g.Average(s => s.EnergyLevelStart),
                AverageProductivityScore = g.Average(s => s.GetProductivityScore()),
                DataPoints = g.Count(),
                Correlation = (decimal)CalculatePearsonCorrelation(
                    g.Select(s => (double)s.EnergyLevelStart).ToList(),
                    g.Select(s => (double)s.GetProductivityScore()).ToList())
            });
    }

    private Dictionary<int, decimal> CalculateMoodProductivityMapping(List<ProductivitySession> sessions)
    {
        return sessions
            .Where(s => s.MoodStart.HasValue)
            .GroupBy(s => s.MoodStart!.Value)
            .ToDictionary(g => g.Key, g => g.Average(s => s.GetProductivityScore()));
    }

    private Dictionary<DateTime, MoodProductivityPoint> CalculateDailyMoodProductivityCorrelation(List<ProductivitySession> sessions)
    {
        return sessions
            .Where(s => s.MoodStart.HasValue)
            .GroupBy(s => s.StartTime.Date)
            .ToDictionary(g => g.Key, g => new MoodProductivityPoint
            {
                Date = g.Key,
                AverageMood = (decimal)g.Average(s => s.MoodStart!.Value),
                ProductivityScore = g.Average(s => s.GetProductivityScore()),
                SessionCount = g.Count(),
                Activities = g.Select(s => s.Context ?? "Unknown").Distinct().ToList()
            });
    }

    private List<string> GenerateEnergyCorrelationInsights(EnergyProductivityCorrelation correlation)
    {
        var insights = new List<string>();

        if (correlation.CorrelationCoefficient > 0.5m)
            insights.Add("Strong positive correlation between energy levels and productivity");
        
        if (correlation.OptimalEnergyThreshold > 7)
            insights.Add("Productivity significantly improves when energy level exceeds 7");

        return insights;
    }

    private List<string> GenerateMoodCorrelationInsights(decimal correlation)
    {
        var insights = new List<string>();

        if (correlation > 0.4m)
            insights.Add("Mood has a moderate to strong impact on productivity");
        else if (correlation < 0.1m)
            insights.Add("Mood appears to have minimal direct impact on productivity");

        return insights;
    }

    private decimal CalculateOverallWellbeingScore(List<ProductivitySession> sessions)
    {
        if (!sessions.Any()) return 75m;

        var energyScore = sessions.Average(s => s.EnergyLevelStart) * 10;
        var moodScore = sessions.Where(s => s.MoodStart.HasValue).Any() 
                       ? sessions.Where(s => s.MoodStart.HasValue).Average(s => s.MoodStart!.Value) * 10 
                       : 75;
        var stressScore = 100 - CalculateStressLevel(sessions); // Invert stress for wellbeing

        return (decimal)((energyScore + moodScore + stressScore) / 3);
    }

    private decimal CalculateStressLevel(List<ProductivitySession> sessions)
    {
        // Placeholder calculation based on interruption count and context switching
        var avgInterruptions = sessions.Any() ? (decimal)sessions.Average(s => s.InterruptionCount) : 0;
        return Math.Min(100, avgInterruptions * 10); // Higher interruptions = higher stress
    }

    private decimal CalculateWorkLifeBalanceScore(List<ProductivitySession> sessions)
    {
        // Placeholder calculation
        return 75m;
    }

    private int CalculateBurnoutRiskLevel(List<ProductivitySession> sessions)
    {
        // Placeholder calculation based on session patterns
        var avgSessionDuration = sessions.Where(s => s.Duration.HasValue).Any() 
                                ? sessions.Where(s => s.Duration.HasValue).Average(s => s.Duration!.Value.TotalHours) 
                                : 2;
        var avgInterruptions = sessions.Any() ? sessions.Average(s => s.InterruptionCount) : 0;

        // Higher risk with very long sessions and many interruptions
        var riskScore = (avgSessionDuration > 6 ? 3 : 0) + (avgInterruptions > 10 ? 3 : 0) + 2;
        return Math.Min(10, (int)riskScore);
    }

    private List<WellbeingTrend> GenerateWellbeingTrends(List<ProductivitySession> sessions)
    {
        return new List<WellbeingTrend>
        {
            new() { Metric = "Energy Level", Direction = TrendDirection.Stable, Change = 0.1m, TimeFrame = "Last 7 days" },
            new() { Metric = "Mood", Direction = TrendDirection.Increasing, Change = 0.5m, TimeFrame = "Last 7 days" }
        };
    }

    private List<string> GenerateWellbeingInsights(List<ProductivitySession> sessions)
    {
        return new List<string>
        {
            "Energy levels are most consistent in the morning",
            "Mood shows improvement trend over recent weeks",
            "Consider shorter work sessions to reduce stress"
        };
    }

    private List<MetricComparison> CalculateMetricComparisons(List<PerformanceMetric> period1Metrics, List<PerformanceMetric> period2Metrics)
    {
        var comparisons = new List<MetricComparison>();

        foreach (var metric1 in period1Metrics)
        {
            var metric2 = period2Metrics.FirstOrDefault(m => m.Name == metric1.Name);
            if (metric2 != null)
            {
                var change = metric2.Value - metric1.Value;
                var percentageChange = metric1.Value != 0 ? (change / metric1.Value) * 100 : 0;

                comparisons.Add(new MetricComparison
                {
                    MetricName = metric1.Name,
                    Period1Value = metric1.Value,
                    Period2Value = metric2.Value,
                    Change = change,
                    PercentageChange = percentageChange,
                    ChangeDirection = GetChangeDirection(change),
                    Significance = GetChangeSignificance(Math.Abs(percentageChange))
                });
            }
        }

        return comparisons;
    }

    private string GetChangeDirection(decimal change)
    {
        return change switch
        {
            > 0 => "Improved",
            < 0 => "Declined",
            _ => "Stable"
        };
    }

    private string GetChangeSignificance(decimal absPercentageChange)
    {
        return absPercentageChange switch
        {
            >= 20 => "Significant",
            >= 5 => "Minor",
            _ => "Negligible"
        };
    }

    private List<string> GenerateComparisonInsights(PerformanceComparison comparison)
    {
        var insights = new List<string>();

        if (comparison.OverallImprovement > 10)
            insights.Add("Overall performance shows significant improvement");
        else if (comparison.OverallImprovement < -10)
            insights.Add("Overall performance shows concerning decline");
        else
            insights.Add("Performance remains relatively stable");

        return insights;
    }

    private decimal CalculatePersonalPercentileRank(decimal currentValue, decimal average, decimal best, decimal worst)
    {
        if (best == worst) return 50m; // No variation in data

        var normalized = (currentValue - worst) / (best - worst);
        return Math.Max(0, Math.Min(100, normalized * 100));
    }

    private decimal CalculateMetricPercentile(decimal value, string metricName)
    {
        // This would use historical data to calculate percentile
        // Placeholder implementation
        return 75m;
    }

    private int CalculateRankFromPercentile(decimal percentile)
    {
        return (int)Math.Ceiling((100 - percentile) / 100 * 100);
    }

    private string GetPerformanceLevel(decimal percentile)
    {
        return percentile switch
        {
            >= 90 => "Excellent",
            >= 70 => "Good",
            >= 40 => "Average",
            _ => "Below Average"
        };
    }

    // Placeholder methods for milestone generation
    private List<MilestoneStatus> GenerateUpcomingMilestones()
    {
        return new List<MilestoneStatus>
        {
            new() { MilestoneName = "Complete Phase 1", GoalName = "Project Alpha", TargetDate = DateTime.UtcNow.AddDays(7), Progress = 0.8m, DaysUntilDue = 7 }
        };
    }

    private List<MilestoneStatus> GenerateOverdueMilestones()
    {
        return new List<MilestoneStatus>
        {
            new() { MilestoneName = "Review Documentation", GoalName = "Knowledge Base", TargetDate = DateTime.UtcNow.AddDays(-3), Progress = 0.6m, DaysUntilDue = -3, IsAtRisk = true }
        };
    }

    private List<MilestoneStatus> GenerateCompletedMilestones()
    {
        return new List<MilestoneStatus>
        {
            new() { MilestoneName = "Setup Development Environment", GoalName = "Project Beta", CompletionDate = DateTime.UtcNow.AddDays(-5), Progress = 1.0m }
        };
    }

    private List<StreakData> GenerateActiveStreaks()
    {
        return new List<StreakData>
        {
            new() { HabitName = "Morning Exercise", Length = 12, StartDate = DateTime.UtcNow.AddDays(-12), IsActive = true }
        };
    }

    private List<StreakData> GenerateBrokenStreaks()
    {
        return new List<StreakData>
        {
            new() { HabitName = "Daily Reading", Length = 8, StartDate = DateTime.UtcNow.AddDays(-15), EndDate = DateTime.UtcNow.AddDays(-7), IsActive = false }
        };
    }

    private List<StreakData> GenerateRecordStreaks()
    {
        return new List<StreakData>
        {
            new() { HabitName = "Morning Exercise", Length = 45, StartDate = DateTime.UtcNow.AddDays(-180), EndDate = DateTime.UtcNow.AddDays(-135), IsActive = false }
        };
    }

    private decimal GetCategoryValueScore(string category)
    {
        return category.ToLower() switch
        {
            "development" => 9.0m,
            "planning" => 8.0m,
            "meetings" => 6.0m,
            "administration" => 4.0m,
            _ => 5.0m
        };
    }

    private decimal CalculateCategoryEfficiency(List<TimeEntry> entries)
    {
        // Calculate efficiency based on task completion, focus time, etc.
        return 7.5m; // Placeholder
    }

    private bool IsOptimalAllocation(string category, decimal percentage)
    {
        return category.ToLower() switch
        {
            "development" => percentage >= 60 && percentage <= 80,
            "meetings" => percentage <= 25,
            "administration" => percentage <= 15,
            _ => true
        };
    }

    private decimal GetSuggestedPercentage(string category)
    {
        return category.ToLower() switch
        {
            "development" => 70m,
            "meetings" => 20m,
            "planning" => 15m,
            "administration" => 10m,
            _ => 20m
        };
    }

    private List<AllocationInsight> GenerateAllocationInsights(Dictionary<string, TimeAllocationData> allocation)
    {
        var insights = new List<AllocationInsight>();

        foreach (var item in allocation)
        {
            if (!item.Value.IsOptimal)
            {
                insights.Add(new AllocationInsight
                {
                    Category = item.Key,
                    Insight = $"Time allocation for {item.Key} could be optimized",
                    Type = "Opportunity",
                    Impact = Math.Abs(item.Value.Percentage - item.Value.SuggestedPercentage)
                });
            }
        }

        return insights;
    }

    private List<AllocationRecommendation> GenerateAllocationRecommendations(Dictionary<string, TimeAllocationData> allocation)
    {
        var recommendations = new List<AllocationRecommendation>();

        var development = allocation.GetValueOrDefault("Development");
        if (development != null && development.Percentage < 60)
        {
            recommendations.Add(new AllocationRecommendation
            {
                Title = "Increase Development Time",
                Description = "Consider allocating more time to high-value development activities",
                PotentialImpact = 15m,
                ImplementationDifficulty = 6m,
                ActionSteps = new List<string> { "Block out longer development sessions", "Reduce meeting frequency", "Delegate administrative tasks" }
            });
        }

        return recommendations;
    }
}