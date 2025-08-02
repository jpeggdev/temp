using HeyDav.Domain.Common.Base;
using HeyDav.Domain.Workflows.Enums;
using HeyDav.Domain.Workflows.ValueObjects;

namespace HeyDav.Domain.Workflows.Entities;

public class Habit : AggregateRoot
{
    private readonly List<HabitEntry> _entries = new();
    private readonly List<string> _tags = new();

    public string Name { get; private set; }
    public string Description { get; private set; }
    public HabitType Type { get; private set; }
    public HabitFrequency Frequency { get; private set; }
    public TimeSpan? TargetDuration { get; private set; }
    public int? TargetCount { get; private set; }
    public string? TargetUnit { get; private set; }
    public bool IsActive { get; private set; }
    public DateTime StartDate { get; private set; }
    public DateTime? EndDate { get; private set; }
    public HabitPriority Priority { get; private set; }
    public string? Reminder { get; private set; } // JSON for reminder settings
    public int CurrentStreak { get; private set; }
    public int LongestStreak { get; private set; }
    public decimal CompletionRate { get; private set; } // 0-100
    public IReadOnlyList<HabitEntry> Entries => _entries.AsReadOnly();
    public IReadOnlyList<string> Tags => _tags.AsReadOnly();

    private Habit(
        string name,
        string description,
        HabitType type,
        HabitFrequency frequency,
        DateTime startDate)
    {
        Name = name;
        Description = description;
        Type = type;
        Frequency = frequency;
        StartDate = startDate;
        IsActive = true;
        Priority = HabitPriority.Medium;
        CurrentStreak = 0;
        LongestStreak = 0;
        CompletionRate = 0;
    }

    public static Habit Create(
        string name,
        string description,
        HabitType type,
        HabitFrequency frequency,
        DateTime startDate,
        TimeSpan? targetDuration = null,
        int? targetCount = null,
        string? targetUnit = null)
    {
        if (string.IsNullOrWhiteSpace(name))
            throw new ArgumentException("Habit name cannot be empty", nameof(name));

        var habit = new Habit(name, description, type, frequency, startDate)
        {
            TargetDuration = targetDuration,
            TargetCount = targetCount,
            TargetUnit = targetUnit
        };

        habit.AddDomainEvent(new HabitCreatedEvent(habit.Id, name));
        return habit;
    }

    public void UpdateDetails(string name, string description, HabitFrequency frequency, HabitPriority priority)
    {
        if (string.IsNullOrWhiteSpace(name))
            throw new ArgumentException("Habit name cannot be empty", nameof(name));

        Name = name;
        Description = description;
        Frequency = frequency;
        Priority = priority;
        UpdateTimestamp();
    }

    public void SetTarget(TimeSpan? duration, int? count, string? unit)
    {
        TargetDuration = duration;
        TargetCount = count;
        TargetUnit = unit;
        UpdateTimestamp();
    }

    public void SetReminder(string reminderSettings)
    {
        Reminder = reminderSettings;
        UpdateTimestamp();
    }

    public HabitEntry LogEntry(DateTime date, bool isCompleted, TimeSpan? actualDuration = null, int? actualCount = null, string? notes = null)
    {
        var existingEntry = _entries.FirstOrDefault(e => e.Date.Date == date.Date);
        if (existingEntry != null)
        {
            existingEntry.Update(isCompleted, actualDuration, actualCount, notes);
        }
        else
        {
            existingEntry = HabitEntry.Create(Id, date, isCompleted, actualDuration, actualCount, notes);
            _entries.Add(existingEntry);
        }

        RecalculateMetrics();
        UpdateTimestamp();
        return existingEntry;
    }

    public void RemoveEntry(Guid entryId)
    {
        var entry = _entries.FirstOrDefault(e => e.Id == entryId);
        if (entry != null)
        {
            _entries.Remove(entry);
            RecalculateMetrics();
            UpdateTimestamp();
        }
    }

    public void Activate()
    {
        IsActive = true;
        UpdateTimestamp();
    }

    public void Deactivate(DateTime? endDate = null)
    {
        IsActive = false;
        EndDate = endDate ?? DateTime.UtcNow;
        UpdateTimestamp();
        AddDomainEvent(new HabitDeactivatedEvent(Id, EndDate.Value));
    }

    public void AddTag(string tag)
    {
        if (!string.IsNullOrWhiteSpace(tag) && !_tags.Contains(tag))
        {
            _tags.Add(tag);
            UpdateTimestamp();
        }
    }

    public void RemoveTag(string tag)
    {
        if (_tags.Remove(tag))
        {
            UpdateTimestamp();
        }
    }

    private void RecalculateMetrics()
    {
        var orderedEntries = _entries.OrderBy(e => e.Date).ToList();
        if (!orderedEntries.Any())
        {
            CurrentStreak = 0;
            LongestStreak = 0;
            CompletionRate = 0;
            return;
        }

        // Calculate current streak
        CurrentStreak = 0;
        var today = DateTime.Today;
        for (int i = orderedEntries.Count - 1; i >= 0; i--)
        {
            var entry = orderedEntries[i];
            var expectedDate = today.AddDays(-(orderedEntries.Count - 1 - i));
            
            if (entry.Date.Date == expectedDate && entry.IsCompleted)
            {
                CurrentStreak++;
            }
            else if (entry.Date.Date < expectedDate)
            {
                break;
            }
        }

        // Calculate longest streak
        int longestStreak = 0;
        int currentStreakCount = 0;
        DateTime? lastDate = null;

        foreach (var entry in orderedEntries)
        {
            if (entry.IsCompleted)
            {
                if (lastDate == null || entry.Date.Date == lastDate.Value.AddDays(1))
                {
                    currentStreakCount++;
                }
                else
                {
                    longestStreak = Math.Max(longestStreak, currentStreakCount);
                    currentStreakCount = 1;
                }
                lastDate = entry.Date.Date;
            }
            else
            {
                longestStreak = Math.Max(longestStreak, currentStreakCount);
                currentStreakCount = 0;
                lastDate = null;
            }
        }
        LongestStreak = Math.Max(longestStreak, currentStreakCount);

        // Calculate completion rate
        var completedEntries = orderedEntries.Count(e => e.IsCompleted);
        CompletionRate = orderedEntries.Count > 0 ? (decimal)completedEntries / orderedEntries.Count * 100 : 0;
    }

    public HabitInsights GetInsights(DateTime fromDate, DateTime toDate)
    {
        var relevantEntries = _entries
            .Where(e => e.Date >= fromDate && e.Date <= toDate)
            .OrderBy(e => e.Date)
            .ToList();

        return new HabitInsights(
            Id,
            Name,
            fromDate,
            toDate,
            relevantEntries.Count,
            relevantEntries.Count(e => e.IsCompleted),
            relevantEntries.Count > 0 ? (decimal)relevantEntries.Count(e => e.IsCompleted) / relevantEntries.Count * 100 : 0,
            CurrentStreak,
            LongestStreak,
            CalculateAverageDuration(relevantEntries),
            CalculateAverageCount(relevantEntries),
            GetBestPerformanceDays(relevantEntries),
            GetWorstPerformanceDays(relevantEntries));
    }

    private TimeSpan? CalculateAverageDuration(List<HabitEntry> entries)
    {
        var durationsEntries = entries.Where(e => e.ActualDuration.HasValue).ToList();
        if (!durationsEntries.Any()) return null;

        var totalTicks = durationsEntries.Sum(e => e.ActualDuration!.Value.Ticks);
        return new TimeSpan(totalTicks / durationsEntries.Count);
    }

    private decimal? CalculateAverageCount(List<HabitEntry> entries)
    {
        var countEntries = entries.Where(e => e.ActualCount.HasValue).ToList();
        if (!countEntries.Any()) return null;

        return (decimal)countEntries.Average(e => e.ActualCount!.Value);
    }

    private List<DayOfWeek> GetBestPerformanceDays(List<HabitEntry> entries)
    {
        var dayPerformance = entries
            .GroupBy(e => e.Date.DayOfWeek)
            .Select(g => new { Day = g.Key, CompletionRate = (decimal)g.Count(e => e.IsCompleted) / g.Count() })
            .OrderByDescending(x => x.CompletionRate)
            .Take(3)
            .Select(x => x.Day)
            .ToList();

        return dayPerformance;
    }

    private List<DayOfWeek> GetWorstPerformanceDays(List<HabitEntry> entries)
    {
        var dayPerformance = entries
            .GroupBy(e => e.Date.DayOfWeek)
            .Select(g => new { Day = g.Key, CompletionRate = (decimal)g.Count(e => e.IsCompleted) / g.Count() })
            .OrderBy(x => x.CompletionRate)
            .Take(3)
            .Select(x => x.Day)
            .ToList();

        return dayPerformance;
    }
}

public class HabitEntry : BaseEntity
{
    public Guid HabitId { get; private set; }
    public DateTime Date { get; private set; }
    public bool IsCompleted { get; private set; }
    public TimeSpan? ActualDuration { get; private set; }
    public int? ActualCount { get; private set; }
    public string? Notes { get; private set; }
    public decimal Mood { get; private set; } // 1-10 scale
    public decimal Energy { get; private set; } // 1-10 scale

    private HabitEntry(
        Guid habitId,
        DateTime date,
        bool isCompleted,
        TimeSpan? actualDuration,
        int? actualCount,
        string? notes)
    {
        HabitId = habitId;
        Date = date.Date; // Ensure it's just the date part
        IsCompleted = isCompleted;
        ActualDuration = actualDuration;
        ActualCount = actualCount;
        Notes = notes;
        Mood = 5; // Default neutral
        Energy = 5; // Default neutral
    }

    public static HabitEntry Create(
        Guid habitId,
        DateTime date,
        bool isCompleted,
        TimeSpan? actualDuration = null,
        int? actualCount = null,
        string? notes = null)
    {
        return new HabitEntry(habitId, date, isCompleted, actualDuration, actualCount, notes);
    }

    public void Update(bool isCompleted, TimeSpan? actualDuration = null, int? actualCount = null, string? notes = null)
    {
        IsCompleted = isCompleted;
        ActualDuration = actualDuration;
        ActualCount = actualCount;
        Notes = notes;
        UpdateTimestamp();
    }

    public void SetMoodAndEnergy(decimal mood, decimal energy)
    {
        if (mood < 1 || mood > 10)
            throw new ArgumentOutOfRangeException(nameof(mood), "Mood must be between 1 and 10");

        if (energy < 1 || energy > 10)
            throw new ArgumentOutOfRangeException(nameof(energy), "Energy must be between 1 and 10");

        Mood = mood;
        Energy = energy;
        UpdateTimestamp();
    }
}

// Domain Events
public record HabitCreatedEvent(Guid HabitId, string Name) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record HabitDeactivatedEvent(Guid HabitId, DateTime EndDate) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}

public record HabitEntryLoggedEvent(Guid HabitId, Guid EntryId, DateTime Date, bool IsCompleted) : IDomainEvent
{
    public DateTime OccurredOn { get; } = DateTime.UtcNow;
}