using HeyDav.Application.Common.Interfaces;
using HeyDav.Domain.Automation.Entities;
using HeyDav.Domain.Automation.Enums;
using HeyDav.Domain.Automation.ValueObjects;
using HeyDav.Infrastructure.Automation.Executors;
using HeyDav.Infrastructure.Automation.Triggers;
using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Logging;

namespace HeyDav.Infrastructure.Automation;

public class AutomationEngine : IAutomationEngine
{
    private readonly IApplicationDbContext _context;
    private readonly IAutomationActionExecutor _actionExecutor;
    private readonly IAutomationTriggerManager _triggerManager;
    private readonly ILogger<AutomationEngine> _logger;

    public AutomationEngine(
        IApplicationDbContext context,
        IAutomationActionExecutor actionExecutor,
        IAutomationTriggerManager triggerManager,
        ILogger<AutomationEngine> logger)
    {
        _context = context ?? throw new ArgumentNullException(nameof(context));
        _actionExecutor = actionExecutor ?? throw new ArgumentNullException(nameof(actionExecutor));
        _triggerManager = triggerManager ?? throw new ArgumentNullException(nameof(triggerManager));
        _logger = logger ?? throw new ArgumentNullException(nameof(logger));
    }

    public async Task<Guid> CreateAutomationRuleAsync(
        string name,
        string description,
        List<AutomationTrigger> triggers,
        List<AutomationCondition> conditions,
        List<AutomationAction> actions,
        AutomationSchedule? schedule = null,
        AutomationConfiguration? configuration = null,
        string? createdBy = null,
        string? category = null,
        CancellationToken cancellationToken = default)
    {
        try
        {
            var rule = new AutomationRule(name, description, createdBy, category);

            foreach (var trigger in triggers)
            {
                rule.AddTrigger(trigger);
            }

            foreach (var condition in conditions)
            {
                rule.AddCondition(condition);
            }

            foreach (var action in actions)
            {
                rule.AddAction(action);
            }

            if (schedule != null)
            {
                rule.UpdateSchedule(schedule);
            }

            if (configuration != null)
            {
                rule.UpdateConfiguration(configuration);
            }

            _context.AutomationRules.Add(rule);
            await _context.SaveChangesAsync(cancellationToken);

            // Register triggers with the trigger manager
            await _triggerManager.RegisterTriggersAsync(rule.Id, triggers, cancellationToken);

            _logger.LogInformation("Created automation rule {RuleId} '{Name}' with {TriggerCount} triggers and {ActionCount} actions",
                rule.Id, name, triggers.Count, actions.Count);

            return rule.Id;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to create automation rule '{Name}'", name);
            throw;
        }
    }

    public async Task<bool> UpdateAutomationRuleAsync(
        Guid ruleId,
        string? name = null,
        string? description = null,
        List<AutomationTrigger>? triggers = null,
        List<AutomationCondition>? conditions = null,
        List<AutomationAction>? actions = null,
        AutomationSchedule? schedule = null,
        AutomationConfiguration? configuration = null,
        CancellationToken cancellationToken = default)
    {
        try
        {
            var rule = await _context.AutomationRules
                .FirstOrDefaultAsync(r => r.Id == ruleId && !r.IsDeleted, cancellationToken);

            if (rule == null) return false;

            if (!string.IsNullOrEmpty(name))
            {
                rule.UpdateName(name);
            }

            if (!string.IsNullOrEmpty(description))
            {
                rule.UpdateDescription(description);
            }

            if (triggers != null)
            {
                // Clear existing triggers and add new ones
                rule.Triggers.Clear();
                foreach (var trigger in triggers)
                {
                    rule.AddTrigger(trigger);
                }
                await _triggerManager.RegisterTriggersAsync(ruleId, triggers, cancellationToken);
            }

            if (conditions != null)
            {
                rule.Conditions.Clear();
                foreach (var condition in conditions)
                {
                    rule.AddCondition(condition);
                }
            }

            if (actions != null)
            {
                rule.Actions.Clear();
                foreach (var action in actions)
                {
                    rule.AddAction(action);
                }
            }

            if (schedule != null)
            {
                rule.UpdateSchedule(schedule);
            }

            if (configuration != null)
            {
                rule.UpdateConfiguration(configuration);
            }

            await _context.SaveChangesAsync(cancellationToken);

            _logger.LogInformation("Updated automation rule {RuleId}", ruleId);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to update automation rule {RuleId}", ruleId);
            return false;
        }
    }

    public async Task<bool> EnableAutomationRuleAsync(Guid ruleId, CancellationToken cancellationToken = default)
    {
        return await UpdateRuleStatusAsync(ruleId, r => r.Enable(), "enable", cancellationToken);
    }

    public async Task<bool> DisableAutomationRuleAsync(Guid ruleId, CancellationToken cancellationToken = default)
    {
        return await UpdateRuleStatusAsync(ruleId, r => r.Disable(), "disable", cancellationToken);
    }

    public async Task<bool> PauseAutomationRuleAsync(Guid ruleId, CancellationToken cancellationToken = default)
    {
        return await UpdateRuleStatusAsync(ruleId, r => r.Pause(), "pause", cancellationToken);
    }

    public async Task<bool> ResumeAutomationRuleAsync(Guid ruleId, CancellationToken cancellationToken = default)
    {
        return await UpdateRuleStatusAsync(ruleId, r => r.Resume(), "resume", cancellationToken);
    }

    public async Task<bool> DeleteAutomationRuleAsync(Guid ruleId, CancellationToken cancellationToken = default)
    {
        try
        {
            var rule = await _context.AutomationRules
                .FirstOrDefaultAsync(r => r.Id == ruleId && !r.IsDeleted, cancellationToken);

            if (rule == null) return false;

            // Unregister triggers
            await _triggerManager.UnregisterTriggersAsync(ruleId, cancellationToken);

            // Soft delete the rule
            rule.MarkAsDeleted();
            await _context.SaveChangesAsync(cancellationToken);

            _logger.LogInformation("Deleted automation rule {RuleId}", ruleId);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to delete automation rule {RuleId}", ruleId);
            return false;
        }
    }

    public async Task<AutomationRule?> GetAutomationRuleAsync(Guid ruleId, CancellationToken cancellationToken = default)
    {
        return await _context.AutomationRules
            .FirstOrDefaultAsync(r => r.Id == ruleId && !r.IsDeleted, cancellationToken);
    }

    public async Task<List<AutomationRule>> GetAutomationRulesAsync(
        string? category = null,
        AutomationRuleStatus? status = null,
        bool includeDisabled = false,
        CancellationToken cancellationToken = default)
    {
        var query = _context.AutomationRules.Where(r => !r.IsDeleted);

        if (!string.IsNullOrEmpty(category))
        {
            query = query.Where(r => r.Category == category);
        }

        if (status.HasValue)
        {
            query = query.Where(r => r.Status == status.Value);
        }

        if (!includeDisabled)
        {
            query = query.Where(r => r.IsEnabled);
        }

        return await query.OrderBy(r => r.Name).ToListAsync(cancellationToken);
    }

    public async Task<Guid> ExecuteAutomationRuleAsync(
        Guid ruleId,
        Dictionary<string, object>? context = null,
        string? triggeredBy = null,
        CancellationToken cancellationToken = default)
    {
        try
        {
            var rule = await _context.AutomationRules
                .FirstOrDefaultAsync(r => r.Id == ruleId && !r.IsDeleted, cancellationToken);

            if (rule == null)
            {
                throw new InvalidOperationException($"Automation rule {ruleId} not found");
            }

            if (!rule.CanExecute())
            {
                throw new InvalidOperationException($"Automation rule {ruleId} cannot be executed (disabled or invalid configuration)");
            }

            var execution = new AutomationExecution(ruleId, context, triggeredBy);
            _context.AutomationExecutions.Add(execution);
            await _context.SaveChangesAsync(cancellationToken);

            // Execute in background
            _ = Task.Run(async () => await ExecuteRuleAsync(rule, execution, cancellationToken));

            return execution.Id;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to start execution of automation rule {RuleId}", ruleId);
            throw;
        }
    }

    public async Task<Guid> TriggerAutomationAsync(
        string triggerName,
        Dictionary<string, object>? context = null,
        string? triggeredBy = null,
        CancellationToken cancellationToken = default)
    {
        try
        {
            var rules = await _context.AutomationRules
                .Where(r => !r.IsDeleted && r.IsEnabled && r.Status == AutomationRuleStatus.Active)
                .Where(r => r.Triggers.Any(t => t.Name == triggerName))
                .ToListAsync(cancellationToken);

            if (!rules.Any())
            {
                _logger.LogWarning("No automation rules found for trigger '{TriggerName}'", triggerName);
                return Guid.Empty;
            }

            var executionIds = new List<Guid>();

            foreach (var rule in rules)
            {
                try
                {
                    // Check if conditions are met
                    if (!rule.EvaluateConditions(context ?? new Dictionary<string, object>()))
                    {
                        _logger.LogDebug("Automation rule {RuleId} conditions not met for trigger '{TriggerName}'", rule.Id, triggerName);
                        continue;
                    }

                    var executionId = await ExecuteAutomationRuleAsync(rule.Id, context, triggeredBy, cancellationToken);
                    executionIds.Add(executionId);
                }
                catch (Exception ex)
                {
                    _logger.LogError(ex, "Failed to execute automation rule {RuleId} for trigger '{TriggerName}'", rule.Id, triggerName);
                }
            }

            _logger.LogInformation("Triggered {Count} automation rules for trigger '{TriggerName}'", executionIds.Count, triggerName);
            return executionIds.FirstOrDefault();
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to process trigger '{TriggerName}'", triggerName);
            throw;
        }
    }

    public async Task<List<AutomationExecution>> GetExecutionHistoryAsync(
        Guid ruleId,
        DateTime? fromDate = null,
        DateTime? toDate = null,
        int skip = 0,
        int take = 50,
        CancellationToken cancellationToken = default)
    {
        var query = _context.AutomationExecutions
            .Where(e => e.AutomationRuleId == ruleId && !e.IsDeleted);

        if (fromDate.HasValue)
        {
            query = query.Where(e => e.StartedAt >= fromDate.Value);
        }

        if (toDate.HasValue)
        {
            query = query.Where(e => e.StartedAt <= toDate.Value);
        }

        return await query
            .OrderByDescending(e => e.StartedAt)
            .Skip(skip)
            .Take(take)
            .ToListAsync(cancellationToken);
    }

    public async Task<AutomationExecution?> GetExecutionAsync(Guid executionId, CancellationToken cancellationToken = default)
    {
        return await _context.AutomationExecutions
            .FirstOrDefaultAsync(e => e.Id == executionId && !e.IsDeleted, cancellationToken);
    }

    public async Task<bool> CancelExecutionAsync(Guid executionId, string? reason = null, CancellationToken cancellationToken = default)
    {
        try
        {
            var execution = await _context.AutomationExecutions
                .FirstOrDefaultAsync(e => e.Id == executionId && !e.IsDeleted, cancellationToken);

            if (execution == null || !execution.IsRunning())
            {
                return false;
            }

            execution.Cancel(reason);
            await _context.SaveChangesAsync(cancellationToken);

            _logger.LogInformation("Cancelled automation execution {ExecutionId}: {Reason}", executionId, reason);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to cancel automation execution {ExecutionId}", executionId);
            return false;
        }
    }

    public async Task ProcessScheduledAutomationsAsync(CancellationToken cancellationToken = default)
    {
        try
        {
            var rules = await _context.AutomationRules
                .Where(r => !r.IsDeleted && r.IsEnabled && r.Status == AutomationRuleStatus.Active)
                .Where(r => r.Schedule != null && r.ShouldExecuteNow())
                .ToListAsync(cancellationToken);

            _logger.LogInformation("Processing {Count} scheduled automation rules", rules.Count);

            foreach (var rule in rules)
            {
                try
                {
                    await ExecuteAutomationRuleAsync(rule.Id, cancellationToken: cancellationToken);
                    
                    // Update next execution time
                    if (rule.Schedule != null)
                    {
                        var nextExecution = rule.Schedule.GetNextExecutionTime();
                        if (nextExecution.HasValue)
                        {
                            rule.UpdateNextExecution(nextExecution.Value);
                        }
                    }
                }
                catch (Exception ex)
                {
                    _logger.LogError(ex, "Failed to process scheduled automation rule {RuleId}", rule.Id);
                }
            }

            if (rules.Any())
            {
                await _context.SaveChangesAsync(cancellationToken);
            }
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to process scheduled automations");
            throw;
        }
    }

    public async Task ProcessTriggeredAutomationsAsync(CancellationToken cancellationToken = default)
    {
        try
        {
            await _triggerManager.ProcessTriggersAsync(cancellationToken);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to process triggered automations");
            throw;
        }
    }

    public async Task CleanupOldExecutionsAsync(TimeSpan maxAge, CancellationToken cancellationToken = default)
    {
        try
        {
            var cutoffDate = DateTime.UtcNow.Subtract(maxAge);
            
            var oldExecutions = await _context.AutomationExecutions
                .Where(e => e.StartedAt < cutoffDate && e.IsCompleted())
                .ToListAsync(cancellationToken);

            foreach (var execution in oldExecutions)
            {
                execution.MarkAsDeleted();
            }

            if (oldExecutions.Any())
            {
                await _context.SaveChangesAsync(cancellationToken);
                _logger.LogInformation("Cleaned up {Count} old automation executions", oldExecutions.Count);
            }
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to cleanup old executions");
            throw;
        }
    }

    public async Task<AutomationTestResult> TestAutomationRuleAsync(
        Guid ruleId,
        Dictionary<string, object>? testContext = null,
        CancellationToken cancellationToken = default)
    {
        var testResult = new AutomationTestResult();
        var startTime = DateTime.UtcNow;

        try
        {
            var rule = await _context.AutomationRules
                .FirstOrDefaultAsync(r => r.Id == ruleId && !r.IsDeleted, cancellationToken);

            if (rule == null)
            {
                testResult.Success = false;
                testResult.ErrorMessage = "Automation rule not found";
                return testResult;
            }

            testResult.Context = testContext ?? new Dictionary<string, object>();

            // Test conditions
            foreach (var condition in rule.Conditions)
            {
                var stepStartTime = DateTime.UtcNow;
                var step = new AutomationTestStep
                {
                    Name = condition.Name,
                    Type = "Condition"
                };

                try
                {
                    step.Success = condition.Evaluate(testResult.Context);
                    step.Result = step.Success ? "Condition passed" : "Condition failed";
                }
                catch (Exception ex)
                {
                    step.Success = false;
                    step.ErrorMessage = ex.Message;
                }

                step.Duration = DateTime.UtcNow - stepStartTime;
                testResult.Steps.Add(step);
            }

            // Test actions (without actually executing them)
            foreach (var action in rule.Actions)
            {
                var stepStartTime = DateTime.UtcNow;
                var step = new AutomationTestStep
                {
                    Name = action.Name,
                    Type = "Action"
                };

                try
                {
                    var testOutput = await _actionExecutor.TestActionAsync(action, testResult.Context, cancellationToken);
                    step.Success = testOutput.Success;
                    step.Result = testOutput.Message;
                    step.Output = testOutput.Data;
                }
                catch (Exception ex)
                {
                    step.Success = false;
                    step.ErrorMessage = ex.Message;
                }

                step.Duration = DateTime.UtcNow - stepStartTime;
                testResult.Steps.Add(step);
            }

            testResult.Success = testResult.Steps.All(s => s.Success);
            testResult.Duration = DateTime.UtcNow - startTime;

            _logger.LogInformation("Tested automation rule {RuleId}: {Success}", ruleId, testResult.Success);
        }
        catch (Exception ex)
        {
            testResult.Success = false;
            testResult.ErrorMessage = ex.Message;
            testResult.Duration = DateTime.UtcNow - startTime;
            _logger.LogError(ex, "Failed to test automation rule {RuleId}", ruleId);
        }

        return testResult;
    }

    public async Task<List<AutomationRuleSummary>> GetAutomationSummariesAsync(
        string? category = null,
        CancellationToken cancellationToken = default)
    {
        var query = _context.AutomationRules.Where(r => !r.IsDeleted);

        if (!string.IsNullOrEmpty(category))
        {
            query = query.Where(r => r.Category == category);
        }

        var rules = await query.ToListAsync(cancellationToken);
        return rules.Select(r => r.GetSummary()).ToList();
    }

    public async Task<AutomationMetrics> GetAutomationMetricsAsync(
        Guid ruleId,
        TimeSpan? timeWindow = null,
        CancellationToken cancellationToken = default)
    {
        var rule = await _context.AutomationRules
            .FirstOrDefaultAsync(r => r.Id == ruleId && !r.IsDeleted, cancellationToken);

        return rule?.Metrics ?? new AutomationMetrics();
    }

    public async Task<Dictionary<string, object>> GetGlobalAutomationStatsAsync(CancellationToken cancellationToken = default)
    {
        var totalRules = await _context.AutomationRules.CountAsync(r => !r.IsDeleted, cancellationToken);
        var activeRules = await _context.AutomationRules.CountAsync(r => !r.IsDeleted && r.IsEnabled, cancellationToken);
        var totalExecutions = await _context.AutomationExecutions.CountAsync(e => !e.IsDeleted, cancellationToken);
        var successfulExecutions = await _context.AutomationExecutions.CountAsync(e => !e.IsDeleted && e.Success, cancellationToken);

        var stats = new Dictionary<string, object>
        {
            ["totalRules"] = totalRules,
            ["activeRules"] = activeRules,
            ["totalExecutions"] = totalExecutions,
            ["successfulExecutions"] = successfulExecutions,
            ["successRate"] = totalExecutions > 0 ? (double)successfulExecutions / totalExecutions * 100 : 0.0
        };

        return stats;
    }

    private async Task<bool> UpdateRuleStatusAsync(
        Guid ruleId,
        Action<AutomationRule> updateAction,
        string actionName,
        CancellationToken cancellationToken)
    {
        try
        {
            var rule = await _context.AutomationRules
                .FirstOrDefaultAsync(r => r.Id == ruleId && !r.IsDeleted, cancellationToken);

            if (rule == null) return false;

            updateAction(rule);
            await _context.SaveChangesAsync(cancellationToken);

            _logger.LogInformation("Successfully {ActionName}d automation rule {RuleId}", actionName, ruleId);
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to {ActionName} automation rule {RuleId}", actionName, ruleId);
            return false;
        }
    }

    private async Task ExecuteRuleAsync(AutomationRule rule, AutomationExecution execution, CancellationToken cancellationToken)
    {
        try
        {
            execution.UpdateStatus(AutomationExecutionStatus.Running);
            await _context.SaveChangesAsync(cancellationToken);

            // Execute actions based on run mode
            if (rule.Configuration.RunMode == AutomationRunMode.Sequential)
            {
                await ExecuteActionsSequentiallyAsync(rule, execution, cancellationToken);
            }
            else
            {
                await ExecuteActionsInParallelAsync(rule, execution, cancellationToken);
            }

            var allSuccessful = execution.ActionResults.All(r => r.Success);
            execution.Complete(allSuccessful, allSuccessful ? "Execution completed successfully" : "Some actions failed");

            // Update rule metrics
            rule.RecordExecution(execution);
            await _context.SaveChangesAsync(cancellationToken);

            _logger.LogInformation("Completed execution {ExecutionId} for rule {RuleId}: {Success}",
                execution.Id, rule.Id, allSuccessful);
        }
        catch (Exception ex)
        {
            execution.Complete(false, ex.Message);
            await _context.SaveChangesAsync(cancellationToken);
            _logger.LogError(ex, "Failed to execute automation rule {RuleId}", rule.Id);
        }
    }

    private async Task ExecuteActionsSequentiallyAsync(AutomationRule rule, AutomationExecution execution, CancellationToken cancellationToken)
    {
        foreach (var action in rule.Actions.Where(a => a.IsEnabled).OrderBy(a => a.Order))
        {
            if (cancellationToken.IsCancellationRequested)
            {
                execution.Cancel("Execution was cancelled");
                return;
            }

            try
            {
                var result = await _actionExecutor.ExecuteActionAsync(action, execution.Context, cancellationToken);
                execution.AddActionResult(result);

                if (!result.Success && !action.ContinueOnError)
                {
                    _logger.LogWarning("Action {ActionName} failed and is configured to stop execution", action.Name);
                    break;
                }
            }
            catch (Exception ex)
            {
                var result = new AutomationActionResult(action.Id, action.Name);
                result.Complete(false, ex.Message);
                execution.AddActionResult(result);

                if (!action.ContinueOnError)
                {
                    _logger.LogError(ex, "Action {ActionName} failed and is configured to stop execution", action.Name);
                    break;
                }
            }
        }
    }

    private async Task ExecuteActionsInParallelAsync(AutomationRule rule, AutomationExecution execution, CancellationToken cancellationToken)
    {
        var actions = rule.Actions.Where(a => a.IsEnabled).ToList();
        var tasks = actions.Select(async action =>
        {
            try
            {
                var result = await _actionExecutor.ExecuteActionAsync(action, execution.Context, cancellationToken);
                execution.AddActionResult(result);
                return result;
            }
            catch (Exception ex)
            {
                var result = new AutomationActionResult(action.Id, action.Name);
                result.Complete(false, ex.Message);
                execution.AddActionResult(result);
                return result;
            }
        });

        await Task.WhenAll(tasks);
    }
}