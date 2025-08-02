using HeyDav.Domain.AgentManagement.Entities;

namespace HeyDav.Application.AgentManagement.Services;

public interface IAgentWorkflowEngine
{
    Task<Guid> CreateWorkflowAsync(WorkflowDefinition definition, CancellationToken cancellationToken = default);
    Task<bool> StartWorkflowAsync(Guid workflowId, Dictionary<string, object>? initialData = null, CancellationToken cancellationToken = default);
    Task<bool> PauseWorkflowAsync(Guid workflowId, CancellationToken cancellationToken = default);
    Task<bool> ResumeWorkflowAsync(Guid workflowId, CancellationToken cancellationToken = default);
    Task<bool> CancelWorkflowAsync(Guid workflowId, string reason, CancellationToken cancellationToken = default);
    Task<WorkflowExecution?> GetWorkflowExecutionAsync(Guid workflowId, CancellationToken cancellationToken = default);
    Task<IEnumerable<WorkflowExecution>> GetActiveWorkflowsAsync(CancellationToken cancellationToken = default);
    Task<bool> HandleStepCompletionAsync(Guid workflowId, string stepId, WorkflowStepResult result, CancellationToken cancellationToken = default);
    Task<bool> HandleStepFailureAsync(Guid workflowId, string stepId, string error, CancellationToken cancellationToken = default);
    Task ProcessPendingWorkflowsAsync(CancellationToken cancellationToken = default);
    Task<WorkflowTemplate> CreateTemplateAsync(string name, WorkflowDefinition definition, CancellationToken cancellationToken = default);
    Task<WorkflowDefinition?> GetTemplateAsync(string templateName, CancellationToken cancellationToken = default);
    Task<IEnumerable<WorkflowTemplate>> GetAvailableTemplatesAsync(CancellationToken cancellationToken = default);
}

public record WorkflowDefinition(
    string Name,
    string Description,
    IEnumerable<WorkflowStep> Steps,
    IEnumerable<WorkflowTransition> Transitions,
    WorkflowSettings Settings,
    Dictionary<string, object>? DefaultData = null);

public record WorkflowStep(
    string Id,
    string Name,
    WorkflowStepType Type,
    TaskRequirements? TaskRequirements = null,
    Dictionary<string, object>? Configuration = null,
    TimeSpan? Timeout = null,
    int MaxRetries = 0,
    IEnumerable<string>? Prerequisites = null,
    bool IsOptional = false);

public record WorkflowTransition(
    string FromStepId,
    string ToStepId,
    WorkflowCondition? Condition = null,
    Dictionary<string, object>? DataTransforms = null);

public record WorkflowCondition(
    WorkflowConditionType Type,
    string Expression,
    Dictionary<string, object>? Parameters = null);

public record WorkflowSettings(
    bool AllowParallelExecution = true,
    bool FailOnStepError = true,
    TimeSpan? MaxExecutionTime = null,
    int MaxConcurrentSteps = 5,
    bool EnableRetries = true,
    WorkflowPriority Priority = WorkflowPriority.Medium);

public record WorkflowExecution(
    Guid Id,
    WorkflowDefinition Definition,
    WorkflowStatus Status,
    DateTime StartedAt,
    DateTime? CompletedAt,
    Dictionary<string, object> Data,
    IEnumerable<WorkflowStepExecution> StepExecutions,
    string? ErrorMessage = null,
    double ProgressPercentage = 0.0);

public record WorkflowStepExecution(
    string StepId,
    string StepName,
    WorkflowStepStatus Status,
    Guid? AssignedAgentId,
    DateTime? StartedAt,
    DateTime? CompletedAt,
    WorkflowStepResult? Result,
    string? ErrorMessage = null,
    int RetryCount = 0);

public record WorkflowStepResult(
    bool Success,
    Dictionary<string, object>? OutputData = null,
    string? Message = null,
    TimeSpan ExecutionTime = default);

public record WorkflowTemplate(
    Guid Id,
    string Name,
    string Description,
    WorkflowDefinition Definition,
    string Category,
    IEnumerable<string> Tags,
    DateTime CreatedAt,
    string CreatedBy,
    int UsageCount = 0);

public enum WorkflowStepType
{
    AgentTask,
    HumanApproval,
    DataTransformation,
    Conditional,
    Parallel,
    Sequential,
    SubWorkflow,
    Notification,
    Delay,
    Script
}

public enum WorkflowConditionType
{
    DataEquals,
    DataNotEquals,
    DataContains,
    DataGreaterThan,
    DataLessThan,
    StepCompleted,
    StepFailed,
    CustomExpression,
    AgentAvailable,
    TimeElapsed
}

public enum WorkflowStatus
{
    Created,
    Running,
    Paused,
    Completed,
    Failed,
    Cancelled
}

public enum WorkflowStepStatus
{
    Pending,
    Running,
    Completed,
    Failed,
    Skipped,
    Cancelled
}

public enum WorkflowPriority
{
    Low = 1,
    Medium = 2,
    High = 3,
    Critical = 4
}