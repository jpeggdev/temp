using HeyDav.Domain.Common.Base;
using HeyDav.Domain.Automation.Enums;
using HeyDav.Domain.Automation.ValueObjects;

namespace HeyDav.Domain.Automation.Entities;

public class AutomationRule : BaseEntity
{
    public string Name { get; private set; } = string.Empty;
    public string Description { get; private set; } = string.Empty;
    public bool IsEnabled { get; private set; } = true;
    public AutomationRuleStatus Status { get; private set; } = AutomationRuleStatus.Active;
    
    public List<AutomationTrigger> Triggers { get; private set; } = new();
    public List<AutomationCondition> Conditions { get; private set; } = new();
    public List<AutomationAction> Actions { get; private set; } = new();
    
    public AutomationSchedule? Schedule { get; private set; }
    public AutomationConfiguration Configuration { get; private set; } = new();
    
    public string? CreatedBy { get; private set; }
    public string? Category { get; private set; }
    public List<string> Tags { get; private set; } = new();
    
    public int ExecutionCount { get; private set; } = 0;
    public DateTime? LastExecutedAt { get; private set; }
    public DateTime? NextExecutionAt { get; private set; }
    public string? LastExecutionResult { get; private set; }
    public bool LastExecutionSuccess { get; private set; } = true;
    
    public List<AutomationExecution> Executions { get; private set; } = new();
    public AutomationMetrics Metrics { get; private set; } = new();

    private AutomationRule() { } // For EF Core

    public AutomationRule(
        string name,
        string description,
        string? createdBy = null,
        string? category = null)
    {
        Name = name ?? throw new ArgumentNullException(nameof(name));
        Description = description ?? throw new ArgumentNullException(nameof(description));
        CreatedBy = createdBy;
        Category = category;
    }

    public void AddTrigger(AutomationTrigger trigger)
    {
        if (trigger == null) throw new ArgumentNullException(nameof(trigger));
        
        Triggers.Add(trigger);
        UpdateTimestamp();
    }

    public void RemoveTrigger(Guid triggerId)
    {
        Triggers.RemoveAll(t => t.Id == triggerId);
        UpdateTimestamp();
    }

    public void AddCondition(AutomationCondition condition)
    {
        if (condition == null) throw new ArgumentNullException(nameof(condition));
        
        Conditions.Add(condition);
        UpdateTimestamp();
    }

    public void RemoveCondition(Guid conditionId)
    {
        Conditions.RemoveAll(c => c.Id == conditionId);
        UpdateTimestamp();
    }

    public void AddAction(AutomationAction action)
    {
        if (action == null) throw new ArgumentNullException(nameof(action));
        
        Actions.Add(action);
        UpdateTimestamp();
    }

    public void RemoveAction(Guid actionId)
    {
        Actions.RemoveAll(a => a.Id == actionId);
        UpdateTimestamp();
    }

    public void UpdateSchedule(AutomationSchedule? schedule)
    {
        Schedule = schedule;
        UpdateTimestamp();
    }

    public void UpdateConfiguration(AutomationConfiguration configuration)
    {
        Configuration = configuration ?? throw new ArgumentNullException(nameof(configuration));
        UpdateTimestamp();
    }

    public void Enable()
    {
        IsEnabled = true;
        Status = AutomationRuleStatus.Active;
        UpdateTimestamp();
    }

    public void Disable()
    {
        IsEnabled = false;
        Status = AutomationRuleStatus.Disabled;
        UpdateTimestamp();
    }

    public void Pause()
    {
        Status = AutomationRuleStatus.Paused;
        UpdateTimestamp();
    }

    public void Resume()
    {
        if (IsEnabled)
        {
            Status = AutomationRuleStatus.Active;
        }
        UpdateTimestamp();
    }

    public void AddTag(string tag)
    {
        if (string.IsNullOrWhiteSpace(tag)) return;
        
        if (!Tags.Contains(tag, StringComparer.OrdinalIgnoreCase))
        {
            Tags.Add(tag);
            UpdateTimestamp();
        }
    }

    public void RemoveTag(string tag)
    {
        if (Tags.RemoveAll(t => string.Equals(t, tag, StringComparison.OrdinalIgnoreCase)) > 0)
        {
            UpdateTimestamp();
        }
    }

    public void RecordExecution(AutomationExecution execution)
    {
        if (execution == null) throw new ArgumentNullException(nameof(execution));
        
        Executions.Add(execution);
        ExecutionCount++;
        LastExecutedAt = execution.StartedAt;
        LastExecutionSuccess = execution.Success;
        LastExecutionResult = execution.Result;
        
        // Update metrics
        Metrics = Metrics.RecordExecution(execution);
        
        // Keep only recent executions (last 100)
        if (Executions.Count > 100)
        {
            Executions.RemoveRange(0, Executions.Count - 100);
        }
        
        UpdateTimestamp();
    }

    public void UpdateNextExecution(DateTime nextExecutionAt)
    {
        NextExecutionAt = nextExecutionAt;
        UpdateTimestamp();
    }

    public bool CanExecute()
    {
        return IsEnabled && 
               Status == AutomationRuleStatus.Active && 
               Triggers.Any() && 
               Actions.Any();
    }

    public bool ShouldExecuteNow()
    {
        if (!CanExecute()) return false;
        
        if (Schedule != null)
        {
            return Schedule.ShouldExecuteNow();
        }
        
        return NextExecutionAt.HasValue && DateTime.UtcNow >= NextExecutionAt.Value;
    }

    public bool EvaluateConditions(Dictionary<string, object> context)
    {
        if (!Conditions.Any()) return true;
        
        return Conditions.All(condition => condition.Evaluate(context));
    }

    public void UpdateName(string name)
    {
        Name = name ?? throw new ArgumentNullException(nameof(name));
        UpdateTimestamp();
    }

    public void UpdateDescription(string description)
    {
        Description = description ?? throw new ArgumentNullException(nameof(description));
        UpdateTimestamp();
    }

    public void UpdateCategory(string? category)
    {
        Category = category;
        UpdateTimestamp();
    }

    public AutomationRuleSummary GetSummary()
    {
        return new AutomationRuleSummary(
            Id,
            Name,
            Description,
            IsEnabled,
            Status,
            Triggers.Count,
            Conditions.Count,
            Actions.Count,
            ExecutionCount,
            LastExecutedAt,
            NextExecutionAt,
            LastExecutionSuccess,
            Category,
            Tags);
    }
}