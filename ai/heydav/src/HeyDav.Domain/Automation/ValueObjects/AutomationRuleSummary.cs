using HeyDav.Domain.Common.Base;
using HeyDav.Domain.Automation.Enums;

namespace HeyDav.Domain.Automation.ValueObjects;

public class AutomationRuleSummary : ValueObject
{
    public Guid Id { get; private set; }
    public string Name { get; private set; } = string.Empty;
    public string Description { get; private set; } = string.Empty;
    public bool IsEnabled { get; private set; }
    public AutomationRuleStatus Status { get; private set; }
    public int TriggerCount { get; private set; }
    public int ConditionCount { get; private set; }
    public int ActionCount { get; private set; }
    public int ExecutionCount { get; private set; }
    public DateTime? LastExecutedAt { get; private set; }
    public DateTime? NextExecutionAt { get; private set; }
    public bool LastExecutionSuccess { get; private set; }
    public string? Category { get; private set; }
    public List<string> Tags { get; private set; } = new();

    public AutomationRuleSummary(
        Guid id,
        string name,
        string description,
        bool isEnabled,
        AutomationRuleStatus status,
        int triggerCount,
        int conditionCount,
        int actionCount,
        int executionCount,
        DateTime? lastExecutedAt,
        DateTime? nextExecutionAt,
        bool lastExecutionSuccess,
        string? category = null,
        List<string>? tags = null)
    {
        Id = id;
        Name = name ?? throw new ArgumentNullException(nameof(name));
        Description = description ?? throw new ArgumentNullException(nameof(description));
        IsEnabled = isEnabled;
        Status = status;
        TriggerCount = triggerCount;
        ConditionCount = conditionCount;
        ActionCount = actionCount;
        ExecutionCount = executionCount;
        LastExecutedAt = lastExecutedAt;
        NextExecutionAt = nextExecutionAt;
        LastExecutionSuccess = lastExecutionSuccess;
        Category = category;
        Tags = tags ?? new List<string>();
    }

    public bool IsActive => IsEnabled && Status == AutomationRuleStatus.Active;
    public bool IsReadyToExecute => IsActive && TriggerCount > 0 && ActionCount > 0;
    public bool IsOverdue => NextExecutionAt.HasValue && DateTime.UtcNow > NextExecutionAt.Value;
    public bool HasNeverExecuted => ExecutionCount == 0;
    
    public string StatusDisplay => Status switch
    {
        AutomationRuleStatus.Active when IsEnabled => "Active",
        AutomationRuleStatus.Active when !IsEnabled => "Disabled",
        AutomationRuleStatus.Paused => "Paused",
        AutomationRuleStatus.Draft => "Draft",
        AutomationRuleStatus.Archived => "Archived",
        AutomationRuleStatus.Error => "Error",
        _ => Status.ToString()
    };

    public string ComponentsSummary => $"{TriggerCount} trigger(s), {ConditionCount} condition(s), {ActionCount} action(s)";

    public TimeSpan? TimeSinceLastExecution => LastExecutedAt.HasValue ? DateTime.UtcNow - LastExecutedAt.Value : null;
    public TimeSpan? TimeUntilNextExecution => NextExecutionAt.HasValue && NextExecutionAt.Value > DateTime.UtcNow 
        ? NextExecutionAt.Value - DateTime.UtcNow 
        : null;

    protected override IEnumerable<object> GetEqualityComponents()
    {
        yield return Id;
        yield return Name;
        yield return Description;
        yield return IsEnabled;
        yield return Status;
        yield return TriggerCount;
        yield return ConditionCount;
        yield return ActionCount;
        yield return ExecutionCount;
        yield return LastExecutedAt?.ToString() ?? string.Empty;
        yield return NextExecutionAt?.ToString() ?? string.Empty;
        yield return LastExecutionSuccess;
        yield return Category ?? string.Empty;

        foreach (var tag in Tags.OrderBy(t => t))
        {
            yield return tag;
        }
    }
}