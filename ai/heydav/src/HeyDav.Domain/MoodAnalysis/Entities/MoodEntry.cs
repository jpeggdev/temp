using HeyDav.Domain.Common.Base;

namespace HeyDav.Domain.MoodAnalysis.Entities;

public class MoodEntry : BaseEntity
{
    private readonly List<string> _activities = new();

    public DateTime RecordedAt { get; private set; }
    public MoodType Mood { get; private set; }
    public int EnergyLevel { get; private set; } // 1-10 scale
    public int StressLevel { get; private set; } // 1-10 scale
    public int FocusLevel { get; private set; } // 1-10 scale
    public DetectionMethod Method { get; private set; }
    public string? Notes { get; private set; }
    public double? Confidence { get; private set; } // For AI detections
    public string? ImageData { get; private set; } // Base64 encoded image snapshot
    public IReadOnlyList<string> Activities => _activities.AsReadOnly();

    private MoodEntry(
        MoodType mood,
        int energyLevel,
        int stressLevel,
        int focusLevel,
        DetectionMethod method)
    {
        RecordedAt = DateTime.UtcNow;
        Mood = mood;
        EnergyLevel = energyLevel;
        StressLevel = stressLevel;
        FocusLevel = focusLevel;
        Method = method;
    }

    public static MoodEntry CreateManual(
        MoodType mood,
        int energyLevel,
        int stressLevel,
        int focusLevel,
        string? notes = null)
    {
        ValidateLevels(energyLevel, stressLevel, focusLevel);

        var entry = new MoodEntry(mood, energyLevel, stressLevel, focusLevel, DetectionMethod.Manual)
        {
            Notes = notes
        };

        return entry;
    }

    public static MoodEntry CreateFromAI(
        MoodType mood,
        int energyLevel,
        int stressLevel,
        int focusLevel,
        double confidence,
        string? imageData = null)
    {
        ValidateLevels(energyLevel, stressLevel, focusLevel);

        if (confidence < 0 || confidence > 1)
            throw new ArgumentOutOfRangeException(nameof(confidence), "Confidence must be between 0 and 1");

        var entry = new MoodEntry(mood, energyLevel, stressLevel, focusLevel, DetectionMethod.AIDetected)
        {
            Confidence = confidence,
            ImageData = imageData
        };

        return entry;
    }

    private static void ValidateLevels(int energyLevel, int stressLevel, int focusLevel)
    {
        if (energyLevel < 1 || energyLevel > 10)
            throw new ArgumentOutOfRangeException(nameof(energyLevel), "Energy level must be between 1 and 10");

        if (stressLevel < 1 || stressLevel > 10)
            throw new ArgumentOutOfRangeException(nameof(stressLevel), "Stress level must be between 1 and 10");

        if (focusLevel < 1 || focusLevel > 10)
            throw new ArgumentOutOfRangeException(nameof(focusLevel), "Focus level must be between 1 and 10");
    }

    public void AddNote(string note)
    {
        if (!string.IsNullOrWhiteSpace(note))
        {
            Notes = string.IsNullOrEmpty(Notes) ? note : $"{Notes}\n{note}";
            UpdateTimestamp();
        }
    }

    public void AddActivity(string activity)
    {
        if (!string.IsNullOrWhiteSpace(activity) && !_activities.Contains(activity, StringComparer.OrdinalIgnoreCase))
        {
            _activities.Add(activity);
            UpdateTimestamp();
        }
    }

    public void RemoveActivity(string activity)
    {
        if (_activities.Remove(activity))
        {
            UpdateTimestamp();
        }
    }

    public void CorrectMood(MoodType mood, int? energyLevel = null, int? stressLevel = null, int? focusLevel = null)
    {
        Mood = mood;
        
        if (energyLevel.HasValue)
        {
            if (energyLevel.Value < 1 || energyLevel.Value > 10)
                throw new ArgumentOutOfRangeException(nameof(energyLevel), "Energy level must be between 1 and 10");
            EnergyLevel = energyLevel.Value;
        }

        if (stressLevel.HasValue)
        {
            if (stressLevel.Value < 1 || stressLevel.Value > 10)
                throw new ArgumentOutOfRangeException(nameof(stressLevel), "Stress level must be between 1 and 10");
            StressLevel = stressLevel.Value;
        }

        if (focusLevel.HasValue)
        {
            if (focusLevel.Value < 1 || focusLevel.Value > 10)
                throw new ArgumentOutOfRangeException(nameof(focusLevel), "Focus level must be between 1 and 10");
            FocusLevel = focusLevel.Value;
        }

        Method = DetectionMethod.Corrected;
        UpdateTimestamp();
    }
}

public enum MoodType
{
    VeryHappy,
    Happy,
    Content,
    Neutral,
    Tired,
    Stressed,
    Frustrated,
    Sad,
    Anxious
}

public enum DetectionMethod
{
    Manual,
    AIDetected,
    Corrected
}