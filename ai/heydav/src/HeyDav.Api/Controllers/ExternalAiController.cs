using Microsoft.AspNetCore.Mvc;
using HeyDav.Infrastructure.Services;

namespace HeyDav.Api.Controllers;

[ApiController]
[Route("api/external-ai")]
public class ExternalAiController : ControllerBase
{
    private readonly IExternalAiService _externalAiService;
    private readonly ILogger<ExternalAiController> _logger;

    public ExternalAiController(IExternalAiService externalAiService, ILogger<ExternalAiController> logger)
    {
        _externalAiService = externalAiService;
        _logger = logger;
    }

    [HttpGet("providers")]
    public async Task<ActionResult<List<ExternalAiProvider>>> GetAvailableProviders()
    {
        try
        {
            var providers = await _externalAiService.GetAvailableProvidersAsync();
            return Ok(providers);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error getting available AI providers");
            return StatusCode(500, "Failed to get available AI providers");
        }
    }

    [HttpGet("providers/{provider}/capabilities")]
    public async Task<ActionResult<ExternalAiCapabilities>> GetProviderCapabilities(ExternalAiProvider provider)
    {
        try
        {
            var capabilities = await _externalAiService.GetProviderCapabilitiesAsync(provider);
            return Ok(capabilities);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error getting capabilities for provider {Provider}", provider);
            return StatusCode(500, $"Failed to get capabilities for provider {provider}");
        }
    }

    [HttpGet("providers/{provider}/status")]
    public async Task<ActionResult<ProviderStatus>> GetProviderStatus(ExternalAiProvider provider)
    {
        try
        {
            var isAvailable = await _externalAiService.IsProviderAvailableAsync(provider);
            var capabilities = await _externalAiService.GetProviderCapabilitiesAsync(provider);
            
            return Ok(new ProviderStatus
            {
                Provider = provider,
                IsAvailable = isAvailable,
                Version = capabilities.Version,
                LastChecked = DateTime.UtcNow
            });
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error getting status for provider {Provider}", provider);
            return StatusCode(500, $"Failed to get status for provider {provider}");
        }
    }

    [HttpPost("execute")]
    public async Task<ActionResult<ExternalAiResponse>> ExecuteCommand([FromBody] ExecuteExternalAiRequest request)
    {
        try
        {
            _logger.LogInformation("Executing external AI command with {Provider}: {Command}", 
                request.Provider, request.Command);

            var response = await _externalAiService.ExecuteCommandAsync(
                request.Command, 
                request.Provider, 
                request.Context);

            if (response.Success)
            {
                return Ok(response);
            }
            else
            {
                return BadRequest(response);
            }
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error executing external AI command with {Provider}: {Command}", 
                request.Provider, request.Command);
            
            return StatusCode(500, new ExternalAiResponse
            {
                Success = false,
                Error = "An error occurred while executing the command",
                ExecutionTime = TimeSpan.Zero
            });
        }
    }

    [HttpPost("batch-execute")]
    public async Task<ActionResult<List<ExternalAiResponse>>> ExecuteBatchCommands([FromBody] BatchExecuteExternalAiRequest request)
    {
        try
        {
            var responses = new List<ExternalAiResponse>();
            
            foreach (var command in request.Commands)
            {
                var response = await _externalAiService.ExecuteCommandAsync(
                    command.Command,
                    command.Provider,
                    command.Context);
                
                responses.Add(response);
                
                // Add a small delay between commands to avoid overwhelming the external service
                if (request.DelayBetweenCommands > 0)
                {
                    await Task.Delay(TimeSpan.FromMilliseconds(request.DelayBetweenCommands));
                }
            }

            return Ok(responses);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error executing batch external AI commands");
            return StatusCode(500, "Failed to execute batch commands");
        }
    }
}

public class ExecuteExternalAiRequest
{
    public string Command { get; set; } = string.Empty;
    public ExternalAiProvider Provider { get; set; }
    public Dictionary<string, object>? Context { get; set; }
}

public class BatchExecuteExternalAiRequest
{
    public List<ExecuteExternalAiRequest> Commands { get; set; } = new();
    public int DelayBetweenCommands { get; set; } = 1000; // milliseconds
}

public class ProviderStatus
{
    public ExternalAiProvider Provider { get; set; }
    public bool IsAvailable { get; set; }
    public string Version { get; set; } = string.Empty;
    public DateTime LastChecked { get; set; }
}