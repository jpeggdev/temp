using Microsoft.Extensions.Logging;
using System.Diagnostics;
using HeyDav.Application.CommandProcessing;

namespace HeyDav.Application.TaskProcessing;

public interface IEnhancedCommandOrchestrator : ICommandOrchestrator
{
    Task<CommandResult> ProcessComplexCommandAsync(CommandRequest request);
    Task<TaskAnalysisResult> AnalyzeCommandAsync(string command, Dictionary<string, object>? context = null);
    Task<List<TaskExecutionResult>> ExecuteTaskBreakdownAsync(List<TaskBreakdown> tasks, ExecutionStrategy strategy);
    Task<CommandResult> ProcessWithTaskAnalysisAsync(CommandRequest request);
}

public class EnhancedCommandOrchestrator : IEnhancedCommandOrchestrator
{
    private readonly ICommandProcessorFactory _processorFactory;
    private readonly ITaskAnalyzer _taskAnalyzer;
    private readonly ITaskExecutionEngine _executionEngine;
    private readonly IPluginManager _pluginManager;
    private readonly ILogger<EnhancedCommandOrchestrator> _logger;

    public EnhancedCommandOrchestrator(
        ICommandProcessorFactory processorFactory,
        ITaskAnalyzer taskAnalyzer,
        ITaskExecutionEngine executionEngine,
        IPluginManager pluginManager,
        ILogger<EnhancedCommandOrchestrator> logger)
    {
        _processorFactory = processorFactory;
        _taskAnalyzer = taskAnalyzer;
        _executionEngine = executionEngine;
        _pluginManager = pluginManager;
        _logger = logger;
    }

    public async Task<CommandResult> ProcessCommandAsync(string command, string source, Dictionary<string, object>? context = null)
    {
        var request = new CommandRequest
        {
            Command = command,
            Source = source,
            Context = context ?? new Dictionary<string, object>(),
            Timestamp = DateTime.UtcNow
        };

        return await ProcessComplexCommandAsync(request);
    }

    public async Task<CommandResult> ProcessCommandAsync(CommandRequest request)
    {
        return await ProcessComplexCommandAsync(request);
    }

    public async Task<CommandResult> ProcessComplexCommandAsync(CommandRequest request)
    {
        var stopwatch = Stopwatch.StartNew();
        
        try
        {
            _logger.LogInformation("Processing complex command: '{Command}' from source: '{Source}'", 
                request.Command, request.Source);

            // Validate request
            if (string.IsNullOrWhiteSpace(request.Command))
            {
                return new CommandResult
                {
                    Success = false,
                    Message = "Command cannot be empty",
                    ProcessingTime = stopwatch.Elapsed
                };
            }

            // Analyze the command to understand its complexity and requirements
            var analysis = await _taskAnalyzer.AnalyzeTaskAsync(request.Command, request.Context);
            
            _logger.LogDebug("Command analysis completed. Intent: {Intent}, Complexity: {Complexity}, Subtasks: {SubtaskCount}",
                analysis.Intent, analysis.Complexity, analysis.Subtasks.Count);

            // For simple tasks, use the original orchestrator logic
            if (analysis.Complexity == TaskComplexity.Simple && analysis.Subtasks.Count <= 1)
            {
                return await ProcessSimpleCommandAsync(request);
            }

            // For complex tasks, use the enhanced processing
            return await ProcessWithTaskAnalysisAsync(request, analysis);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error processing complex command: '{Command}' from source: '{Source}'", 
                request.Command, request.Source);

            return new CommandResult
            {
                Success = false,
                Message = $"An error occurred while processing your command: {ex.Message}",
                ProcessingTime = stopwatch.Elapsed,
                ProcessorUsed = "error-handler"
            };
        }
    }

    public async Task<TaskAnalysisResult> AnalyzeCommandAsync(string command, Dictionary<string, object>? context = null)
    {
        return await _taskAnalyzer.AnalyzeTaskAsync(command, context);
    }

    public async Task<List<TaskExecutionResult>> ExecuteTaskBreakdownAsync(List<TaskBreakdown> tasks, ExecutionStrategy strategy)
    {
        var results = new List<TaskExecutionResult>();
        
        try
        {
            _logger.LogInformation("Executing task breakdown with {TaskCount} tasks using {ExecutionMode} mode", 
                tasks.Count, strategy.Mode);

            switch (strategy.Mode)
            {
                case ExecutionMode.Sequential:
                    results = await ExecuteSequentiallyAsync(tasks);
                    break;
                
                case ExecutionMode.Parallel:
                    results = await ExecuteInParallelAsync(tasks, strategy.MaxParallelTasks);
                    break;
                
                case ExecutionMode.Hybrid:
                    results = await ExecuteHybridAsync(tasks, strategy);
                    break;
                
                case ExecutionMode.Batch:
                    results = await ExecuteBatchAsync(tasks, strategy);
                    break;
                
                default:
                    results = await ExecuteSequentiallyAsync(tasks);
                    break;
            }

            _logger.LogInformation("Task breakdown execution completed. Success rate: {SuccessRate}%", 
                results.Count(r => r.Success) * 100.0 / results.Count);

            return results;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error executing task breakdown");
            return results;
        }
    }

    public async Task<CommandResult> ProcessWithTaskAnalysisAsync(CommandRequest request)
    {
        var analysis = await _taskAnalyzer.AnalyzeTaskAsync(request.Command, request.Context);
        return await ProcessWithTaskAnalysisAsync(request, analysis);
    }

    public async Task<CommandResult> ProcessWithSpecificProcessorAsync(string command, string processorType, string source)
    {
        var request = new CommandRequest
        {
            Command = command,
            Source = source,
            ProcessorType = processorType,
            Timestamp = DateTime.UtcNow
        };

        return await ProcessComplexCommandAsync(request);
    }

    public async Task<List<CommandCapabilities>> GetAvailableCommandsAsync()
    {
        try
        {
            var capabilities = new List<CommandCapabilities>();
            
            // Get capabilities from traditional processors
            var processors = _processorFactory.GetAllProcessors();
            capabilities.AddRange(processors.Select(p => p.Capabilities));

            // Get capabilities from loaded plugins
            var plugins = await _pluginManager.GetLoadedPluginsAsync();
            foreach (var plugin in plugins)
            {
                var pluginCapabilities = await plugin.GetCapabilitiesAsync();
                var commandCapabilities = new CommandCapabilities
                {
                    SupportedCommands = pluginCapabilities.SupportedCommands,
                    Description = $"Plugin: {plugin.Name}",
                    SupportedSources = new List<string> { "all" }
                };
                capabilities.Add(commandCapabilities);
            }

            _logger.LogDebug("Retrieved capabilities for {ProcessorCount} processors and {PluginCount} plugins", 
                processors.Count(), plugins.Count);
            
            return capabilities;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error retrieving available commands");
            return new List<CommandCapabilities>();
        }
    }

    private async Task<CommandResult> ProcessSimpleCommandAsync(CommandRequest request)
    {
        var stopwatch = Stopwatch.StartNew();
        
        try
        {
            // Get the appropriate processor
            ICommandProcessor processor;
            
            if (!string.IsNullOrEmpty(request.ProcessorType))
            {
                _logger.LogDebug("Using specified processor: {ProcessorType}", request.ProcessorType);
                processor = _processorFactory.GetProcessor(request.ProcessorType);
            }
            else
            {
                _logger.LogDebug("Finding best processor for command: {Command}", request.Command);
                processor = _processorFactory.GetBestProcessor(request.Command);
            }

            // Process the command
            var result = await processor.ProcessAsync(request);
            result.ProcessingTime = stopwatch.Elapsed;
            
            _logger.LogInformation("Simple command processed by {ProcessorType} in {ElapsedMs}ms. Success: {Success}", 
                processor.ProcessorType, stopwatch.ElapsedMilliseconds, result.Success);

            return result;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error processing simple command: '{Command}'", request.Command);
            throw;
        }
    }

    private async Task<CommandResult> ProcessWithTaskAnalysisAsync(CommandRequest request, TaskAnalysisResult analysis)
    {
        var stopwatch = Stopwatch.StartNew();
        
        try
        {
            // Check if human approval is required
            if (analysis.SuggestedStrategy.RequiresHumanApproval)
            {
                _logger.LogWarning("Task requires human approval: {Command}", request.Command);
                return new CommandResult
                {
                    Success = false,
                    Message = "This task requires human approval due to its complexity or potential impact.",
                    Data = analysis,
                    ProcessingTime = stopwatch.Elapsed,
                    Action = CommandAction.Query
                };
            }

            // Execute pre-execution checks
            foreach (var check in analysis.SuggestedStrategy.PreExecutionChecks)
            {
                var checkResult = await ExecutePreExecutionCheckAsync(check, analysis);
                if (!checkResult)
                {
                    return new CommandResult
                    {
                        Success = false,
                        Message = $"Pre-execution check failed: {check}",
                        ProcessingTime = stopwatch.Elapsed
                    };
                }
            }

            // Execute the task breakdown
            var executionResults = await ExecuteTaskBreakdownAsync(analysis.Subtasks, analysis.SuggestedStrategy);
            
            // Aggregate results
            var overallSuccess = executionResults.All(r => r.Success);
            var aggregatedData = new Dictionary<string, object>
            {
                { "analysis", analysis },
                { "execution_results", executionResults },
                { "metrics", CalculateAggregatedMetrics(executionResults) }
            };

            var message = overallSuccess 
                ? "Complex command executed successfully" 
                : "Complex command executed with some failures";

            if (!overallSuccess)
            {
                var failures = executionResults.Where(r => !r.Success).ToList();
                message += $". {failures.Count} subtask(s) failed.";
            }

            return new CommandResult
            {
                Success = overallSuccess,
                Message = message,
                Data = aggregatedData,
                ProcessingTime = stopwatch.Elapsed,
                ProcessorUsed = "enhanced-orchestrator",
                Metadata = new Dictionary<string, object>
                {
                    { "subtask_count", executionResults.Count },
                    { "execution_mode", analysis.SuggestedStrategy.Mode.ToString() },
                    { "total_duration", executionResults.Sum(r => r.ExecutionTime.TotalMilliseconds) }
                }
            };
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error processing complex command with task analysis: '{Command}'", request.Command);
            throw;
        }
    }

    private async Task<List<TaskExecutionResult>> ExecuteSequentiallyAsync(List<TaskBreakdown> tasks)
    {
        var results = new List<TaskExecutionResult>();
        
        foreach (var task in tasks.OrderBy(t => t.Priority))
        {
            var executionRequest = CreateTaskExecutionRequest(task);
            var result = await _executionEngine.ExecuteTaskAsync(executionRequest);
            results.Add(result);

            // Stop on failure if fail-fast is enabled
            if (!result.Success && ShouldFailFast(task))
            {
                _logger.LogWarning("Task failed with fail-fast enabled. Stopping execution.");
                break;
            }
        }

        return results;
    }

    private async Task<List<TaskExecutionResult>> ExecuteInParallelAsync(List<TaskBreakdown> tasks, int maxParallelTasks)
    {
        var results = new List<TaskExecutionResult>();
        var semaphore = new SemaphoreSlim(maxParallelTasks, maxParallelTasks);
        
        var executionTasks = tasks.Select(async task =>
        {
            await semaphore.WaitAsync();
            try
            {
                var executionRequest = CreateTaskExecutionRequest(task);
                return await _executionEngine.ExecuteTaskAsync(executionRequest);
            }
            finally
            {
                semaphore.Release();
            }
        });

        var taskResults = await Task.WhenAll(executionTasks);
        results.AddRange(taskResults);

        return results;
    }

    private async Task<List<TaskExecutionResult>> ExecuteHybridAsync(List<TaskBreakdown> tasks, ExecutionStrategy strategy)
    {
        var results = new List<TaskExecutionResult>();
        var dependencies = await _taskAnalyzer.AnalyzeDependenciesAsync(tasks);
        
        // Execute in parallel groups respecting dependencies
        foreach (var parallelGroup in dependencies.ParallelGroups)
        {
            var groupTasks = tasks.Where(t => parallelGroup.Contains(t.Id)).ToList();
            var groupResults = await ExecuteInParallelAsync(groupTasks, strategy.MaxParallelTasks);
            results.AddRange(groupResults);

            // Check if any critical task in the group failed
            if (groupResults.Any(r => !r.Success && ShouldFailFast(tasks.First(t => t.Id == r.TaskId))))
            {
                _logger.LogWarning("Critical task failed in parallel group. Stopping execution.");
                break;
            }
        }

        return results;
    }

    private async Task<List<TaskExecutionResult>> ExecuteBatchAsync(List<TaskBreakdown> tasks, ExecutionStrategy strategy)
    {
        var results = new List<TaskExecutionResult>();
        var batchSize = Math.Min(tasks.Count, strategy.MaxParallelTasks);
        
        for (int i = 0; i < tasks.Count; i += batchSize)
        {
            var batch = tasks.Skip(i).Take(batchSize).ToList();
            var batchResults = await ExecuteInParallelAsync(batch, batchSize);
            results.AddRange(batchResults);

            // Add delay between batches to prevent resource exhaustion
            if (i + batchSize < tasks.Count)
            {
                await Task.Delay(TimeSpan.FromSeconds(1));
            }
        }

        return results;
    }

    private TaskExecutionRequest CreateTaskExecutionRequest(TaskBreakdown task)
    {
        return new TaskExecutionRequest
        {
            Id = task.Id,
            Task = task,
            CommandRequest = new CommandRequest
            {
                Command = task.Description,
                Source = "orchestrator",
                Parameters = task.Parameters.ToDictionary(kvp => kvp.Key, kvp => kvp.Value.ToString() ?? ""),
                Timestamp = DateTime.UtcNow
            }
        };
    }

    private async Task<bool> ExecutePreExecutionCheckAsync(string checkName, TaskAnalysisResult analysis)
    {
        try
        {
            _logger.LogDebug("Executing pre-execution check: {CheckName}", checkName);

            switch (checkName)
            {
                case "VerifyAgentAvailability":
                    return await VerifyAgentAvailabilityAsync(analysis.RequiredAgents);
                
                case "ConfirmDestructiveOperations":
                    return await ConfirmDestructiveOperationsAsync(analysis);
                
                default:
                    _logger.LogWarning("Unknown pre-execution check: {CheckName}", checkName);
                    return true; // Default to allowing execution
            }
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error executing pre-execution check: {CheckName}", checkName);
            return false;
        }
    }

    private async Task<bool> VerifyAgentAvailabilityAsync(List<string> requiredAgents)
    {
        // Check if required processors are available
        var availableProcessors = _processorFactory.GetAllProcessors().Select(p => p.ProcessorType).ToList();
        
        foreach (var agent in requiredAgents)
        {
            var processorName = $"{agent}Processor";
            if (!availableProcessors.Any(p => p.Contains(agent, StringComparison.OrdinalIgnoreCase)))
            {
                _logger.LogWarning("Required agent not available: {Agent}", agent);
                return false;
            }
        }

        return true;
    }

    private async Task<bool> ConfirmDestructiveOperationsAsync(TaskAnalysisResult analysis)
    {
        // In a real implementation, this might show a confirmation dialog or check user permissions
        var hasDestructiveOperations = analysis.Subtasks.Any(t => 
            t.Intent == TaskIntent.Delete || 
            t.Description.ToLowerInvariant().Contains("delete") ||
            t.Description.ToLowerInvariant().Contains("remove"));

        if (hasDestructiveOperations)
        {
            _logger.LogWarning("Destructive operations detected in task analysis");
            // For now, we'll allow it but log the warning
            // In production, this might require explicit user confirmation
        }

        return true;
    }

    private bool ShouldFailFast(TaskBreakdown task)
    {
        // Fail fast on critical tasks or destructive operations
        return task.Priority > 5 || task.Intent == TaskIntent.Delete;
    }

    private Dictionary<string, object> CalculateAggregatedMetrics(List<TaskExecutionResult> results)
    {
        return new Dictionary<string, object>
        {
            { "total_tasks", results.Count },
            { "successful_tasks", results.Count(r => r.Success) },
            { "failed_tasks", results.Count(r => !r.Success) },
            { "total_execution_time", results.Sum(r => r.ExecutionTime.TotalMilliseconds) },
            { "average_execution_time", results.Count > 0 ? results.Average(r => r.ExecutionTime.TotalMilliseconds) : 0 },
            { "processors_used", results.Select(r => r.ProcessorUsed).Distinct().ToList() }
        };
    }
}