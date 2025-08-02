using HeyDav.Domain.Common.Base;
using HeyDav.Domain.Automation.Entities;

namespace HeyDav.Domain.Automation.ValueObjects;

public class AutomationMetrics : ValueObject
{
    public int TotalExecutions { get; private set; } = 0;
    public int SuccessfulExecutions { get; private set; } = 0;
    public int FailedExecutions { get; private set; } = 0;
    public TimeSpan AverageExecutionTime { get; private set; } = TimeSpan.Zero;
    public TimeSpan TotalExecutionTime { get; private set; } = TimeSpan.Zero;
    public TimeSpan MinExecutionTime { get; private set; } = TimeSpan.MaxValue;
    public TimeSpan MaxExecutionTime { get; private set; } = TimeSpan.Zero;
    public DateTime? LastSuccessfulExecution { get; private set; }
    public DateTime? LastFailedExecution { get; private set; }
    public double SuccessRate { get; private set; } = 0.0;
    public Dictionary<string, int> ErrorCounts { get; private set; } = new();
    public List<DateTime> RecentExecutions { get; private set; } = new();

    public AutomationMetrics() { }

    public AutomationMetrics(
        int totalExecutions,
        int successfulExecutions,
        int failedExecutions,
        TimeSpan averageExecutionTime,
        TimeSpan totalExecutionTime,
        TimeSpan minExecutionTime,
        TimeSpan maxExecutionTime,
        DateTime? lastSuccessfulExecution,
        DateTime? lastFailedExecution,
        Dictionary<string, int>? errorCounts = null,
        List<DateTime>? recentExecutions = null)
    {
        TotalExecutions = totalExecutions;
        SuccessfulExecutions = successfulExecutions;
        FailedExecutions = failedExecutions;
        AverageExecutionTime = averageExecutionTime;
        TotalExecutionTime = totalExecutionTime;
        MinExecutionTime = minExecutionTime;
        MaxExecutionTime = maxExecutionTime;
        LastSuccessfulExecution = lastSuccessfulExecution;
        LastFailedExecution = lastFailedExecution;
        ErrorCounts = errorCounts ?? new Dictionary<string, int>();
        RecentExecutions = recentExecutions ?? new List<DateTime>();
        SuccessRate = totalExecutions > 0 ? (double)successfulExecutions / totalExecutions * 100 : 0.0;
    }

    public AutomationMetrics RecordExecution(AutomationExecution execution)
    {
        var newTotalExecutions = TotalExecutions + 1;
        var newSuccessfulExecutions = SuccessfulExecutions + (execution.Success ? 1 : 0);
        var newFailedExecutions = FailedExecutions + (execution.Success ? 0 : 1);
        
        var newTotalExecutionTime = TotalExecutionTime.Add(execution.Duration);
        var newAverageExecutionTime = TimeSpan.FromMilliseconds(newTotalExecutionTime.TotalMilliseconds / newTotalExecutions);
        
        var newMinExecutionTime = MinExecutionTime == TimeSpan.MaxValue 
            ? execution.Duration 
            : execution.Duration < MinExecutionTime ? execution.Duration : MinExecutionTime;
        var newMaxExecutionTime = execution.Duration > MaxExecutionTime ? execution.Duration : MaxExecutionTime;
        
        var newLastSuccessfulExecution = execution.Success ? execution.StartedAt : LastSuccessfulExecution;
        var newLastFailedExecution = !execution.Success ? execution.StartedAt : LastFailedExecution;
        
        var newErrorCounts = new Dictionary<string, int>(ErrorCounts);
        if (!execution.Success && !string.IsNullOrEmpty(execution.ErrorMessage))
        {
            var errorKey = execution.ErrorMessage.Length > 100 
                ? execution.ErrorMessage.Substring(0, 100) + "..." 
                : execution.ErrorMessage;
            newErrorCounts[errorKey] = newErrorCounts.GetValueOrDefault(errorKey, 0) + 1;
        }
        
        var newRecentExecutions = new List<DateTime>(RecentExecutions) { execution.StartedAt };
        if (newRecentExecutions.Count > 50) // Keep only last 50 executions
        {
            newRecentExecutions.RemoveAt(0);
        }
        
        return new AutomationMetrics(
            newTotalExecutions,
            newSuccessfulExecutions,
            newFailedExecutions,
            newAverageExecutionTime,
            newTotalExecutionTime,
            newMinExecutionTime,
            newMaxExecutionTime,
            newLastSuccessfulExecution,
            newLastFailedExecution,
            newErrorCounts,
            newRecentExecutions);
    }

    public AutomationMetrics Reset()
    {
        return new AutomationMetrics();
    }

    public bool IsHealthy()
    {
        return SuccessRate >= 90.0 && TotalExecutions > 0;
    }

    public TimeSpan? GetTimeSinceLastExecution()
    {
        if (!RecentExecutions.Any()) return null;
        return DateTime.UtcNow - RecentExecutions.Last();
    }

    public double GetExecutionsPerDay()
    {
        if (!RecentExecutions.Any()) return 0.0;
        
        var daysSinceFirst = (DateTime.UtcNow - RecentExecutions.First()).TotalDays;
        return daysSinceFirst > 0 ? RecentExecutions.Count / daysSinceFirst : 0.0;
    }

    public List<string> GetTopErrors(int count = 5)
    {
        return ErrorCounts
            .OrderByDescending(kvp => kvp.Value)
            .Take(count)
            .Select(kvp => $"{kvp.Key} ({kvp.Value} times)")
            .ToList();
    }

    public AutomationHealthScore GetHealthScore()
    {
        var score = 100.0;
        
        // Penalize low success rate
        if (SuccessRate < 95) score -= (95 - SuccessRate) * 2;
        if (SuccessRate < 80) score -= 20;
        if (SuccessRate < 50) score -= 40;
        
        // Penalize recent failures
        var recentFailures = RecentExecutions.TakeLast(10).Count() - (int)(SuccessRate / 100 * RecentExecutions.TakeLast(10).Count());
        if (recentFailures > 3) score -= recentFailures * 5;
        
        // Penalize long execution times
        if (AverageExecutionTime > TimeSpan.FromMinutes(5)) score -= 10;
        if (AverageExecutionTime > TimeSpan.FromMinutes(15)) score -= 20;
        
        return new AutomationHealthScore(Math.Max(0, score));
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return TotalExecutions;
        yield return SuccessfulExecutions;
        yield return FailedExecutions;
        yield return AverageExecutionTime;
        yield return TotalExecutionTime;
        yield return MinExecutionTime;
        yield return MaxExecutionTime;
        yield return LastSuccessfulExecution?.ToString() ?? string.Empty;
        yield return LastFailedExecution?.ToString() ?? string.Empty;
        yield return SuccessRate;

        foreach (var kvp in ErrorCounts.OrderBy(kvp => kvp.Key))
        {
            yield return kvp.Key;
            yield return kvp.Value;
        }

        foreach (var execution in RecentExecutions.OrderBy(e => e))
        {
            yield return execution;
        }
    }
}

public class AutomationHealthScore : ValueObject
{
    public double Score { get; private set; }
    public AutomationHealthLevel Level { get; private set; }
    public string Description { get; private set; } = string.Empty;

    public AutomationHealthScore(double score)
    {
        Score = Math.Max(0, Math.Min(100, score));
        Level = GetHealthLevel(Score);
        Description = GetHealthDescription(Level);
    }

    private static AutomationHealthLevel GetHealthLevel(double score)
    {
        return score switch
        {
            >= 90 => AutomationHealthLevel.Excellent,
            >= 80 => AutomationHealthLevel.Good,
            >= 60 => AutomationHealthLevel.Fair,
            >= 40 => AutomationHealthLevel.Poor,
            _ => AutomationHealthLevel.Critical
        };
    }

    private static string GetHealthDescription(AutomationHealthLevel level)
    {
        return level switch
        {
            AutomationHealthLevel.Excellent => "Automation is performing excellently with high success rate and optimal execution times.",
            AutomationHealthLevel.Good => "Automation is performing well with good success rate and acceptable execution times.",
            AutomationHealthLevel.Fair => "Automation is performing adequately but may need some attention to improve reliability.",
            AutomationHealthLevel.Poor => "Automation is experiencing issues and requires attention to improve performance.",
            AutomationHealthLevel.Critical => "Automation is in critical condition and needs immediate attention.",
            _ => "Unknown health status."
        };
    }

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return Score;
        yield return Level;
        yield return Description;
    }
}

public enum AutomationHealthLevel
{
    Critical = 0,
    Poor = 1,
    Fair = 2,
    Good = 3,
    Excellent = 4
}