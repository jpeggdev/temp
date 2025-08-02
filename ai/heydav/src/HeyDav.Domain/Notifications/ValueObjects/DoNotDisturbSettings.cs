using HeyDav.Domain.Common.Base;
using HeyDav.Domain.Notifications.Enums;

namespace HeyDav.Domain.Notifications.ValueObjects;

public class DoNotDisturbSettings : ValueObject
{
    public DoNotDisturbMode Mode { get; private set; } = DoNotDisturbMode.Disabled;
    public TimeOnly? StartTime { get; private set; }
    public TimeOnly? EndTime { get; private set; }
    public List<DayOfWeek> Days { get; private set; } = new();
    public DateTime? EnabledUntil { get; private set; }
    public List<NotificationPriority> AllowedPriorities { get; private set; } = new();
    public List<NotificationType> AllowedTypes { get; private set; } = new();
    public bool AllowBreakthrough { get; private set; } = true;

    public DoNotDisturbSettings() { }

    public DoNotDisturbSettings(
        DoNotDisturbMode mode,
        TimeOnly? startTime = null,
        TimeOnly? endTime = null,
        List<DayOfWeek>? days = null,
        DateTime? enabledUntil = null,
        List<NotificationPriority>? allowedPriorities = null,
        List<NotificationType>? allowedTypes = null,
        bool allowBreakthrough = true)
    {
        Mode = mode;
        StartTime = startTime;
        EndTime = endTime;
        Days = days ?? new List<DayOfWeek>();
        EnabledUntil = enabledUntil;
        AllowedPriorities = allowedPriorities ?? new List<NotificationPriority>();
        AllowedTypes = allowedTypes ?? new List<NotificationType>();
        AllowBreakthrough = allowBreakthrough;
    }

    public DoNotDisturbSettings Enable()
    {
        return new DoNotDisturbSettings(
            DoNotDisturbMode.Enabled, StartTime, EndTime, Days, EnabledUntil, 
            AllowedPriorities, AllowedTypes, AllowBreakthrough);
    }

    public DoNotDisturbSettings Disable()
    {
        return new DoNotDisturbSettings(
            DoNotDisturbMode.Disabled, StartTime, EndTime, Days, EnabledUntil, 
            AllowedPriorities, AllowedTypes, AllowBreakthrough);
    }

    public DoNotDisturbSettings WithSchedule(TimeOnly startTime, TimeOnly endTime, params DayOfWeek[] days)
    {
        return new DoNotDisturbSettings(
            DoNotDisturbMode.Schedule, startTime, endTime, days.ToList(), EnabledUntil, 
            AllowedPriorities, AllowedTypes, AllowBreakthrough);
    }

    public DoNotDisturbSettings EnableUntil(DateTime until)
    {
        return new DoNotDisturbSettings(
            DoNotDisturbMode.Enabled, StartTime, EndTime, Days, until, 
            AllowedPriorities, AllowedTypes, AllowBreakthrough);
    }

    public DoNotDisturbSettings WithFocusMode()
    {
        return new DoNotDisturbSettings(
            DoNotDisturbMode.Focus, StartTime, EndTime, Days, EnabledUntil, 
            new List<NotificationPriority> { NotificationPriority.Critical, NotificationPriority.Urgent }, 
            AllowedTypes, AllowBreakthrough);
    }

    public DoNotDisturbSettings WithMeetingMode()
    {
        return new DoNotDisturbSettings(
            DoNotDisturbMode.Meeting, StartTime, EndTime, Days, EnabledUntil, 
            new List<NotificationPriority> { NotificationPriority.Critical }, 
            new List<NotificationType> { NotificationType.SystemAlert, NotificationType.SecurityAlert }, 
            AllowBreakthrough);
    }

    public DoNotDisturbSettings WithSleepMode(TimeOnly bedtime, TimeOnly wakeTime)
    {
        return new DoNotDisturbSettings(
            DoNotDisturbMode.Sleep, bedtime, wakeTime, Days, EnabledUntil, 
            new List<NotificationPriority> { NotificationPriority.Critical }, 
            new List<NotificationType> { NotificationType.SystemAlert, NotificationType.SecurityAlert }, 
            false);
    }

    public DoNotDisturbSettings AllowPriority(NotificationPriority priority)
    {
        var newPriorities = new List<NotificationPriority>(AllowedPriorities);
        if (!newPriorities.Contains(priority))
        {
            newPriorities.Add(priority);
        }

        return new DoNotDisturbSettings(
            Mode, StartTime, EndTime, Days, EnabledUntil, 
            newPriorities, AllowedTypes, AllowBreakthrough);
    }

    public DoNotDisturbSettings AllowType(NotificationType type)
    {
        var newTypes = new List<NotificationType>(AllowedTypes);
        if (!newTypes.Contains(type))
        {
            newTypes.Add(type);
        }

        return new DoNotDisturbSettings(
            Mode, StartTime, EndTime, Days, EnabledUntil, 
            AllowedPriorities, newTypes, AllowBreakthrough);
    }

    public bool IsInDoNotDisturbPeriod(DateTime dateTime)
    {
        if (Mode == DoNotDisturbMode.Disabled)
            return false;

        if (Mode == DoNotDisturbMode.Enabled)
        {
            if (EnabledUntil.HasValue && dateTime > EnabledUntil.Value)
                return false;
            return true;
        }

        if (Mode == DoNotDisturbMode.Schedule)
        {
            if (!Days.Contains(dateTime.DayOfWeek))
                return false;

            if (StartTime.HasValue && EndTime.HasValue)
            {
                var time = TimeOnly.FromDateTime(dateTime);
                
                if (StartTime.Value <= EndTime.Value)
                {
                    return time >= StartTime.Value && time <= EndTime.Value;
                }
                else
                {
                    // Overnight schedule (e.g., 22:00 to 06:00)
                    return time >= StartTime.Value || time <= EndTime.Value;
                }
            }
        }

        if (Mode == DoNotDisturbMode.Focus || Mode == DoNotDisturbMode.Meeting || Mode == DoNotDisturbMode.Sleep)
        {
            return true;
        }

        return false;
    }

    public bool ShouldAllowNotification(NotificationType type, NotificationPriority priority)
    {
        if (Mode == DoNotDisturbMode.Disabled)
            return true;

        if (AllowedTypes.Contains(type))
            return true;

        if (AllowedPriorities.Contains(priority))
            return true;

        if (AllowBreakthrough && priority == NotificationPriority.Critical)
            return true;

        return false;
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return Mode;
        yield return StartTime?.ToString() ?? string.Empty;
        yield return EndTime?.ToString() ?? string.Empty;
        yield return EnabledUntil?.ToString() ?? string.Empty;
        yield return AllowBreakthrough;

        foreach (var day in Days.OrderBy(d => d))
        {
            yield return day;
        }

        foreach (var priority in AllowedPriorities.OrderBy(p => p))
        {
            yield return priority;
        }

        foreach (var type in AllowedTypes.OrderBy(t => t))
        {
            yield return type;
        }
    }
}