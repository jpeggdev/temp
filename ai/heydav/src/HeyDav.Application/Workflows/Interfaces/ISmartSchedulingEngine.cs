using HeyDav.Domain.TodoManagement.Entities;
using HeyDav.Domain.Goals.Entities;
using HeyDav.Domain.Workflows.ValueObjects;
using HeyDav.Application.Workflows.Models;

namespace HeyDav.Application.Workflows.Interfaces;

public interface ISmartSchedulingEngine
{
    Task<ScheduleOptimizationResult> OptimizeScheduleAsync(OptimizeScheduleRequest request, CancellationToken cancellationToken = default);
    Task<TimeSlotRecommendation> RecommendTimeSlotAsync(TimeSlotRecommendationRequest request, CancellationToken cancellationToken = default);
    Task<List<ProductivityInsight>> AnalyzeProductivityPatternsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<ConflictResolution> ResolveSchedulingConflictAsync(SchedulingConflictRequest request, CancellationToken cancellationToken = default);
    Task<FocusTimeRecommendation> RecommendFocusTimeAsync(FocusTimeRequest request, CancellationToken cancellationToken = default);
}

public interface IProductivityPatternAnalyzer
{
    Task<UserProductivityPatterns> AnalyzeUserPatternsAsync(string userId, CancellationToken cancellationToken = default);
    Task<UserProductivityPatterns> AnalyzeUserPatternsAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default);
    Task<List<ProductivityPattern>> GetHistoricalPatternsAsync(string userId, int dayCount = 30, CancellationToken cancellationToken = default);
    Task UpdatePatternsAsync(string userId, ProductivityDataPoint dataPoint, CancellationToken cancellationToken = default);
}

public interface IEnergyLevelPredictor
{
    Task<Dictionary<TimeSpan, int>> PredictEnergyLevelsAsync(string userId, DateTime date, DateTime endDate, CancellationToken cancellationToken = default);
    Task<int> PredictEnergyLevelAsync(string userId, DateTime dateTime, CancellationToken cancellationToken = default);
    Task UpdateEnergyModelAsync(string userId, DateTime dateTime, int actualEnergyLevel, CancellationToken cancellationToken = default);
}

public interface ICalendarIntegration
{
    Task<List<CalendarCommitment>> GetCommitmentsAsync(string userId, DateTime startDate, DateTime endDate, CancellationToken cancellationToken = default);
    Task<bool> CreateEventAsync(string userId, ScheduledTask task, CancellationToken cancellationToken = default);
    Task<bool> UpdateEventAsync(string userId, string eventId, ScheduledTask task, CancellationToken cancellationToken = default);
    Task<bool> DeleteEventAsync(string userId, string eventId, CancellationToken cancellationToken = default);
    Task<List<AvailableTimeSlot>> FindAvailableSlotAsync(string userId, TimeSpan duration, DateTime preferredDate, CancellationToken cancellationToken = default);
}

public interface ISchedulingOptimizer
{
    Task<OptimizedSchedule> OptimizeAsync(SchedulingContext context, CancellationToken cancellationToken = default);
    Task<List<SchedulingConflict>> DetectConflictsAsync(List<ScheduledTask> tasks, CancellationToken cancellationToken = default);
    Task<decimal> CalculateScheduleScoreAsync(OptimizedSchedule schedule, SchedulingContext context, CancellationToken cancellationToken = default);
}