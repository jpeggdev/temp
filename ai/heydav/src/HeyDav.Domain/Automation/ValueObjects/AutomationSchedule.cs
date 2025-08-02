using HeyDav.Domain.Common.Base;
using HeyDav.Domain.Automation.Enums;

namespace HeyDav.Domain.Automation.ValueObjects;

public class AutomationSchedule : ValueObject
{
    public AutomationScheduleType Type { get; private set; }
    public DateTime? StartDate { get; private set; }
    public DateTime? EndDate { get; private set; }
    public TimeOnly? TimeOfDay { get; private set; }
    public List<DayOfWeek> DaysOfWeek { get; private set; } = new();
    public List<int> DaysOfMonth { get; private set; } = new();
    public List<int> MonthsOfYear { get; private set; } = new();
    public TimeSpan? Interval { get; private set; }
    public string? CronExpression { get; private set; }
    public string? TimeZoneId { get; private set; }
    public Dictionary<string, object> CustomConfiguration { get; private set; } = new();

    public AutomationSchedule() { }

    public AutomationSchedule(
        AutomationScheduleType type,
        DateTime? startDate = null,
        DateTime? endDate = null,
        TimeOnly? timeOfDay = null,
        List<DayOfWeek>? daysOfWeek = null,
        List<int>? daysOfMonth = null,
        List<int>? monthsOfYear = null,
        TimeSpan? interval = null,
        string? cronExpression = null,
        string? timeZoneId = null,
        Dictionary<string, object>? customConfiguration = null)
    {
        Type = type;
        StartDate = startDate;
        EndDate = endDate;
        TimeOfDay = timeOfDay;
        DaysOfWeek = daysOfWeek ?? new List<DayOfWeek>();
        DaysOfMonth = daysOfMonth ?? new List<int>();
        MonthsOfYear = monthsOfYear ?? new List<int>();
        Interval = interval;
        CronExpression = cronExpression;
        TimeZoneId = timeZoneId ?? TimeZoneInfo.Local.Id;
        CustomConfiguration = customConfiguration ?? new Dictionary<string, object>();
    }

    // Factory methods for common schedule types
    public static AutomationSchedule CreateOnceSchedule(DateTime executeAt, string? timeZoneId = null)
    {
        return new AutomationSchedule(
            AutomationScheduleType.Once,
            startDate: executeAt,
            timeZoneId: timeZoneId);
    }

    public static AutomationSchedule CreateDailySchedule(TimeOnly timeOfDay, DateTime? startDate = null, DateTime? endDate = null, string? timeZoneId = null)
    {
        return new AutomationSchedule(
            AutomationScheduleType.Daily,
            startDate: startDate,
            endDate: endDate,
            timeOfDay: timeOfDay,
            timeZoneId: timeZoneId);
    }

    public static AutomationSchedule CreateWeeklySchedule(List<DayOfWeek> daysOfWeek, TimeOnly timeOfDay, DateTime? startDate = null, DateTime? endDate = null, string? timeZoneId = null)
    {
        return new AutomationSchedule(
            AutomationScheduleType.Weekly,
            startDate: startDate,
            endDate: endDate,
            timeOfDay: timeOfDay,
            daysOfWeek: daysOfWeek,
            timeZoneId: timeZoneId);
    }

    public static AutomationSchedule CreateMonthlySchedule(List<int> daysOfMonth, TimeOnly timeOfDay, DateTime? startDate = null, DateTime? endDate = null, string? timeZoneId = null)
    {
        return new AutomationSchedule(
            AutomationScheduleType.Monthly,
            startDate: startDate,
            endDate: endDate,
            timeOfDay: timeOfDay,
            daysOfMonth: daysOfMonth,
            timeZoneId: timeZoneId);
    }

    public static AutomationSchedule CreateIntervalSchedule(TimeSpan interval, DateTime? startDate = null, DateTime? endDate = null, string? timeZoneId = null)
    {
        return new AutomationSchedule(
            AutomationScheduleType.Interval,
            startDate: startDate,
            endDate: endDate,
            interval: interval,
            timeZoneId: timeZoneId);
    }

    public static AutomationSchedule CreateCronSchedule(string cronExpression, DateTime? startDate = null, DateTime? endDate = null, string? timeZoneId = null)
    {
        return new AutomationSchedule(
            AutomationScheduleType.Cron,
            startDate: startDate,
            endDate: endDate,
            cronExpression: cronExpression,
            timeZoneId: timeZoneId);
    }

    public bool ShouldExecuteNow()
    {
        var now = GetCurrentTime();
        
        if (StartDate.HasValue && now < StartDate.Value)
            return false;
            
        if (EndDate.HasValue && now > EndDate.Value)
            return false;

        return Type switch
        {
            AutomationScheduleType.Once => StartDate.HasValue && Math.Abs((now - StartDate.Value).TotalMinutes) < 1,
            AutomationScheduleType.Daily => ShouldExecuteDaily(now),
            AutomationScheduleType.Weekly => ShouldExecuteWeekly(now),
            AutomationScheduleType.Monthly => ShouldExecuteMonthly(now),
            AutomationScheduleType.Interval => ShouldExecuteInterval(now),
            AutomationScheduleType.Cron => ShouldExecuteCron(now),
            AutomationScheduleType.Custom => ShouldExecuteCustom(now),
            _ => false
        };
    }

    public DateTime? GetNextExecutionTime(DateTime? fromTime = null)
    {
        var from = fromTime ?? GetCurrentTime();
        
        if (EndDate.HasValue && from > EndDate.Value)
            return null;

        return Type switch
        {
            AutomationScheduleType.Once => StartDate > from ? StartDate : null,
            AutomationScheduleType.Daily => GetNextDailyExecution(from),
            AutomationScheduleType.Weekly => GetNextWeeklyExecution(from),
            AutomationScheduleType.Monthly => GetNextMonthlyExecution(from),
            AutomationScheduleType.Interval => GetNextIntervalExecution(from),
            AutomationScheduleType.Cron => GetNextCronExecution(from),
            AutomationScheduleType.Custom => GetNextCustomExecution(from),
            _ => null
        };
    }

    private DateTime GetCurrentTime()
    {
        var timeZone = TimeZoneInfo.FindSystemTimeZoneById(TimeZoneId ?? TimeZoneInfo.Local.Id);
        return TimeZoneInfo.ConvertTimeFromUtc(DateTime.UtcNow, timeZone);
    }

    private bool ShouldExecuteDaily(DateTime now)
    {
        if (!TimeOfDay.HasValue) return false;
        
        var currentTime = TimeOnly.FromDateTime(now);
        return Math.Abs((currentTime - TimeOfDay.Value).TotalMinutes) < 1;
    }

    private bool ShouldExecuteWeekly(DateTime now)
    {
        if (!TimeOfDay.HasValue || !DaysOfWeek.Any()) return false;
        
        var currentTime = TimeOnly.FromDateTime(now);
        var currentDay = now.DayOfWeek;
        
        return DaysOfWeek.Contains(currentDay) && Math.Abs((currentTime - TimeOfDay.Value).TotalMinutes) < 1;
    }

    private bool ShouldExecuteMonthly(DateTime now)
    {
        if (!TimeOfDay.HasValue || !DaysOfMonth.Any()) return false;
        
        var currentTime = TimeOnly.FromDateTime(now);
        var currentDay = now.Day;
        
        return DaysOfMonth.Contains(currentDay) && Math.Abs((currentTime - TimeOfDay.Value).TotalMinutes) < 1;
    }

    private bool ShouldExecuteInterval(DateTime now)
    {
        if (!Interval.HasValue || !StartDate.HasValue) return false;
        
        var elapsed = now - StartDate.Value;
        var intervalCount = elapsed.TotalMilliseconds / Interval.Value.TotalMilliseconds;
        
        return Math.Abs(intervalCount - Math.Round(intervalCount)) < 0.01;
    }

    private bool ShouldExecuteCron(DateTime now)
    {
        // This would require a CRON expression parser library
        // For now, return false as a placeholder
        return false;
    }

    private bool ShouldExecuteCustom(DateTime now)
    {
        // Custom logic would be implemented here
        return false;
    }

    private DateTime? GetNextDailyExecution(DateTime from)
    {
        if (!TimeOfDay.HasValue) return null;
        
        var today = DateOnly.FromDateTime(from);
        var targetTime = today.ToDateTime(TimeOfDay.Value);
        
        if (targetTime > from)
        {
            return targetTime;
        }
        
        return today.AddDays(1).ToDateTime(TimeOfDay.Value);
    }

    private DateTime? GetNextWeeklyExecution(DateTime from)
    {
        if (!TimeOfDay.HasValue || !DaysOfWeek.Any()) return null;
        
        var currentDay = from.DayOfWeek;
        var nextDays = DaysOfWeek.Where(d => d >= currentDay).OrderBy(d => d);
        
        if (nextDays.Any())
        {
            var nextDay = nextDays.First();
            var daysUntilNext = ((int)nextDay - (int)currentDay) % 7;
            var targetDate = DateOnly.FromDateTime(from.AddDays(daysUntilNext));
            var targetTime = targetDate.ToDateTime(TimeOfDay.Value);
            
            if (targetTime > from)
                return targetTime;
        }
        
        // Next week
        var firstDay = DaysOfWeek.OrderBy(d => d).First();
        var daysUntilNextWeek = 7 - (int)currentDay + (int)firstDay;
        var nextWeekDate = DateOnly.FromDateTime(from.AddDays(daysUntilNextWeek));
        return nextWeekDate.ToDateTime(TimeOfDay.Value);
    }

    private DateTime? GetNextMonthlyExecution(DateTime from)
    {
        if (!TimeOfDay.HasValue || !DaysOfMonth.Any()) return null;
        
        var currentDay = from.Day;
        var nextDays = DaysOfMonth.Where(d => d >= currentDay).OrderBy(d => d);
        
        if (nextDays.Any())
        {
            var nextDay = nextDays.First();
            var targetDate = new DateTime(from.Year, from.Month, Math.Min(nextDay, DateTime.DaysInMonth(from.Year, from.Month)));
            var targetTime = targetDate.Date.Add(TimeOfDay.Value.ToTimeSpan());
            
            if (targetTime > from)
                return targetTime;
        }
        
        // Next month
        var nextMonth = from.AddMonths(1);
        var firstDay = DaysOfMonth.OrderBy(d => d).First();
        var nextMonthDate = new DateTime(nextMonth.Year, nextMonth.Month, Math.Min(firstDay, DateTime.DaysInMonth(nextMonth.Year, nextMonth.Month)));
        return nextMonthDate.Date.Add(TimeOfDay.Value.ToTimeSpan());
    }

    private DateTime? GetNextIntervalExecution(DateTime from)
    {
        if (!Interval.HasValue || !StartDate.HasValue) return null;
        
        if (from < StartDate.Value)
            return StartDate.Value;
        
        var elapsed = from - StartDate.Value;
        var intervalCount = Math.Ceiling(elapsed.TotalMilliseconds / Interval.Value.TotalMilliseconds);
        
        return StartDate.Value.AddMilliseconds(intervalCount * Interval.Value.TotalMilliseconds);
    }

    private DateTime? GetNextCronExecution(DateTime from)
    {
        // This would require a CRON expression parser library
        return null;
    }

    private DateTime? GetNextCustomExecution(DateTime from)
    {
        // Custom logic would be implemented here
        return null;
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return Type;
        yield return StartDate?.ToString() ?? string.Empty;
        yield return EndDate?.ToString() ?? string.Empty;
        yield return TimeOfDay?.ToString() ?? string.Empty;
        yield return Interval?.ToString() ?? string.Empty;
        yield return CronExpression ?? string.Empty;
        yield return TimeZoneId ?? string.Empty;

        foreach (var day in DaysOfWeek.OrderBy(d => d))
        {
            yield return day;
        }

        foreach (var day in DaysOfMonth.OrderBy(d => d))
        {
            yield return day;
        }

        foreach (var month in MonthsOfYear.OrderBy(m => m))
        {
            yield return month;
        }

        foreach (var kvp in CustomConfiguration.OrderBy(kvp => kvp.Key))
        {
            yield return kvp.Key;
            yield return kvp.Value;
        }
    }
}