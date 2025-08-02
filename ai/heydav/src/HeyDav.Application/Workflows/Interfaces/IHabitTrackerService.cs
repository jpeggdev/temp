using HeyDav.Domain.Workflows.Entities;
using HeyDav.Domain.Workflows.ValueObjects;
using HeyDav.Application.Workflows.Models;

namespace HeyDav.Application.Workflows.Interfaces;

public interface IHabitTrackerService
{
    Task<Habit> CreateHabitAsync(CreateHabitRequest request, CancellationToken cancellationToken = default);
    Task<HabitEntry> LogHabitEntryAsync(LogHabitEntryRequest request, CancellationToken cancellationToken = default);
    Task<List<Habit>> GetUserHabitsAsync(string userId, bool includeInactive = false, CancellationToken cancellationToken = default);
    Task<HabitInsights> GetHabitInsightsAsync(Guid habitId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<HabitDashboard> GetHabitDashboardAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<HabitRecommendation>> GetHabitRecommendationsAsync(string userId, CancellationToken cancellationToken = default);
    Task<HabitAnalyticsReport> GenerateAnalyticsReportAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<bool> UpdateHabitAsync(Guid habitId, UpdateHabitRequest request, CancellationToken cancellationToken = default);
    Task<bool> DeactivateHabitAsync(Guid habitId, DateTime? endDate = null, CancellationToken cancellationToken = default);
    Task<HabitStreakInfo> GetHabitStreakAsync(Guid habitId, CancellationToken cancellationToken = default);
}

public interface IHabitRepository
{
    Task<Habit?> GetByIdAsync(Guid id, CancellationToken cancellationToken = default);
    Task<List<Habit>> GetByUserIdAsync(string userId, bool includeInactive = false, CancellationToken cancellationToken = default);
    Task AddAsync(Habit habit, CancellationToken cancellationToken = default);
    Task<int> SaveChangesAsync(CancellationToken cancellationToken = default);
}

public interface IHabitAnalytics
{
    Task<UserHabitPatterns> AnalyzeUserPatternsAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<HabitTrend>> AnalyzeTrendsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<HabitCorrelationReport> AnalyzeCorrelationsAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<HabitPrediction>> PredictHabitSuccessAsync(string userId, List<Guid> habitIds, CancellationToken cancellationToken = default);
}

public interface IMotivationEngine
{
    Task ProcessHabitEntryAsync(Habit habit, HabitEntry entry, CancellationToken cancellationToken = default);
    Task<string> GetDailyMotivationAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<MotivationalMessage>> GetPersonalizedMessagesAsync(string userId, CancellationToken cancellationToken = default);
    Task SendEncouragementAsync(string userId, Guid habitId, MotivationTrigger trigger, CancellationToken cancellationToken = default);
}

public interface IGamificationService
{
    Task InitializeHabitTrackingAsync(Guid habitId, CancellationToken cancellationToken = default);
    Task RecordHabitCompletionAsync(Guid habitId, DateTime date, CancellationToken cancellationToken = default);
    Task<List<Achievement>> GetRecentAchievementsAsync(string userId, int days = 7, CancellationToken cancellationToken = default);
    Task<List<Achievement>> GetAchievementsInPeriodAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<UserGamificationProfile> GetUserProfileAsync(string userId, CancellationToken cancellationToken = default);
    Task<List<Badge>> GetAvailableBadgesAsync(string userId, CancellationToken cancellationToken = default);
}