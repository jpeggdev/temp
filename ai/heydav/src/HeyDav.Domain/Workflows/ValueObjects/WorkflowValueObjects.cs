using HeyDav.Domain.Common.Base;
using HeyDav.Domain.Workflows.Enums;

namespace HeyDav.Domain.Workflows.ValueObjects;

public class WorkflowTrigger : ValueObject
{
    public TriggerType Type { get; }
    public string? Schedule { get; } // Cron expression for scheduled triggers
    public string? EventName { get; } // Event name for event triggers
    public string? Condition { get; } // Condition expression for conditional triggers
    public Dictionary<string, object> Parameters { get; }

    private WorkflowTrigger(TriggerType type, string? schedule = null, string? eventName = null, string? condition = null, Dictionary<string, object>? parameters = null)
    {
        Type = type;
        Schedule = schedule;
        EventName = eventName;
        Condition = condition;
        Parameters = parameters ?? new Dictionary<string, object>();
    }

    public static WorkflowTrigger Manual()
    {
        return new WorkflowTrigger(TriggerType.Manual);
    }

    public static WorkflowTrigger Scheduled(string cronExpression)
    {
        if (string.IsNullOrWhiteSpace(cronExpression))
            throw new ArgumentException("Cron expression cannot be empty", nameof(cronExpression));

        return new WorkflowTrigger(TriggerType.Scheduled, schedule: cronExpression);
    }

    public static WorkflowTrigger Event(string eventName, Dictionary<string, object>? parameters = null)
    {
        if (string.IsNullOrWhiteSpace(eventName))
            throw new ArgumentException("Event name cannot be empty", nameof(eventName));

        return new WorkflowTrigger(TriggerType.Event, eventName: eventName, parameters: parameters);
    }

    public static WorkflowTrigger Conditional(string condition, Dictionary<string, object>? parameters = null)
    {
        if (string.IsNullOrWhiteSpace(condition))
            throw new ArgumentException("Condition cannot be empty", nameof(condition));

        return new WorkflowTrigger(TriggerType.Conditional, condition: condition, parameters: parameters);
    }

    public static WorkflowTrigger Webhook(Dictionary<string, object> parameters)
    {
        return new WorkflowTrigger(TriggerType.Webhook, parameters: parameters);
    }

    public static WorkflowTrigger Integration(string integrationName, Dictionary<string, object> parameters)
    {
        parameters["integrationName"] = integrationName;
        return new WorkflowTrigger(TriggerType.Integration, parameters: parameters);
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return Type;
        yield return Schedule ?? "";
        yield return EventName ?? "";
        yield return Condition ?? "";
        foreach (var param in Parameters.OrderBy(x => x.Key))
        {
            yield return param;
        }
    }
}

public class WorkflowResult : ValueObject
{
    public bool IsSuccess { get; }
    public string? Message { get; }
    public Dictionary<string, object> Data { get; }
    public DateTime CreatedAt { get; }

    private WorkflowResult(bool isSuccess, string? message = null, Dictionary<string, object>? data = null)
    {
        IsSuccess = isSuccess;
        Message = message;
        Data = data ?? new Dictionary<string, object>();
        CreatedAt = DateTime.UtcNow;
    }

    public static WorkflowResult Success(string? message = null, Dictionary<string, object>? data = null)
    {
        return new WorkflowResult(true, message, data);
    }

    public static WorkflowResult Failed(string message, Dictionary<string, object>? data = null)
    {
        if (string.IsNullOrWhiteSpace(message))
            throw new ArgumentException("Failure message cannot be empty", nameof(message));

        return new WorkflowResult(false, message, data);
    }

    public static WorkflowResult Cancelled(string? reason = null)
    {
        return new WorkflowResult(false, reason ?? "Workflow was cancelled");
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return IsSuccess;
        yield return Message ?? "";
        yield return CreatedAt;
        foreach (var item in Data.OrderBy(x => x.Key))
        {
            yield return item;
        }
    }
}

public class SchedulingPreferences : ValueObject
{
    public SchedulingStrategy Strategy { get; }
    public TimeSpan? PreferredStartTime { get; }
    public TimeSpan? PreferredEndTime { get; }
    public List<DayOfWeek> PreferredDays { get; }
    public List<DayOfWeek> AvoidDays { get; }
    public TimeSpan? MinimumBreakDuration { get; }
    public TimeSpan? MaximumFocusTime { get; }
    public int EnergyThreshold { get; } // 1-10 scale
    public bool AllowWeekends { get; }
    public bool AllowInterruptions { get; }

    private SchedulingPreferences(
        SchedulingStrategy strategy,
        TimeSpan? preferredStartTime = null,
        TimeSpan? preferredEndTime = null,
        List<DayOfWeek>? preferredDays = null,
        List<DayOfWeek>? avoidDays = null,
        TimeSpan? minimumBreakDuration = null,
        TimeSpan? maximumFocusTime = null,
        int energyThreshold = 5,
        bool allowWeekends = true,
        bool allowInterruptions = false)
    {
        Strategy = strategy;
        PreferredStartTime = preferredStartTime;
        PreferredEndTime = preferredEndTime;
        PreferredDays = preferredDays ?? new List<DayOfWeek>();
        AvoidDays = avoidDays ?? new List<DayOfWeek>();
        MinimumBreakDuration = minimumBreakDuration;
        MaximumFocusTime = maximumFocusTime;
        EnergyThreshold = energyThreshold;
        AllowWeekends = allowWeekends;
        AllowInterruptions = allowInterruptions;
    }

    public static SchedulingPreferences Default()
    {
        return new SchedulingPreferences(SchedulingStrategy.Balanced);
    }

    public static SchedulingPreferences Create(
        SchedulingStrategy strategy,
        TimeSpan? preferredStartTime = null,
        TimeSpan? preferredEndTime = null,
        List<DayOfWeek>? preferredDays = null,
        List<DayOfWeek>? avoidDays = null,
        TimeSpan? minimumBreakDuration = null,
        TimeSpan? maximumFocusTime = null,
        int energyThreshold = 5,
        bool allowWeekends = true,
        bool allowInterruptions = false)
    {
        if (energyThreshold < 1 || energyThreshold > 10)
            throw new ArgumentOutOfRangeException(nameof(energyThreshold), "Energy threshold must be between 1 and 10");

        return new SchedulingPreferences(
            strategy,
            preferredStartTime,
            preferredEndTime,
            preferredDays,
            avoidDays,
            minimumBreakDuration,
            maximumFocusTime,
            energyThreshold,
            allowWeekends,
            allowInterruptions);
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return Strategy;
        yield return PreferredStartTime?.ToString() ?? "";
        yield return PreferredEndTime?.ToString() ?? "";
        foreach (var day in PreferredDays.OrderBy(x => x))
            yield return day;
        foreach (var day in AvoidDays.OrderBy(x => x))
            yield return day;
        yield return MinimumBreakDuration?.ToString() ?? "";
        yield return MaximumFocusTime?.ToString() ?? "";
        yield return EnergyThreshold;
        yield return AllowWeekends;
        yield return AllowInterruptions;
    }
}

public class HabitInsights : ValueObject
{
    public Guid HabitId { get; }
    public string HabitName { get; }
    public DateTime FromDate { get; }
    public DateTime ToDate { get; }
    public int TotalDays { get; }
    public int CompletedDays { get; }
    public decimal CompletionRate { get; }
    public int CurrentStreak { get; }
    public int LongestStreak { get; }
    public TimeSpan? AverageDuration { get; }
    public decimal? AverageCount { get; }
    public List<DayOfWeek> BestPerformanceDays { get; }
    public List<DayOfWeek> WorstPerformanceDays { get; }

    public HabitInsights(
        Guid habitId,
        string habitName,
        DateTime fromDate,
        DateTime toDate,
        int totalDays,
        int completedDays,
        decimal completionRate,
        int currentStreak,
        int longestStreak,
        TimeSpan? averageDuration,
        decimal? averageCount,
        List<DayOfWeek> bestPerformanceDays,
        List<DayOfWeek> worstPerformanceDays)
    {
        HabitId = habitId;
        HabitName = habitName;
        FromDate = fromDate;
        ToDate = toDate;
        TotalDays = totalDays;
        CompletedDays = completedDays;
        CompletionRate = completionRate;
        CurrentStreak = currentStreak;
        LongestStreak = longestStreak;
        AverageDuration = averageDuration;
        AverageCount = averageCount;
        BestPerformanceDays = bestPerformanceDays;
        WorstPerformanceDays = worstPerformanceDays;
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return HabitId;
        yield return HabitName;
        yield return FromDate;
        yield return ToDate;
        yield return TotalDays;
        yield return CompletedDays;
        yield return CompletionRate;
        yield return CurrentStreak;
        yield return LongestStreak;
        yield return AverageDuration?.ToString() ?? "";
        yield return AverageCount ?? 0;
        foreach (var day in BestPerformanceDays.OrderBy(x => x))
            yield return day;
        foreach (var day in WorstPerformanceDays.OrderBy(x => x))
            yield return day;
    }
}

public class ProductivityPattern : ValueObject
{
    public DayOfWeek DayOfWeek { get; }
    public TimeSpan TimeOfDay { get; }
    public ProductivityMetric Metric { get; }
    public decimal Value { get; }
    public decimal Confidence { get; } // 0-100
    public DateTime AnalyzedAt { get; }

    public ProductivityPattern(
        DayOfWeek dayOfWeek,
        TimeSpan timeOfDay,
        ProductivityMetric metric,
        decimal value,
        decimal confidence)
    {
        DayOfWeek = dayOfWeek;
        TimeOfDay = timeOfDay;
        Metric = metric;
        Value = value;
        Confidence = confidence;
        AnalyzedAt = DateTime.UtcNow;
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return DayOfWeek;
        yield return TimeOfDay;
        yield return Metric;
        yield return Value;
        yield return Confidence;
        yield return AnalyzedAt.Date;
    }
}