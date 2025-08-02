using Microsoft.Extensions.Logging;
using System.Collections.Concurrent;
using System.Diagnostics;
using HeyDav.Application.CommandProcessing;

namespace HeyDav.Application.TaskProcessing;

public class TaskExecutionEngine : ITaskExecutionEngine
{
    private readonly ICommandProcessorFactory _processorFactory;
    private readonly ILogger<TaskExecutionEngine> _logger;
    private readonly ConcurrentDictionary<string, TaskExecutionState> _activeTasks = new();
    private readonly SemaphoreSlim _resourceSemaphore;
    private readonly ExecutionMetrics _metrics = new();
    private readonly Timer _metricsTimer;

    public event EventHandler<TaskProgressEventArgs>? TaskProgress;
    public event EventHandler<TaskCompletedEventArgs>? TaskCompleted;

    public TaskExecutionEngine(ICommandProcessorFactory processorFactory, ILogger<TaskExecutionEngine> logger)
    {
        _processorFactory = processorFactory;
        _logger = logger;
        _resourceSemaphore = new SemaphoreSlim(5, 5); // Default max 5 concurrent tasks
        
        // Start metrics collection timer
        _metricsTimer = new Timer(UpdateMetrics, null, TimeSpan.FromSeconds(30), TimeSpan.FromSeconds(30));
    }

    public async Task<TaskExecutionResult> ExecuteTaskAsync(TaskExecutionRequest request)
    {
        var stopwatch = Stopwatch.StartNew();
        var taskState = new TaskExecutionState
        {
            Id = request.Id,
            Status = TaskStatus.Queued,
            StartTime = DateTime.UtcNow,
            CancellationToken = request.CancellationToken
        };

        _activeTasks[request.Id] = taskState;

        try
        {
            _logger.LogInformation("Starting task execution: {TaskId} - {Description}", 
                request.Id, request.Task.Description);

            // Update status to running
            await UpdateTaskStatusAsync(request.Id, TaskStatus.Running, "Task execution started");

            // Wait for resource availability
            await _resourceSemaphore.WaitAsync(request.CancellationToken);

            try
            {
                // Execute the task
                var result = await ExecuteSingleTaskAsync(request, taskState);
                result.ExecutionTime = stopwatch.Elapsed;

                // Update metrics
                UpdateExecutionMetrics(result);

                // Update final status
                var finalStatus = result.Success ? TaskStatus.Completed : TaskStatus.Failed;
                await UpdateTaskStatusAsync(request.Id, finalStatus, result.Message);

                // Notify completion
                OnTaskCompleted(new TaskCompletedEventArgs { TaskId = request.Id, Result = result });

                _logger.LogInformation("Task execution completed: {TaskId} - Success: {Success} ({Duration}ms)", 
                    request.Id, result.Success, stopwatch.ElapsedMilliseconds);

                return result;
            }
            finally
            {
                _resourceSemaphore.Release();
                _activeTasks.TryRemove(request.Id, out _);
            }
        }
        catch (OperationCanceledException)
        {
            await UpdateTaskStatusAsync(request.Id, TaskStatus.Cancelled, "Task was cancelled");
            _activeTasks.TryRemove(request.Id, out _);

            return new TaskExecutionResult
            {
                TaskId = request.Id,
                Success = false,
                Message = "Task was cancelled",
                Status = TaskStatus.Cancelled,
                ExecutionTime = stopwatch.Elapsed,
                StartTime = taskState.StartTime,
                EndTime = DateTime.UtcNow
            };
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error executing task: {TaskId}", request.Id);
            
            await UpdateTaskStatusAsync(request.Id, TaskStatus.Failed, $"Task failed: {ex.Message}");
            _activeTasks.TryRemove(request.Id, out _);

            return new TaskExecutionResult
            {
                TaskId = request.Id,
                Success = false,
                Message = $"Task execution failed: {ex.Message}",
                Status = TaskStatus.Failed,
                ExecutionTime = stopwatch.Elapsed,
                StartTime = taskState.StartTime,
                EndTime = DateTime.UtcNow,
                Errors = new List<string> { ex.ToString() }
            };
        }
    }

    public async Task<TaskExecutionResult> ExecuteTasksAsync(List<TaskExecutionRequest> requests, ExecutionStrategy strategy)
    {
        var stopwatch = Stopwatch.StartNew();
        var overallResult = new TaskExecutionResult
        {
            TaskId = "batch-" + Guid.NewGuid().ToString("N")[..8],
            StartTime = DateTime.UtcNow,
            SubResults = new List<CommandResult>()
        };

        try
        {
            _logger.LogInformation("Executing batch of {TaskCount} tasks using strategy: {Strategy}", 
                requests.Count, strategy.Mode);

            List<TaskExecutionResult> results;

            switch (strategy.Mode)
            {
                case ExecutionMode.Sequential:
                    results = await ExecuteSequentiallyAsync(requests, strategy);
                    break;

                case ExecutionMode.Parallel:
                    results = await ExecuteInParallelAsync(requests, strategy);
                    break;

                case ExecutionMode.Hybrid:
                    results = await ExecuteHybridAsync(requests, strategy);
                    break;

                case ExecutionMode.Batch:
                    results = await ExecuteBatchAsync(requests, strategy);
                    break;

                case ExecutionMode.Streaming:
                    results = await ExecuteStreamingAsync(requests, strategy);
                    break;

                default:
                    results = await ExecuteSequentiallyAsync(requests, strategy);
                    break;
            }

            // Aggregate results
            overallResult.Success = results.All(r => r.Success);
            overallResult.Message = overallResult.Success 
                ? $"Batch execution completed successfully. {results.Count} tasks executed."
                : $"Batch execution completed with failures. {results.Count(r => r.Success)}/{results.Count} tasks succeeded.";

            overallResult.Data = new
            {
                strategy = strategy.Mode.ToString(),
                task_count = results.Count,
                successful_tasks = results.Count(r => r.Success),
                failed_tasks = results.Count(r => !r.Success),
                total_duration = results.Sum(r => r.ExecutionTime.TotalMilliseconds),
                individual_results = results
            };

            overallResult.Status = overallResult.Success ? TaskStatus.Completed : TaskStatus.Failed;
            overallResult.ExecutionTime = stopwatch.Elapsed;
            overallResult.EndTime = DateTime.UtcNow;

            // Convert TaskExecutionResults to CommandResults for compatibility
            overallResult.SubResults = results.Select(r => new CommandResult
            {
                Success = r.Success,
                Message = r.Message,
                Data = r.Data,
                ProcessingTime = r.ExecutionTime,
                ProcessorUsed = r.ProcessorUsed
            }).ToList();

            _logger.LogInformation("Batch execution completed: Success rate {SuccessRate}% ({Duration}ms)", 
                results.Count(r => r.Success) * 100.0 / results.Count, stopwatch.ElapsedMilliseconds);

            return overallResult;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error during batch execution");
            
            overallResult.Success = false;
            overallResult.Message = $"Batch execution failed: {ex.Message}";
            overallResult.Status = TaskStatus.Failed;
            overallResult.ExecutionTime = stopwatch.Elapsed;
            overallResult.EndTime = DateTime.UtcNow;
            overallResult.Errors.Add(ex.ToString());

            return overallResult;
        }
    }

    public async Task<TaskStatus> GetTaskStatusAsync(string taskId)
    {
        if (_activeTasks.TryGetValue(taskId, out var taskState))
        {
            return taskState.Status;
        }

        return TaskStatus.Pending; // Task not found, assume it hasn't started
    }

    public async Task<List<TaskStatus>> GetAllTaskStatusesAsync()
    {
        return _activeTasks.Values.Select(t => t.Status).ToList();
    }

    public async Task<bool> CancelTaskAsync(string taskId)
    {
        try
        {
            if (_activeTasks.TryGetValue(taskId, out var taskState))
            {
                taskState.CancellationTokenSource?.Cancel();
                await UpdateTaskStatusAsync(taskId, TaskStatus.Cancelled, "Task cancellation requested");
                
                _logger.LogInformation("Task cancellation requested: {TaskId}", taskId);
                return true;
            }

            _logger.LogWarning("Task not found for cancellation: {TaskId}", taskId);
            return false;
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error cancelling task: {TaskId}", taskId);
            return false;
        }
    }

    public async Task<TaskExecutionResult> RetryTaskAsync(string taskId)
    {
        try
        {
            _logger.LogInformation("Retrying task: {TaskId}", taskId);

            // For now, we can't retry without the original request
            // In a full implementation, we'd store the original requests
            throw new NotImplementedException("Task retry requires storing original task requests");
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error retrying task: {TaskId}", taskId);
            
            return new TaskExecutionResult
            {
                TaskId = taskId,
                Success = false,
                Message = $"Retry failed: {ex.Message}",
                Status = TaskStatus.Failed
            };
        }
    }

    public async Task<ExecutionMetrics> GetExecutionMetricsAsync()
    {
        return _metrics;
    }

    private async Task<TaskExecutionResult> ExecuteSingleTaskAsync(TaskExecutionRequest request, TaskExecutionState taskState)
    {
        try
        {
            // Find the appropriate processor for this task
            var processor = FindBestProcessorForTask(request.Task);
            
            // Create command request from task breakdown
            var commandRequest = new CommandRequest
            {
                Command = request.Task.Description,
                Source = request.CommandRequest.Source,
                Context = request.Context.SharedData,
                Parameters = request.Task.Parameters.ToDictionary(kvp => kvp.Key, kvp => kvp.Value.ToString() ?? ""),
                Priority = (CommandPriority)Math.Min((int)request.Task.Priority, 3),
                Timestamp = DateTime.UtcNow
            };

            // Execute the command
            var commandResult = await processor.ProcessAsync(commandRequest);

            // Create task execution result
            var result = new TaskExecutionResult
            {
                TaskId = request.Id,
                Success = commandResult.Success,
                Message = commandResult.Message,
                Data = commandResult.Data,
                Status = commandResult.Success ? TaskStatus.Completed : TaskStatus.Failed,
                StartTime = taskState.StartTime,
                EndTime = DateTime.UtcNow,
                ProcessorUsed = processor.ProcessorType,
                Metadata = commandResult.Metadata
            };

            // Convert CommandResult to TaskExecutionResult format
            result.SubResults = new List<CommandResult> { commandResult };

            if (!commandResult.Success)
            {
                result.Errors.Add(commandResult.Message);
            }

            return result;
        }
        catch (Exception ex)
        {
            return new TaskExecutionResult
            {
                TaskId = request.Id,
                Success = false,
                Message = $"Task execution failed: {ex.Message}",
                Status = TaskStatus.Failed,
                StartTime = taskState.StartTime,
                EndTime = DateTime.UtcNow,
                Errors = new List<string> { ex.ToString() }
            };
        }
    }

    private async Task<List<TaskExecutionResult>> ExecuteSequentiallyAsync(List<TaskExecutionRequest> requests, ExecutionStrategy strategy)
    {
        var results = new List<TaskExecutionResult>();

        foreach (var request in requests)
        {
            if (strategy.Configuration?.FailFast == true && results.Any(r => !r.Success))
            {
                _logger.LogInformation("Stopping sequential execution due to fail-fast setting");
                break;
            }

            var result = await ExecuteTaskAsync(request);
            results.Add(result);

            // Add delay between tasks if specified
            if (results.Count < requests.Count && strategy.Configuration?.RetryDelay > TimeSpan.Zero)
            {
                await Task.Delay(strategy.Configuration.RetryDelay);
            }
        }

        return results;
    }

    private async Task<List<TaskExecutionResult>> ExecuteInParallelAsync(List<TaskExecutionRequest> requests, ExecutionStrategy strategy)
    {
        var semaphore = new SemaphoreSlim(strategy.MaxParallelTasks, strategy.MaxParallelTasks);
        var tasks = requests.Select(async request =>
        {
            await semaphore.WaitAsync();
            try
            {
                return await ExecuteTaskAsync(request);
            }
            finally
            {
                semaphore.Release();
            }
        });

        var results = await Task.WhenAll(tasks);
        return results.ToList();
    }

    private async Task<List<TaskExecutionResult>> ExecuteHybridAsync(List<TaskExecutionRequest> requests, ExecutionStrategy strategy)
    {
        // For hybrid execution, we'd need dependency information
        // For now, fall back to parallel execution
        _logger.LogWarning("Hybrid execution not fully implemented, falling back to parallel");
        return await ExecuteInParallelAsync(requests, strategy);
    }

    private async Task<List<TaskExecutionResult>> ExecuteBatchAsync(List<TaskExecutionRequest> requests, ExecutionStrategy strategy)
    {
        var results = new List<TaskExecutionResult>();
        var batchSize = strategy.MaxParallelTasks;

        for (int i = 0; i < requests.Count; i += batchSize)
        {
            var batch = requests.Skip(i).Take(batchSize).ToList();
            var batchResults = await ExecuteInParallelAsync(batch, strategy);
            results.AddRange(batchResults);

            // Add delay between batches
            if (i + batchSize < requests.Count)
            {
                await Task.Delay(TimeSpan.FromSeconds(1));
            }
        }

        return results;
    }

    private async Task<List<TaskExecutionResult>> ExecuteStreamingAsync(List<TaskExecutionRequest> requests, ExecutionStrategy strategy)
    {
        // Streaming execution would process tasks as they become available
        // For now, fall back to sequential execution
        _logger.LogWarning("Streaming execution not fully implemented, falling back to sequential");
        return await ExecuteSequentiallyAsync(requests, strategy);
    }

    private ICommandProcessor FindBestProcessorForTask(TaskBreakdown task)
    {
        // Try to find a processor that can handle this task based on intent
        var intentBasedProcessor = task.Intent switch
        {
            TaskIntent.Create => "TodoCommandProcessor",
            TaskIntent.Update => "TodoCommandProcessor", 
            TaskIntent.Delete => "TodoCommandProcessor",
            TaskIntent.Read => "GeneralCommandProcessor",
            TaskIntent.Schedule => "ScheduleCommandProcessor",
            TaskIntent.Analyze => "GeneralCommandProcessor",
            _ => "GeneralCommandProcessor"
        };

        try
        {
            return _processorFactory.GetProcessor(intentBasedProcessor);
        }
        catch
        {
            // Fall back to best processor based on description
            return _processorFactory.GetBestProcessor(task.Description);
        }
    }

    private async Task UpdateTaskStatusAsync(string taskId, TaskStatus status, string message)
    {
        if (_activeTasks.TryGetValue(taskId, out var taskState))
        {
            taskState.Status = status;
            taskState.LastMessage = message;
            taskState.LastUpdate = DateTime.UtcNow;

            // Calculate progress percentage
            var progress = status switch
            {
                TaskStatus.Pending => 0f,
                TaskStatus.Queued => 10f,
                TaskStatus.Running => 50f,
                TaskStatus.Completed => 100f,
                TaskStatus.Failed => 100f,
                TaskStatus.Cancelled => 100f,
                _ => 0f
            };

            OnTaskProgress(new TaskProgressEventArgs
            {
                TaskId = taskId,
                Status = status,
                ProgressPercentage = progress,
                Message = message
            });
        }
    }

    private void UpdateExecutionMetrics(TaskExecutionResult result)
    {
        lock (_metrics)
        {
            _metrics.TotalTasksExecuted++;
            
            if (result.Success)
                _metrics.SuccessfulTasks++;
            else
                _metrics.FailedTasks++;

            _metrics.TotalExecutionTime = _metrics.TotalExecutionTime.Add(result.ExecutionTime);
            
            if (_metrics.TotalTasksExecuted > 0)
            {
                _metrics.AverageExecutionTime = TimeSpan.FromTicks(_metrics.TotalExecutionTime.Ticks / _metrics.TotalTasksExecuted);
            }

            // Update processor usage stats
            if (!string.IsNullOrEmpty(result.ProcessorUsed))
            {
                _metrics.ProcessorUsage[result.ProcessorUsed] = _metrics.ProcessorUsage.GetValueOrDefault(result.ProcessorUsed, 0) + 1;
                
                if (!_metrics.ProcessorPerformance.ContainsKey(result.ProcessorUsed))
                {
                    _metrics.ProcessorPerformance[result.ProcessorUsed] = result.ExecutionTime;
                }
                else
                {
                    var currentAvg = _metrics.ProcessorPerformance[result.ProcessorUsed];
                    var usage = _metrics.ProcessorUsage[result.ProcessorUsed];
                    _metrics.ProcessorPerformance[result.ProcessorUsed] = TimeSpan.FromTicks(
                        (currentAvg.Ticks * (usage - 1) + result.ExecutionTime.Ticks) / usage);
                }
            }
        }
    }

    private void UpdateMetrics(object? state)
    {
        lock (_metrics)
        {
            _metrics.ConcurrentTasksPeak = Math.Max(_metrics.ConcurrentTasksPeak, _activeTasks.Count);
            
            // In a real implementation, you'd also track memory and CPU usage
            // For now, we'll just update the concurrent tasks peak
        }
    }

    private void OnTaskProgress(TaskProgressEventArgs args)
    {
        TaskProgress?.Invoke(this, args);
    }

    private void OnTaskCompleted(TaskCompletedEventArgs args)
    {
        TaskCompleted?.Invoke(this, args);
    }

    public void Dispose()
    {
        _resourceSemaphore?.Dispose();
        _metricsTimer?.Dispose();
    }
}

internal class TaskExecutionState
{
    public string Id { get; set; } = string.Empty;
    public TaskStatus Status { get; set; }
    public DateTime StartTime { get; set; }
    public DateTime LastUpdate { get; set; } = DateTime.UtcNow;
    public string LastMessage { get; set; } = string.Empty;
    public CancellationToken CancellationToken { get; set; }
    public CancellationTokenSource? CancellationTokenSource { get; set; }
}