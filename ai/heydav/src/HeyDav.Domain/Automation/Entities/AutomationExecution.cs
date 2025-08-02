using HeyDav.Domain.Common.Base;
using HeyDav.Domain.Automation.Enums;

namespace HeyDav.Domain.Automation.Entities;

public class AutomationExecution : BaseEntity
{
    public Guid AutomationRuleId { get; private set; }
    public DateTime StartedAt { get; private set; }
    public DateTime? CompletedAt { get; private set; }
    public TimeSpan Duration { get; private set; }
    public bool Success { get; private set; }
    public string? Result { get; private set; }
    public string? ErrorMessage { get; private set; }
    public AutomationExecutionStatus Status { get; private set; }
    
    public Dictionary<string, object> Context { get; private set; } = new();
    public Dictionary<string, object> Output { get; private set; } = new();
    public List<AutomationActionResult> ActionResults { get; private set; } = new();
    
    public string? TriggeredBy { get; private set; }
    public string? TriggerData { get; private set; }
    
    private AutomationExecution() { } // For EF Core

    public AutomationExecution(
        Guid automationRuleId,
        Dictionary<string, object>? context = null,
        string? triggeredBy = null,
        string? triggerData = null)
    {
        AutomationRuleId = automationRuleId;
        StartedAt = DateTime.UtcNow;
        Status = AutomationExecutionStatus.Running;
        Context = context ?? new Dictionary<string, object>();
        TriggeredBy = triggeredBy;
        TriggerData = triggerData;
    }

    public void Complete(bool success, string? result = null, string? errorMessage = null)
    {
        CompletedAt = DateTime.UtcNow;
        Duration = CompletedAt.Value - StartedAt;
        Success = success;
        Result = result;
        ErrorMessage = errorMessage;
        Status = success ? AutomationExecutionStatus.Completed : AutomationExecutionStatus.Failed;
        UpdateTimestamp();
    }

    public void Cancel(string? reason = null)
    {
        CompletedAt = DateTime.UtcNow;
        Duration = CompletedAt.Value - StartedAt;
        Success = false;
        ErrorMessage = reason ?? "Execution was cancelled";
        Status = AutomationExecutionStatus.Cancelled;
        UpdateTimestamp();
    }

    public void AddActionResult(AutomationActionResult actionResult)
    {
        if (actionResult == null) throw new ArgumentNullException(nameof(actionResult));
        
        ActionResults.Add(actionResult);
        UpdateTimestamp();
    }

    public void UpdateContext(string key, object value)
    {
        Context[key] = value;
        UpdateTimestamp();
    }

    public void SetOutput(string key, object value)
    {
        Output[key] = value;
        UpdateTimestamp();
    }

    public void UpdateStatus(AutomationExecutionStatus status)
    {
        Status = status;
        UpdateTimestamp();
    }

    public bool IsRunning()
    {
        return Status == AutomationExecutionStatus.Running;
    }

    public bool IsCompleted()
    {
        return Status == AutomationExecutionStatus.Completed ||
               Status == AutomationExecutionStatus.Failed ||
               Status == AutomationExecutionStatus.Cancelled;
    }

    public TimeSpan GetElapsedTime()
    {
        var endTime = CompletedAt ?? DateTime.UtcNow;
        return endTime - StartedAt;
    }
}

public class AutomationActionResult : BaseEntity
{
    public Guid ActionId { get; private set; }
    public string ActionName { get; private set; } = string.Empty;
    public DateTime StartedAt { get; private set; }
    public DateTime? CompletedAt { get; private set; }
    public TimeSpan Duration { get; private set; }
    public bool Success { get; private set; }
    public string? Result { get; private set; }
    public string? ErrorMessage { get; private set; }
    public Dictionary<string, object> Output { get; private set; } = new();

    private AutomationActionResult() { } // For EF Core

    public AutomationActionResult(Guid actionId, string actionName)
    {
        ActionId = actionId;
        ActionName = actionName ?? throw new ArgumentNullException(nameof(actionName));
        StartedAt = DateTime.UtcNow;
    }

    public void Complete(bool success, string? result = null, string? errorMessage = null, Dictionary<string, object>? output = null)
    {
        CompletedAt = DateTime.UtcNow;
        Duration = CompletedAt.Value - StartedAt;
        Success = success;
        Result = result;
        ErrorMessage = errorMessage;
        Output = output ?? new Dictionary<string, object>();
        UpdateTimestamp();
    }
}