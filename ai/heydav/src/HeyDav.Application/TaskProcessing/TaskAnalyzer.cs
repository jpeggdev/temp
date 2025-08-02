using Microsoft.Extensions.Logging;
using System.Text.RegularExpressions;

namespace HeyDav.Application.TaskProcessing;

public class TaskAnalyzer : ITaskAnalyzer
{
    private readonly ILogger<TaskAnalyzer> _logger;
    private readonly Dictionary<string, TaskIntent> _intentPatterns;
    private readonly Dictionary<string, List<string>> _agentCapabilities;

    public TaskAnalyzer(ILogger<TaskAnalyzer> logger)
    {
        _logger = logger;
        _intentPatterns = InitializeIntentPatterns();
        _agentCapabilities = InitializeAgentCapabilities();
    }

    public async Task<TaskAnalysisResult> AnalyzeTaskAsync(string command, Dictionary<string, object>? context = null)
    {
        _logger.LogDebug("Analyzing task: {Command}", command);

        var result = new TaskAnalysisResult
        {
            OriginalCommand = command,
            Intent = await IdentifyIntentAsync(command),
            Complexity = await EstimateComplexityAsync(command),
            ExtractedParameters = ExtractParameters(command),
            DetectedEntities = ExtractEntities(command)
        };

        result.RequiredAgents = await IdentifyRequiredAgentsAsync(command);
        result.RequiredCapabilities = DetermineRequiredCapabilities(result.Intent, result.DetectedEntities);
        result.Subtasks = await BreakdownComplexTaskAsync(command, context);
        result.Dependencies = await AnalyzeDependenciesAsync(result.Subtasks);
        result.SuggestedStrategy = await SuggestExecutionStrategyAsync(result);
        result.ConfidenceScore = CalculateConfidenceScore(result);
        result.EstimatedDuration = EstimateDuration(result);

        _logger.LogDebug("Task analysis completed. Intent: {Intent}, Complexity: {Complexity}, Subtasks: {SubtaskCount}",
            result.Intent, result.Complexity, result.Subtasks.Count);

        return result;
    }

    public async Task<List<TaskBreakdown>> BreakdownComplexTaskAsync(string command, Dictionary<string, object>? context = null)
    {
        var tasks = new List<TaskBreakdown>();
        var intent = await IdentifyIntentAsync(command);
        var complexity = await EstimateComplexityAsync(command);

        // Simple tasks don't need breakdown
        if (complexity == TaskComplexity.Simple)
        {
            tasks.Add(new TaskBreakdown
            {
                Description = command,
                Intent = intent,
                Complexity = complexity,
                RequiredAgents = await IdentifyRequiredAgentsAsync(command)
            });
            return tasks;
        }

        // Pattern-based task breakdown
        tasks.AddRange(await BreakdownByPatterns(command, intent));

        // If no patterns matched, use generic breakdown
        if (tasks.Count == 0)
        {
            tasks.AddRange(await GenericTaskBreakdown(command, intent));
        }

        // Assign priorities and estimate durations
        for (int i = 0; i < tasks.Count; i++)
        {
            tasks[i].Priority = tasks.Count - i; // Earlier tasks have higher priority
            tasks[i].EstimatedDuration = EstimateSubtaskDuration(tasks[i]);
            tasks[i].RequiredAgents = await IdentifyRequiredAgentsAsync(tasks[i].Description);
        }

        return tasks;
    }

    public async Task<TaskComplexity> EstimateComplexityAsync(string command)
    {
        var score = 0;
        var commandLower = command.ToLowerInvariant();

        // Check for complexity indicators
        var complexityIndicators = new Dictionary<string, int>
        {
            { "and", 1 }, { "then", 1 }, { "after", 1 }, { "before", 1 },
            { "also", 1 }, { "plus", 1 }, { "additionally", 1 },
            { "create", 1 }, { "update", 1 }, { "delete", 1 }, { "modify", 1 },
            { "analyze", 2 }, { "process", 2 }, { "generate", 2 }, { "transform", 2 },
            { "schedule", 2 }, { "optimize", 3 }, { "integrate", 3 }, { "synchronize", 3 },
            { "all", 1 }, { "every", 1 }, { "multiple", 2 }, { "batch", 2 }
        };

        foreach (var indicator in complexityIndicators)
        {
            if (commandLower.Contains(indicator.Key))
            {
                score += indicator.Value;
            }
        }

        // Check for multiple entities
        var entities = ExtractEntities(command);
        if (entities.Count > 3) score += 2;
        else if (entities.Count > 1) score += 1;

        // Check command length (longer commands tend to be more complex)
        if (command.Length > 100) score += 2;
        else if (command.Length > 50) score += 1;

        // Check for conditional logic
        if (Regex.IsMatch(commandLower, @"\b(if|when|unless|while|until)\b"))
        {
            score += 2;
        }

        return score switch
        {
            <= 2 => TaskComplexity.Simple,
            <= 5 => TaskComplexity.Moderate,
            <= 8 => TaskComplexity.Complex,
            _ => TaskComplexity.Advanced
        };
    }

    public async Task<List<string>> IdentifyRequiredAgentsAsync(string command)
    {
        var requiredAgents = new List<string>();
        var commandLower = command.ToLowerInvariant();

        foreach (var agentCapability in _agentCapabilities)
        {
            foreach (var capability in agentCapability.Value)
            {
                if (commandLower.Contains(capability.ToLowerInvariant()))
                {
                    if (!requiredAgents.Contains(agentCapability.Key))
                    {
                        requiredAgents.Add(agentCapability.Key);
                    }
                }
            }
        }

        return requiredAgents;
    }

    public async Task<TaskDependencyGraph> AnalyzeDependenciesAsync(List<TaskBreakdown> tasks)
    {
        var graph = new TaskDependencyGraph();
        
        // Analyze dependencies based on task descriptions and intents
        foreach (var task in tasks)
        {
            var dependencies = new List<string>();
            
            // Look for explicit dependency keywords
            if (task.Description.ToLowerInvariant().Contains("after") ||
                task.Description.ToLowerInvariant().Contains("then") ||
                task.Description.ToLowerInvariant().Contains("once"))
            {
                // Find tasks that this one depends on
                var precedingTasks = tasks.Where(t => t.Priority > task.Priority).Select(t => t.Id).ToList();
                dependencies.AddRange(precedingTasks);
            }

            // Intent-based dependencies
            if (task.Intent == TaskIntent.Update || task.Intent == TaskIntent.Delete)
            {
                var createTasks = tasks.Where(t => t.Intent == TaskIntent.Create).Select(t => t.Id).ToList();
                dependencies.AddRange(createTasks);
            }

            graph.Dependencies[task.Id] = dependencies;
        }

        // Calculate execution order
        graph.ExecutionOrder = TopologicalSort(tasks, graph.Dependencies);
        graph.ParallelGroups = IdentifyParallelGroups(tasks, graph.Dependencies);
        graph.HasCircularDependency = DetectCircularDependencies(graph.Dependencies);

        return graph;
    }

    public async Task<ExecutionStrategy> SuggestExecutionStrategyAsync(TaskAnalysisResult analysis)
    {
        var strategy = new ExecutionStrategy();

        // Determine execution mode based on dependencies and complexity
        if (analysis.Dependencies.ParallelGroups.Count > 1)
        {
            strategy.Mode = ExecutionMode.Hybrid;
        }
        else if (analysis.Subtasks.All(t => t.CanRunInParallel) && analysis.Subtasks.Count > 1)
        {
            strategy.Mode = ExecutionMode.Parallel;
        }
        else
        {
            strategy.Mode = ExecutionMode.Sequential;
        }

        // Set parallel task limits based on complexity
        strategy.MaxParallelTasks = analysis.Complexity switch
        {
            TaskComplexity.Simple => 1,
            TaskComplexity.Moderate => 2,
            TaskComplexity.Complex => 3,
            TaskComplexity.Advanced => 5,
            _ => 3
        };

        // Set timeouts based on estimated duration
        strategy.TimeoutPerTask = analysis.EstimatedDuration.TotalMinutes switch
        {
            < 1 => TimeSpan.FromMinutes(2),
            < 5 => TimeSpan.FromMinutes(10),
            < 15 => TimeSpan.FromMinutes(30),
            _ => TimeSpan.FromHours(1)
        };

        // Require approval for complex or long-running tasks
        strategy.RequiresHumanApproval = analysis.Complexity == TaskComplexity.Advanced ||
                                       analysis.EstimatedDuration > TimeSpan.FromMinutes(30);

        // Add pre-execution checks
        if (analysis.RequiredAgents.Count > 1)
        {
            strategy.PreExecutionChecks.Add("VerifyAgentAvailability");
        }
        
        if (analysis.Subtasks.Any(t => t.Intent == TaskIntent.Delete))
        {
            strategy.PreExecutionChecks.Add("ConfirmDestructiveOperations");
        }

        return strategy;
    }

    private async Task<TaskIntent> IdentifyIntentAsync(string command)
    {
        var commandLower = command.ToLowerInvariant();

        foreach (var pattern in _intentPatterns)
        {
            if (commandLower.Contains(pattern.Key))
            {
                return pattern.Value;
            }
        }

        return TaskIntent.Unknown;
    }

    private Dictionary<string, TaskIntent> InitializeIntentPatterns()
    {
        return new Dictionary<string, TaskIntent>
        {
            { "create", TaskIntent.Create }, { "add", TaskIntent.Create }, { "new", TaskIntent.Create },
            { "make", TaskIntent.Create }, { "build", TaskIntent.Create }, { "generate", TaskIntent.Generate },
            { "read", TaskIntent.Read }, { "get", TaskIntent.Read }, { "show", TaskIntent.Read },
            { "view", TaskIntent.Read }, { "list", TaskIntent.Read }, { "display", TaskIntent.Read },
            { "update", TaskIntent.Update }, { "edit", TaskIntent.Update }, { "modify", TaskIntent.Update },
            { "change", TaskIntent.Update }, { "set", TaskIntent.Update },
            { "delete", TaskIntent.Delete }, { "remove", TaskIntent.Delete }, { "drop", TaskIntent.Delete },
            { "search", TaskIntent.Search }, { "find", TaskIntent.Search }, { "query", TaskIntent.Search },
            { "analyze", TaskIntent.Analyze }, { "process", TaskIntent.Process }, { "transform", TaskIntent.Transform },
            { "schedule", TaskIntent.Schedule }, { "plan", TaskIntent.Schedule },
            { "notify", TaskIntent.Notify }, { "alert", TaskIntent.Notify }, { "remind", TaskIntent.Notify },
            { "execute", TaskIntent.Execute }, { "run", TaskIntent.Execute }, { "perform", TaskIntent.Execute },
            { "monitor", TaskIntent.Monitor }, { "track", TaskIntent.Monitor }, { "watch", TaskIntent.Monitor },
            { "backup", TaskIntent.Backup }, { "save", TaskIntent.Backup },
            { "sync", TaskIntent.Sync }, { "synchronize", TaskIntent.Sync },
            { "validate", TaskIntent.Validate }, { "verify", TaskIntent.Validate }, { "check", TaskIntent.Validate },
            { "aggregate", TaskIntent.Aggregate }, { "combine", TaskIntent.Aggregate }, { "merge", TaskIntent.Aggregate },
            { "filter", TaskIntent.Filter }, { "sort", TaskIntent.Sort }, { "order", TaskIntent.Sort },
            { "export", TaskIntent.Export }, { "import", TaskIntent.Import }
        };
    }

    private Dictionary<string, List<string>> InitializeAgentCapabilities()
    {
        return new Dictionary<string, List<string>>
        {
            { "TodoAgent", new List<string> { "todo", "task", "reminder", "schedule", "deadline", "priority" } },
            { "GoalAgent", new List<string> { "goal", "objective", "milestone", "target", "achievement", "progress" } },
            { "NewsAgent", new List<string> { "news", "article", "feed", "update", "information", "current" } },
            { "EmailAgent", new List<string> { "email", "message", "send", "inbox", "notification" } },
            { "AnalyticsAgent", new List<string> { "analyze", "report", "metrics", "statistics", "data", "trend" } },
            { "ScheduleAgent", new List<string> { "calendar", "appointment", "meeting", "schedule", "time", "date" } },
            { "FileAgent", new List<string> { "file", "document", "folder", "directory", "save", "load", "export", "import" } },
            { "SystemAgent", new List<string> { "system", "status", "health", "performance", "resource", "monitor" } }
        };
    }

    private Dictionary<string, object> ExtractParameters(string command)
    {
        var parameters = new Dictionary<string, object>();
        
        // Extract quoted strings
        var quotedStrings = Regex.Matches(command, @"""([^""]*)""");
        for (int i = 0; i < quotedStrings.Count; i++)
        {
            parameters[$"quoted_{i}"] = quotedStrings[i].Groups[1].Value;
        }

        // Extract numbers
        var numbers = Regex.Matches(command, @"\b\d+(?:\.\d+)?\b");
        for (int i = 0; i < numbers.Count; i++)
        {
            if (double.TryParse(numbers[i].Value, out var number))
            {
                parameters[$"number_{i}"] = number;
            }
        }

        // Extract dates (simple patterns)
        var datePatterns = new[]
        {
            @"\b\d{1,2}\/\d{1,2}\/\d{4}\b",
            @"\b\d{4}-\d{2}-\d{2}\b",
            @"\b(today|tomorrow|yesterday)\b"
        };

        foreach (var pattern in datePatterns)
        {
            var matches = Regex.Matches(command.ToLowerInvariant(), pattern);
            for (int i = 0; i < matches.Count; i++)
            {
                parameters[$"date_{i}"] = matches[i].Value;
            }
        }

        return parameters;
    }

    private List<string> ExtractEntities(string command)
    {
        var entities = new List<string>();
        
        // Extract capitalized words (potential entity names)
        var capitalizedWords = Regex.Matches(command, @"\b[A-Z][a-z]+\b");
        entities.AddRange(capitalizedWords.Cast<Match>().Select(m => m.Value));

        // Extract quoted strings as entities
        var quotedStrings = Regex.Matches(command, @"""([^""]*)""");
        entities.AddRange(quotedStrings.Cast<Match>().Select(m => m.Groups[1].Value));

        // Extract email addresses
        var emails = Regex.Matches(command, @"\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b");
        entities.AddRange(emails.Cast<Match>().Select(m => m.Value));

        return entities.Distinct().ToList();
    }

    private List<string> DetermineRequiredCapabilities(TaskIntent intent, List<string> entities)
    {
        var capabilities = new List<string>();

        capabilities.Add(intent.ToString());

        // Add capabilities based on detected entities
        foreach (var entity in entities)
        {
            if (entity.Contains("@"))
            {
                capabilities.Add("EmailHandling");
            }
            if (Regex.IsMatch(entity, @"\d{4}-\d{2}-\d{2}"))
            {
                capabilities.Add("DateHandling");
            }
        }

        return capabilities;
    }

    private async Task<List<TaskBreakdown>> BreakdownByPatterns(string command, TaskIntent intent)
    {
        var tasks = new List<TaskBreakdown>();
        
        // Pattern for "do X and Y and Z"
        if (command.Contains(" and "))
        {
            var parts = command.Split(new[] { " and " }, StringSplitOptions.RemoveEmptyEntries);
            foreach (var part in parts)
            {
                tasks.Add(new TaskBreakdown
                {
                    Description = part.Trim(),
                    Intent = await IdentifyIntentAsync(part),
                    Complexity = await EstimateComplexityAsync(part)
                });
            }
        }

        // Pattern for "first X, then Y, finally Z"
        var sequentialPattern = @"\b(first|then|next|after|finally)\s+(.+?)(?=\s+(?:first|then|next|after|finally|$))";
        var sequentialMatches = Regex.Matches(command, sequentialPattern, RegexOptions.IgnoreCase);
        
        if (sequentialMatches.Count > 0)
        {
            foreach (Match match in sequentialMatches)
            {
                var taskDescription = match.Groups[2].Value.Trim();
                if (!string.IsNullOrEmpty(taskDescription))
                {
                    tasks.Add(new TaskBreakdown
                    {
                        Description = taskDescription,
                        Intent = await IdentifyIntentAsync(taskDescription),
                        Complexity = await EstimateComplexityAsync(taskDescription),
                        CanRunInParallel = false
                    });
                }
            }
        }

        return tasks;
    }

    private async Task<List<TaskBreakdown>> GenericTaskBreakdown(string command, TaskIntent intent)
    {
        var tasks = new List<TaskBreakdown>();

        // For complex tasks, create generic subtasks based on intent
        switch (intent)
        {
            case TaskIntent.Create:
                tasks.Add(new TaskBreakdown { Description = "Validate input parameters", Intent = TaskIntent.Validate });
                tasks.Add(new TaskBreakdown { Description = command, Intent = intent });
                tasks.Add(new TaskBreakdown { Description = "Verify creation result", Intent = TaskIntent.Validate });
                break;

            case TaskIntent.Update:
                tasks.Add(new TaskBreakdown { Description = "Fetch current state", Intent = TaskIntent.Read });
                tasks.Add(new TaskBreakdown { Description = command, Intent = intent });
                tasks.Add(new TaskBreakdown { Description = "Verify update result", Intent = TaskIntent.Validate });
                break;

            case TaskIntent.Delete:
                tasks.Add(new TaskBreakdown { Description = "Verify item exists", Intent = TaskIntent.Read });
                tasks.Add(new TaskBreakdown { Description = "Create backup if needed", Intent = TaskIntent.Backup });
                tasks.Add(new TaskBreakdown { Description = command, Intent = intent });
                break;

            default:
                tasks.Add(new TaskBreakdown { Description = command, Intent = intent });
                break;
        }

        return tasks;
    }

    private TimeSpan EstimateSubtaskDuration(TaskBreakdown task)
    {
        return task.Complexity switch
        {
            TaskComplexity.Simple => TimeSpan.FromSeconds(30),
            TaskComplexity.Moderate => TimeSpan.FromMinutes(2),
            TaskComplexity.Complex => TimeSpan.FromMinutes(5),
            TaskComplexity.Advanced => TimeSpan.FromMinutes(15),
            _ => TimeSpan.FromMinutes(1)
        };
    }

    private float CalculateConfidenceScore(TaskAnalysisResult result)
    {
        var score = 0.5f; // Base confidence

        // Increase confidence if intent was identified
        if (result.Intent != TaskIntent.Unknown) score += 0.2f;

        // Increase confidence based on number of detected entities
        score += Math.Min(result.DetectedEntities.Count * 0.1f, 0.2f);

        // Increase confidence if parameters were extracted
        score += Math.Min(result.ExtractedParameters.Count * 0.05f, 0.1f);

        return Math.Min(score, 1.0f);
    }

    private TimeSpan EstimateDuration(TaskAnalysisResult result)
    {
        var baseDuration = result.Complexity switch
        {
            TaskComplexity.Simple => TimeSpan.FromMinutes(1),
            TaskComplexity.Moderate => TimeSpan.FromMinutes(5),
            TaskComplexity.Complex => TimeSpan.FromMinutes(15),
            TaskComplexity.Advanced => TimeSpan.FromMinutes(30),
            _ => TimeSpan.FromMinutes(5)
        };

        // Add time for each subtask
        var subtaskTime = result.Subtasks.Sum(t => t.EstimatedDuration.TotalMilliseconds);
        
        return baseDuration.Add(TimeSpan.FromMilliseconds(subtaskTime));
    }

    private List<string> TopologicalSort(List<TaskBreakdown> tasks, Dictionary<string, List<string>> dependencies)
    {
        var result = new List<string>();
        var visited = new HashSet<string>();
        var temp = new HashSet<string>();

        foreach (var task in tasks)
        {
            if (!visited.Contains(task.Id))
            {
                TopologicalSortUtil(task.Id, dependencies, visited, temp, result);
            }
        }

        result.Reverse();
        return result;
    }

    private void TopologicalSortUtil(string taskId, Dictionary<string, List<string>> dependencies, 
        HashSet<string> visited, HashSet<string> temp, List<string> result)
    {
        if (temp.Contains(taskId)) return; // Circular dependency detected
        if (visited.Contains(taskId)) return;

        temp.Add(taskId);

        if (dependencies.ContainsKey(taskId))
        {
            foreach (var dependency in dependencies[taskId])
            {
                TopologicalSortUtil(dependency, dependencies, visited, temp, result);
            }
        }

        temp.Remove(taskId);
        visited.Add(taskId);
        result.Add(taskId);
    }

    private List<List<string>> IdentifyParallelGroups(List<TaskBreakdown> tasks, Dictionary<string, List<string>> dependencies)
    {
        var groups = new List<List<string>>();
        var processed = new HashSet<string>();

        foreach (var task in tasks)
        {
            if (processed.Contains(task.Id)) continue;

            var group = new List<string> { task.Id };
            processed.Add(task.Id);

            // Find tasks that can run in parallel with this one
            foreach (var otherTask in tasks)
            {
                if (processed.Contains(otherTask.Id)) continue;
                
                if (CanRunInParallel(task.Id, otherTask.Id, dependencies))
                {
                    group.Add(otherTask.Id);
                    processed.Add(otherTask.Id);
                }
            }

            groups.Add(group);
        }

        return groups;
    }

    private bool CanRunInParallel(string task1, string task2, Dictionary<string, List<string>> dependencies)
    {
        // Tasks can run in parallel if neither depends on the other
        var task1Deps = dependencies.GetValueOrDefault(task1, new List<string>());
        var task2Deps = dependencies.GetValueOrDefault(task2, new List<string>());

        return !task1Deps.Contains(task2) && !task2Deps.Contains(task1);
    }

    private bool DetectCircularDependencies(Dictionary<string, List<string>> dependencies)
    {
        var visited = new HashSet<string>();
        var recursionStack = new HashSet<string>();

        foreach (var taskId in dependencies.Keys)
        {
            if (HasCircularDependency(taskId, dependencies, visited, recursionStack))
            {
                return true;
            }
        }

        return false;
    }

    private bool HasCircularDependency(string taskId, Dictionary<string, List<string>> dependencies, 
        HashSet<string> visited, HashSet<string> recursionStack)
    {
        if (recursionStack.Contains(taskId)) return true;
        if (visited.Contains(taskId)) return false;

        visited.Add(taskId);
        recursionStack.Add(taskId);

        if (dependencies.ContainsKey(taskId))
        {
            foreach (var dependency in dependencies[taskId])
            {
                if (HasCircularDependency(dependency, dependencies, visited, recursionStack))
                {
                    return true;
                }
            }
        }

        recursionStack.Remove(taskId);
        return false;
    }
}