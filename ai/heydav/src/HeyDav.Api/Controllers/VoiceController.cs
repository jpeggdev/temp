using Microsoft.AspNetCore.Mvc;
using HeyDav.Infrastructure.Services;

namespace HeyDav.Api.Controllers;

[ApiController]
[Route("api/[controller]")]
public class VoiceController : ControllerBase
{
    private readonly IVoiceCommandService _voiceCommandService;
    private readonly ILogger<VoiceController> _logger;

    public VoiceController(IVoiceCommandService voiceCommandService, ILogger<VoiceController> logger)
    {
        _voiceCommandService = voiceCommandService;
        _logger = logger;
    }

    [HttpPost("start")]
    public async Task<ActionResult> StartVoiceCommands()
    {
        try
        {
            if (_voiceCommandService.IsRunning)
            {
                return Ok(new { Message = "Voice command service is already running", IsRunning = true });
            }

            await _voiceCommandService.StartAsync();
            return Ok(new { Message = "Voice command service started", IsRunning = true });
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error starting voice command service");
            return StatusCode(500, "Failed to start voice command service");
        }
    }

    [HttpPost("stop")]
    public async Task<ActionResult> StopVoiceCommands()
    {
        try
        {
            if (!_voiceCommandService.IsRunning)
            {
                return Ok(new { Message = "Voice command service is not running", IsRunning = false });
            }

            await _voiceCommandService.StopAsync();
            return Ok(new { Message = "Voice command service stopped", IsRunning = false });
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error stopping voice command service");
            return StatusCode(500, "Failed to stop voice command service");
        }
    }

    [HttpGet("status")]
    public ActionResult GetVoiceStatus()
    {
        try
        {
            return Ok(new 
            { 
                IsRunning = _voiceCommandService.IsRunning,
                Status = _voiceCommandService.IsRunning ? "Active" : "Inactive"
            });
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error getting voice command service status");
            return StatusCode(500, "Failed to get voice command service status");
        }
    }
}