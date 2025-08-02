using HeyDav.Domain.Common.Base;
using HeyDav.Domain.Analytics.Enums;
using HeyDav.Domain.Analytics.ValueObjects;

namespace HeyDav.Domain.Analytics.Entities;

public class ProductivitySession : BaseEntity
{
    public string UserId { get; private set; }
    public DateTime StartTime { get; private set; }
    public DateTime? EndTime { get; private set; }
    public TimeSpan? Duration => EndTime?.Subtract(StartTime);
    public SessionType Type { get; private set; }
    public string? Context { get; private set; }
    public string? Description { get; private set; }
    public int EnergyLevelStart { get; private set; } // 1-10 scale
    public int? EnergyLevelEnd { get; private set; } // 1-10 scale
    public int? MoodStart { get; private set; } // 1-10 scale
    public int? MoodEnd { get; private set; } // 1-10 scale
    public int? FocusScore { get; private set; } // 1-10 scale
    public int InterruptionCount { get; private set; }
    public SessionMetrics Metrics { get; private set; }
    public List<string> Tags { get; private set; }
    public Dictionary<string, object> Metadata { get; private set; }

    private ProductivitySession() 
    {
        UserId = string.Empty;
        Tags = new List<string>();
        Metadata = new Dictionary<string, object>();
        Metrics = SessionMetrics.Empty();
    }

    public ProductivitySession(
        string userId,
        DateTime startTime,
        SessionType type,
        string? context = null,
        string? description = null,
        int energyLevelStart = 5,
        int? moodStart = null,
        List<string>? tags = null,
        Dictionary<string, object>? metadata = null)
    {
        if (string.IsNullOrWhiteSpace(userId))
            throw new ArgumentException("User ID cannot be empty", nameof(userId));
        
        if (energyLevelStart < 1 || energyLevelStart > 10)
            throw new ArgumentOutOfRangeException(nameof(energyLevelStart), "Energy level must be between 1 and 10");
        
        if (moodStart.HasValue && (moodStart < 1 || moodStart > 10))
            throw new ArgumentOutOfRangeException(nameof(moodStart), "Mood must be between 1 and 10");

        UserId = userId;
        StartTime = startTime;
        Type = type;
        Context = context;
        Description = description;
        EnergyLevelStart = energyLevelStart;
        MoodStart = moodStart;
        Tags = tags ?? new List<string>();
        Metadata = metadata ?? new Dictionary<string, object>();
        Metrics = SessionMetrics.Empty();
    }

    public void EndSession(
        DateTime endTime,
        int? energyLevelEnd = null,
        int? moodEnd = null,
        int? focusScore = null,
        int interruptionCount = 0,
        SessionMetrics? metrics = null)
    {
        if (endTime <= StartTime)
            throw new ArgumentException("End time must be after start time", nameof(endTime));
        
        if (energyLevelEnd.HasValue && (energyLevelEnd < 1 || energyLevelEnd > 10))
            throw new ArgumentOutOfRangeException(nameof(energyLevelEnd), "Energy level must be between 1 and 10");
        
        if (moodEnd.HasValue && (moodEnd < 1 || moodEnd > 10))
            throw new ArgumentOutOfRangeException(nameof(moodEnd), "Mood must be between 1 and 10");
        
        if (focusScore.HasValue && (focusScore < 1 || focusScore > 10))
            throw new ArgumentOutOfRangeException(nameof(focusScore), "Focus score must be between 1 and 10");

        EndTime = endTime;
        EnergyLevelEnd = energyLevelEnd;
        MoodEnd = moodEnd;
        FocusScore = focusScore;
        InterruptionCount = interruptionCount;
        Metrics = metrics ?? SessionMetrics.Empty();
    }

    public void UpdateMetrics(SessionMetrics metrics)
    {
        Metrics = metrics ?? throw new ArgumentNullException(nameof(metrics));
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

    public decimal GetProductivityScore()
    {
        if (!Duration.HasValue) return 0;

        // Calculate based on multiple factors
        var energyScore = EnergyLevelEnd ?? EnergyLevelStart;
        var moodScore = MoodEnd ?? MoodStart ?? 5;
        var focusContribution = FocusScore ?? 5;
        var interruptionPenalty = Math.Max(0, 10 - InterruptionCount);

        // Weighted average
        var score = (energyScore * 0.3m + moodScore * 0.2m + focusContribution * 0.3m + interruptionPenalty * 0.2m);
        return Math.Round(score, 1);
    }
}