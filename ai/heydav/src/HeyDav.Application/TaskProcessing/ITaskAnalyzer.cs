using HeyDav.Application.CommandProcessing;

namespace HeyDav.Application.TaskProcessing;

public interface ITaskAnalyzer
{
    Task<TaskAnalysisResult> AnalyzeTaskAsync(string command, Dictionary<string, object>? context = null);
    Task<List<TaskBreakdown>> BreakdownComplexTaskAsync(string command, Dictionary<string, object>? context = null);
    Task<TaskComplexity> EstimateComplexityAsync(string command);
    Task<List<string>> IdentifyRequiredAgentsAsync(string command);
    Task<TaskDependencyGraph> AnalyzeDependenciesAsync(List<TaskBreakdown> tasks);
    Task<ExecutionStrategy> SuggestExecutionStrategyAsync(TaskAnalysisResult analysis);
}

public class TaskAnalysisResult
{
    public string OriginalCommand { get; set; } = string.Empty;
    public TaskIntent Intent { get; set; }
    public TaskComplexity Complexity { get; set; }
    public List<string> RequiredCapabilities { get; set; } = new();
    public List<string> RequiredAgents { get; set; } = new();
    public List<TaskBreakdown> Subtasks { get; set; } = new();
    public TaskDependencyGraph Dependencies { get; set; } = new();
    public ExecutionStrategy SuggestedStrategy { get; set; } = new();
    public Dictionary<string, object> ExtractedParameters { get; set; } = new();
    public List<string> DetectedEntities { get; set; } = new();
    public float ConfidenceScore { get; set; }
    public TimeSpan EstimatedDuration { get; set; }
}

public class TaskBreakdown
{
    public string Id { get; set; } = Guid.NewGuid().ToString();
    public string Description { get; set; } = string.Empty;
    public TaskIntent Intent { get; set; }
    public TaskComplexity Complexity { get; set; }
    public List<string> RequiredAgents { get; set; } = new();
    public List<string> Dependencies { get; set; } = new(); // Other task IDs this depends on
    public Dictionary<string, object> Parameters { get; set; } = new();
    public int Priority { get; set; } = 1;
    public TimeSpan EstimatedDuration { get; set; }
    public bool CanRunInParallel { get; set; } = true;
}

public class TaskDependencyGraph
{
    public Dictionary<string, List<string>> Dependencies { get; set; } = new();
    public List<string> ExecutionOrder { get; set; } = new();
    public List<List<string>> ParallelGroups { get; set; } = new();
    public bool HasCircularDependency { get; set; }
}

public class ExecutionStrategy
{
    public ExecutionMode Mode { get; set; }
    public int MaxParallelTasks { get; set; } = 3;
    public TimeSpan TimeoutPerTask { get; set; } = TimeSpan.FromMinutes(5);
    public bool RequiresHumanApproval { get; set; }
    public List<string> PreExecutionChecks { get; set; } = new();
    public Dictionary<string, object> ResourceLimits { get; set; } = new();
    public ExecutionConfiguration? Configuration { get; set; }
}

public class ExecutionConfiguration
{
    public TimeSpan DefaultTimeout { get; set; } = TimeSpan.FromMinutes(5);
    public int MaxRetries { get; set; } = 3;
    public TimeSpan RetryDelay { get; set; } = TimeSpan.FromSeconds(5);
    public bool FailFast { get; set; } = false;
    public bool LogDetailedMetrics { get; set; } = true;
    public Dictionary<string, object> CustomSettings { get; set; } = new();
}

public enum TaskIntent
{
    Unknown,
    Create,
    Read,
    Update,
    Delete,
    Search,
    Analyze,
    Transform,
    Schedule,
    Notify,
    Execute,
    Monitor,
    Backup,
    Sync,
    Process,
    Generate,
    Validate,
    Aggregate,
    Filter,
    Sort,
    Export,
    Import
}

public enum TaskComplexity
{
    Simple,    // Single step, single agent
    Moderate,  // Multiple steps, single agent or simple multi-agent
    Complex,   // Multiple steps, multiple agents, some dependencies
    Advanced   // Complex dependencies, resource coordination, long-running
}

public enum ExecutionMode
{
    Sequential,
    Parallel,
    Hybrid,
    Batch,
    Streaming
}