using HeyDav.Domain.Workflows.Enums;
using HeyDav.Domain.Workflows.ValueObjects;

namespace HeyDav.Application.Workflows.Models;

public class CreateWorkflowTemplateRequest
{
    public string Name { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public WorkflowCategory Category { get; set; }
    public WorkflowDifficulty Difficulty { get; set; }
    public TimeSpan EstimatedDuration { get; set; }
    public bool IsBuiltIn { get; set; }
    public string? CreatedBy { get; set; }
    public WorkflowTrigger? AutoTrigger { get; set; }
    public string? ConfigurationSchema { get; set; }
    public List<CreateStepTemplateRequest> StepTemplates { get; set; } = new();
    public List<string> Tags { get; set; } = new();
}

public class CreateStepTemplateRequest
{
    public string Name { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public WorkflowStepType Type { get; set; }
    public int Order { get; set; }
    public bool IsRequired { get; set; } = true;
    public string? Configuration { get; set; }
}

public class UpdateWorkflowTemplateRequest
{
    public string Name { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public WorkflowDifficulty Difficulty { get; set; }
    public TimeSpan EstimatedDuration { get; set; }
    public WorkflowTrigger? AutoTrigger { get; set; }
    public string? ConfigurationSchema { get; set; }
}

public class CreateWorkflowInstanceRequest
{
    public string? Name { get; set; }
    public string? UserId { get; set; }
    public WorkflowTrigger? TriggerSource { get; set; }
    public string? Configuration { get; set; }
}

public class WorkflowTemplateFilter
{
    public WorkflowCategory? Category { get; set; }
    public WorkflowDifficulty? Difficulty { get; set; }
    public bool? IsActive { get; set; }
    public bool? IsBuiltIn { get; set; }
    public string? CreatedBy { get; set; }
    public List<string>? Tags { get; set; }
    public TimeSpan? MaxDuration { get; set; }
    public decimal? MinRating { get; set; }
    public string? SearchText { get; set; }
}

public class WorkflowTemplateAnalytics
{
    public Guid TemplateId { get; set; }
    public string TemplateName { get; set; } = string.Empty;
    public int TotalUsage { get; set; }
    public decimal SuccessRate { get; set; }
    public TimeSpan? AverageCompletionTime { get; set; }
    public List<string> MostCommonFailurePoints { get; set; } = new();
    public decimal UserSatisfactionScore { get; set; }
    public List<WorkflowOptimizationSuggestion> OptimizationSuggestions { get; set; } = new();
}

public class WorkflowOptimizationSuggestion
{
    public OptimizationType Type { get; set; }
    public OptimizationPriority Priority { get; set; }
    public string Title { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public string Impact { get; set; } = string.Empty;
    public string? ActionRequired { get; set; }
    public Dictionary<string, object> Metadata { get; set; } = new();
}


public class WorkflowInsight
{
    public Guid Id { get; set; }
    public string Title { get; set; } = string.Empty;
    public string Description { get; set; } = string.Empty;
    public InsightType Type { get; set; }
    public InsightPriority Priority { get; set; }
    public Dictionary<string, object> Data { get; set; } = new();
    public DateTime GeneratedAt { get; set; }
    public bool IsActionable { get; set; }
    public string? RecommendedAction { get; set; }
}

public class ProductivityScore
{
    public string UserId { get; set; } = string.Empty;
    public DateTime FromDate { get; set; }
    public DateTime ToDate { get; set; }
    public decimal OverallScore { get; set; } // 0-100
    public decimal TaskCompletionScore { get; set; }
    public decimal GoalProgressScore { get; set; }
    public decimal HabitConsistencyScore { get; set; }
    public decimal WorkflowEfficiencyScore { get; set; }
    public decimal TimeManagementScore { get; set; }
    public List<ProductivityFactor> TopStrengths { get; set; } = new();
    public List<ProductivityFactor> ImprovementAreas { get; set; } = new();
    public List<string> Recommendations { get; set; } = new();
}

public class ProductivityFactor
{
    public string Name { get; set; } = string.Empty;
    public decimal Score { get; set; }
    public string Description { get; set; } = string.Empty;
    public ProductivityMetric Metric { get; set; }
}

public enum OptimizationType
{
    Structure,
    Steps,
    Timing,
    Configuration,
    UserExperience,
    Performance
}

public enum OptimizationPriority
{
    Low,
    Medium,
    High,
    Critical
}

public enum InsightType
{
    Pattern,
    Anomaly,
    Opportunity,
    Warning,
    Achievement,
    Recommendation
}

public enum InsightPriority
{
    Low,
    Medium,
    High,
    Urgent
}