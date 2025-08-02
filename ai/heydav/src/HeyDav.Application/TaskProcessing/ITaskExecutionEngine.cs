using HeyDav.Application.CommandProcessing;

namespace HeyDav.Application.TaskProcessing;

public interface ITaskExecutionEngine
{
    Task<TaskExecutionResult> ExecuteTaskAsync(TaskExecutionRequest request);
    Task<TaskExecutionResult> ExecuteTasksAsync(List<TaskExecutionRequest> requests, ExecutionStrategy strategy);
    Task<TaskStatus> GetTaskStatusAsync(string taskId);
    Task<List<TaskStatus>> GetAllTaskStatusesAsync();
    Task<bool> CancelTaskAsync(string taskId);
    Task<TaskExecutionResult> RetryTaskAsync(string taskId);
    Task<ExecutionMetrics> GetExecutionMetricsAsync();
    event EventHandler<TaskProgressEventArgs> TaskProgress;
    event EventHandler<TaskCompletedEventArgs> TaskCompleted;
}

public class TaskExecutionRequest
{
    public string Id { get; set; } = Guid.NewGuid().ToString();
    public TaskBreakdown Task { get; set; } = new();
    public CommandRequest CommandRequest { get; set; } = new() { Command = "", Source = "" };
    public ExecutionContext Context { get; set; } = new();
    public Dictionary<string, object> Resources { get; set; } = new();
    public CancellationToken CancellationToken { get; set; }
}

public class TaskExecutionResult
{
    public string TaskId { get; set; } = string.Empty;
    public bool Success { get; set; }
    public string Message { get; set; } = string.Empty;
    public object? Data { get; set; }
    public List<CommandResult> SubResults { get; set; } = new();
    public TaskStatus Status { get; set; }
    public TimeSpan ExecutionTime { get; set; }
    public DateTime StartTime { get; set; }
    public DateTime? EndTime { get; set; }
    public string ProcessorUsed { get; set; } = string.Empty;
    public Dictionary<string, object> Metadata { get; set; } = new();
    public List<string> Errors { get; set; } = new();
    public List<string> Warnings { get; set; } = new();
    public ExecutionMetrics Metrics { get; set; } = new();
}

public class ExecutionContext
{
    public string UserId { get; set; } = string.Empty;
    public string SessionId { get; set; } = string.Empty;
    public Dictionary<string, object> SharedData { get; set; } = new();
    public ResourcePool Resources { get; set; } = new();
    public ExecutionConfiguration Configuration { get; set; } = new();
}

public class ResourcePool
{
    public int MaxConcurrentTasks { get; set; } = 5;
    public int MaxMemoryMB { get; set; } = 1024;
    public int MaxCpuPercent { get; set; } = 80;
    public Dictionary<string, object> AvailableResources { get; set; } = new();
    public Dictionary<string, object> AllocatedResources { get; set; } = new();
}


public class ExecutionMetrics
{
    public int TotalTasksExecuted { get; set; }
    public int SuccessfulTasks { get; set; }
    public int FailedTasks { get; set; }
    public int CancelledTasks { get; set; }
    public TimeSpan TotalExecutionTime { get; set; }
    public TimeSpan AverageExecutionTime { get; set; }
    public Dictionary<string, int> ProcessorUsage { get; set; } = new();
    public Dictionary<string, TimeSpan> ProcessorPerformance { get; set; } = new();
    public int ConcurrentTasksPeak { get; set; }
    public double MemoryUsagePeak { get; set; }
    public double CpuUsagePeak { get; set; }
}

public enum TaskStatus
{
    Pending,
    Queued,
    Running,
    Completed,
    Failed,
    Cancelled,
    Retrying,
    Paused
}

public class TaskProgressEventArgs : EventArgs
{
    public string TaskId { get; set; } = string.Empty;
    public TaskStatus Status { get; set; }
    public float ProgressPercentage { get; set; }
    public string Message { get; set; } = string.Empty;
    public Dictionary<string, object> Data { get; set; } = new();
}

public class TaskCompletedEventArgs : EventArgs
{
    public string TaskId { get; set; } = string.Empty;
    public TaskExecutionResult Result { get; set; } = new();
}