using Microsoft.AspNetCore.Mvc;
using HeyDav.Application.CommandProcessing;

namespace HeyDav.Api.Controllers;

[ApiController]
[Route("api/[controller]")]
public class CommandController : ControllerBase
{
    private readonly ICommandOrchestrator _commandOrchestrator;
    private readonly ILogger<CommandController> _logger;

    public CommandController(ICommandOrchestrator commandOrchestrator, ILogger<CommandController> logger)
    {
        _commandOrchestrator = commandOrchestrator;
        _logger = logger;
    }

    [HttpPost("execute")]
    public async Task<ActionResult<CommandResult>> ExecuteCommand([FromBody] ExecuteCommandRequest request)
    {
        try
        {
            _logger.LogInformation("Received command execution request from {Source}: {Command}", 
                request.Source, request.Command);

            var result = await _commandOrchestrator.ProcessCommandAsync(
                request.Command, 
                request.Source ?? "api", 
                request.Context);

            return Ok(result);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error executing command: {Command}", request.Command);
            return StatusCode(500, new CommandResult
            {
                Success = false,
                Message = "An error occurred while processing your command",
                ProcessingTime = TimeSpan.Zero
            });
        }
    }

    [HttpPost("execute/{processorType}")]
    public async Task<ActionResult<CommandResult>> ExecuteCommandWithProcessor(
        string processorType, 
        [FromBody] ExecuteCommandRequest request)
    {
        try
        {
            _logger.LogInformation("Received command execution request with processor {ProcessorType} from {Source}: {Command}", 
                processorType, request.Source, request.Command);

            var result = await _commandOrchestrator.ProcessWithSpecificProcessorAsync(
                request.Command, 
                processorType, 
                request.Source ?? "api");

            return Ok(result);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error executing command with processor {ProcessorType}: {Command}", 
                processorType, request.Command);
            return StatusCode(500, new CommandResult
            {
                Success = false,
                Message = "An error occurred while processing your command",
                ProcessingTime = TimeSpan.Zero
            });
        }
    }

    [HttpGet("capabilities")]
    public async Task<ActionResult<List<CommandCapabilities>>> GetCapabilities()
    {
        try
        {
            var capabilities = await _commandOrchestrator.GetAvailableCommandsAsync();
            return Ok(capabilities);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error retrieving command capabilities");
            return StatusCode(500, new List<CommandCapabilities>());
        }
    }
}

public class ExecuteCommandRequest
{
    public string Command { get; set; } = string.Empty;
    public string? Source { get; set; }
    public Dictionary<string, object>? Context { get; set; }
}