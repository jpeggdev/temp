using HeyDav.Domain.AgentManagement.Entities;
using HeyDav.Domain.AgentManagement.Interfaces;
using Microsoft.Extensions.Logging;
using System.Collections.Concurrent;
using System.Text.Json;

namespace HeyDav.Application.AgentManagement.Services;

public class AgentWorkflowEngine(
    IAgentCapabilityMatcher capabilityMatcher,
    IAgentRepository agentRepository,
    IAgentTaskRepository taskRepository,
    ILogger<AgentWorkflowEngine> logger) : IAgentWorkflowEngine
{
    private readonly IAgentCapabilityMatcher _capabilityMatcher = capabilityMatcher ?? throw new ArgumentNullException(nameof(capabilityMatcher));
    private readonly IAgentRepository _agentRepository = agentRepository ?? throw new ArgumentNullException(nameof(agentRepository));
    private readonly IAgentTaskRepository _taskRepository = taskRepository ?? throw new ArgumentNullException(nameof(taskRepository));
    private readonly ILogger<AgentWorkflowEngine> _logger = logger ?? throw new ArgumentNullException(nameof(logger));

    private readonly ConcurrentDictionary<Guid, WorkflowExecution> _activeWorkflows = new();
    private readonly ConcurrentDictionary<string, WorkflowTemplate> _templates = new();

    public Task<Guid> CreateWorkflowAsync(WorkflowDefinition definition, CancellationToken cancellationToken = default)
    {
        try
        {
            var workflowId = Guid.NewGuid();
            var execution = new WorkflowExecution(
                workflowId,
                definition,
                WorkflowStatus.Created,
                DateTime.UtcNow,
                null,
                definition.DefaultData ?? new Dictionary<string, object>(),
                definition.Steps.Select(step => new WorkflowStepExecution(
                    step.Id,
                    step.Name,
                    WorkflowStepStatus.Pending,
                    null,
                    null,
                    null,
                    null
                )),
                null,
                0.0
            );

            _activeWorkflows[workflowId] = execution;

            _logger.LogInformation("Created workflow {WorkflowId} with name '{WorkflowName}' containing {StepCount} steps", 
                workflowId, definition.Name, definition.Steps.Count());

            return Task.FromResult(workflowId);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to create workflow '{WorkflowName}'", definition.Name);
            throw;
        }
    }

    public async Task<bool> StartWorkflowAsync(Guid workflowId, Dictionary<string, object>? initialData = null, CancellationToken cancellationToken = default)
    {
        try
        {
            if (!_activeWorkflows.TryGetValue(workflowId, out var execution))
            {
                _logger.LogWarning("Workflow {WorkflowId} not found", workflowId);
                return false;
            }

            if (execution.Status != WorkflowStatus.Created && execution.Status != WorkflowStatus.Paused)
            {
                _logger.LogWarning("Workflow {WorkflowId} is in {Status} state and cannot be started", workflowId, execution.Status);
                return false;
            }

            // Merge initial data
            var mergedData = new Dictionary<string, object>(execution.Data);
            if (initialData != null)
            {
                foreach (var kvp in initialData)
                {
                    mergedData[kvp.Key] = kvp.Value;
                }
            }

            var updatedExecution = execution with 
            { 
                Status = WorkflowStatus.Running,
                Data = mergedData,
                StartedAt = DateTime.UtcNow
            };

            _activeWorkflows[workflowId] = updatedExecution;

            _logger.LogInformation("Started workflow {WorkflowId} ({WorkflowName})", workflowId, execution.Definition.Name);

            // Start executing initial steps
            await ProcessWorkflowStepsAsync(workflowId, cancellationToken);
            
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to start workflow {WorkflowId}", workflowId);
            return false;
        }
    }

    public Task<bool> PauseWorkflowAsync(Guid workflowId, CancellationToken cancellationToken = default)
    {
        try
        {
            if (!_activeWorkflows.TryGetValue(workflowId, out var execution))
            {
                _logger.LogWarning("Workflow {WorkflowId} not found", workflowId);
                return Task.FromResult(false);
            }

            if (execution.Status != WorkflowStatus.Running)
            {
                _logger.LogWarning("Workflow {WorkflowId} is not running and cannot be paused", workflowId);
                return Task.FromResult(false);
            }

            var pausedExecution = execution with { Status = WorkflowStatus.Paused };
            _activeWorkflows[workflowId] = pausedExecution;

            _logger.LogInformation("Paused workflow {WorkflowId} ({WorkflowName})", workflowId, execution.Definition.Name);
            return Task.FromResult(true);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to pause workflow {WorkflowId}", workflowId);
            return Task.FromResult(false);
        }
    }

    public async Task<bool> ResumeWorkflowAsync(Guid workflowId, CancellationToken cancellationToken = default)
    {
        try
        {
            if (!_activeWorkflows.TryGetValue(workflowId, out var execution))
            {
                _logger.LogWarning("Workflow {WorkflowId} not found", workflowId);
                return false;
            }

            if (execution.Status != WorkflowStatus.Paused)
            {
                _logger.LogWarning("Workflow {WorkflowId} is not paused and cannot be resumed", workflowId);
                return false;
            }

            var resumedExecution = execution with { Status = WorkflowStatus.Running };
            _activeWorkflows[workflowId] = resumedExecution;

            _logger.LogInformation("Resumed workflow {WorkflowId} ({WorkflowName})", workflowId, execution.Definition.Name);

            // Continue processing steps
            await ProcessWorkflowStepsAsync(workflowId, cancellationToken);
            
            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to resume workflow {WorkflowId}", workflowId);
            return false;
        }
    }

    public Task<bool> CancelWorkflowAsync(Guid workflowId, string reason, CancellationToken cancellationToken = default)
    {
        try
        {
            if (!_activeWorkflows.TryGetValue(workflowId, out var execution))
            {
                _logger.LogWarning("Workflow {WorkflowId} not found", workflowId);
                return Task.FromResult(false);
            }

            var cancelledExecution = execution with 
            { 
                Status = WorkflowStatus.Cancelled,
                CompletedAt = DateTime.UtcNow,
                ErrorMessage = $"Cancelled: {reason}"
            };

            _activeWorkflows[workflowId] = cancelledExecution;

            _logger.LogInformation("Cancelled workflow {WorkflowId} ({WorkflowName}). Reason: {Reason}", 
                workflowId, execution.Definition.Name, reason);
            
            return Task.FromResult(true);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to cancel workflow {WorkflowId}", workflowId);
            return Task.FromResult(false);
        }
    }

    public Task<WorkflowExecution?> GetWorkflowExecutionAsync(Guid workflowId, CancellationToken cancellationToken = default)
    {
        _activeWorkflows.TryGetValue(workflowId, out var execution);
        return Task.FromResult(execution);
    }

    public Task<IEnumerable<WorkflowExecution>> GetActiveWorkflowsAsync(CancellationToken cancellationToken = default)
    {
        var activeWorkflows = _activeWorkflows.Values
            .Where(w => w.Status == WorkflowStatus.Running || w.Status == WorkflowStatus.Paused)
            .ToList();

        return Task.FromResult<IEnumerable<WorkflowExecution>>(activeWorkflows);
    }

    public async Task<bool> HandleStepCompletionAsync(Guid workflowId, string stepId, WorkflowStepResult result, CancellationToken cancellationToken = default)
    {
        try
        {
            if (!_activeWorkflows.TryGetValue(workflowId, out var execution))
            {
                _logger.LogWarning("Workflow {WorkflowId} not found for step completion", workflowId);
                return false;
            }

            var stepExecution = execution.StepExecutions.FirstOrDefault(s => s.StepId == stepId);
            if (stepExecution == null)
            {
                _logger.LogWarning("Step {StepId} not found in workflow {WorkflowId}", stepId, workflowId);
                return false;
            }

            // Update step execution
            var updatedStepExecutions = execution.StepExecutions.Select(s =>
                s.StepId == stepId
                    ? s with 
                    { 
                        Status = result.Success ? WorkflowStepStatus.Completed : WorkflowStepStatus.Failed,
                        CompletedAt = DateTime.UtcNow,
                        Result = result
                    }
                    : s
            ).ToList();

            // Update workflow data with output data
            var updatedData = new Dictionary<string, object>(execution.Data);
            if (result.OutputData != null)
            {
                foreach (var kvp in result.OutputData)
                {
                    updatedData[kvp.Key] = kvp.Value;
                }
            }

            // Calculate progress
            var completedSteps = updatedStepExecutions.Count(s => s.Status == WorkflowStepStatus.Completed);
            var totalSteps = updatedStepExecutions.Count;
            var progress = totalSteps > 0 ? (double)completedSteps / totalSteps * 100.0 : 0.0;

            var updatedExecution = execution with
            {
                StepExecutions = updatedStepExecutions,
                Data = updatedData,
                ProgressPercentage = progress
            };

            _activeWorkflows[workflowId] = updatedExecution;

            _logger.LogInformation("Completed step {StepId} in workflow {WorkflowId}. Success: {Success}", 
                stepId, workflowId, result.Success);

            // Continue processing next steps
            await ProcessWorkflowStepsAsync(workflowId, cancellationToken);

            return true;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to handle step completion for workflow {WorkflowId}, step {StepId}", workflowId, stepId);
            return false;
        }
    }

    public async Task<bool> HandleStepFailureAsync(Guid workflowId, string stepId, string error, CancellationToken cancellationToken = default)
    {
        try
        {
            var result = new WorkflowStepResult(false, null, error);
            return await HandleStepCompletionAsync(workflowId, stepId, result, cancellationToken);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to handle step failure for workflow {WorkflowId}, step {StepId}", workflowId, stepId);
            return false;
        }
    }

    public async Task ProcessPendingWorkflowsAsync(CancellationToken cancellationToken = default)
    {
        try
        {
            var runningWorkflows = _activeWorkflows.Values
                .Where(w => w.Status == WorkflowStatus.Running)
                .ToList();

            foreach (var workflow in runningWorkflows)
            {
                await ProcessWorkflowStepsAsync(workflow.Id, cancellationToken);
            }

            _logger.LogDebug("Processed {WorkflowCount} running workflows", runningWorkflows.Count);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to process pending workflows");
        }
    }

    public Task<WorkflowTemplate> CreateTemplateAsync(string name, WorkflowDefinition definition, CancellationToken cancellationToken = default)
    {
        try
        {
            var template = new WorkflowTemplate(
                Guid.NewGuid(),
                name,
                definition.Description,
                definition,
                "Custom", // Default category
                ExtractTagsFromDefinition(definition),
                DateTime.UtcNow,
                "System", // Default creator
                0
            );

            _templates[name] = template;

            _logger.LogInformation("Created workflow template '{TemplateName}' with {StepCount} steps", 
                name, definition.Steps.Count());

            return Task.FromResult(template);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to create workflow template '{TemplateName}'", name);
            throw;
        }
    }

    public Task<WorkflowDefinition?> GetTemplateAsync(string templateName, CancellationToken cancellationToken = default)
    {
        _templates.TryGetValue(templateName, out var template);
        return Task.FromResult(template?.Definition);
    }

    public Task<IEnumerable<WorkflowTemplate>> GetAvailableTemplatesAsync(CancellationToken cancellationToken = default)
    {
        return Task.FromResult<IEnumerable<WorkflowTemplate>>(_templates.Values.ToList());
    }

    private async Task ProcessWorkflowStepsAsync(Guid workflowId, CancellationToken cancellationToken)
    {
        try
        {
            if (!_activeWorkflows.TryGetValue(workflowId, out var execution))
            {
                return;
            }

            if (execution.Status != WorkflowStatus.Running)
            {
                return;
            }

            // Find steps ready to execute
            var readySteps = GetReadySteps(execution);
            
            if (!readySteps.Any())
            {
                // Check if workflow is complete
                await CheckWorkflowCompletionAsync(workflowId);
                return;
            }

            // Execute ready steps
            var concurrentLimit = execution.Definition.Settings.MaxConcurrentSteps;
            var currentRunningSteps = execution.StepExecutions.Count(s => s.Status == WorkflowStepStatus.Running);
            var availableSlots = Math.Max(0, concurrentLimit - currentRunningSteps);

            var stepsToExecute = readySteps.Take(availableSlots);

            foreach (var stepExecution in stepsToExecute)
            {
                await ExecuteStepAsync(workflowId, stepExecution, cancellationToken);
            }
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to process workflow steps for {WorkflowId}", workflowId);
        }
    }

    private IEnumerable<WorkflowStepExecution> GetReadySteps(WorkflowExecution execution)
    {
        var readySteps = new List<WorkflowStepExecution>();

        foreach (var stepExecution in execution.StepExecutions.Where(s => s.Status == WorkflowStepStatus.Pending))
        {
            var step = execution.Definition.Steps.First(s => s.Id == stepExecution.StepId);
            
            // Check if prerequisites are met
            if (step.Prerequisites?.Any() == true)
            {
                var prerequisitesMet = step.Prerequisites.All(prereq =>
                    execution.StepExecutions.Any(s => s.StepId == prereq && s.Status == WorkflowStepStatus.Completed));
                
                if (!prerequisitesMet)
                {
                    continue;
                }
            }

            // Check workflow transitions
            var incomingTransitions = execution.Definition.Transitions.Where(t => t.ToStepId == stepExecution.StepId);
            if (incomingTransitions.Any())
            {
                var transitionConditionsMet = incomingTransitions.Any(transition =>
                    EvaluateTransitionCondition(execution, transition));
                
                if (!transitionConditionsMet)
                {
                    continue;
                }
            }

            readySteps.Add(stepExecution);
        }

        return readySteps;
    }

    private bool EvaluateTransitionCondition(WorkflowExecution execution, WorkflowTransition transition)
    {
        // Check if the source step is completed
        var sourceStep = execution.StepExecutions.FirstOrDefault(s => s.StepId == transition.FromStepId);
        if (sourceStep?.Status != WorkflowStepStatus.Completed)
        {
            return false;
        }

        // If no condition is specified, transition is valid
        if (transition.Condition == null)
        {
            return true;
        }

        // Evaluate the condition
        return EvaluateCondition(execution, transition.Condition);
    }

    private bool EvaluateCondition(WorkflowExecution execution, WorkflowCondition condition)
    {
        try
        {
            return condition.Type switch
            {
                WorkflowConditionType.DataEquals => EvaluateDataEquals(execution.Data, condition),
                WorkflowConditionType.DataNotEquals => !EvaluateDataEquals(execution.Data, condition),
                WorkflowConditionType.DataContains => EvaluateDataContains(execution.Data, condition),
                WorkflowConditionType.DataGreaterThan => EvaluateDataComparison(execution.Data, condition, ">"),
                WorkflowConditionType.DataLessThan => EvaluateDataComparison(execution.Data, condition, "<"),
                WorkflowConditionType.StepCompleted => EvaluateStepStatus(execution, condition, WorkflowStepStatus.Completed),
                WorkflowConditionType.StepFailed => EvaluateStepStatus(execution, condition, WorkflowStepStatus.Failed),
                WorkflowConditionType.TimeElapsed => EvaluateTimeElapsed(execution, condition),
                _ => true // Default to true for unsupported conditions
            };
        }
        catch (Exception ex)
        {
            _logger.LogWarning(ex, "Failed to evaluate condition {ConditionType}: {Expression}", condition.Type, condition.Expression);
            return false;
        }
    }

    private async Task ExecuteStepAsync(Guid workflowId, WorkflowStepExecution stepExecution, CancellationToken cancellationToken)
    {
        try
        {
            if (!_activeWorkflows.TryGetValue(workflowId, out var execution))
            {
                return;
            }

            var step = execution.Definition.Steps.First(s => s.Id == stepExecution.StepId);

            // Update step status to running
            var updatedStepExecutions = execution.StepExecutions.Select(s =>
                s.StepId == stepExecution.StepId
                    ? s with { Status = WorkflowStepStatus.Running, StartedAt = DateTime.UtcNow }
                    : s
            ).ToList();

            var updatedExecution = execution with { StepExecutions = updatedStepExecutions };
            _activeWorkflows[workflowId] = updatedExecution;

            _logger.LogInformation("Starting execution of step {StepId} ({StepName}) in workflow {WorkflowId}", 
                step.Id, step.Name, workflowId);

            // Execute step based on type
            await ExecuteStepByTypeAsync(workflowId, step, cancellationToken);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to execute step {StepId} in workflow {WorkflowId}", stepExecution.StepId, workflowId);
            await HandleStepFailureAsync(workflowId, stepExecution.StepId, ex.Message, cancellationToken);
        }
    }

    private async Task ExecuteStepByTypeAsync(Guid workflowId, WorkflowStep step, CancellationToken cancellationToken)
    {
        switch (step.Type)
        {
            case WorkflowStepType.AgentTask:
                await ExecuteAgentTaskStepAsync(workflowId, step, cancellationToken);
                break;
            
            case WorkflowStepType.Delay:
                await ExecuteDelayStepAsync(workflowId, step, cancellationToken);
                break;
            
            case WorkflowStepType.DataTransformation:
                await ExecuteDataTransformationStepAsync(workflowId, step, cancellationToken);
                break;
            
            case WorkflowStepType.Conditional:
                await ExecuteConditionalStepAsync(workflowId, step, cancellationToken);
                break;
            
            default:
                _logger.LogWarning("Unsupported step type {StepType} for step {StepId}", step.Type, step.Id);
                await HandleStepFailureAsync(workflowId, step.Id, $"Unsupported step type: {step.Type}", cancellationToken);
                break;
        }
    }

    private async Task ExecuteAgentTaskStepAsync(Guid workflowId, WorkflowStep step, CancellationToken cancellationToken)
    {
        try
        {
            if (step.TaskRequirements == null)
            {
                await HandleStepFailureAsync(workflowId, step.Id, "No task requirements specified for agent task step", cancellationToken);
                return;
            }

            // Find best agent for the task
            var bestMatch = await _capabilityMatcher.FindBestMatchAsync(step.TaskRequirements, cancellationToken);
            if (bestMatch == null)
            {
                await HandleStepFailureAsync(workflowId, step.Id, "No suitable agent found for task", cancellationToken);
                return;
            }

            // Create and assign task to agent
            var agentTask = AgentTask.Create(
                step.TaskRequirements.Description,
                step.TaskRequirements.RequiredCapabilities ?? Enumerable.Empty<string>(),
                Domain.AgentManagement.Enums.TaskPriority.Medium // Convert from workflow priority if needed
            );

            bestMatch.Agent.AssignTask(agentTask);
            await _agentRepository.UpdateAsync(bestMatch.Agent, cancellationToken);
            await _taskRepository.AddAsync(agentTask, cancellationToken);

            _logger.LogInformation("Assigned task for step {StepId} to agent {AgentId} ({AgentName})", 
                step.Id, bestMatch.Agent.Id, bestMatch.Agent.Name);

            // For now, simulate task completion after a delay
            // In a real implementation, this would be handled by the agent task processing system
            _ = Task.Run(async () =>
            {
                await Task.Delay(TimeSpan.FromSeconds(5), cancellationToken);
                
                var result = new WorkflowStepResult(
                    true,
                    new Dictionary<string, object> { ["agentId"] = bestMatch.Agent.Id.ToString(), ["agentName"] = bestMatch.Agent.Name },
                    $"Task completed by {bestMatch.Agent.Name}",
                    TimeSpan.FromSeconds(5)
                );
                
                await HandleStepCompletionAsync(workflowId, step.Id, result, CancellationToken.None);
            }, cancellationToken);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to execute agent task step {StepId}", step.Id);
            await HandleStepFailureAsync(workflowId, step.Id, ex.Message, cancellationToken);
        }
    }

    private async Task ExecuteDelayStepAsync(Guid workflowId, WorkflowStep step, CancellationToken cancellationToken)
    {
        try
        {
            var delayMs = 1000; // Default 1 second
            
            if (step.Configuration?.TryGetValue("delayMs", out var delayValue) == true)
            {
                if (delayValue is int intDelay)
                    delayMs = intDelay;
                else if (int.TryParse(delayValue.ToString(), out var parsedDelay))
                    delayMs = parsedDelay;
            }

            _logger.LogInformation("Executing delay step {StepId} for {DelayMs}ms", step.Id, delayMs);

            await Task.Delay(delayMs, cancellationToken);

            var result = new WorkflowStepResult(
                true,
                new Dictionary<string, object> { ["delayMs"] = delayMs },
                $"Delay of {delayMs}ms completed",
                TimeSpan.FromMilliseconds(delayMs)
            );

            await HandleStepCompletionAsync(workflowId, step.Id, result, cancellationToken);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to execute delay step {StepId}", step.Id);
            await HandleStepFailureAsync(workflowId, step.Id, ex.Message, cancellationToken);
        }
    }

    private async Task ExecuteDataTransformationStepAsync(Guid workflowId, WorkflowStep step, CancellationToken cancellationToken)
    {
        try
        {
            // Simple data transformation logic
            var outputData = new Dictionary<string, object>();
            
            if (step.Configuration?.TryGetValue("transformations", out var transformValue) == true)
            {
                // Apply transformations (simplified example)
                outputData["transformed"] = true;
                outputData["timestamp"] = DateTime.UtcNow;
            }

            var result = new WorkflowStepResult(
                true,
                outputData,
                "Data transformation completed",
                TimeSpan.FromMilliseconds(100)
            );

            await HandleStepCompletionAsync(workflowId, step.Id, result, cancellationToken);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to execute data transformation step {StepId}", step.Id);
            await HandleStepFailureAsync(workflowId, step.Id, ex.Message, cancellationToken);
        }
    }

    private async Task ExecuteConditionalStepAsync(Guid workflowId, WorkflowStep step, CancellationToken cancellationToken)
    {
        try
        {
            // Conditional steps are handled by the transition logic
            // This step just marks itself as completed
            var result = new WorkflowStepResult(
                true,
                new Dictionary<string, object> { ["conditional"] = true },
                "Conditional step evaluated",
                TimeSpan.FromMilliseconds(10)
            );

            await HandleStepCompletionAsync(workflowId, step.Id, result, cancellationToken);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to execute conditional step {StepId}", step.Id);
            await HandleStepFailureAsync(workflowId, step.Id, ex.Message, cancellationToken);
        }
    }

    private async Task CheckWorkflowCompletionAsync(Guid workflowId)
    {
        try
        {
            if (!_activeWorkflows.TryGetValue(workflowId, out var execution))
            {
                return;
            }

            var allStepsCompleted = execution.StepExecutions.All(s => 
                s.Status == WorkflowStepStatus.Completed || 
                s.Status == WorkflowStepStatus.Skipped ||
                (s.Status == WorkflowStepStatus.Failed && execution.Definition.Steps.First(step => step.Id == s.StepId).IsOptional));

            var anyStepFailed = execution.StepExecutions.Any(s => 
                s.Status == WorkflowStepStatus.Failed && 
                !execution.Definition.Steps.First(step => step.Id == s.StepId).IsOptional);

            if (anyStepFailed && execution.Definition.Settings.FailOnStepError)
            {
                var completedExecution = execution with
                {
                    Status = WorkflowStatus.Failed,
                    CompletedAt = DateTime.UtcNow,
                    ErrorMessage = "One or more required steps failed",
                    ProgressPercentage = 100.0
                };

                _activeWorkflows[workflowId] = completedExecution;
                _logger.LogWarning("Workflow {WorkflowId} failed due to step failures", workflowId);
            }
            else if (allStepsCompleted)
            {
                var completedExecution = execution with
                {
                    Status = WorkflowStatus.Completed,
                    CompletedAt = DateTime.UtcNow,
                    ProgressPercentage = 100.0
                };

                _activeWorkflows[workflowId] = completedExecution;
                _logger.LogInformation("Workflow {WorkflowId} ({WorkflowName}) completed successfully", 
                    workflowId, execution.Definition.Name);
            }
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Failed to check workflow completion for {WorkflowId}", workflowId);
        }
    }

    private static bool EvaluateDataEquals(Dictionary<string, object> data, WorkflowCondition condition)
    {
        if (!condition.Parameters?.TryGetValue("key", out var keyObj) == true || keyObj is not string key)
            return false;

        if (!condition.Parameters.TryGetValue("value", out var expectedValue))
            return false;

        return data.TryGetValue(key, out var actualValue) && Equals(actualValue, expectedValue);
    }

    private static bool EvaluateDataContains(Dictionary<string, object> data, WorkflowCondition condition)
    {
        if (!condition.Parameters?.TryGetValue("key", out var keyObj) == true || keyObj is not string key)
            return false;

        if (!condition.Parameters.TryGetValue("value", out var searchValue))
            return false;

        if (!data.TryGetValue(key, out var actualValue))
            return false;

        return actualValue?.ToString()?.Contains(searchValue.ToString() ?? "", StringComparison.OrdinalIgnoreCase) == true;
    }

    private static bool EvaluateDataComparison(Dictionary<string, object> data, WorkflowCondition condition, string operation)
    {
        if (!condition.Parameters?.TryGetValue("key", out var keyObj) == true || keyObj is not string key)
            return false;

        if (!condition.Parameters.TryGetValue("value", out var compareValue))
            return false;

        if (!data.TryGetValue(key, out var actualValue))
            return false;

        // Simple numeric comparison
        if (double.TryParse(actualValue?.ToString(), out var actualNum) && 
            double.TryParse(compareValue?.ToString(), out var compareNum))
        {
            return operation switch
            {
                ">" => actualNum > compareNum,
                "<" => actualNum < compareNum,
                _ => false
            };
        }

        return false;
    }

    private static bool EvaluateStepStatus(WorkflowExecution execution, WorkflowCondition condition, WorkflowStepStatus expectedStatus)
    {
        if (!condition.Parameters?.TryGetValue("stepId", out var stepIdObj) == true || stepIdObj is not string stepId)
            return false;

        var stepExecution = execution.StepExecutions.FirstOrDefault(s => s.StepId == stepId);
        return stepExecution?.Status == expectedStatus;
    }

    private static bool EvaluateTimeElapsed(WorkflowExecution execution, WorkflowCondition condition)
    {
        if (!condition.Parameters?.TryGetValue("minutes", out var minutesObj) == true)
            return false;

        if (!double.TryParse(minutesObj.ToString(), out var minutes))
            return false;

        var elapsed = DateTime.UtcNow - execution.StartedAt;
        return elapsed.TotalMinutes >= minutes;
    }

    private static IEnumerable<string> ExtractTagsFromDefinition(WorkflowDefinition definition)
    {
        var tags = new List<string>();
        
        // Extract tags from step types
        foreach (var step in definition.Steps)
        {
            tags.Add(step.Type.ToString().ToLowerInvariant());
        }

        // Add general tags based on workflow characteristics
        if (definition.Settings.AllowParallelExecution)
            tags.Add("parallel");
        
        if (definition.Steps.Any(s => s.Type == WorkflowStepType.AgentTask))
            tags.Add("agent-workflow");

        return tags.Distinct();
    }
}