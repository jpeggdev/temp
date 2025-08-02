using HeyDav.Domain.Automation.Entities;
using HeyDav.Domain.Automation.Enums;
using HeyDav.Domain.Automation.ValueObjects;

namespace HeyDav.Infrastructure.Automation;

public interface IAutomationEngine
{
    Task<Guid> CreateAutomationRuleAsync(
        string name,
        string description,
        List<AutomationTrigger> triggers,
        List<AutomationCondition> conditions,
        List<AutomationAction> actions,
        AutomationSchedule? schedule = null,
        AutomationConfiguration? configuration = null,
        string? createdBy = null,
        string? category = null,
        CancellationToken cancellationToken = default);

    Task<bool> UpdateAutomationRuleAsync(
        Guid ruleId,
        string? name = null,
        string? description = null,
        List<AutomationTrigger>? triggers = null,
        List<AutomationCondition>? conditions = null,
        List<AutomationAction>? actions = null,
        AutomationSchedule? schedule = null,
        AutomationConfiguration? configuration = null,
        CancellationToken cancellationToken = default);

    Task<bool> EnableAutomationRuleAsync(Guid ruleId, CancellationToken cancellationToken = default);
    Task<bool> DisableAutomationRuleAsync(Guid ruleId, CancellationToken cancellationToken = default);
    Task<bool> PauseAutomationRuleAsync(Guid ruleId, CancellationToken cancellationToken = default);
    Task<bool> ResumeAutomationRuleAsync(Guid ruleId, CancellationToken cancellationToken = default);
    Task<bool> DeleteAutomationRuleAsync(Guid ruleId, CancellationToken cancellationToken = default);

    Task<AutomationRule?> GetAutomationRuleAsync(Guid ruleId, CancellationToken cancellationToken = default);
    Task<List<AutomationRule>> GetAutomationRulesAsync(
        string? category = null,
        AutomationRuleStatus? status = null,
        bool includeDisabled = false,
        CancellationToken cancellationToken = default);

    Task<Guid> ExecuteAutomationRuleAsync(
        Guid ruleId,
        Dictionary<string, object>? context = null,
        string? triggeredBy = null,
        CancellationToken cancellationToken = default);

    Task<Guid> TriggerAutomationAsync(
        string triggerName,
        Dictionary<string, object>? context = null,
        string? triggeredBy = null,
        CancellationToken cancellationToken = default);

    Task<List<AutomationExecution>> GetExecutionHistoryAsync(
        Guid ruleId,
        DateTime? fromDate = null,
        DateTime? toDate = null,
        int skip = 0,
        int take = 50,
        CancellationToken cancellationToken = default);

    Task<AutomationExecution?> GetExecutionAsync(Guid executionId, CancellationToken cancellationToken = default);
    Task<bool> CancelExecutionAsync(Guid executionId, string? reason = null, CancellationToken cancellationToken = default);

    Task ProcessScheduledAutomationsAsync(CancellationToken cancellationToken = default);
    Task ProcessTriggeredAutomationsAsync(CancellationToken cancellationToken = default);
    Task CleanupOldExecutionsAsync(TimeSpan maxAge, CancellationToken cancellationToken = default);

    Task<AutomationTestResult> TestAutomationRuleAsync(
        Guid ruleId,
        Dictionary<string, object>? testContext = null,
        CancellationToken cancellationToken = default);

    Task<List<AutomationRuleSummary>> GetAutomationSummariesAsync(
        string? category = null,
        CancellationToken cancellationToken = default);

    Task<AutomationMetrics> GetAutomationMetricsAsync(
        Guid ruleId,
        TimeSpan? timeWindow = null,
        CancellationToken cancellationToken = default);

    Task<Dictionary<string, object>> GetGlobalAutomationStatsAsync(CancellationToken cancellationToken = default);
}

public class AutomationTestResult
{
    public bool Success { get; set; }
    public string? ErrorMessage { get; set; }
    public List<AutomationTestStep> Steps { get; set; } = new();
    public Dictionary<string, object> Context { get; set; } = new();
    public TimeSpan Duration { get; set; }
}

public class AutomationTestStep
{
    public string Name { get; set; } = string.Empty;
    public string Type { get; set; } = string.Empty;
    public bool Success { get; set; }
    public string? Result { get; set; }
    public string? ErrorMessage { get; set; }
    public TimeSpan Duration { get; set; }
    public Dictionary<string, object> Output { get; set; } = new();
}