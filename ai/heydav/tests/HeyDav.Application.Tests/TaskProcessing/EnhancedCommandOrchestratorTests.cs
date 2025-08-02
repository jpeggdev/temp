using Microsoft.Extensions.Logging;
using Moq;
using Xunit;
using HeyDav.Application.TaskProcessing;
using HeyDav.Application.CommandProcessing;

namespace HeyDav.Application.Tests.TaskProcessing;

public class EnhancedCommandOrchestratorTests
{
    private readonly Mock<ICommandProcessorFactory> _mockProcessorFactory;
    private readonly Mock<ITaskAnalyzer> _mockTaskAnalyzer;
    private readonly Mock<ITaskExecutionEngine> _mockExecutionEngine;
    private readonly Mock<IPluginManager> _mockPluginManager;
    private readonly Mock<ILogger<EnhancedCommandOrchestrator>> _mockLogger;
    private readonly Mock<ICommandProcessor> _mockProcessor;
    private readonly EnhancedCommandOrchestrator _orchestrator;

    public EnhancedCommandOrchestratorTests()
    {
        _mockProcessorFactory = new Mock<ICommandProcessorFactory>();
        _mockTaskAnalyzer = new Mock<ITaskAnalyzer>();
        _mockExecutionEngine = new Mock<ITaskExecutionEngine>();
        _mockPluginManager = new Mock<IPluginManager>();
        _mockLogger = new Mock<ILogger<EnhancedCommandOrchestrator>>();
        _mockProcessor = new Mock<ICommandProcessor>();

        _orchestrator = new EnhancedCommandOrchestrator(
            _mockProcessorFactory.Object,
            _mockTaskAnalyzer.Object,
            _mockExecutionEngine.Object,
            _mockPluginManager.Object,
            _mockLogger.Object);
    }

    [Fact]
    public async Task ProcessComplexCommandAsync_EmptyCommand_ReturnsFailure()
    {
        // Arrange
        var request = new CommandRequest
        {
            Command = "",
            Source = "test"
        };

        // Act
        var result = await _orchestrator.ProcessComplexCommandAsync(request);

        // Assert
        Assert.False(result.Success);
        Assert.Contains("cannot be empty", result.Message);
    }

    [Fact]
    public async Task ProcessComplexCommandAsync_SimpleTask_UsesOriginalOrchestrator()
    {
        // Arrange
        var request = new CommandRequest
        {
            Command = "simple command",
            Source = "test"
        };

        var analysis = new TaskAnalysisResult
        {
            OriginalCommand = request.Command,
            Intent = TaskIntent.Create,
            Complexity = TaskComplexity.Simple,
            Subtasks = new List<TaskBreakdown>
            {
                new() { Id = "1", Description = "simple task" }
            }
        };

        var commandResult = new CommandResult
        {
            Success = true,
            Message = "Command processed successfully",
            ProcessorUsed = "TestProcessor"
        };

        _mockTaskAnalyzer.Setup(x => x.AnalyzeTaskAsync(request.Command, request.Context))
            .ReturnsAsync(analysis);

        _mockProcessorFactory.Setup(x => x.GetBestProcessor(request.Command))
            .Returns(_mockProcessor.Object);

        _mockProcessor.Setup(x => x.ProcessAsync(It.IsAny<CommandRequest>()))
            .ReturnsAsync(commandResult);

        // Act
        var result = await _orchestrator.ProcessComplexCommandAsync(request);

        // Assert
        Assert.True(result.Success);
        Assert.Equal("Command processed successfully", result.Message);
    }

    [Fact]
    public async Task ProcessComplexCommandAsync_ComplexTask_UsesTaskAnalysis()
    {
        // Arrange
        var request = new CommandRequest
        {
            Command = "complex command with multiple steps",
            Source = "test"
        };

        var analysis = new TaskAnalysisResult
        {
            OriginalCommand = request.Command,
            Intent = TaskIntent.Create,
            Complexity = TaskComplexity.Complex,
            SuggestedStrategy = new ExecutionStrategy
            {
                Mode = ExecutionMode.Sequential,
                RequiresHumanApproval = false,
                PreExecutionChecks = new List<string>()
            },
            Subtasks = new List<TaskBreakdown>
            {
                new() { Id = "1", Description = "step 1" },
                new() { Id = "2", Description = "step 2" }
            }
        };

        var executionResults = new List<TaskExecutionResult>
        {
            new() { TaskId = "1", Success = true, Message = "Step 1 completed" },
            new() { TaskId = "2", Success = true, Message = "Step 2 completed" }
        };

        _mockTaskAnalyzer.Setup(x => x.AnalyzeTaskAsync(request.Command, request.Context))
            .ReturnsAsync(analysis);

        _mockExecutionEngine.Setup(x => x.ExecuteTasksAsync(It.IsAny<List<TaskExecutionRequest>>(), analysis.SuggestedStrategy))
            .ReturnsAsync(new TaskExecutionResult
            {
                Success = true,
                Message = "Batch execution completed successfully",
                SubResults = executionResults.Select(r => new CommandResult
                {
                    Success = r.Success,
                    Message = r.Message
                }).ToList()
            });

        // Mock ExecuteTaskBreakdownAsync to return the expected results
        _mockExecutionEngine.Setup(x => x.ExecuteTaskAsync(It.IsAny<TaskExecutionRequest>()))
            .ReturnsAsync((TaskExecutionRequest req) => executionResults.First(r => r.TaskId == req.Id));

        // Act
        var result = await _orchestrator.ProcessComplexCommandAsync(request);

        // Assert
        Assert.True(result.Success);
        Assert.Contains("Complex command executed successfully", result.Message);
        Assert.Equal("enhanced-orchestrator", result.ProcessorUsed);
    }

    [Fact]
    public async Task ProcessComplexCommandAsync_TaskRequiresApproval_ReturnsFailure()
    {
        // Arrange
        var request = new CommandRequest
        {
            Command = "dangerous command",
            Source = "test"
        };

        var analysis = new TaskAnalysisResult
        {
            OriginalCommand = request.Command,
            Intent = TaskIntent.Delete,
            Complexity = TaskComplexity.Advanced,
            SuggestedStrategy = new ExecutionStrategy
            {
                RequiresHumanApproval = true
            },
            Subtasks = new List<TaskBreakdown>()
        };

        _mockTaskAnalyzer.Setup(x => x.AnalyzeTaskAsync(request.Command, request.Context))
            .ReturnsAsync(analysis);

        // Act
        var result = await _orchestrator.ProcessComplexCommandAsync(request);

        // Assert
        Assert.False(result.Success);
        Assert.Contains("requires human approval", result.Message);
        Assert.Equal(CommandAction.Query, result.Action);
    }

    [Fact]
    public async Task ExecuteTaskBreakdownAsync_SequentialMode_ProcessesInOrder()
    {
        // Arrange
        var tasks = new List<TaskBreakdown>
        {
            new() { Id = "1", Description = "task 1", Priority = 2 },
            new() { Id = "2", Description = "task 2", Priority = 1 }
        };

        var strategy = new ExecutionStrategy
        {
            Mode = ExecutionMode.Sequential,
            MaxParallelTasks = 1
        };

        var executionResults = new List<TaskExecutionResult>
        {
            new() { TaskId = "1", Success = true, Message = "Task 1 completed" },
            new() { TaskId = "2", Success = true, Message = "Task 2 completed" }
        };

        // Mock the execution engine to return results for each task
        _mockExecutionEngine.SetupSequence(x => x.ExecuteTaskAsync(It.IsAny<TaskExecutionRequest>()))
            .ReturnsAsync(executionResults[0])
            .ReturnsAsync(executionResults[1]);

        // Act
        var results = await _orchestrator.ExecuteTaskBreakdownAsync(tasks, strategy);

        // Assert
        Assert.Equal(2, results.Count);
        Assert.All(results, r => Assert.True(r.Success));
    }

    [Fact]
    public async Task AnalyzeCommandAsync_ValidCommand_ReturnsAnalysis()
    {
        // Arrange
        var command = "test command";
        var context = new Dictionary<string, object> { { "key", "value" } };

        var expectedAnalysis = new TaskAnalysisResult
        {
            OriginalCommand = command,
            Intent = TaskIntent.Create,
            Complexity = TaskComplexity.Simple
        };

        _mockTaskAnalyzer.Setup(x => x.AnalyzeTaskAsync(command, context))
            .ReturnsAsync(expectedAnalysis);

        // Act
        var result = await _orchestrator.AnalyzeCommandAsync(command, context);

        // Assert
        Assert.Equal(expectedAnalysis, result);
        _mockTaskAnalyzer.Verify(x => x.AnalyzeTaskAsync(command, context), Times.Once);
    }

    [Fact]
    public async Task GetAvailableCommandsAsync_IncludesProcessorsAndPlugins_ReturnsAllCapabilities()
    {
        // Arrange
        var processorCapabilities = new List<CommandCapabilities>
        {
            new() { SupportedCommands = new List<string> { "todo", "create" }, Description = "Todo Processor" }
        };

        var mockPlugin = new Mock<IPlugin>();
        mockPlugin.Setup(x => x.Name).Returns("Test Plugin");
        mockPlugin.Setup(x => x.GetCapabilitiesAsync()).ReturnsAsync(new PluginCapabilities
        {
            SupportedCommands = new List<string> { "plugin-command" }
        });

        var processors = new List<ICommandProcessor> { _mockProcessor.Object };
        var plugins = new List<IPlugin> { mockPlugin.Object };

        _mockProcessor.Setup(x => x.Capabilities).Returns(processorCapabilities[0]);
        _mockProcessorFactory.Setup(x => x.GetAllProcessors()).Returns(processors);
        _mockPluginManager.Setup(x => x.GetLoadedPluginsAsync()).ReturnsAsync(plugins);

        // Act
        var result = await _orchestrator.GetAvailableCommandsAsync();

        // Assert
        Assert.Equal(2, result.Count); // 1 processor + 1 plugin
        Assert.Contains(result, c => c.Description == "Todo Processor");
        Assert.Contains(result, c => c.Description.Contains("Test Plugin"));
    }

    [Fact]
    public async Task ProcessWithTaskAnalysisAsync_CreatesCorrectRequest()
    {
        // Arrange
        var request = new CommandRequest
        {
            Command = "test command",
            Source = "test"
        };

        var analysis = new TaskAnalysisResult
        {
            OriginalCommand = request.Command,
            Intent = TaskIntent.Create,
            Complexity = TaskComplexity.Simple,
            SuggestedStrategy = new ExecutionStrategy
            {
                RequiresHumanApproval = false,
                PreExecutionChecks = new List<string>()
            },
            Subtasks = new List<TaskBreakdown>
            {
                new() { Id = "1", Description = "simple task" }
            }
        };

        var executionResults = new List<TaskExecutionResult>
        {
            new() { TaskId = "1", Success = true, Message = "Task completed" }
        };

        _mockTaskAnalyzer.Setup(x => x.AnalyzeTaskAsync(request.Command, request.Context))
            .ReturnsAsync(analysis);

        _mockExecutionEngine.Setup(x => x.ExecuteTaskAsync(It.IsAny<TaskExecutionRequest>()))
            .ReturnsAsync(executionResults[0]);

        // Act
        var result = await _orchestrator.ProcessWithTaskAnalysisAsync(request);

        // Assert
        Assert.True(result.Success);
        Assert.Equal("enhanced-orchestrator", result.ProcessorUsed);
        Assert.NotNull(result.Data);
    }
}