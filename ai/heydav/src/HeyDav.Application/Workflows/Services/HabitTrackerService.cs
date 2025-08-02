using HeyDav.Domain.Workflows.Entities;
using HeyDav.Domain.Workflows.Enums;
using HeyDav.Domain.Workflows.ValueObjects;
using HeyDav.Application.Workflows.Interfaces;
using HeyDav.Application.Workflows.Models;

namespace HeyDav.Application.Workflows.Services;

public class HabitTrackerService : IHabitTrackerService
{
    private readonly IHabitRepository _habitRepository;
    private readonly IHabitAnalytics _habitAnalytics;
    private readonly IMotivationEngine _motivationEngine;
    private readonly IGamificationService _gamificationService;

    public HabitTrackerService(
        IHabitRepository habitRepository,
        IHabitAnalytics habitAnalytics,
        IMotivationEngine motivationEngine,
        IGamificationService gamificationService)
    {
        _habitRepository = habitRepository;
        _habitAnalytics = habitAnalytics;
        _motivationEngine = motivationEngine;
        _gamificationService = gamificationService;
    }

    public async Task<Habit> CreateHabitAsync(CreateHabitRequest request, CancellationToken cancellationToken = default)
    {
        var habit = Habit.Create(
            request.Name,
            request.Description,
            request.Type,
            request.Frequency,
            request.StartDate,
            request.TargetDuration,
            request.TargetCount,
            request.TargetUnit);

        habit.SetTarget(request.TargetDuration, request.TargetCount, request.TargetUnit);
        habit.Priority = request.Priority;

        if (!string.IsNullOrEmpty(request.ReminderSettings))
        {
            habit.SetReminder(request.ReminderSettings);
        }

        foreach (var tag in request.Tags)
        {
            habit.AddTag(tag);
        }

        await _habitRepository.AddAsync(habit, cancellationToken);
        await _habitRepository.SaveChangesAsync(cancellationToken);

        // Initialize gamification tracking
        await _gamificationService.InitializeHabitTrackingAsync(habit.Id, cancellationToken);

        return habit;
    }

    public async Task<HabitEntry> LogHabitEntryAsync(LogHabitEntryRequest request, CancellationToken cancellationToken = default)
    {
        var habit = await _habitRepository.GetByIdAsync(request.HabitId, cancellationToken);
        if (habit == null)
            throw new ArgumentException($"Habit with ID {request.HabitId} not found");

        var entry = habit.LogEntry(
            request.Date,
            request.IsCompleted,
            request.ActualDuration,
            request.ActualCount,
            request.Notes);

        if (request.Mood.HasValue && request.Energy.HasValue)
        {
            entry.SetMoodAndEnergy(request.Mood.Value, request.Energy.Value);
        }

        await _habitRepository.SaveChangesAsync(cancellationToken);

        // Update gamification scores
        if (request.IsCompleted)
        {
            await _gamificationService.RecordHabitCompletionAsync(request.HabitId, request.Date, cancellationToken);
        }

        // Trigger motivational message if appropriate
        await _motivationEngine.ProcessHabitEntryAsync(habit, entry, cancellationToken);

        return entry;
    }

    public async Task<List<Habit>> GetUserHabitsAsync(string userId, bool includeInactive = false, CancellationToken cancellationToken = default)
    {
        return await _habitRepository.GetByUserIdAsync(userId, includeInactive, cancellationToken);
    }

    public async Task<HabitInsights> GetHabitInsightsAsync(Guid habitId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var habit = await _habitRepository.GetByIdAsync(habitId, cancellationToken);
        if (habit == null)
            throw new ArgumentException($"Habit with ID {habitId} not found");

        return habit.GetInsights(fromDate, toDate);
    }

    public async Task<HabitDashboard> GetHabitDashboardAsync(string userId, CancellationToken cancellationToken = default)
    {
        var habits = await _habitRepository.GetByUserIdAsync(userId, includeInactive: false, cancellationToken);
        var today = DateTime.Today;
        var thisWeek = today.AddDays(-(int)today.DayOfWeek);
        var thisMonth = new DateTime(today.Year, today.Month, 1);

        var dashboard = new HabitDashboard
        {
            UserId = userId,
            TotalActiveHabits = habits.Count,
            TodayCompletedHabits = await CountCompletedHabitsAsync(habits, today, today, cancellationToken),
            WeeklyProgress = await CalculateWeeklyProgressAsync(habits, thisWeek, cancellationToken),
            MonthlyProgress = await CalculateMonthlyProgressAsync(habits, thisMonth, cancellationToken),
            CurrentStreaks = habits.Select(h => new HabitStreakInfo
            {
                HabitId = h.Id,
                HabitName = h.Name,
                CurrentStreak = h.CurrentStreak,
                LongestStreak = h.LongestStreak
            }).ToList(),
            TodaysHabits = await GetTodaysHabitsAsync(habits, today, cancellationToken),
            RecentAchievements = await _gamificationService.GetRecentAchievementsAsync(userId, 7, cancellationToken),
            MotivationalMessage = await _motivationEngine.GetDailyMotivationAsync(userId, cancellationToken)
        };

        dashboard.OverallProgress = CalculateOverallProgress(dashboard);
        return dashboard;
    }

    public async Task<List<HabitRecommendation>> GetHabitRecommendationsAsync(string userId, CancellationToken cancellationToken = default)
    {
        var existingHabits = await _habitRepository.GetByUserIdAsync(userId, includeInactive: false, cancellationToken);
        var userPatterns = await _habitAnalytics.AnalyzeUserPatternsAsync(userId, cancellationToken);
        
        var recommendations = new List<HabitRecommendation>();

        // Recommend based on user patterns and missing areas
        var habitCategories = existingHabits.Select(h => h.Type).Distinct().ToList();

        // Health habits recommendation
        if (!habitCategories.Contains(HabitType.Positive) || !existingHabits.Any(h => h.Tags.Contains("health")))
        {
            recommendations.Add(new HabitRecommendation
            {
                Category = "Health & Wellness",
                Title = "Daily Exercise",
                Description = "Regular physical activity improves both physical and mental health.",
                SuggestedFrequency = HabitFrequency.Daily,
                EstimatedTime = TimeSpan.FromMinutes(30),
                Difficulty = HabitPriority.Medium,
                Benefits = new[] { "Increased energy", "Better mood", "Improved focus", "Better sleep" },
                StartingTips = new[] { "Start with 10 minutes daily", "Choose activities you enjoy", "Track your energy levels" }
            });
        }

        // Productivity habits
        if (!existingHabits.Any(h => h.Tags.Contains("productivity")))
        {
            recommendations.Add(new HabitRecommendation
            {
                Category = "Productivity",
                Title = "Daily Planning Session",
                Description = "Start each day by planning your priorities and schedule.",
                SuggestedFrequency = HabitFrequency.Daily,
                EstimatedTime = TimeSpan.FromMinutes(10),
                Difficulty = HabitPriority.Low,
                Benefits = new[] { "Better time management", "Reduced stress", "Clearer focus", "Higher achievement" },
                StartingTips = new[] { "Use the same time each day", "Keep it simple initially", "Review and adjust weekly" }
            });
        }

        // Learning habits
        if (!existingHabits.Any(h => h.Tags.Contains("learning")))
        {
            recommendations.Add(new HabitRecommendation
            {
                Category = "Personal Development",
                Title = "Daily Reading",
                Description = "Read for personal or professional development every day.",
                SuggestedFrequency = HabitFrequency.Daily,
                EstimatedTime = TimeSpan.FromMinutes(20),
                Difficulty = HabitPriority.Low,
                Benefits = new[] { "Expanded knowledge", "Improved vocabulary", "Better critical thinking", "Stress reduction" },
                StartingTips = new[] { "Start with just 5 pages", "Choose engaging topics", "Read at the same time daily" }
            });
        }

        // Mindfulness habits
        if (!existingHabits.Any(h => h.Tags.Contains("mindfulness")))
        {
            recommendations.Add(new HabitRecommendation
            {
                Category = "Mental Health",
                Title = "Meditation or Mindfulness",
                Description = "Practice daily meditation or mindfulness to reduce stress and improve focus.",
                SuggestedFrequency = HabitFrequency.Daily,
                EstimatedTime = TimeSpan.FromMinutes(10),
                Difficulty = HabitPriority.Medium,
                Benefits = new[] { "Reduced stress", "Better emotional regulation", "Improved focus", "Better sleep" },
                StartingTips = new[] { "Start with 2-3 minutes", "Use guided meditations", "Find a quiet space" }
            });
        }

        // Habit stacking recommendations based on existing successful habits
        var successfulHabits = existingHabits.Where(h => h.CompletionRate > 80).ToList();
        if (successfulHabits.Any())
        {
            var stackingHabit = successfulHabits.OrderByDescending(h => h.CurrentStreak).First();
            recommendations.Add(new HabitRecommendation
            {
                Category = "Habit Stacking",
                Title = $"Stack with '{stackingHabit.Name}'",
                Description = $"Add a new habit immediately after your successful '{stackingHabit.Name}' habit.",
                SuggestedFrequency = stackingHabit.Frequency,
                EstimatedTime = TimeSpan.FromMinutes(5),
                Difficulty = HabitPriority.Low,
                Benefits = new[] { "Higher success rate", "Automatic triggers", "Compound benefits" },
                StartingTips = new[] { "Make it very small initially", "Do it immediately after the existing habit", "Celebrate the completion" }
            });
        }

        return recommendations.OrderByDescending(r => CalculateRecommendationScore(r, userPatterns)).ToList();
    }

    public async Task<HabitAnalyticsReport> GenerateAnalyticsReportAsync(string userId, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken = default)
    {
        var habits = await _habitRepository.GetByUserIdAsync(userId, includeInactive: true, cancellationToken);
        var activeHabits = habits.Where(h => h.IsActive).ToList();

        var report = new HabitAnalyticsReport
        {
            UserId = userId,
            ReportPeriod = $"{fromDate:MMM dd, yyyy} - {toDate:MMM dd, yyyy}",
            TotalHabits = habits.Count,
            ActiveHabits = activeHabits.Count,
            CompletedDays = await CalculateCompletedDaysAsync(activeHabits, fromDate, toDate, cancellationToken),
            TotalPossibleDays = activeHabits.Sum(h => CalculatePossibleDays(h, fromDate, toDate)),
            OverallCompletionRate = 0, // Will be calculated below
            HabitInsights = new List<HabitInsights>(),
            Achievements = await _gamificationService.GetAchievementsInPeriodAsync(userId, fromDate, toDate, cancellationToken),
            Trends = await _habitAnalytics.AnalyzeTrendsAsync(userId, fromDate, toDate, cancellationToken),
            Recommendations = await GenerateImprovementRecommendationsAsync(activeHabits, fromDate, toDate, cancellationToken)
        };

        // Calculate overall completion rate
        if (report.TotalPossibleDays > 0)
        {
            report.OverallCompletionRate = (decimal)report.CompletedDays / report.TotalPossibleDays * 100;
        }

        // Generate insights for each habit
        foreach (var habit in activeHabits)
        {
            var insights = habit.GetInsights(fromDate, toDate);
            report.HabitInsights.Add(insights);
        }

        return report;
    }

    public async Task<bool> UpdateHabitAsync(Guid habitId, UpdateHabitRequest request, CancellationToken cancellationToken = default)
    {
        var habit = await _habitRepository.GetByIdAsync(habitId, cancellationToken);
        if (habit == null) return false;

        habit.UpdateDetails(request.Name, request.Description, request.Frequency, request.Priority);
        habit.SetTarget(request.TargetDuration, request.TargetCount, request.TargetUnit);

        if (!string.IsNullOrEmpty(request.ReminderSettings))
        {
            habit.SetReminder(request.ReminderSettings);
        }

        await _habitRepository.SaveChangesAsync(cancellationToken);
        return true;
    }

    public async Task<bool> DeactivateHabitAsync(Guid habitId, DateTime? endDate = null, CancellationToken cancellationToken = default)
    {
        var habit = await _habitRepository.GetByIdAsync(habitId, cancellationToken);
        if (habit == null) return false;

        habit.Deactivate(endDate);
        await _habitRepository.SaveChangesAsync(cancellationToken);
        return true;
    }

    public async Task<HabitStreakInfo> GetHabitStreakAsync(Guid habitId, CancellationToken cancellationToken = default)
    {
        var habit = await _habitRepository.GetByIdAsync(habitId, cancellationToken);
        if (habit == null)
            throw new ArgumentException($"Habit with ID {habitId} not found");

        return new HabitStreakInfo
        {
            HabitId = habit.Id,
            HabitName = habit.Name,
            CurrentStreak = habit.CurrentStreak,
            LongestStreak = habit.LongestStreak,
            LastCompletedDate = habit.Entries.Where(e => e.IsCompleted).MaxBy(e => e.Date)?.Date
        };
    }

    // Private helper methods
    private async Task<int> CountCompletedHabitsAsync(List<Habit> habits, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken)
    {
        int count = 0;
        foreach (var habit in habits)
        {
            var entries = habit.Entries.Where(e => e.Date >= fromDate && e.Date <= toDate && e.IsCompleted);
            count += entries.Count();
        }
        return count;
    }

    private async Task<decimal> CalculateWeeklyProgressAsync(List<Habit> habits, DateTime weekStartDate, CancellationToken cancellationToken)
    {
        var weekEndDate = weekStartDate.AddDays(6);
        var completedDays = await CountCompletedHabitsAsync(habits, weekStartDate, weekEndDate, cancellationToken);
        var possibleDays = habits.Sum(h => CalculatePossibleDays(h, weekStartDate, weekEndDate));
        
        return possibleDays > 0 ? (decimal)completedDays / possibleDays * 100 : 0;
    }

    private async Task<decimal> CalculateMonthlyProgressAsync(List<Habit> habits, DateTime monthStartDate, CancellationToken cancellationToken)
    {
        var monthEndDate = monthStartDate.AddMonths(1).AddDays(-1);
        var completedDays = await CountCompletedHabitsAsync(habits, monthStartDate, monthEndDate, cancellationToken);
        var possibleDays = habits.Sum(h => CalculatePossibleDays(h, monthStartDate, monthEndDate));
        
        return possibleDays > 0 ? (decimal)completedDays / possibleDays * 100 : 0;
    }

    private async Task<List<TodayHabitStatus>> GetTodaysHabitsAsync(List<Habit> habits, DateTime date, CancellationToken cancellationToken)
    {
        return habits.Select(habit => new TodayHabitStatus
        {
            HabitId = habit.Id,
            HabitName = habit.Name,
            IsCompleted = habit.Entries.Any(e => e.Date.Date == date.Date && e.IsCompleted),
            TargetDuration = habit.TargetDuration,
            TargetCount = habit.TargetCount,
            ActualDuration = habit.Entries.FirstOrDefault(e => e.Date.Date == date.Date)?.ActualDuration,
            ActualCount = habit.Entries.FirstOrDefault(e => e.Date.Date == date.Date)?.ActualCount,
            CurrentStreak = habit.CurrentStreak
        }).ToList();
    }

    private decimal CalculateOverallProgress(HabitDashboard dashboard)
    {
        if (dashboard.TotalActiveHabits == 0) return 0;
        
        var weeklyWeight = 0.4m;
        var monthlyWeight = 0.3m;
        var streakWeight = 0.3m;
        
        var avgStreak = dashboard.CurrentStreaks.Any() 
            ? dashboard.CurrentStreaks.Average(s => s.CurrentStreak) 
            : 0;
        var normalizedStreak = Math.Min(avgStreak / 30m * 100, 100); // Normalize to 100 for 30-day streak
        
        return (dashboard.WeeklyProgress * weeklyWeight) + 
               (dashboard.MonthlyProgress * monthlyWeight) + 
               (normalizedStreak * streakWeight);
    }

    private int CalculatePossibleDays(Habit habit, DateTime fromDate, DateTime toDate)
    {
        var days = (toDate - fromDate).Days + 1;
        
        return habit.Frequency switch
        {
            HabitFrequency.Daily => days,
            HabitFrequency.Weekly => days / 7,
            HabitFrequency.Monthly => days / 30,
            _ => days
        };
    }

    private async Task<int> CalculateCompletedDaysAsync(List<Habit> habits, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken)
    {
        int total = 0;
        foreach (var habit in habits)
        {
            var completedEntries = habit.Entries.Where(e => 
                e.Date >= fromDate && 
                e.Date <= toDate && 
                e.IsCompleted).Count();
            total += completedEntries;
        }
        return total;
    }

    private async Task<List<string>> GenerateImprovementRecommendationsAsync(List<Habit> habits, DateTime fromDate, DateTime toDate, CancellationToken cancellationToken)
    {
        var recommendations = new List<string>();

        // Analyze completion rates
        var lowPerformingHabits = habits.Where(h => h.CompletionRate < 60).ToList();
        if (lowPerformingHabits.Any())
        {
            recommendations.Add($"Consider simplifying or adjusting {lowPerformingHabits.Count} habits with low completion rates");
        }

        // Analyze streaks
        var habitsWithBrokenStreaks = habits.Where(h => h.LongestStreak > h.CurrentStreak && h.CurrentStreak < 7).ToList();
        if (habitsWithBrokenStreaks.Any())
        {
            recommendations.Add("Focus on rebuilding consistency for habits where you've lost your streak");
        }

        // Suggest habit stacking
        var consistentHabits = habits.Where(h => h.CurrentStreak >= 14).ToList();
        if (consistentHabits.Any() && habits.Count < 5)
        {
            recommendations.Add("Consider adding a new habit by stacking it with your most consistent habit");
        }

        return recommendations;
    }

    private decimal CalculateRecommendationScore(HabitRecommendation recommendation, UserHabitPatterns patterns)
    {
        decimal score = 50; // Base score

        // Adjust based on difficulty preference
        if (recommendation.Difficulty == HabitPriority.Low) score += 20;
        else if (recommendation.Difficulty == HabitPriority.High) score -= 10;

        // Adjust based on time commitment
        if (recommendation.EstimatedTime <= TimeSpan.FromMinutes(15)) score += 15;
        else if (recommendation.EstimatedTime >= TimeSpan.FromHours(1)) score -= 15;

        // Category preferences (would be based on user data)
        if (recommendation.Category == "Health & Wellness") score += 10;

        return Math.Max(0, Math.Min(100, score));
    }
}

// Supporting classes for patterns analysis
public class UserHabitPatterns
{
    public string UserId { get; set; } = string.Empty;
    public List<string> SuccessfulCategories { get; set; } = new();
    public TimeSpan PreferredHabitDuration { get; set; }
    public List<DayOfWeek> MostConsistentDays { get; set; } = new();
    public decimal AverageCompletionRate { get; set; }
    public HabitFrequency PreferredFrequency { get; set; }
    public List<string> MotivationalFactors { get; set; } = new();
}