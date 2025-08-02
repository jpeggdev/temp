using HeyDav.Domain.Common.Base;
using HeyDav.Domain.Analytics.Enums;
using HeyDav.Domain.Analytics.ValueObjects;

namespace HeyDav.Domain.Analytics.Entities;

public class TimeEntry : BaseEntity
{
    public string UserId { get; private set; }
    public DateTime StartTime { get; private set; }
    public DateTime? EndTime { get; private set; }
    public TimeSpan? Duration => EndTime?.Subtract(StartTime);
    public string Activity { get; private set; }
    public string? Project { get; private set; }
    public string? Category { get; private set; }
    public string? Description { get; private set; }
    public bool IsManual { get; private set; }
    public TimeTrackingSource Source { get; private set; }
    public List<string> Tags { get; private set; }
    public Dictionary<string, object> Metadata { get; private set; }
    public Guid? TaskId { get; private set; }
    public Guid? GoalId { get; private set; }
    public bool IsBillable { get; private set; }
    public decimal? HourlyRate { get; private set; }

    private TimeEntry() 
    {
        UserId = string.Empty;
        Activity = string.Empty;
        Tags = new List<string>();
        Metadata = new Dictionary<string, object>();
    }

    public TimeEntry(
        string userId,
        DateTime startTime,
        string activity,
        TimeTrackingSource source = TimeTrackingSource.Manual,
        string? project = null,
        string? category = null,
        string? description = null,
        bool isManual = true,
        List<string>? tags = null,
        Dictionary<string, object>? metadata = null,
        Guid? taskId = null,
        Guid? goalId = null,
        bool isBillable = false,
        decimal? hourlyRate = null)
    {
        if (string.IsNullOrWhiteSpace(userId))
            throw new ArgumentException("User ID cannot be empty", nameof(userId));
        
        if (string.IsNullOrWhiteSpace(activity))
            throw new ArgumentException("Activity cannot be empty", nameof(activity));

        UserId = userId;
        StartTime = startTime;
        Activity = activity;
        Source = source;
        Project = project;
        Category = category;
        Description = description;
        IsManual = isManual;
        Tags = tags ?? new List<string>();
        Metadata = metadata ?? new Dictionary<string, object>();
        TaskId = taskId;
        GoalId = goalId;
        IsBillable = isBillable;
        HourlyRate = hourlyRate;
    }

    public void Stop(DateTime endTime)
    {
        if (endTime <= StartTime)
            throw new ArgumentException("End time must be after start time", nameof(endTime));

        EndTime = endTime;
    }

    public void UpdateEndTime(DateTime endTime)
    {
        if (endTime <= StartTime)
            throw new ArgumentException("End time must be after start time", nameof(endTime));

        EndTime = endTime;
    }

    public void UpdateActivity(string activity)
    {
        if (string.IsNullOrWhiteSpace(activity))
            throw new ArgumentException("Activity cannot be empty", nameof(activity));

        Activity = activity;
    }

    public void UpdateProject(string? project)
    {
        Project = project;
    }

    public void UpdateCategory(string? category)
    {
        Category = category;
    }

    public void UpdateDescription(string? description)
    {
        Description = description;
    }

    public void AddTag(string tag)
    {
        if (!string.IsNullOrWhiteSpace(tag) && !Tags.Contains(tag))
        {
            Tags.Add(tag);
        }
    }

    public void RemoveTag(string tag)
    {
        Tags.Remove(tag);
    }

    public void UpdateMetadata(string key, object value)
    {
        if (!string.IsNullOrWhiteSpace(key))
        {
            Metadata[key] = value;
        }
    }

    public void MarkAsBillable(decimal? hourlyRate = null)
    {
        IsBillable = true;
        HourlyRate = hourlyRate;
    }

    public void MarkAsNonBillable()
    {
        IsBillable = false;
        HourlyRate = null;
    }

    public decimal? CalculateBillableAmount()
    {
        if (!IsBillable || !HourlyRate.HasValue || !Duration.HasValue)
            return null;

        return (decimal)Duration.Value.TotalHours * HourlyRate.Value;
    }

    public bool IsActive => !EndTime.HasValue;

    public TimeSpan GetCurrentDuration()
    {
        return EndTime?.Subtract(StartTime) ?? DateTime.UtcNow.Subtract(StartTime);
    }
}