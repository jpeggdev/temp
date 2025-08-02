using HeyDav.Domain.Common.Base;
using HeyDav.Domain.Notifications.Enums;

namespace HeyDav.Domain.Notifications.ValueObjects;

public class NotificationSchedulingPreference : ValueObject
{
    public NotificationGroupingStrategy GroupingStrategy { get; private set; } = NotificationGroupingStrategy.Smart;
    public NotificationBatchingStrategy BatchingStrategy { get; private set; } = NotificationBatchingStrategy.Smart;
    public TimeSpan? BatchingWindow { get; private set; }
    public int? MaxBatchSize { get; private set; }
    public TimeSpan? MinimumDelay { get; private set; }
    public TimeSpan? MaximumDelay { get; private set; }
    public List<TimeOnly> PreferredTimes { get; private set; } = new();
    public bool RespectWorkingHours { get; private set; } = true;
    public TimeOnly WorkingHoursStart { get; private set; } = new(9, 0);
    public TimeOnly WorkingHoursEnd { get; private set; } = new(17, 0);
    public List<DayOfWeek> WorkingDays { get; private set; } = new() 
    { 
        DayOfWeek.Monday, DayOfWeek.Tuesday, DayOfWeek.Wednesday, 
        DayOfWeek.Thursday, DayOfWeek.Friday 
    };

    public NotificationSchedulingPreference() { }

    public NotificationSchedulingPreference(
        NotificationGroupingStrategy groupingStrategy = NotificationGroupingStrategy.Smart,
        NotificationBatchingStrategy batchingStrategy = NotificationBatchingStrategy.Smart,
        TimeSpan? batchingWindow = null,
        int? maxBatchSize = null,
        TimeSpan? minimumDelay = null,
        TimeSpan? maximumDelay = null,
        List<TimeOnly>? preferredTimes = null,
        bool respectWorkingHours = true,
        TimeOnly? workingHoursStart = null,
        TimeOnly? workingHoursEnd = null,
        List<DayOfWeek>? workingDays = null)
    {
        GroupingStrategy = groupingStrategy;
        BatchingStrategy = batchingStrategy;
        BatchingWindow = batchingWindow;
        MaxBatchSize = maxBatchSize;
        MinimumDelay = minimumDelay;
        MaximumDelay = maximumDelay;
        PreferredTimes = preferredTimes ?? new List<TimeOnly>();
        RespectWorkingHours = respectWorkingHours;
        WorkingHoursStart = workingHoursStart ?? new TimeOnly(9, 0);
        WorkingHoursEnd = workingHoursEnd ?? new TimeOnly(17, 0);
        WorkingDays = workingDays ?? new List<DayOfWeek> 
        { 
            DayOfWeek.Monday, DayOfWeek.Tuesday, DayOfWeek.Wednesday, 
            DayOfWeek.Thursday, DayOfWeek.Friday 
        };
    }

    public NotificationSchedulingPreference WithGroupingStrategy(NotificationGroupingStrategy strategy)
    {
        return new NotificationSchedulingPreference(
            strategy, BatchingStrategy, BatchingWindow, MaxBatchSize, MinimumDelay, MaximumDelay,
            PreferredTimes, RespectWorkingHours, WorkingHoursStart, WorkingHoursEnd, WorkingDays);
    }

    public NotificationSchedulingPreference WithBatchingStrategy(NotificationBatchingStrategy strategy, TimeSpan? window = null, int? maxSize = null)
    {
        return new NotificationSchedulingPreference(
            GroupingStrategy, strategy, window, maxSize, MinimumDelay, MaximumDelay,
            PreferredTimes, RespectWorkingHours, WorkingHoursStart, WorkingHoursEnd, WorkingDays);
    }

    public NotificationSchedulingPreference WithDelayConstraints(TimeSpan? minimumDelay, TimeSpan? maximumDelay)
    {
        return new NotificationSchedulingPreference(
            GroupingStrategy, BatchingStrategy, BatchingWindow, MaxBatchSize, minimumDelay, maximumDelay,
            PreferredTimes, RespectWorkingHours, WorkingHoursStart, WorkingHoursEnd, WorkingDays);
    }

    public NotificationSchedulingPreference WithPreferredTimes(params TimeOnly[] times)
    {
        return new NotificationSchedulingPreference(
            GroupingStrategy, BatchingStrategy, BatchingWindow, MaxBatchSize, MinimumDelay, MaximumDelay,
            times.ToList(), RespectWorkingHours, WorkingHoursStart, WorkingHoursEnd, WorkingDays);
    }

    public NotificationSchedulingPreference WithWorkingHours(TimeOnly start, TimeOnly end, params DayOfWeek[] days)
    {
        return new NotificationSchedulingPreference(
            GroupingStrategy, BatchingStrategy, BatchingWindow, MaxBatchSize, MinimumDelay, MaximumDelay,
            PreferredTimes, true, start, end, days.ToList());
    }

    public NotificationSchedulingPreference WithoutWorkingHours()
    {
        return new NotificationSchedulingPreference(
            GroupingStrategy, BatchingStrategy, BatchingWindow, MaxBatchSize, MinimumDelay, MaximumDelay,
            PreferredTimes, false, WorkingHoursStart, WorkingHoursEnd, WorkingDays);
    }

    public bool IsWithinWorkingHours(DateTime dateTime)
    {
        if (!RespectWorkingHours)
            return true;

        if (!WorkingDays.Contains(dateTime.DayOfWeek))
            return false;

        var time = TimeOnly.FromDateTime(dateTime);
        return time >= WorkingHoursStart && time <= WorkingHoursEnd;
    }

    public DateTime GetNextPreferredTime(DateTime from)
    {
        if (!PreferredTimes.Any())
            return from;

        var currentTime = TimeOnly.FromDateTime(from);
        var today = DateOnly.FromDateTime(from);

        // Find next preferred time today
        var nextTimeToday = PreferredTimes
            .Where(t => t > currentTime)
            .OrderBy(t => t)
            .FirstOrDefault();

        if (nextTimeToday != default)
        {
            var candidate = today.ToDateTime(nextTimeToday);
            if (IsWithinWorkingHours(candidate))
                return candidate;
        }

        // Find first preferred time tomorrow (or next working day)
        var nextDay = today.AddDays(1);
        while (!WorkingDays.Contains(nextDay.DayOfWeek) && RespectWorkingHours)
        {
            nextDay = nextDay.AddDays(1);
        }

        var firstTimeNextDay = PreferredTimes.OrderBy(t => t).First();
        return nextDay.ToDateTime(firstTimeNextDay);
    }

    public bool ShouldBatch(int currentBatchSize, TimeSpan timeSinceLastNotification)
    {
        return BatchingStrategy switch
        {
            NotificationBatchingStrategy.None => false,
            NotificationBatchingStrategy.Count => MaxBatchSize.HasValue && currentBatchSize < MaxBatchSize.Value,
            NotificationBatchingStrategy.TimeWindow => BatchingWindow.HasValue && timeSinceLastNotification < BatchingWindow.Value,
            NotificationBatchingStrategy.Priority => true, // Handled elsewhere based on priority
            NotificationBatchingStrategy.UserActivity => true, // Handled elsewhere based on user activity
            NotificationBatchingStrategy.Smart => true, // Complex logic handled elsewhere
            _ => false
        };
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return GroupingStrategy;
        yield return BatchingStrategy;
        yield return BatchingWindow?.ToString() ?? string.Empty;
        yield return MaxBatchSize ?? 0;
        yield return MinimumDelay?.ToString() ?? string.Empty;
        yield return MaximumDelay?.ToString() ?? string.Empty;
        yield return RespectWorkingHours;
        yield return WorkingHoursStart.ToString();
        yield return WorkingHoursEnd.ToString();

        foreach (var time in PreferredTimes.OrderBy(t => t))
        {
            yield return time.ToString();
        }

        foreach (var day in WorkingDays.OrderBy(d => d))
        {
            yield return day;
        }
    }
}