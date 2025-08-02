namespace HeyDav.Application.AgentManagement.Models;

public enum FallbackStrategy
{
    None,
    QueueAndRetry,
    AlternateAgent,
    HumanEscalation,
    DeferToLater,
    CascadeToMultiple
}

public enum AgentType
{
    Productivity,
    Analytics,
    Planning,
    Communication,
    Research,
    Automation,
    PersonalAssistant
}

public class FallbackOption
{
    public string OptionName { get; set; } = string.Empty;
    public AgentType AlternateAgentType { get; set; }
    public decimal SuccessProbability { get; set; }
    public TimeSpan EstimatedDelay { get; set; }
    public string Description { get; set; } = string.Empty;
}

public enum FallbackReason
{
    AgentUnavailable,
    TaskComplexityTooHigh,
    ResourceLimits,
    TimeoutExpired,
    QualityThresholdNotMet,
    UserRequest
}

public class FallbackAnalysis
{
    public TimeSpan AnalysisPeriod { get; set; }
    public int TotalFallbackEvents { get; set; }
    public Dictionary<FallbackReason, int> ReasonBreakdown { get; set; } = new();
    public decimal FallbackRate { get; set; }
    public List<string> Recommendations { get; set; } = new();
}

public class ScalingDecision
{
    public bool ShouldScale { get; set; }
    public ScalingDirection Direction { get; set; }
    public int RecommendedAgentCount { get; set; }
    public string Justification { get; set; } = string.Empty;
    public decimal Confidence { get; set; }
}

public enum ScalingDirection
{
    Up,
    Down,
    Maintain
}

public class LoadBalancingReport
{
    public DateTime GeneratedAt { get; set; }
    public Dictionary<AgentType, LoadMetrics> AgentLoads { get; set; } = new();
    public decimal OverallSystemLoad { get; set; }
    public List<string> LoadBalancingInsights { get; set; } = new();
}

public class LoadMetrics
{
    public decimal CurrentLoad { get; set; }
    public decimal AverageLoad { get; set; }
    public decimal PeakLoad { get; set; }
    public int ActiveTasks { get; set; }
    public int QueuedTasks { get; set; }
    public TimeSpan AverageResponseTime { get; set; }
}