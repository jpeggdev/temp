using Microsoft.Extensions.Logging;
using Moq;
using Xunit;
using HeyDav.Application.TaskProcessing;

namespace HeyDav.Application.Tests.TaskProcessing;

public class TaskAnalyzerTests
{
    private readonly Mock<ILogger<TaskAnalyzer>> _mockLogger;
    private readonly TaskAnalyzer _taskAnalyzer;

    public TaskAnalyzerTests()
    {
        _mockLogger = new Mock<ILogger<TaskAnalyzer>>();
        _taskAnalyzer = new TaskAnalyzer(_mockLogger.Object);
    }

    [Fact]
    public async Task AnalyzeTaskAsync_SimpleCommand_ReturnsSimpleComplexity()
    {
        // Arrange
        var command = "create a new todo item";

        // Act
        var result = await _taskAnalyzer.AnalyzeTaskAsync(command);

        // Assert
        Assert.Equal(command, result.OriginalCommand);
        Assert.Equal(TaskIntent.Create, result.Intent);
        Assert.Equal(TaskComplexity.Simple, result.Complexity);
        Assert.Single(result.Subtasks);
        Assert.True(result.ConfidenceScore > 0);
    }

    [Fact]
    public async Task AnalyzeTaskAsync_ComplexCommand_ReturnsComplexComplexity()
    {
        // Arrange
        var command = "create a new todo item and then schedule it for tomorrow and also send me a reminder";

        // Act
        var result = await _taskAnalyzer.AnalyzeTaskAsync(command);

        // Assert
        Assert.Equal(command, result.OriginalCommand);
        Assert.True(result.Complexity >= TaskComplexity.Moderate);
        Assert.True(result.Subtasks.Count > 1);
        Assert.Contains("TodoAgent", result.RequiredAgents);
    }

    [Theory]
    [InlineData("create", TaskIntent.Create)]
    [InlineData("add", TaskIntent.Create)]
    [InlineData("read", TaskIntent.Read)]
    [InlineData("get", TaskIntent.Read)]
    [InlineData("update", TaskIntent.Update)]
    [InlineData("edit", TaskIntent.Update)]
    [InlineData("delete", TaskIntent.Delete)]
    [InlineData("remove", TaskIntent.Delete)]
    [InlineData("search", TaskIntent.Search)]
    [InlineData("find", TaskIntent.Search)]
    public async Task EstimateComplexityAsync_SimpleCommands_ReturnsCorrectIntent(string verb, TaskIntent expectedIntent)
    {
        // Arrange
        var command = $"{verb} something";

        // Act
        var result = await _taskAnalyzer.AnalyzeTaskAsync(command);

        // Assert
        Assert.Equal(expectedIntent, result.Intent);
    }

    [Theory]
    [InlineData("hello", TaskComplexity.Simple)]
    [InlineData("create a todo and send email", TaskComplexity.Moderate)]
    [InlineData("create multiple todos and schedule them and analyze the data", TaskComplexity.Complex)]
    [InlineData("create todos and schedule them and send emails and analyze trends and generate reports", TaskComplexity.Advanced)]
    public async Task EstimateComplexityAsync_VariousCommands_ReturnsCorrectComplexity(string command, TaskComplexity expectedComplexity)
    {
        // Act
        var complexity = await _taskAnalyzer.EstimateComplexityAsync(command);

        // Assert
        Assert.Equal(expectedComplexity, complexity);
    }

    [Fact]
    public async Task BreakdownComplexTaskAsync_SimpleTask_ReturnsSingleSubtask()
    {
        // Arrange
        var command = "create a todo";

        // Act
        var subtasks = await _taskAnalyzer.BreakdownComplexTaskAsync(command);

        // Assert
        Assert.Single(subtasks);
        Assert.Equal(command, subtasks[0].Description);
        Assert.Equal(TaskIntent.Create, subtasks[0].Intent);
    }

    [Fact]
    public async Task BreakdownComplexTaskAsync_CommandWithAnd_ReturnsMultipleSubtasks()
    {
        // Arrange
        var command = "create a todo and send an email";

        // Act
        var subtasks = await _taskAnalyzer.BreakdownComplexTaskAsync(command);

        // Assert
        Assert.True(subtasks.Count >= 2);
        Assert.Contains(subtasks, t => t.Description.Contains("todo"));
        Assert.Contains(subtasks, t => t.Description.Contains("email"));
    }

    [Fact]
    public async Task IdentifyRequiredAgentsAsync_TodoCommand_ReturnsTodoAgent()
    {
        // Arrange
        var command = "create a new todo item";

        // Act
        var agents = await _taskAnalyzer.IdentifyRequiredAgentsAsync(command);

        // Assert
        Assert.Contains("TodoAgent", agents);
    }

    [Fact]
    public async Task IdentifyRequiredAgentsAsync_EmailCommand_ReturnsEmailAgent()
    {
        // Arrange
        var command = "send an email notification";

        // Act
        var agents = await _taskAnalyzer.IdentifyRequiredAgentsAsync(command);

        // Assert
        Assert.Contains("EmailAgent", agents);
    }

    [Fact]
    public async Task AnalyzeDependenciesAsync_TasksWithDependencies_ReturnsCorrectGraph()
    {
        // Arrange
        var tasks = new List<TaskBreakdown>
        {
            new() { Id = "1", Description = "create data", Intent = TaskIntent.Create, Priority = 3 },
            new() { Id = "2", Description = "process data after creation", Intent = TaskIntent.Update, Priority = 2 },
            new() { Id = "3", Description = "delete old data", Intent = TaskIntent.Delete, Priority = 1 }
        };

        // Act
        var graph = await _taskAnalyzer.AnalyzeDependenciesAsync(tasks);

        // Assert
        Assert.NotNull(graph);
        Assert.NotEmpty(graph.ExecutionOrder);
        Assert.False(graph.HasCircularDependency);
    }

    [Fact]
    public async Task SuggestExecutionStrategyAsync_SimpleAnalysis_ReturnsValidStrategy()
    {
        // Arrange
        var analysis = new TaskAnalysisResult
        {
            OriginalCommand = "test command",
            Intent = TaskIntent.Create,
            Complexity = TaskComplexity.Simple,
            Subtasks = new List<TaskBreakdown>
            {
                new() { Id = "1", Description = "test", CanRunInParallel = true }
            },
            Dependencies = new TaskDependencyGraph { ParallelGroups = new List<List<string>> { new() { "1" } } },
            EstimatedDuration = TimeSpan.FromMinutes(2)
        };

        // Act
        var strategy = await _taskAnalyzer.SuggestExecutionStrategyAsync(analysis);

        // Assert
        Assert.NotNull(strategy);
        Assert.True(strategy.MaxParallelTasks > 0);
        Assert.True(strategy.TimeoutPerTask > TimeSpan.Zero);
        Assert.False(strategy.RequiresHumanApproval); // Simple task shouldn't require approval
    }

    [Fact]
    public async Task SuggestExecutionStrategyAsync_ComplexAnalysis_RequiresApproval()
    {
        // Arrange
        var analysis = new TaskAnalysisResult
        {
            OriginalCommand = "complex command",
            Intent = TaskIntent.Delete,
            Complexity = TaskComplexity.Advanced,
            Subtasks = new List<TaskBreakdown>(),
            Dependencies = new TaskDependencyGraph(),
            EstimatedDuration = TimeSpan.FromHours(1)
        };

        // Act
        var strategy = await _taskAnalyzer.SuggestExecutionStrategyAsync(analysis);

        // Assert
        Assert.True(strategy.RequiresHumanApproval);
        Assert.Contains("ConfirmDestructiveOperations", strategy.PreExecutionChecks);
    }

    [Fact]
    public async Task AnalyzeTaskAsync_WithParameters_ExtractsParameters()
    {
        // Arrange
        var command = "create \"My Important Task\" with priority 5 and due date 2024-12-31";

        // Act
        var result = await _taskAnalyzer.AnalyzeTaskAsync(command);

        // Assert
        Assert.NotEmpty(result.ExtractedParameters);
        Assert.True(result.ExtractedParameters.Count > 0);
        Assert.True(result.ConfidenceScore > 0.5f);
    }

    [Fact]
    public async Task AnalyzeTaskAsync_WithEntities_DetectsEntities()
    {
        // Arrange
        var command = "send email to John Smith at john@example.com";

        // Act
        var result = await _taskAnalyzer.AnalyzeTaskAsync(command);

        // Assert
        Assert.NotEmpty(result.DetectedEntities);
        Assert.Contains(result.DetectedEntities, e => e.Contains("John") || e.Contains("Smith"));
        Assert.Contains(result.DetectedEntities, e => e.Contains("@"));
    }

    [Fact]
    public async Task AnalyzeTaskAsync_EmptyCommand_HandlesGracefully()
    {
        // Arrange
        var command = "";

        // Act
        var result = await _taskAnalyzer.AnalyzeTaskAsync(command);

        // Assert
        Assert.Equal(TaskIntent.Unknown, result.Intent);
        Assert.Equal(TaskComplexity.Simple, result.Complexity);
        Assert.Empty(result.Subtasks);
    }

    [Fact]
    public async Task AnalyzeTaskAsync_NullContext_HandlesGracefully()
    {
        // Arrange
        var command = "test command";

        // Act
        var result = await _taskAnalyzer.AnalyzeTaskAsync(command, null);

        // Assert
        Assert.NotNull(result);
        Assert.Equal(command, result.OriginalCommand);
    }
}